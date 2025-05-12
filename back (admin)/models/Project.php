<?php
require_once 'config/Database.php';

class Project {
    // Database connection and table name
    private $conn;
    private $table_name = "projet";

    // Object properties
    public $id_projet;
    public $titre;
    public $description;
    public $id_categorie;
    public $id_utilisateur;
    public $date_soumission;
    public $statut;
    public $montant_demande;

    // Constructor with DB connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all projects
    public function getProjects() {
        $query = "SELECT p.*, c.nom as categorie_nom, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom 
                FROM " . $this->table_name . " p 
                JOIN categorie c ON p.id_categorie = c.id_categorie
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                ORDER BY p.date_soumission DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single project by ID
    public function getProjectById($id) {
        $query = "SELECT p.*, c.nom as categorie_nom, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom 
                FROM " . $this->table_name . " p 
                JOIN categorie c ON p.id_categorie = c.id_categorie
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                WHERE p.id_projet = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new project
    public function createProject($titre, $description, $id_categorie, $id_utilisateur, $montant_demande) {
        $query = "INSERT INTO " . $this->table_name . " 
                (titre, description, id_categorie, id_utilisateur, date_soumission, statut, montant_demande) 
                VALUES 
                (:titre, :description, :id_categorie, :id_utilisateur, :date_soumission, :statut, :montant_demande)";
        
        $date_soumission = date('Y-m-d');
        $statut = 'Soumis';
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $titre = htmlspecialchars(strip_tags($titre));
        $description = htmlspecialchars(strip_tags($description));
        
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':date_soumission', $date_soumission);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':montant_demande', $montant_demande);
        
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

    // Update an existing project
    public function updateProject($id, $titre, $description, $id_categorie, $statut, $montant_demande) {
        $query = "UPDATE " . $this->table_name . " 
                SET titre = :titre, description = :description, id_categorie = :id_categorie, 
                statut = :statut, montant_demande = :montant_demande
                WHERE id_projet = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $titre = htmlspecialchars(strip_tags($titre));
        $description = htmlspecialchars(strip_tags($description));
        $statut = htmlspecialchars(strip_tags($statut));
        
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':montant_demande', $montant_demande);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete a project
    public function deleteProject($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_projet = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Count total projects
    public function countProjects() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Get projects by status
    public function getProjectsByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE statut = :statut";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Get categories for dropdown
    public function getCategories() {
        $query = "SELECT * FROM categorie ORDER BY nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get project count per status for chart
    public function getProjectStatusCounts() {
        $query = "SELECT statut, COUNT(*) as total FROM " . $this->table_name . " GROUP BY statut";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get project count per category for chart
    public function getProjectCategoryCounts() {
        $query = "SELECT c.nom as category, COUNT(*) as total FROM " . $this->table_name . " p JOIN categorie c ON p.id_categorie = c.id_categorie GROUP BY c.nom ORDER BY total DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 