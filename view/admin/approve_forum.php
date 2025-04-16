<?php
session_start();

// Restrict to logged-in admins
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../../frontOffice/login.php");
    exit;
}

require_once '../../config/config.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: collaborativespace.php?error=Invalid forum ID");
    exit;
}

$forum_id = $_GET['id'];
$pdo = config::getConnexion();

try {
    // Verify forum exists
    $stmt = $pdo->prepare("SELECT id FROM forums WHERE id = :id");
    $stmt->execute(['id' => $forum_id]);
    if (!$stmt->fetch()) {
        header("Location: collaborativespace.php?error=Forum not found");
        exit;
    }

    // Load approved forums
    $approved_file = '../../config/approved_forums.json';
    $approved_forums = file_exists($approved_file) ? json_decode(file_get_contents($approved_file), true) : [];

    // Add forum to approved list
    if (!in_array($forum_id, $approved_forums)) {
        $approved_forums[] = $forum_id;
        file_put_contents($approved_file, json_encode($approved_forums, JSON_PRETTY_PRINT));
    }

    header("Location: collaborativespace.php?message=Forum approved successfully");
    exit;
} catch (PDOException $e) {
    error_log("Error approving forum: " . $e->getMessage());
    header("Location: collaborativespace.php?error=Failed to approve forum");
    exit;
}
?>