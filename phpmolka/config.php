<?php

class config
{
    private static $pdo = null;

    public static function getConnexion() {
        // Database credentials
        $servername = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'molka';

        // Use a singleton pattern to ensure only one database connection is used
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                // Uncomment below for debugging purposes
                // echo "Connected successfully";
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

?>
