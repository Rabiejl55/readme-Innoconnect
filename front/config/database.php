<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mohameddb');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Database connection class
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, 
                DB_USER, 
                DB_PASSWORD
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            throw new Exception("Database Connection Error: " . $e->getMessage());
        }
    }
    
    // Singleton pattern to get database instance
    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Get the PDO connection
    public function getConnection() {
        return $this->conn;
    }
}

// Helper function to get database connection
function getDbConnection() {
    try {
        return Database::getInstance()->getConnection();
    } catch(Exception $e) {
        // Log error
        error_log($e->getMessage());
        
        // Return false on failure
        return false;
    }
}
?> 