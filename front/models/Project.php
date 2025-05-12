<?php
require_once 'config/database.php';

class Project {
    private $db;
    
    public function __construct() {
        // Use the database connection from the config
        try {
            $this->db = getDbConnection();
            
            if (!$this->db) {
                throw new Exception('Unable to get database connection');
            }
        } catch(Exception $e) {
            // Throw exception for controller to handle
            throw new Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }
    
    // Get all projects
    public function getProjects() {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.nom as nom_categorie, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom 
                                       FROM projet p 
                                       LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
                                       LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Throw a proper exception instead of echoing HTML
            throw new Exception('Erreur lors de la récupération des projets: ' . $e->getMessage());
        }
    }
    
    // Get a specific project by ID
    public function getProjectById($id) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.nom as nom_categorie, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom 
                                       FROM projet p 
                                       LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
                                       LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                                       WHERE p.id_projet = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur lors de la récupération du projet</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    // Create a new project
    public function createProject($titre, $description, $id_categorie, $id_utilisateur, $montant_demande) {
        try {
            $date_soumission = date('Y-m-d');
            $statut = 'Soumis';
            
            $stmt = $this->db->prepare("INSERT INTO projet (titre, description, id_categorie, id_utilisateur, date_soumission, statut, montant_demande) 
                                      VALUES (:titre, :description, :id_categorie, :id_utilisateur, :date_soumission, :statut, :montant_demande)");
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':date_soumission', $date_soumission);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':montant_demande', $montant_demande);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur lors de la création du projet</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    // Update an existing project
    public function updateProject($id, $titre, $description, $id_categorie, $statut, $montant_demande) {
        try {
            $stmt = $this->db->prepare("UPDATE projet SET titre = :titre, description = :description, 
                                       id_categorie = :id_categorie, statut = :statut, montant_demande = :montant_demande
                                       WHERE id_projet = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':montant_demande', $montant_demande);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur lors de la mise à jour du projet</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    // Delete a project
    public function deleteProject($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM projet WHERE id_projet = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur lors de la suppression du projet</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    // Get all categories for dropdown
    public function getCategories() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categorie");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur lors de la récupération des catégories</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return [];
        }
    }
}
?> 