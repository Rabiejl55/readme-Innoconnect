<?php
session_start();
session_regenerate_id(true); // Régénérer l'ID de session
$_SESSION = array();
session_destroy();

// Empêchement de la mise en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: login.php?success=You have been logged out");
exit;
?>