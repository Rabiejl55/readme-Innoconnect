<?php
/**
 * Modèle pour la gestion des catégories
 */
class Category {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Database connection using PDO
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=mohameddb', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            die();
        }
    }
    
    /**
     * Récupère toutes les catégories
     * 
     * @return array Liste des catégories
     */
    public function getAllCategories() {
        $query = "SELECT * FROM categorie ORDER BY nom ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère une catégorie par son ID
     * 
     * @param int $id ID de la catégorie
     * @return array|false Données de la catégorie ou false si non trouvée
     */
    public function getCategoryById($id) {
        $query = "SELECT * FROM categorie WHERE id_categorie = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée une nouvelle catégorie
     * 
     * @param string $nom Nom de la catégorie
     * @param string $description Description de la catégorie
     * @return boolean Succès de l'opération
     */
    public function createCategory($nom, $description) {
        $query = "INSERT INTO categorie (nom, description) VALUES (:nom, :description)";
        $stmt = $this->db->prepare($query);
        
        // Nettoyage et sécurisation des données
        $nom = htmlspecialchars(strip_tags($nom));
        $description = htmlspecialchars(strip_tags($description));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":description", $description);
        
        // Exécution de la requête
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Met à jour une catégorie existante
     * 
     * @param int $id ID de la catégorie
     * @param string $nom Nouveau nom de la catégorie
     * @param string $description Nouvelle description de la catégorie
     * @return boolean Succès de l'opération
     */
    public function updateCategory($id, $nom, $description) {
        $query = "UPDATE categorie SET nom = :nom, description = :description WHERE id_categorie = :id";
        $stmt = $this->db->prepare($query);
        
        // Nettoyage et sécurisation des données
        $nom = htmlspecialchars(strip_tags($nom));
        $description = htmlspecialchars(strip_tags($description));
        $id = (int)$id;
        
        // Liaison des paramètres
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        // Exécution de la requête
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprime une catégorie
     * 
     * @param int $id ID de la catégorie
     * @return boolean Succès de l'opération
     */
    public function deleteCategory($id) {
        $query = "DELETE FROM categorie WHERE id_categorie = :id";
        $stmt = $this->db->prepare($query);
        
        // Liaison des paramètres
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        // Exécution de la requête
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Vérifie si une catégorie est utilisée par des projets
     * 
     * @param int $id ID de la catégorie
     * @return boolean True si la catégorie est utilisée, sinon False
     */
    public function isCategoryUsed($id) {
        $query = "SELECT COUNT(*) as count FROM projet WHERE id_categorie = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['count'] > 0);
    }
    
    /**
     * Compte le nombre total de catégories
     * 
     * @return int Nombre de catégories
     */
    public function countCategories() {
        $sql = "SELECT COUNT(*) FROM categorie";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
} 