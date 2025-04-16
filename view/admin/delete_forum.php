<?php
session_start();

// Restrict access
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../frontOffice/login.php');
    exit;
}

require_once '../../config/config.php';

try {
    $pdo = config::getConnexion();

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header('Location: collaborativespace.php?error=Invalid post ID');
        exit;
    }

    $post_id = (int)$_GET['id'];

    // Vérifier si le post existe
    $stmt = $pdo->prepare("SELECT id FROM forums WHERE id = :id");
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetch()) {
        header('Location: collaborativespace.php?error=Post not found');
        exit;
    }

    // Supprimer les messages associés (foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM messages WHERE forum_id = :id");
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Supprimer le post
    $stmt = $pdo->prepare("DELETE FROM forums WHERE id = :id");
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        header('Location: collaborativespace.php?message=Post deleted successfully');
        exit;
    } else {
        header('Location: collaborativespace.php?error=Failed to delete post');
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error in delete_forum: " . $e->getMessage());
    header('Location: collaborativespace.php?error=Database error occurred');
    exit;
}
?>