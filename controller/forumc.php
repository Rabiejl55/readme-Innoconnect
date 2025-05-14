<?php
require_once '../config/config.php';
require_once '../Model/forum.php';

class ForumC {
    private function safeErrorLog($message, $destination = 'C:\xampp2\logs\debug.log') {
        $logDir = dirname($destination);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        if (@is_writable($destination) || @touch($destination)) {
            error_log($message . PHP_EOL, 3, $destination);
        } else {
            error_log("Custom log failed ($destination): $message");
        }
    }

    // Cache for filtered texts to reduce API calls
    private $textCache = [];
    

    // Method to filter bad words using ONLY Ninjas API with caching
    public function filterBadWords($text) {
        if (empty($text)) {
            return $text;
        }
        
        // Check if we already filtered this exact text
        $cacheKey = md5($text);
        if (isset($this->textCache[$cacheKey])) {
            $this->safeErrorLog("Using cached filtered text for: " . substr($text, 0, 30) . "...");
            return $this->textCache[$cacheKey];
        }
        
        $apiKey = 'afWIv62+O1X5vi/oeu1deg==xmCsovym3B0HeLRA';
        // The correct endpoint for the API Ninjas profanity filter
        $url = 'https://api.api-ninjas.com/v1/profanityfilter?text=' . urlencode($text);
        
        try {
            // Initialize cURL session
            $ch = curl_init($url);
            
            // Set cURL options - using GET request as per the API documentation
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key: ' . $apiKey,
                'Content-Type: application/json'
            ]);
            // Set a reasonable timeout to prevent hanging
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            // Execute cURL session and get the response
            $response = curl_exec($ch);
            
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Check for errors or rate limiting
            if (curl_errno($ch) || $httpCode != 200) {
                $error = curl_error($ch);
                curl_close($ch);
                $this->safeErrorLog("API Error (HTTP $httpCode): $error, Response: $response");
                
                // If API fails, just return the original text
                $this->textCache[$cacheKey] = $text;
                return $text;
            }
            
            // Close cURL session
            curl_close($ch);
            
            // Decode the JSON response
            $result = json_decode($response, true);
            
            $this->safeErrorLog("API Response: " . print_r($result, true));
            
