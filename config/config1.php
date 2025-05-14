<?php
class Config {
    private static $conn = null;

    public static function getConnexion() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=localhost;dbname=innoconnect;charset=utf8",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Échec de la connexion : " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}

function getUserType($userId, $conn) {
    try {
        $stmt = $conn->prepare("SELECT type FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['type'] : null;
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du type d'utilisateur : " . $e->getMessage());
    }
}

function redirectToDashboard() {
    header("Location: /espace_comm/espace communotaire/index.html");
    exit();
}
?>