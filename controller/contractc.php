<?php
class ContractC {
    private $db;

    public function __construct() {
        try {
            // Connect to the database
            $this->db = new PDO("mysql:host=127.0.0.1;dbname=innoconnect", "root", "");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function afficherContracts() {
        // Select all contracts without ordering by created_at
        $sql = "SELECT * FROM contracts"; // Removed ORDER BY clause
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterContract($contract) {
        // Insert a new contract
        $sql = "INSERT INTO contracts (user_id, nom, description, type) VALUES (:user_id, :nom, :description, :type)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $contract->getId(), // Corrected method name
            ':nom' => $contract->getNom(),
            ':description' => $contract->getDescription(),
            ':type' => $contract->getType()
        ]);
    }

    public function supprimerContract($id) {
        // Delete a contract by ID
        $sql = "DELETE FROM contracts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
?>