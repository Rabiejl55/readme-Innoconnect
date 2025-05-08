<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/BadWordsController.php');
require_once(__DIR__ . '/../model/Reclamation.php');

class ReclamationController {
    private $db;
    private $badWordsController;

    public function __construct() {
        try {
            $this->db = config::getConnexion();
            $this->badWordsController = new BadWordsController();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addReclamation($reclamation) {
        // Validate for bad words first
        $badWordsCheck = $this->badWordsController->containsBadWords($reclamation->getDescription());
        
        if ($badWordsCheck['hasBadWords']) {
            $_SESSION['errors'] = ['Your claim contains inappropriate language. Please revise your text.'];
            return false;
        }

        // Validate description length
        if (strlen($reclamation->getDescription()) > 25) {
            $_SESSION['errors'] = ['Description cannot exceed 25 characters.'];
            return false;
        }

        if (empty($reclamation->getDescription())) {
            $_SESSION['errors'] = ['Description cannot be empty.'];
            return false;
        }

        $query = "INSERT INTO reclamation (date_reclamation, description_reclamation, etat_reclamation) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            $reclamation->getDate(),
            $reclamation->getDescription(),
            $reclamation->getEtat()
        ]);
    }

    public function updateReclamation($id, $date, $description, $etat) {
        // Validate for bad words first
        $badWordsCheck = $this->badWordsController->containsBadWords($description);
        
        if ($badWordsCheck['hasBadWords']) {
            $_SESSION['errors'] = ['Your claim contains inappropriate language. Please revise your text.'];
            return false;
        }

        // Validate description length
        if (strlen($description) > 25) {
            $_SESSION['errors'] = ['Description cannot exceed 25 characters.'];
            return false;
        }

        if (empty($description)) {
            $_SESSION['errors'] = ['Description cannot be empty.'];
            return false;
        }

        $query = "UPDATE reclamation SET date_reclamation = ?, description_reclamation = ?, etat_reclamation = ? WHERE id_reclamation = ?";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([$date, $description, $etat, $id]);
    }

    public function deleteReclamation($id) {
        $query = "DELETE FROM reclamation WHERE id_reclamation = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getReclamations() {
        $query = "SELECT * FROM reclamation ORDER BY date_reclamation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getReclamationById($id) {
        $query = "SELECT * FROM reclamation WHERE id_reclamation = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>