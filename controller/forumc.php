<?php
require_once '../config/config.php';
require_once '../Model/forum.php';

class ForumC {
    public function afficherForums() {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT f.id, f.titre, f.category, f.date_creation, m.message 
                      FROM forums f 
                      LEFT JOIN messages m ON f.id = m.forum_id 
                      WHERE m.id = (SELECT MIN(id) FROM messages WHERE forum_id = f.id)";
            $stmt = $pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching forums: " . $e->getMessage());
            return [];
        }
    }

    public function ajouterForum($forum) {
        try {
            $pdo = config::getConnexion();
            // Insert forum
            $query = "INSERT INTO forums (titre, category, user_id, date_creation) 
                      VALUES (:titre, :category, :user_id, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'titre' => $forum->getTitre(),
                'category' => $forum->getCategory(),
                'user_id' => $forum->getUserId()
            ]);
            $forum_id = $pdo->lastInsertId();

            // Insert initial message
            $query = "INSERT INTO messages (forum_id, user_id, message, date_creation) 
                      VALUES (:forum_id, :user_id, :message, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'forum_id' => $forum_id,
                'user_id' => $forum->getUserId(),
                'message' => $forum->getMessage()
            ]);
        } catch (PDOException $e) {
            error_log("Error adding forum: " . $e->getMessage());
            throw $e;
        }
    }

    public function modifierForum($forum) {
        try {
            $pdo = config::getConnexion();
            // Update forum
            $query = "UPDATE forums SET titre = :titre, category = :category WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'titre' => $forum->getTitre(),
                'category' => $forum->getCategory(),
                'id' => $forum->getId()
            ]);

            // Update first message
            $query = "UPDATE messages SET message = :message 
                      WHERE forum_id = :forum_id 
                      AND id = (SELECT MIN(id) FROM messages WHERE forum_id = :forum_id)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'message' => $forum->getMessage(),
                'forum_id' => $forum->getId()
            ]);
        } catch (PDOException $e) {
            error_log("Error updating forum: " . $e->getMessage());
            throw $e;
        }
    }

    public function supprimerForum($id) {
        try {
            $pdo = config::getConnexion();
            // Delete messages first
            $query = "DELETE FROM messages WHERE forum_id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);

            // Delete forum
            $query = "DELETE FROM forums WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting forum: " . $e->getMessage());
            throw $e;
        }
    }

    public function ajouterMessage($forum_id, $user_id, $message) {
        try {
            $pdo = config::getConnexion();
            $query = "INSERT INTO messages (forum_id, user_id, message, date_creation) 
                      VALUES (:forum_id, :user_id, :message, NOW())";
            $stmt = $pdo->prepare($query);
            error_log("Adding message: forum_id=$forum_id, user_id=$user_id, message=$message");
            $stmt->execute([
                'forum_id' => $forum_id,
                'user_id' => $user_id,
                'message' => $message
            ]);
        } catch (PDOException $e) {
            error_log("Error adding message: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMessagesByForumId($forum_id) {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT user_id, message, date_creation 
                      FROM messages 
                      WHERE forum_id = :forum_id 
                      ORDER BY date_creation ASC";
            $stmt = $pdo->prepare($query);
            error_log("Fetching messages for forum_id: $forum_id");
            $stmt->execute(['forum_id' => $forum_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching messages: " . $e->getMessage());
            return [];
        }
    }
}
?>