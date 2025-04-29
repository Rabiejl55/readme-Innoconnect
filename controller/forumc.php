<?php
require_once '../config/config.php';
require_once '../Model/forum.php';

class ForumC {
    public function afficherForums() {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT f.id, f.titre, f.category, f.user_id, f.date_creation, f.image, 
                             m.message, m.image AS message_image 
                      FROM forums f 
                      LEFT JOIN messages m ON f.id = m.forum_id 
                      AND m.id = (SELECT MIN(id) FROM messages WHERE forum_id = f.id)
                      ORDER BY COALESCE((SELECT MAX(date_creation) FROM messages WHERE forum_id = f.id), f.date_creation) DESC";
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
            $query = "INSERT INTO forums (titre, category, user_id, date_creation, image) 
                      VALUES (:titre, :category, :user_id, NOW(), :image)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'titre' => $forum->getTitre(),
                'category' => $forum->getCategory(),
                'user_id' => $forum->getUserId(),
                'image' => $forum->getImage()
            ]);
            $forum_id = $pdo->lastInsertId();
            $query = "INSERT INTO messages (forum_id, user_id, message, date_creation, image) 
                      VALUES (:forum_id, :user_id, :message, NOW(), :message_image)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'forum_id' => $forum_id,
                'user_id' => $forum->getUserId(),
                'message' => $forum->getMessage(),
                'message_image' => $forum->getMessageImage()
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
            $query = "UPDATE forums SET titre = :titre, category = :category, image = :image WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'titre' => $forum->getTitre(),
                'category' => $forum->getCategory(),
                'image' => $forum->getImage(),
                'id' => $forum->getId()
            ]);
            // Check if first message exists
            $query = "SELECT id FROM messages WHERE forum_id = :forum_id ORDER BY id ASC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['forum_id' => $forum->getId()]);
            $message_id = $stmt->fetchColumn();
            if ($message_id) {
                // Update existing first message
                $query = "UPDATE messages SET message = :message, image = :message_image 
                          WHERE id = :message_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'message' => $forum->getMessage(),
                    'message_image' => $forum->getMessageImage(),
                    'message_id' => $message_id
                ]);
            } else {
                // Insert new message if none exists
                $query = "INSERT INTO messages (forum_id, user_id, message, date_creation, image) 
                          VALUES (:forum_id, :user_id, :message, NOW(), :message_image)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'forum_id' => $forum->getId(),
                    'user_id' => $forum->getUserId(),
                    'message' => $forum->getMessage(),
                    'message_image' => $forum->getMessageImage()
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error updating forum ID {$forum->getId()}: " . $e->getMessage());
            throw $e;
        }
    }

    public function supprimerForum($id) {
        try {
            $pdo = config::getConnexion();
            // Delete forum image
            $query = "SELECT image FROM forums WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            if ($image = $stmt->fetchColumn()) {
                $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                if ($imagePath && file_exists($imagePath)) {
                    if (!unlink($imagePath)) {
                        error_log("Failed to delete forum image: $imagePath");
                    } else {
                        error_log("Successfully deleted forum image: $imagePath");
                    }
                } else {
                    error_log("Forum image not found: $image, resolved path: $imagePath");
                }
            }
            // Delete message images
            $query = "SELECT image FROM messages WHERE forum_id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            while ($image = $stmt->fetchColumn()) {
                if ($image) {
                    $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                    if ($imagePath && file_exists($imagePath)) {
                        if (!unlink($imagePath)) {
                            error_log("Failed to delete message image: $imagePath");
                        } else {
                            error_log("Successfully deleted message image: $imagePath");
                        }
                    } else {
                        error_log("Message image not found: $image, resolved path: $imagePath");
                    }
                }
            }
            // Delete reactions
            $query = "DELETE FROM reactions WHERE forum_id = :id OR message_id IN (SELECT id FROM messages WHERE forum_id = :id)";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            // Delete messages
            $query = "DELETE FROM messages WHERE forum_id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            // Delete forum
            $query = "DELETE FROM forums WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting forum ID $id: " . $e->getMessage());
            throw $e;
        }
    }

    public function ajouterMessage($forum_id, $user_id, $message, $image = null) {
        try {
            $pdo = config::getConnexion();
            $query = "INSERT INTO messages (forum_id, user_id, message, date_creation, image) 
                      VALUES (:forum_id, :user_id, :message, NOW(), :image)";
            $stmt = $pdo->prepare($query);
            error_log("Adding message: forum_id=$forum_id, user_id=$user_id, message=$message, image=$image");
            $stmt->execute([
                'forum_id' => $forum_id,
                'user_id' => $user_id,
                'message' => $message,
                'image' => $image
            ]);
        } catch (PDOException $e) {
            error_log("Error adding message: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMessagesByForumId($forum_id) {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT id, user_id, message, date_creation, image 
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

    public function addReaction($user_id, $target_type, $target_id, $reaction_type) {
        try {
            $pdo = config::getConnexion();
            $column = $target_type === 'forum' ? 'forum_id' : 'message_id';
            $table = $target_type === 'forum' ? 'forums' : 'messages';
            error_log("Validating $target_type ID: $target_id");
            $query = "SELECT id FROM $table WHERE id = :target_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['target_id' => $target_id]);
            if (!$stmt->fetch()) {
                error_log("Invalid $target_type ID: $target_id");
                return ['success' => false, 'message' => "Invalid $target_type ID: $target_id"];
            }

            // Check if reaction exists
            $query = "SELECT id FROM reactions WHERE user_id = :user_id AND $column = :target_id AND reaction_type = :reaction_type";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'user_id' => $user_id,
                'target_id' => $target_id,
                'reaction_type' => $reaction_type
            ]);
            if ($stmt->fetch()) {
                // Reaction exists, delete it
                $query = "DELETE FROM reactions WHERE user_id = :user_id AND $column = :target_id AND reaction_type = :reaction_type";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id,
                    'reaction_type' => $reaction_type
                ]);
                error_log("Reaction deleted: user_id=$user_id, $column=$target_id, reaction_type=$reaction_type");
                return ['success' => true, 'message' => 'Reaction removed'];
            } else {
                // Delete any existing reaction from this user on this target
                $query = "DELETE FROM reactions WHERE user_id = :user_id AND $column = :target_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id
                ]);
                // Add new reaction
                $query = "INSERT INTO reactions (user_id, $column, reaction_type, created_at) 
                          VALUES (:user_id, :target_id, :reaction_type, NOW())";
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id,
                    'reaction_type' => $reaction_type
                ]);
                error_log("Reaction attempt: user_id=$user_id, $column=$target_id, reaction_type=$reaction_type, success=" . ($result ? 'true' : 'false'));
                return [
                    'success' => $result,
                    'message' => $result ? 'Reaction added' : 'Failed to insert reaction'
                ];
            }
        } catch (PDOException $e) {
            error_log("Error adding/removing reaction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getReactionCount($target_type, $target_id, $reaction_type) {
        try {
            $pdo = config::getConnexion();
            $column = $target_type === 'forum' ? 'forum_id' : 'message_id';
            $query = "SELECT COUNT(*) FROM reactions 
                      WHERE $column = :target_id AND reaction_type = :reaction_type";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'target_id' => $target_id,
                'reaction_type' => $reaction_type
            ]);
            $count = $stmt->fetchColumn();
            error_log("Reaction count: $target_type=$target_id, reaction_type=$reaction_type, count=$count");
            return $count;
        } catch (PDOException $e) {
            error_log("Error fetching reaction count: " . $e->getMessage());
            return 0;
        }
    }

    public function deleteImage($table, $id, $column) {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT $column FROM $table WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            $image = $stmt->fetchColumn();
            if ($image) {
                // Resolve path relative to frontOffice
                $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                error_log("Attempting to delete image: table=$table, id=$id, column=$column, path=$image, resolved=$imagePath");
                if ($imagePath && file_exists($imagePath)) {
                    if (!unlink($imagePath)) {
                        error_log("Failed to delete image: $imagePath");
                    } else {
                        error_log("Successfully deleted image: $imagePath");
                    }
                } else {
                    error_log("Image not found: $image, resolved path: $imagePath");
                }
            } else {
                error_log("No image found for $table id=$id, column=$column");
            }
            $query = "UPDATE $table SET $column = NULL WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            error_log("Updated $table id=$id, set $column=NULL");
        } catch (PDOException $e) {
            error_log("Error deleting image from $table id=$id, column=$column: " . $e->getMessage());
            throw $e;
        }
    }
}
?>