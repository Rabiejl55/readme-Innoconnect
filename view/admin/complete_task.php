<?php
session_start();

// Restrict to logged-in admins
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../../frontOffice/login.php");
    exit;
}

require_once '../../config/config.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: collaborativespace.php?error=Invalid task ID");
    exit;
}

$task_id = $_GET['id'];
$pdo = config::getConnexion();

try {
    // Verify task (forum) exists
    $stmt = $pdo->prepare("SELECT id FROM forums WHERE id = :id");
    $stmt->execute(['id' => $task_id]);
    if (!$stmt->fetch()) {
        header("Location: collaborativespace.php?error=Task not found");
        exit;
    }

    // Mark as completed
    $_SESSION['completed_tasks'] = $_SESSION['completed_tasks'] ?? [];
    $_SESSION['completed_tasks'][] = $task_id;
    $_SESSION['completed_tasks'] = array_unique($_SESSION['completed_tasks']);

    header("Location: collaborativespace.php?message=Task marked as completed");
    exit;
} catch (PDOException $e) {
    error_log("Error completing task: " . $e->getMessage());
    header("Location: collaborativespace.php?error=Failed to complete task");
    exit;
}
?>