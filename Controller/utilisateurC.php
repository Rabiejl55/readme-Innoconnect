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

    public function updateUser($userId, $nom, $prenom, $email, $type, $photo_profil) {
        try {
            $stmt = $this->conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, type = ?, photo_profil = ? WHERE id_utilisateur = ?");
            $stmt->bindValue(1, $nom, PDO::PARAM_STR);
            $stmt->bindValue(2, $prenom, PDO::PARAM_STR);
            $stmt->bindValue(3, $email, PDO::PARAM_STR);
            $stmt->bindValue(4, $type, PDO::PARAM_STR);
            $stmt->bindValue(5, $userId, PDO::PARAM_INT);
            $stmt->bindValue(6, $photo_profil, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    public function afficherUser($sortColumn = 'id_utilisateur', $sortOrder = 'ASC') {
        $conn = config::getConnexion();
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = ['id_utilisateur', 'nom', 'prenom', 'email', 'type', 'date_inscription'];
        $sortColumn = in_array($sortColumn, $allowedColumns) ? $sortColumn : 'id_utilisateur';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM utilisateur ORDER BY $sortColumn $sortOrder";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUsers($searchTerm, $sortColumn = 'id_utilisateur', $sortOrder = 'ASC') {
        $conn = config::getConnexion();
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = ['id_utilisateur', 'nom', 'prenom', 'email', 'type', 'date_inscription'];
        $sortColumn = in_array($sortColumn, $allowedColumns) ? $sortColumn : 'id_utilisateur';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM utilisateur WHERE nom LIKE :search OR prenom LIKE :search OR email LIKE :search ORDER BY $sortColumn $sortOrder";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['search' => "%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function ajouterUser($nom, $prenom, $email, $mot_de_passe, $type, $date_inscription, $photo_profil) {
        try {
            $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, type, date_inscription,photo_profil) 
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :type, :date_inscription, :photo_profil)";
            $db = config::getConnexion();
            $stmt = $db->prepare($sql);
            
            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':mot_de_passe', $mot_de_passe, PDO::PARAM_STR);
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
            $stmt->bindValue(':date_inscription', $date_inscription, PDO::PARAM_STR);
            $stmt->bindValue(':photo_profil', $photo_profil, PDO::PARAM_STR);
            
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error adding user: ' . $e->getMessage());
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
            $db = config::getConnexion();
            
            // Get the last 30 days of registration data
            $sql = "SELECT DATE(date_inscription) AS reg_date, COUNT(*) AS count 
                    FROM utilisateur 
                    WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(date_inscription)
                    ORDER BY reg_date ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Prepare data for the chart
            $labels = [];
            $growthData = [];
            $currentDate = new DateTime();
            $startDate = (clone $currentDate)->modify('-30 days');
    
            // Initialize data for each day in the last 30 days
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($startDate, $interval, $currentDate);
    
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[$row['reg_date']] = (int)$row['count'];
            }
    
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $growthData[] = isset($dataMap[$dateStr]) ? $dataMap[$dateStr] : 0;
            }
    
            return [
                'labels' => $labels,
                'growthData' => $growthData
            ];
        } catch (Exception $e) {
            // Log the error and return empty data
            error_log('Error in getGrowthData: ' . $e->getMessage());
            return ['labels' => [], 'growthData' => []];
        }
    }
}
?>