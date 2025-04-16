<?php
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    header("Location: ../frontOffice/login.php");
    exit;
}

if (!isset($_GET['id_utilisateur']) || empty($_GET['id_utilisateur'])) {
    header("Location: listeUser.php?error=User ID is missing");
    exit;
}

$userIdToDelete = (int)$_GET['id_utilisateur'];

$success = $userC->deleteUser($userIdToDelete);
if ($success) {
    header("Location: listeUser.php?success=User deleted successfully");
} else {
    header("Location: listeUser.php?error=Failed to delete user");
}
exit;
?>