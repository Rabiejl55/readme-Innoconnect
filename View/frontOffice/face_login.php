<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

logMessage("Starting face login process");

// Vérifier si une image a été capturée depuis la webcam
if (!isset($_FILES['webcam_image']) || $_FILES['webcam_image']['error'] !== UPLOAD_ERR_OK) {
    logMessage("No webcam image received");
    echo json_encode(['status' => 'error', 'message' => 'No webcam image received']);
    exit;
}

// Dossier temporaire pour l'image webcam
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/uploads/temp/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExt = strtolower(pathinfo($_FILES['webcam_image']['name'], PATHINFO_EXTENSION));
$webcamImagePath = $uploadDir . 'webcam_' . time() . '.' . $fileExt;

if (!move_uploaded_file($_FILES['webcam_image']['tmp_name'], $webcamImagePath)) {
    logMessage("Failed to save webcam image");
    echo json_encode(['status' => 'error', 'message' => 'Failed to save webcam image']);
    exit;
}

logMessage("Webcam image saved at: $webcamImagePath");

// Générer l'encodage pour l'image webcam
$python_script = 'C:\xampp\htdocs\ProjetInnoconnect\scripts\generate_temp_encoding.py';
$tempEncodingPath = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/face_encodings/temp_encoding.npy';
$command = escapeshellcmd('"C:\Users\ferie\AppData\Local\Programs\Python\Python311\python.exe" ' . $python_script . ' ' . $webcamImagePath . ' ' . $tempEncodingPath);
$output = shell_exec($command);

logMessage("Generate temp encoding result: $output");

if (strpos($output, "Aucun visage détecté") !== false || !file_exists($tempEncodingPath)) {
    logMessage("No face detected in webcam image");
    unlink($webcamImagePath);
    echo json_encode(['status' => 'error', 'message' => 'No face detected in webcam image']);
    exit;
}

// Charger l'encodage de la webcam
$webcamEncoding = unserialize(file_get_contents($tempEncodingPath));

// Récupérer tous les utilisateurs
$db = config::getConnexion();
$stmt = $db->query("SELECT id_utilisateur FROM utilisateur");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matchFound = false;
$matchedUserId = null;

foreach ($users as $user) {
    $userId = $user['id_utilisateur'];
    $encodingFile = $_SERVER['DOCUMENT_ROOT'] . "/ProjetInnoconnect/face_encodings/user_{$userId}.npy";

    if (!file_exists($encodingFile)) {
        logMessage("Encoding file not found for user $userId");
        continue;
    }

    $storedEncoding = unserialize(file_get_contents($encodingFile));

    // Comparer les encodages avec face_recognition
    $python_compare_script = 'C:\xampp\htdocs\ProjetInnoconnect\scripts\compare_encodings.py';
    $command = escapeshellcmd('"C:\Users\ferie\AppData\Local\Programs\Python\Python311\python.exe" ' . $python_compare_script . ' ' . $tempEncodingPath . ' ' . $encodingFile);
    $compareOutput = shell_exec($command);

    logMessage("Comparison result for user $userId: $compareOutput");

    if (trim($compareOutput) === "True") {
        $matchFound = true;
        $matchedUserId = $userId;
        break;
    }
}

// Nettoyer les fichiers temporaires
unlink($webcamImagePath);
unlink($tempEncodingPath);

if ($matchFound) {
    logMessage("Face matched for user ID: $matchedUserId");
    $_SESSION['user_id'] = $matchedUserId;
    echo json_encode(['status' => 'success', 'message' => 'Face recognized successfully']);
} else {
    logMessage("No matching face found");
    echo json_encode(['status' => 'error', 'message' => 'No matching face found']);
}
?>