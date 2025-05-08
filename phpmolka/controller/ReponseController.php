<?php
require_once(__DIR__ . '/../model/Reponse.php');
require_once(__DIR__ . '/../config.php');

class ReponseController {
    private $connexion;

    public function __construct() {
        try {
            $this->connexion = config::getConnexion();
        } catch (Exception $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw $e;
        }
    }

    public function addReponse($id_reclamation, $id_user, $description, $date) {
        try {
            $this->connexion->beginTransaction();

            error_log("Tentative d'ajout d'une réponse - ID Réclamation: $id_reclamation, Description: $description");

            // Vérifier si la réclamation existe
            $checkQuery = "SELECT id_reclamation FROM reclamation WHERE id_reclamation = :id_reclamation";
            $checkStmt = $this->connexion->prepare($checkQuery);
            $checkStmt->execute([':id_reclamation' => $id_reclamation]);
            if (!$checkStmt->fetch()) {
                error_log("La réclamation $id_reclamation n'existe pas");
                throw new Exception("La réclamation spécifiée n'existe pas");
            }

            // Ajouter la réponse
            $query = "INSERT INTO reponse (id_reclamation_reponse, id_user_reponse, description_reponse, date_reponse) 
                     VALUES (:id_reclamation, :id_user, :description, :date)";
            $stmt = $this->connexion->prepare($query);
            $result = $stmt->execute([
                ':id_reclamation' => $id_reclamation,
                ':id_user' => $id_user,
                ':description' => $description,
                ':date' => $date
            ]);
            
            if (!$result) {
                error_log("Échec de l'insertion de la réponse");
                $this->connexion->rollBack();
                return false;
            }

            $newResponseId = $this->connexion->lastInsertId();
            error_log("Réponse ajoutée avec succès, ID: $newResponseId");

            // Mettre à jour le statut de la réclamation en "Resolved"
            $updateQuery = "UPDATE reclamation SET etat_reclamation = 'Resolved' WHERE id_reclamation = :id_reclamation";
            $updateStmt = $this->connexion->prepare($updateQuery);
            $updateResult = $updateStmt->execute([':id_reclamation' => $id_reclamation]);

            if (!$updateResult) {
                error_log("Échec de la mise à jour du statut de la réclamation");
                $this->connexion->rollBack();
                return false;
            }

            $this->connexion->commit();
            return $newResponseId;

        } catch (PDOException $e) {
            $this->connexion->rollBack();
            error_log("Erreur dans addReponse: " . $e->getMessage());
            throw $e;
        }
    }

    public function getReponsesByReclamation($id_reclamation) {
        try {
            error_log("Récupération des réponses pour la réclamation ID: $id_reclamation");
            
            $query = "SELECT r.id_reponse, r.id_reclamation_reponse, r.id_user_reponse, r.description_reponse, r.date_reponse,
                            rec.description_reclamation, rec.etat_reclamation 
                     FROM reponse r 
                     LEFT JOIN reclamation rec ON r.id_reclamation_reponse = rec.id_reclamation 
                     WHERE r.id_reclamation_reponse = :id_reclamation 
                     ORDER BY r.date_reponse DESC";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([':id_reclamation' => $id_reclamation]);
            $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Nombre de réponses trouvées: " . count($responses));
            return $responses;
            
        } catch (PDOException $e) {
            error_log("Erreur dans getReponsesByReclamation: " . $e->getMessage());
            throw $e;
        }
    }

    public function getReponseById($id_reponse) {
        try {
            error_log("Récupération de la réponse ID: $id_reponse");
            
            $query = "SELECT r.id_reponse, r.id_reclamation_reponse, r.id_user_reponse, r.description_reponse, r.date_reponse,
                            rec.description_reclamation, rec.etat_reclamation 
                     FROM reponse r 
                     LEFT JOIN reclamation rec ON r.id_reclamation_reponse = rec.id_reclamation 
                     WHERE r.id_reponse = :id_reponse";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([':id_reponse' => $id_reponse]);
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$response) {
                error_log("Réponse non trouvée");
            } else {
                error_log("Réponse trouvée");
            }
            
            return $response;
            
        } catch (PDOException $e) {
            error_log("Erreur dans getReponseById: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateReponse($id_reponse, $description) {
        try {
            error_log("Tentative de mise à jour de la réponse ID: $id_reponse");
            
            // Vérifier si la réponse existe
            $checkQuery = "SELECT id_reponse FROM reponse WHERE id_reponse = :id_reponse";
            $checkStmt = $this->connexion->prepare($checkQuery);
            $checkStmt->execute([':id_reponse' => $id_reponse]);
            if (!$checkStmt->fetch()) {
                error_log("La réponse $id_reponse n'existe pas");
                throw new Exception("La réponse spécifiée n'existe pas");
            }

            $query = "UPDATE reponse SET description_reponse = :description 
                     WHERE id_reponse = :id_reponse";
            $stmt = $this->connexion->prepare($query);
            $result = $stmt->execute([
                ':description' => $description,
                ':id_reponse' => $id_reponse
            ]);

            if (!$result) {
                error_log("Échec de la mise à jour de la réponse");
                return false;
            }

            error_log("Réponse mise à jour avec succès");
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur dans updateReponse: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteReponse($id_reponse) {
        try {
            error_log("Tentative de suppression de la réponse ID: $id_reponse");
            
            $this->connexion->beginTransaction();

            // Récupérer l'ID de la réclamation avant de supprimer la réponse
            $query = "SELECT id_reclamation_reponse FROM reponse WHERE id_reponse = :id_reponse";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([':id_reponse' => $id_reponse]);
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$response) {
                error_log("La réponse $id_reponse n'existe pas");
                $this->connexion->rollBack();
                throw new Exception("La réponse spécifiée n'existe pas");
            }

            $id_reclamation = $response['id_reclamation_reponse'];
            error_log("ID de la réclamation associée: $id_reclamation");

            // Supprimer la réponse
            $deleteQuery = "DELETE FROM reponse WHERE id_reponse = :id_reponse";
            $deleteStmt = $this->connexion->prepare($deleteQuery);
            $result = $deleteStmt->execute([':id_reponse' => (int)$id_reponse]);
            
            if (!$result) {
                error_log("Échec de la suppression de la réponse");
                $this->connexion->rollBack();
                return false;
            }

            // Vérifier s'il reste des réponses pour cette réclamation
            $checkQuery = "SELECT COUNT(*) as count FROM reponse WHERE id_reclamation_reponse = :id_reclamation";
            $checkStmt = $this->connexion->prepare($checkQuery);
            $checkStmt->execute([':id_reclamation' => $id_reclamation]);
            $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];

            error_log("Nombre de réponses restantes pour la réclamation: $count");

            // S'il n'y a plus de réponses, mettre le statut à "Pending"
            if ($count == 0) {
                $updateQuery = "UPDATE reclamation SET etat_reclamation = 'Pending' WHERE id_reclamation = :id_reclamation";
                $updateStmt = $this->connexion->prepare($updateQuery);
                $updateResult = $updateStmt->execute([':id_reclamation' => $id_reclamation]);
                
                if (!$updateResult) {
                    error_log("Échec de la mise à jour du statut de la réclamation");
                    $this->connexion->rollBack();
                    return false;
                }
                error_log("Statut de la réclamation mis à jour à 'Pending'");
            }

            $this->connexion->commit();
            error_log("Suppression de la réponse réussie");
            return true;

        } catch (PDOException $e) {
            $this->connexion->rollBack();
            error_log("Erreur dans deleteReponse: " . $e->getMessage());
            throw $e;
        }
    }
}
?>