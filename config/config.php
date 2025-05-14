<?php

class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=innoconnect',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
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