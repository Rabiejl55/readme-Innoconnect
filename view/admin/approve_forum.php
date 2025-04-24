<?php
session_start();

// Inclure la connexion à la base de données
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = config::getConnexion();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        header("Location: collaborativespace.php?error=Invalid forum ID");
        exit();
    }

    // Mettre à jour le statut du forum
    $query = "UPDATE forums SET status = 'approved' WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);

    // Ajouter l'ID du forum à approved_forums.json
    $approved_file = __DIR__ . '/../../config/approved_forums.json';
    $approved_forums = file_exists($approved_file) ? json_decode(file_get_contents($approved_file), true) : [];
    $approved_forums[$id] = true;
    file_put_contents($approved_file, json_encode($approved_forums, JSON_PRETTY_PRINT));

    header("Location: collaborativespace.php?message=Forum post approved successfully");
    exit();
} catch (PDOException $e) {
    error_log("Error approving forum: " . $e->getMessage());
    header("Location: collaborativespace.php?error=Failed to approve forum post");
    exit();
}
?>