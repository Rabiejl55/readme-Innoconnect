<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';

class userC {
    private $conn;

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    public function getUserType($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT type FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['type'] : null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du type d'utilisateur : " . $e->getMessage());
            return null;
        }
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
            return null;
        }
    }

    public function updateUser($userId, $nom, $prenom, $email, $type) {
        try {
            $stmt = $this->conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, type = ? WHERE id_utilisateur = ?");
            $stmt->bindValue(1, $nom, PDO::PARAM_STR);
            $stmt->bindValue(2, $prenom, PDO::PARAM_STR);
            $stmt->bindValue(3, $email, PDO::PARAM_STR);
            $stmt->bindValue(4, $type, PDO::PARAM_STR);
            $stmt->bindValue(5, $userId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    public function afficherUser() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM utilisateur");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
            return [];
        }
    }

    public function emailExists($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
            $stmt->bindValue(1, $email, PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'email : " . $e->getMessage());
            return false;
        }
    }

    public function ajouterUser($nom, $prenom, $email, $mot_de_passe, $type) {
        try {
            if ($this->emailExists($email)) {
                throw new Exception("Cet email est déjà utilisé.");
            }
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $nom, PDO::PARAM_STR);
            $stmt->bindValue(2, $prenom, PDO::PARAM_STR);
            $stmt->bindValue(3, $email, PDO::PARAM_STR);
            $stmt->bindValue(4, $mot_de_passe_hache, PDO::PARAM_STR);
            $stmt->bindValue(5, $type, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage());
            throw $e;
        }
    }

    public function connexionUser($email, $mot_de_passe) {
        error_log("Tentative de connexion - Email: $email, Mot de passe: $mot_de_passe");

        try {
            $stmt = $this->conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
            if (!$stmt) {
                error_log("Erreur de préparation de la requête: " . $this->conn->errorInfo()[2]);
                return null;
            }

            $stmt->bindValue(1, $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                error_log("Utilisateur trouvé: " . print_r($user, true));
                if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    error_log("Mot de passe correct.");
                    return $user;
                } else {
                    error_log("Mot de passe incorrect.");
                }
            } else {
                error_log("Aucun utilisateur trouvé pour cet email.");
            }

            return null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la connexion de l'utilisateur : " . $e->getMessage());
            return null;
        }
    }

    public function deleteUser($userId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $success = $stmt->execute();
            if (!$success) {
                error_log("Erreur lors de la suppression de l'utilisateur.");
            }
            return $success;
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    public function getStats() {
        try {
            $stats = [
                'total' => $this->conn->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
                'investisseurs' => $this->conn->query("SELECT COUNT(*) FROM utilisateur WHERE type = 'investisseur'")->fetchColumn(),
                'innovateurs' => $this->conn->query("SELECT COUNT(*) FROM utilisateur WHERE type = 'innovateur'")->fetchColumn(),
                'recent' => $this->conn->query("SELECT COUNT(*) FROM utilisateur WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
                'actifs' => $this->conn->query("SELECT COUNT(*) FROM utilisateur WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
                'administrateurs' => $this->conn->query("SELECT COUNT(*) FROM utilisateur WHERE type = 'administrateur'")->fetchColumn(),
            ];
            return $stats;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
            return [
                'total' => 0,
                'investisseurs' => 0,
                'innovateurs' => 0,
                'recent' => 0,
                'actifs' => 0,
                'administrateurs' => 0,
            ];
        }
    }

    public function getChartData() {
        $stats = $this->getStats();
        return [
            'administrateurs' => $stats['administrateurs'],
            'investisseurs' => $stats['investisseurs'],
            'innovateurs' => $stats['innovateurs'],
        ];
    }

    public function getGrowthData() {
        try {
            $growthData = [];
            $labels = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE DATE(date_inscription) = ?");
                $stmt->bindValue(1, $date, PDO::PARAM_STR);
                $stmt->execute();
                $count = $stmt->fetchColumn();
                $growthData[] = $count;
                $labels[] = date('d M', strtotime($date));
            }
            return ['growthData' => $growthData, 'labels' => $labels];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des données de croissance : " . $e->getMessage());
            return ['growthData' => array_fill(0, 30, 0), 'labels' => array_fill(0, 30, '')];
        }
    }
}
?>