<?php
require_once 'config/Database.php';

class Category {
    // Database connection and table name
    private $conn;
    private $table_name = "categorie";

    // Object properties
    public $id_categorie;
    public $nom;
    public $description;

    // Constructor with DB connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all categories
    public function getAllCategories() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single category by ID
    public function getCategoryById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new category
    public function createCategory($nom, $description) {
        $query = "INSERT INTO " . $this->table_name . " (nom, description) VALUES (:nom, :description)";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $nom = htmlspecialchars(strip_tags($nom));
        $description = htmlspecialchars(strip_tags($description));
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        
        try {
            $this->conn->beginTransaction();
            $stmt->execute();
            $id = $this->conn->lastInsertId();
            $this->conn->commit();
            return $id;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Update an existing category
    public function updateCategory($id, $nom, $description) {
        $query = "UPDATE " . $this->table_name . " 
                SET nom = :nom, description = :description 
                WHERE id_categorie = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $nom = htmlspecialchars(strip_tags($nom));
        $description = htmlspecialchars(strip_tags($description));
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete a category
    public function deleteCategory($id) {
        // First check if the category is being used in any projects
        if ($this->isCategoryUsed($id)) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Check if a category is used in any projects
    public function isCategoryUsed($id) {
        $query = "SELECT COUNT(*) as count FROM projet WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['count'] > 0);
    }

    // Count total categories
    public function countCategories() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
} 