            if (isset($result['censored'])) {
                $filteredText = $result['censored'];
                $hasProfanity = isset($result['has_profanity']) ? $result['has_profanity'] : false;
                
                $this->safeErrorLog("Text contains profanity: " . ($hasProfanity ? "Yes" : "No"));
                $this->safeErrorLog("Original text: " . $text);
                $this->safeErrorLog("Filtered text: " . $filteredText);
                
                // Cache the result and return
                $this->textCache[$cacheKey] = $filteredText;
                return $filteredText;
            } else {
                $this->safeErrorLog("Unexpected API response format: " . $response);
                
                // If response format is wrong, return original text
                $this->textCache[$cacheKey] = $text;
                return $text;
            }
        } catch (Exception $e) {
            $this->safeErrorLog("Exception in API call: " . $e->getMessage());
            
            // If exception occurs, return original text
            $this->textCache[$cacheKey] = $text;
            return $text;
        }
    }

    public function afficherForums($search = '', $sort = 'date_desc', $limit = 100, $offset = 0) {
        try {
            $pdo = config::getConnexion();
            $params = [];
            
            // Base query
            $query = "SELECT f.*, 
                             m.image AS message_image, 
                             m.message AS first_message,
                             (SELECT COUNT(*) FROM reactions WHERE forum_id = f.id) AS reaction_count,
                             (SELECT COUNT(*) FROM messages WHERE forum_id = f.id) AS message_count
                      FROM forums f 
                      LEFT JOIN messages m ON m.forum_id = f.id AND m.id = (
                          SELECT MIN(id) FROM messages WHERE forum_id = f.id
                      )";

            // Handle search
            if (!empty($search)) {
                $search = trim(preg_replace('/[^\w\s]/', '', $search));
                if (strlen($search) < 3) {
                    $this->safeErrorLog("Search term too short: '$search'");
                    return [];
                }
                $query .= " WHERE (
                    f.titre LIKE :search OR 
                    f.category LIKE :search OR 
                    EXISTS (
                        SELECT 1 FROM messages m2 
                        WHERE m2.forum_id = f.id 
                        AND m2.message LIKE :search
                    )
                )";
                $params['search'] = "%$search%";
            }

            // Handle sorting
            switch ($sort) {
                case 'title_asc':
                    $query .= " ORDER BY f.titre ASC";
                    break;
                case 'title_desc':
                    $query .= " ORDER BY f.titre DESC";
                    break;
                case 'category_asc':
                    $query .= " ORDER BY f.category ASC";
                    break;
                case 'category_desc':
                    $query .= " ORDER BY f.category DESC";
                    break;
                case 'date_asc':
                    $query .= " ORDER BY f.date_creation ASC";
                    break;
                case 'reactions_asc':
                    $query .= " ORDER BY reaction_count ASC";
                    break;
                case 'reactions_desc':
                    $query .= " ORDER BY reaction_count DESC";
                    break;
                case 'date_desc':
                default:
                    $query .= " ORDER BY f.date_creation DESC";
                    break;
            }
            $query .= " LIMIT :limit OFFSET :offset";

            // Prepare and execute
            $stmt = $pdo->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->safeErrorLog("Fetched " . count($forums) . " forums with search='$search', sort='$sort', limit=$limit, offset=$offset");
            return $forums;
        } catch (Exception $e) {
            $this->safeErrorLog("afficherForums error: " . $e->getMessage());
            return [];
        }
    }

    public function ajouterForum($forum) {
        try {
            $pdo = config::getConnexion();
            
            // Filter bad words from title and message
            $filteredTitle = $this->filterBadWords($forum->getTitre());
            $filteredMessage = $this->filterBadWords($forum->getMessage());
            
            // Update the forum object with filtered content
            $forum->setTitre($filteredTitle);
            $forum->setMessage($filteredMessage);
            
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
            $this->safeErrorLog("Added forum ID $forum_id");
        } catch (PDOException $e) {
            $this->safeErrorLog("Error adding forum: " . $e->getMessage());
            throw $e;
        }
    }

    public function modifierForum($forum) {
        try {
            $pdo = config::getConnexion();
            
            // Filter bad words from title and message
            $filteredTitle = $this->filterBadWords($forum->getTitre());
            $filteredMessage = $this->filterBadWords($forum->getMessage());
            
            // Update the forum object with filtered content
            $forum->setTitre($filteredTitle);
            $forum->setMessage($filteredMessage);
            
            $query = "UPDATE forums SET titre = :titre, category = :category, image = :image WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'titre' => $forum->getTitre(),
                'category' => $forum->getCategory(),
                'image' => $forum->getImage(),
                'id' => $forum->getId()
            ]);
            $query = "SELECT id FROM messages WHERE forum_id = :forum_id ORDER BY id ASC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['forum_id' => $forum->getId()]);
            $message_id = $stmt->fetchColumn();
            if ($message_id) {
                $query = "UPDATE messages SET message = :message, image = :message_image 
                          WHERE id = :message_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'message' => $forum->getMessage(),
                    'message_image' => $forum->getMessageImage(),
                    'message_id' => $message_id
                ]);
            } else {
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
            $this->safeErrorLog("Updated forum ID " . $forum->getId());
        } catch (PDOException $e) {
            $this->safeErrorLog("Error updating forum ID {$forum->getId()}: " . $e->getMessage());
            throw $e;
        }
    }

    public function supprimerForum($id) {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT image FROM forums WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            if ($image = $stmt->fetchColumn()) {
                $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                if ($imagePath && file_exists($imagePath)) {
                    if (!unlink($imagePath)) {
                        $this->safeErrorLog("Failed to delete forum image: $imagePath");
                    } else {
                        $this->safeErrorLog("Successfully deleted forum image: $imagePath");
                    }
                } else {
                    $this->safeErrorLog("Forum image not found: $image, resolved path: $imagePath");
                }
            }
            $query = "SELECT image FROM messages WHERE forum_id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            while ($image = $stmt->fetchColumn()) {
                if ($image) {
                    $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                    if ($imagePath && file_exists($imagePath)) {
                        if (!unlink($imagePath)) {
                            $this->safeErrorLog("Failed to delete message image: $imagePath");
                        } else {
                            $this->safeErrorLog("Successfully deleted message image: $imagePath");
                        }
                    } else {
                        $this->safeErrorLog("Message image not found: $image, resolved path: $imagePath");
                    }
                }
            }
            $query = "DELETE FROM reactions WHERE forum_id = :id OR message_id IN (SELECT id FROM messages WHERE forum_id = :id)";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            $query = "DELETE FROM messages WHERE forum_id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            $query = "DELETE FROM forums WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            $this->safeErrorLog("Deleted forum ID $id");
        } catch (PDOException $e) {
            $this->safeErrorLog("Error deleting forum ID $id: " . $e->getMessage());
            throw $e;
        }
    }

    public function ajouterMessage($forum_id, $user_id, $message, $image = null) {
        try {
            $pdo = config::getConnexion();
            
            // Filter bad words from message
            $filteredMessage = $this->filterBadWords($message);
            
            $query = "INSERT INTO messages (forum_id, user_id, message, date_creation, image) 
                      VALUES (:forum_id, :user_id, :message, NOW(), :image)";
            $stmt = $pdo->prepare($query);
            $this->safeErrorLog("Adding message: forum_id=$forum_id, user_id=$user_id, message=$message, image=$image");
            $stmt->execute([
                'forum_id' => $forum_id,
                'user_id' => $user_id,
                'message' => $filteredMessage,
                'image' => $image
            ]);
            $this->safeErrorLog("Added message to forum ID $forum_id");
        } catch (PDOException $e) {
            $this->safeErrorLog("Error adding message: " . $e->getMessage());
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
            $this->safeErrorLog("Fetching messages for forum_id: $forum_id");
            $stmt->execute(['forum_id' => $forum_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->safeErrorLog("Fetched " . count($messages) . " messages for forum ID $forum_id");
            return $messages;
        } catch (PDOException $e) {
            $this->safeErrorLog("Error fetching messages: " . $e->getMessage());
            return [];
        }
    }

    public function addReaction($user_id, $target_type, $target_id, $reaction_type) {
        try {
            $pdo = config::getConnexion();
            $column = $target_type === 'forum' ? 'forum_id' : 'message_id';
            $table = $target_type === 'forum' ? 'forums' : 'messages';
            $this->safeErrorLog("Validating $target_type ID: $target_id");
            $query = "SELECT id FROM $table WHERE id = :target_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['target_id' => $target_id]);
            if (!$stmt->fetch()) {
                $this->safeErrorLog("Invalid $target_type ID: $target_id");
                return ['success' => false, 'message' => "Invalid $target_type ID: $target_id"];
            }

            $query = "SELECT id FROM reactions WHERE user_id = :user_id AND $column = :target_id AND reaction_type = :reaction_type";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'user_id' => $user_id,
                'target_id' => $target_id,
                'reaction_type' => $reaction_type
            ]);
            if ($stmt->fetch()) {
                $query = "DELETE FROM reactions WHERE user_id = :user_id AND $column = :target_id AND reaction_type = :reaction_type";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id,
                    'reaction_type' => $reaction_type
                ]);
                $this->safeErrorLog("Reaction deleted: user_id=$user_id, $column=$target_id, reaction_type=$reaction_type");
                return ['success' => true, 'message' => 'Reaction removed'];
            } else {
                $query = "DELETE FROM reactions WHERE user_id = :user_id AND $column = :target_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id
                ]);
                $query = "INSERT INTO reactions (user_id, $column, reaction_type, created_at) 
                          VALUES (:user_id, :target_id, :reaction_type, NOW())";
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute([
                    'user_id' => $user_id,
                    'target_id' => $target_id,
                    'reaction_type' => $reaction_type
                ]);
                $this->safeErrorLog("Reaction attempt: user_id=$user_id, $column=$target_id, reaction_type=$reaction_type, success=" . ($result ? 'true' : 'false'));
                return [
                    'success' => $result,
                    'message' => $result ? 'Reaction added' : 'Failed to insert reaction'
                ];
            }
        } catch (PDOException $e) {
            $this->safeErrorLog("Error adding/removing reaction: " . $e->getMessage());
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
            $this->safeErrorLog("Reaction count: $target_type=$target_id, reaction_type=$reaction_type, count=$count");
            return $count;
        } catch (PDOException $e) {
            $this->safeErrorLog("Error fetching reaction count: " . $e->getMessage());
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
                $imagePath = realpath(__DIR__ . '/../../frontOffice/' . $image);
                $this->safeErrorLog("Attempting to delete image: table=$table, id=$id, column=$column, path=$image, resolved=$imagePath");
                if ($imagePath && file_exists($imagePath)) {
                    if (!unlink($imagePath)) {
                        $this->safeErrorLog("Failed to delete image: $imagePath");
                    } else {
                        $this->safeErrorLog("Successfully deleted image: $imagePath");
                    }
                } else {
                    $this->safeErrorLog("Image not found: $image, resolved path: $imagePath");
                }
            } else {
                $this->safeErrorLog("No image found for $table id=$id, column=$column");
            }
            $query = "UPDATE $table SET $column = NULL WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            $this->safeErrorLog("Updated $table id=$id, set $column=NULL");
        } catch (PDOException $e) {
            $this->safeErrorLog("Error deleting image from $table id=$id, column=$column: " . $e->getMessage());
            throw $e;
        }
    }
}
?>