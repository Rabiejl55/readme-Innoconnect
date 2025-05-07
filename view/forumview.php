<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function safeErrorLog($message, $destination = 'C:\xampp2\logs\debug.log') {
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
safeErrorLog('Starting forumview.php');

// Set default timezone
date_default_timezone_set('UTC');

require_once '../controller/forumc.php';
require_once '../Model/forum.php';

$forumC = new ForumC();
$errorMessage = '';

// Handle search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$forums = $forumC->afficherForums($search, $sort);

// Validate input
function validateInput($data, $maxLength, $minLength, $fieldName, &$errors) {
    $data = trim($data);
    if (empty($data)) {
        $errors[] = "$fieldName cannot be empty.";
        return false;
    }
    if (strlen($data) < $minLength) {
        $errors[] = "$fieldName must be at least $minLength characters.";
        return false;
    }
    if (strlen($data) > $maxLength) {
        $errors[] = "$fieldName must be $maxLength characters or less.";
        return false;
    }
    if (!preg_match('/^[A-Za-z0-9\s.,!?\'"-]+$/', $data)) {
        $errors[] = "$fieldName contains invalid characters.";
        return false;
    }
    return $data;
}

// Validate image
function validateImage($file, $fieldName, &$errors) {
    if ($file['size'] == 0 || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "$fieldName must be a JPEG or PNG image.";
        return false;
    }
    if ($file['size'] > $maxSize) {
        $errors[] = "$fieldName must be less than 5MB.";
        return false;
    }
    $uploadDir = '../Uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        safeErrorLog("Uploaded $fieldName to $targetPath");
        return $targetPath;
    }
    $errors[] = "Failed to upload $fieldName.";
    return false;
}

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="forums_export_' . date('Ymd_His') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Category', 'User ID', 'Created At']);
    foreach ($forums as $forum) {
        fputcsv($output, [
            $forum['id'],
            $forum['titre'],
            $forum['category'],
            $forum['user_id'],
            $forum['date_creation'],
        ]);
    }
    fclose($output);
    safeErrorLog("Exported forums to CSV");
    exit;
}

// Handle forum deletion
if (isset($_GET['delete'])) {
    $errors = [];
    $forum_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if (!$forum_id || $forum_id <= 0) {
        $errors[] = "Invalid forum ID.";
    } else {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id FROM forums WHERE id = :id");
            $stmt->execute(['id' => $forum_id]);
            if (!$stmt->fetch()) {
                $errors[] = "Forum not found.";
            } else {
                $forumC->supprimerForum($forum_id);
                header("Location: forumview.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Failed to delete discussion: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle create forum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['titre'], $_POST['message'], $_POST['category']) && !isset($_POST['forum_id'])) {
    safeErrorLog("Create forum POST: " . print_r($_POST, true));
    safeErrorLog("Create forum FILES: " . print_r($_FILES, true));
    $errors = [];
    $titre = validateInput($_POST['titre'], 50, 3, "Title", $errors);
    $message = validateInput($_POST['message'], 500, 10, "Message", $errors);
    $category = validateInput($_POST['category'], 50, 3, "Category", $errors);
    $image = validateImage($_FILES['image'] ?? ['size' => 0, 'error' => UPLOAD_ERR_NO_FILE], "Image", $errors);
    $message_image = validateImage($_FILES['message_image'] ?? ['size' => 0, 'error' => UPLOAD_ERR_NO_FILE], "Message Image", $errors);
    if (empty($errors)) {
        try {
            $forum = new Forum($titre, $message, $category, 1, $image, $message_image);
            $forumC->ajouterForum($forum);
            header("Location: forumview.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to create discussion: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle edit forum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['titre'], $_POST['message'], $_POST['category'], $_POST['edit_forum'])) {
    safeErrorLog("Edit forum POST: " . print_r($_POST, true));
    safeErrorLog("Edit forum FILES: " . print_r($_FILES, true));
    $errors = [];
    $forum_id = filter_var($_POST['forum_id'], FILTER_VALIDATE_INT);
    $titre = validateInput($_POST['titre'], 50, 3, "Title", $errors);
    $message = validateInput($_POST['message'], 500, 10, "Message", $errors);
    $category = validateInput($_POST['category'], 50, 3, "Category", $errors);
    $image = validateImage($_FILES['image'] ?? ['size' => 0, 'error' => UPLOAD_ERR_NO_FILE], "Image", $errors);
    $message_image = validateImage($_FILES['message_image'] ?? ['size' => 0, 'error' => UPLOAD_ERR_NO_FILE], "Message Image", $errors);
    if (!$forum_id || $forum_id <= 0) {
        $errors[] = "Invalid forum ID.";
    } else {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT f.id, f.image, m.image AS message_image 
                               FROM forums f 
                               LEFT JOIN messages m ON m.forum_id = f.id AND m.id = (SELECT MIN(id) FROM messages WHERE forum_id = f.id) 
                               WHERE f.id = :id");
        $stmt->execute(['id' => $forum_id]);
        $existingForum = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingForum) {
            $errors[] = "Forum not found.";
        }
    }
    if (empty($errors)) {
        try {
            $finalImage = $image !== false ? $image : $existingForum['image'];
            $finalMessageImage = $message_image !== false ? $message_image : $existingForum['message_image'];
            $forum = new Forum($titre, $message, $category, 1, $finalImage, $finalMessageImage);
            $forum->setId($forum_id);
            $forumC->modifierForum($forum);
            header("Location: forumview.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to update discussion: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['message'], $_POST['new_message'])) {
    safeErrorLog("New message POST: " . print_r($_POST, true));
    safeErrorLog("New message FILES: " . print_r($_FILES, true));
    $errors = [];
    $forum_id = filter_var($_POST['forum_id'], FILTER_VALIDATE_INT);
    $message = validateInput($_POST['message'], 500, 10, "Message", $errors);
    $image = validateImage($_FILES['image'] ?? ['size' => 0, 'error' => UPLOAD_ERR_NO_FILE], "Image", $errors);
    if (!$forum_id || $forum_id <= 0) {
        $errors[] = "Invalid forum ID.";
    } else {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT id FROM forums WHERE id = :id");
        $stmt->execute(['id' => $forum_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Forum not found.";
        }
    }
    if (empty($errors)) {
        try {
            $forumC->ajouterMessage($forum_id, 1, $message, $image);
            header("Location: forumview.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to add reply: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle delete image
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_image'], $_POST['table'], $_POST['id'], $_POST['column'])) {
    safeErrorLog("Delete image POST: " . print_r($_POST, true));
    $errors = [];
    $table = $_POST['table'];
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $column = $_POST['column'];
    if (!in_array($table, ['forums', 'messages']) || !$id || $id <= 0 || $column !== 'image') {
        $errors[] = "Invalid delete image request.";
    } else {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE id = :id");
            $stmt->execute(['id' => $id]);
            if (!$stmt->fetch()) {
                $errors[] = ucfirst($table) . " not found.";
            } else {
                $forumC->deleteImage($table, $id, $column);
                header("Location: forumview.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Failed to delete image: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle reactions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reaction_type'], $_POST['target_type'], $_POST['target_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    $errors = [];
    $reaction_type = trim($_POST['reaction_type'] ?? '');
    $target_type = trim($_POST['target_type'] ?? '');
    $target_id = filter_var($_POST['target_id'] ?? 0, FILTER_VALIDATE_INT);
    $user_id = 1;
    safeErrorLog("Reaction request: user_id=$user_id, target_type=$target_type, target_id=$target_id, reaction_type=$reaction_type");

    if (!in_array($reaction_type, ['like', 'love', 'haha'])) {
        $errors[] = "Invalid reaction type: $reaction_type";
    }
    if (!in_array($target_type, ['forum', 'message']) || !$target_id || $target_id <= 0) {
        $errors[] = "Invalid target: type=$target_type, id=$target_id";
    }

    if (empty($errors)) {
        try {
            $result = $forumC->addReaction($user_id, $target_type, $target_id, $reaction_type);
            $response = [
                'success' => $result['success'],
                'message' => $result['message'],
                'counts' => [
                    'like' => $forumC->getReactionCount($target_type, $target_id, 'like'),
                    'love' => $forumC->getReactionCount($target_type, $target_id, 'love'),
                    'haha' => $forumC->getReactionCount($target_type, $target_id, 'haha')
                ]
            ];
            safeErrorLog("Reaction response: " . json_encode($response));
            echo json_encode($response);
        } catch (Exception $e) {
            $errors[] = "Server error: " . $e->getMessage();
            $response = ['success' => false, 'message' => implode(', ', $errors)];
            safeErrorLog("Reaction error: " . json_encode($response));
            echo json_encode($response);
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
        safeErrorLog("Reaction validation error: " . json_encode($response));
        echo json_encode($response);
    }
    exit;
}

function timeAgo($datetime) {
    try {
        $date = new DateTime($datetime);
        return $date->format('F j, Y, H:i');
    } catch (Exception $e) {
        safeErrorLog("timeAgo error: " . $e->getMessage());
        return 'Invalid date';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaborative Forum - InnoConnect</title>
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f4f7fb;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }
        .header {
            background: linear-gradient(90deg, #6f42c1, #8a5ed6);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header .navmenu ul li a {
            color: white;
            font-weight: 500;
            transition: color 0.3s;
        }
        .header .navmenu ul li a:hover {
            color: #e0d4ff;
        }
        .btn-custom {
            background-color: #6f42c1;
            color: white;
            border-radius: 20px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #5a32a1;
            transform: translateY(-2px);
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin: 20px 0;
            opacity: 0;
            transform: translateY(-100%);
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: none;
        }
        .form-container.active {
            opacity: 1;
            transform: translateY(0);
            display: block;
        }
        .forum-list {
            margin-top: 40px;
        }
        .forum-post {
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        .forum-post:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }
        .forum-post .post-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 10px;
        }
        .forum-post .post-header .user-info {
            font-weight: 600;
            color: #6f42c1;
            font-size: 1.1em;
        }
        .forum-post .post-header .timestamp {
            color: #6c757d;
            font-size: 0.9em;
            white-space: nowrap;
        }
        .forum-post .post-title {
            font-size: 1.5em;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .forum-post .category {
            background-color: #17a2b8;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }
        .forum-post .post-body {
            margin-bottom: 20px;
        }
        .forum-post .post-content {
            font-size: 1em;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .forum-post .post-image-container {
            width: 100%;
            margin: 15px 0;
        }
        .forum-post .post-image {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: block;
        }
        .forum-post .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f1f3f5;
        }
        .forum-post .reaction-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .forum-post .action-buttons {
            display: flex;
            gap: 10px;
        }
        .message-post {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-left: 20px;
        }
        .message-post::before {
            content: '';
            position: absolute;
            top: 20px;
            left: -10px;
            border: 10px solid transparent;
            border-right-color: #f8f9fa;
        }
        .message-post .user-info {
            font-weight: 600;
            color: #6f42c1;
        }
        .message-post .timestamp {
            color: #666;
            font-size: 0.85em;
            white-space: nowrap;
        }
        .message-post .post-image {
            max-width: 100%;
            border-radius: 10px;
            margin: 10px 0;
        }
        .reaction-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            align-items: center;
        }
        .reaction-btn {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            padding: 5px;
        }
        .reaction-btn:hover {
            transform: scale(1.3) translateY(-2px);
            box-shadow: 0 4px 8px rgba(111, 66, 193, 0.3);
            animation: bounce 0.3s ease;
        }
        .reaction-btn.active {
            transform: scale(1.1);
            color: #6f42c1;
            font-weight: bold;
        }
        @keyframes bounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1.3); }
        }
        .reaction-count {
            font-size: 0.9em;
            color: #6f42c1;
            margin-left: 4px;
            display: inline-flex;
            align-items: center;
        }
        .reaction-feedback {
            color: #6f42c1;
            font-size: 0.9em;
            margin-left: 10px;
            display: none;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9em;
            border-radius: 15px;
        }
        .btn-primary {
            background-color: #6f42c1;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5a32a1;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background: #6f42c1;
            color: white;
            border-radius: 12px 12px 0 0;
        }
        footer {
            background: #6f42c1;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        .form-control, .form-control:focus {
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease;
        }
        .form-control:hover {
            border-color: #6f42c1;
        }
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 5px rgba(111, 66, 193, 0.3);
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .input-error {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .input-error.show {
            opacity: 1;
        }
        .toggle-form-btn {
            background-color: #6f42c1;
            color: white;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1em;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .toggle-form-btn:hover {
            background-color: #5a32a1;
            transform: scale(1.05);
            animation: pulse 1.5s infinite;
        }
        .toggle-form-btn i {
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }
        .toggle-form-btn.active i {
            transform: rotate(45deg);
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .scroll-top {
            bottom: 30px;
            left: 30px;
            right: auto;
            background-color: #6f42c1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .scroll-top.visible {
            opacity: 1;
        }
        .control-bar {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .control-bar .search-container {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        .control-bar .search-container input {
            padding-left: 35px;
        }
        .control-bar .search-container i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6f42c1;
        }
        .control-bar .sort-container select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 8px 12px;
            font-size: 0.9em;
            color: #333;
            background-color: #fff;
            transition: border-color 0.2s ease;
        }
        .control-bar .sort-container select:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 5px rgba(111, 66, 193, 0.3);
        }
        .control-bar .btn-export {
            background-color: #17a2b8;
            color: white;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        .control-bar .btn-export:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }
        .control-bar .btn-export i {
            margin-right: 5px;
        }
        .loading-spinner {
            display: none;
            font-size: 0.9em;
            color: #6f42c1;
            margin-left: 10px;
        }
        .loading-spinner.active {
            display: inline-block;
        }
        @media (max-width: 768px) {
            .forum-post {
                padding: 20px;
                margin-bottom: 20px;
            }
            .forum-post .post-title {
                font-size: 1.3em;
            }
            .forum-post .post-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .forum-post .post-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .forum-post .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            .btn-sm {
                font-size: 0.8em;
                padding: 5px 10px;
            }
            .form-container {
                padding: 15px;
            }
            .toggle-form-btn {
                padding: 8px 16px;
                font-size: 0.9em;
            }
            .scroll-top {
                bottom: 20px;
                left: 20px;
            }
            .reaction-btn {
                font-size: 1.2em;
                padding: 4px;
            }
            .reaction-count {
                font-size: 0.8em;
            }
            .timestamp {
                font-size: 0.8em;
            }
            .control-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .control-bar .search-container {
                min-width: 100%;
            }
            .control-bar .sort-container select,
            .control-bar .btn-export {
                width: 100%;
            }
        }
        .chatbot-float-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            background: linear-gradient(135deg, #6f42c1 60%, #8a5ed6 100%);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 16px rgba(111,66,193,0.2);
            font-size: 2em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .chatbot-float-btn:hover {
            background: linear-gradient(135deg, #5a32a1 60%, #6f42c1 100%);
            transform: scale(1.08);
            box-shadow: 0 8px 32px rgba(111,66,193,0.25);
        }
        .chatbot-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.18);
            justify-content: center;
            align-items: center;
            transition: background 0.3s;
        }
        .chatbot-modal.active {
            display: flex;
            animation: chatbot-fade-in 0.25s;
        }
        @keyframes chatbot-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .chatbot-modal-content {
            background: #fff;
            padding: 0 0 12px 0;
            border-radius: 18px;
            width: 370px;
            max-width: 98vw;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1.5px solid #ece6fa;
            animation: chatbot-modal-pop 0.3s;
        }
        @keyframes chatbot-modal-pop {
            from { transform: scale(0.95); opacity: 0.7; }
            to { transform: scale(1); opacity: 1; }
        }
        .chatbot-header {
            background: linear-gradient(90deg, #6f42c1 60%, #8a5ed6 100%);
            color: #fff;
            border-radius: 18px 18px 0 0;
            padding: 16px 18px 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        .chatbot-avatar {
            background: #fff;
            color: #6f42c1;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(111,66,193,0.08);
        }
        .chatbot-title {
            font-weight: 600;
            font-size: 1.1em;
            flex: 1;
            margin-left: 8px;
        }
        .chatbot-close {
            position: absolute;
            top: 12px;
            right: 18px;
            font-size: 1.7em;
            color: #fff;
            cursor: pointer;
            opacity: 0.85;
            transition: color 0.2s, opacity 0.2s;
        }
        .chatbot-close:hover {
            color: #ffe6e6;
            opacity: 1;
        }
        .chatbot-messages {
            font-size: 1em;
            min-height: 60px;
            max-height: 260px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 18px 12px 10px 12px;
            border-radius: 0 0 10px 10px;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .chatbot-bubble {
            display: inline-block;
            padding: 9px 15px;
            border-radius: 18px;
            margin-bottom: 2px;
            max-width: 85%;
            word-break: break-word;
            box-shadow: 0 2px 8px rgba(111,66,193,0.04);
            font-size: 0.98em;
        }
        .chatbot-bubble.user {
            background: linear-gradient(90deg, #e0d4ff 60%, #f3eaff 100%);
            color: #4b2e83;
            align-self: flex-end;
            border-bottom-right-radius: 6px;
        }
        .chatbot-bubble.bot {
            background: linear-gradient(90deg, #f7f6fa 60%, #ece6fa 100%);
            color: #6f42c1;
            align-self: flex-start;
            border-bottom-left-radius: 6px;
        }
        .chatbot-input-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px 0 14px;
        }
        .chatbot-input {
            border-radius: 18px;
            border: 1.5px solid #ece6fa;
            resize: none;
            font-size: 1em;
            padding: 8px 12px;
            flex: 1;
            min-height: 38px;
            max-height: 80px;
            box-shadow: none;
            transition: border 0.2s;
        }
        .chatbot-input:focus {
            border: 1.5px solid #6f42c1;
            outline: none;
        }
        .chatbot-send-btn {
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3em;
            background: linear-gradient(135deg, #6f42c1 60%, #8a5ed6 100%);
            border: none;
            color: #fff;
            box-shadow: 0 2px 8px rgba(111,66,193,0.10);
            transition: background 0.2s, transform 0.2s;
        }
        .chatbot-send-btn:active, .chatbot-send-btn:focus {
            background: linear-gradient(135deg, #5a32a1 60%, #6f42c1 100%);
            transform: scale(0.97);
        }
        .chatbot-send-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .chatbot-loading-spinner {
            margin-left: 6px;
            color: #6f42c1;
            font-size: 1.3em;
            animation: chatbot-spin 1s linear infinite;
        }
        @keyframes chatbot-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 600px) {
            .chatbot-modal-content { width: 99vw; }
            .chatbot-float-btn { right: 10px; bottom: 10px; }
            .chatbot-header { padding: 12px 8px 10px 8px; }
            .chatbot-input-row { padding: 8px 4px 0 4px; }
        }
    </style>
</head>
<body data-aos-easing="ease" data-aos-duration="1000" data-aos-delay="0">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="../view/index.html" class="logo d-flex align-items-center me-auto">
                <img src="../assets/img/innoconnect.jpg" alt="InnoConnect Logo" class="img-fluid" style="max-height: 45px;">
                <h1 class="sitename">InnoConnect</h1>
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="../view/index.html">Home</a></li>
                    <li><a href="forumview.php" class="active">Forum</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="../view/index.html#services">Services</a></li>
                    <li><a href="#team">Team</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main class="main" style="padding-top: 120px;">
        <div class="container">
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <?php if (!empty($search) && empty($forums) && strlen($search) < 3): ?>
                <div class="alert alert-warning">Search term must be at least 3 characters long.</div>
            <?php elseif (!empty($search) && empty($forums)): ?>
                <div class="alert alert-info">No discussions found for "<?php echo htmlspecialchars($search); ?>". Try a different search term.</div>
            <?php endif; ?>
            <div class="d-flex justify-content-end mb-3">
                <button class="toggle-form-btn" onclick="toggleForm()">
                    <i class="bi bi-plus-lg"></i>
                    <span>Start a Discussion</span>
                </button>
            </div>

            <div id="create-forum" class="form-container" data-aos="fade-up">
                <h2 class="mb-4">Start a New Discussion</h2>
                <form id="createForumForm" method="POST" enctype="multipart/form-data" onsubmit="return validateCreateForm(this)">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Title</label>
                        <input type="text" class="form-control" id="titre" name="titre" placeholder="Enter discussion title">
                        <div class="input-error" id="titreError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Share your thoughts..."></textarea>
                        <div class="input-error" id="messageError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" placeholder="Enter category (e.g., General)" value="General">
                        <div class="input-error" id="categoryError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Forum Image (Optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png">
                    </div>
                    <div class="mb-3">
                        <label for="message_image" class="form-label">Message Image (Optional)</label>
                        <input type="file" class="form-control" id="message_image" name="message_image" accept="image/jpeg,image/png">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-custom">Submit Discussion</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="control-bar" data-aos="fade-up">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search forums..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="sort-container">
                    <select id="sortSelect" class="form-select">
                        <option value="date_desc" <?php echo $sort === 'date_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                    </select>
                </div>
                <a href="?export=csv&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="btn btn-export">
                    <i class="bi bi-download"></i> Export as CSV
                </a>
                <span class="loading-spinner">Searching...</span>
            </div>

            <div class="forum-list" data-aos="fade-up" data-aos-delay="200">
                <h2 class="mb-4">Ongoing Discussions</h2>
                <?php if (empty($forums) && empty($search)): ?>
                    <div class="alert alert-info text-center">No discussions found. Start a new discussion!</div>
                <?php else: ?>
                    <?php foreach ($forums as $forum): ?>
                        <div class="forum-post">
                            <div class="post-header">
                                <span class="user-info">User <?php echo htmlspecialchars($forum['user_id'] ?? 'Unknown'); ?></span>
                                <span class="timestamp"><?php echo timeAgo($forum['date_creation']); ?></span>
                            </div>
                            <div class="post-body">
                                <div class="post-title"><?php echo htmlspecialchars($forum['titre']); ?></div>
                                <span class="category"><?php echo htmlspecialchars($forum['category']); ?></span>
                                <div class="post-content"><?php echo nl2br(htmlspecialchars($forum['first_message'] ?? '')); ?></div>
                                <?php if (!empty($forum['image'])): ?>
                                    <div class="post-image-container">
                                        <img src="<?php echo htmlspecialchars($forum['image']); ?>" class="post-image" alt="Forum Image">
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($forum['message_image'])): ?>
                                    <div class="post-image-container">
                                        <img src="<?php echo htmlspecialchars($forum['message_image']); ?>" class="post-image" alt="Message Image">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="post-footer">
                                <div class="reaction-buttons" data-forum-id="<?php echo $forum['id']; ?>">
                                    <?php
                                    $reactions = [
                                        'like' => '👍',
                                        'love' => '❤️',
                                        'haha' => '😄'
                                    ];
                                    $pdo = config::getConnexion();
                                    foreach ($reactions as $type => $emoji):
                                        $count = $forumC->getReactionCount('forum', $forum['id'], $type);
                                        $hasReacted = $pdo->prepare("SELECT id FROM reactions WHERE forum_id = :forum_id AND user_id = :user_id AND reaction_type = :type");
                                        $hasReacted->execute(['forum_id' => $forum['id'], 'user_id' => 1, 'type' => $type]);
                                        $active = $hasReacted->fetch() ? 'active' : '';
                                    ?>
                                        <button class="reaction-btn <?php echo $active; ?>" data-type="<?php echo $type; ?>" data-target="forum" data-id="<?php echo $forum['id']; ?>" title="<?php echo ucfirst($type); ?>">
                                            <span><?php echo $emoji; ?></span>
                                            <?php if ($count > 0): ?>
                                                <span class="reaction-count"><?php echo $count; ?></span>
                                            <?php endif; ?>
                                        </button>
                                        <span class="reaction-feedback" id="forum-<?php echo $forum['id']; ?>-<?php echo $type; ?>-feedback"></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $forum['id']; ?>">
                                        <i class="bi bi-chat-dots"></i> View & Reply
                                    </button>
                                    <button class="btn btn-primary btn-sm edit-forum-btn" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $forum['id']; ?>" data-forum-id="<?php echo $forum['id']; ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <a href="?delete=<?php echo $forum['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="messageModal<?php echo $forum['id']; ?>" tabindex="-1" aria-labelledby="messageModalLabel<?php echo $forum['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="messageModalLabel<?php echo $forum['id']; ?>">
                                            Discussion: <?php echo htmlspecialchars($forum['titre']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h6 class="mb-3">Conversation</h6>
                                        <div class="existing-messages" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                                            <?php
                                            $messages = $forumC->getMessagesByForumId($forum['id']);
                                            if (empty($messages)) {
                                                echo "<p class='text-muted'>No messages yet. Be the first to reply!</p>";
                                            } else {
                                                foreach ($messages as $message) {
                                                    ?>
                                                    <div class="message-post">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="user-info">User <?php echo htmlspecialchars($message['user_id'] ?? 'Unknown'); ?></span>
                                                            <span class="timestamp"><?php echo timeAgo($message['date_creation']); ?></span>
                                                        </div>
                                                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                                        <?php if (!empty($message['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($message['image']); ?>" class="post-image" alt="Message Image">
                                                        <?php endif; ?>
                                                        <div class="reaction-buttons" data-message-id="<?php echo $message['id']; ?>">
                                                            <?php
                                                            foreach ($reactions as $type => $emoji) {
                                                                $count = $forumC->getReactionCount('message', $message['id'], $type);
                                                                $hasReacted = $pdo->prepare("SELECT id FROM reactions WHERE message_id = :message_id AND user_id = :user_id AND reaction_type = :type");
                                                                $hasReacted->execute(['message_id' => $message['id'], 'user_id' => 1, 'type' => $type]);
                                                                $active = $hasReacted->fetch() ? 'active' : '';
                                                                ?>
                                                                <button class="reaction-btn <?php echo $active; ?>" data-type="<?php echo $type; ?>" data-target="message" data-id="<?php echo $message['id']; ?>" title="<?php echo ucfirst($type); ?>">
                                                                    <span><?php echo $emoji; ?></span>
                                                                    <?php if ($count > 0): ?>
                                                                        <span class="reaction-count"><?php echo $count; ?></span>
                                                                    <?php endif; ?>
                                                                </button>
                                                                <span class="reaction-feedback" id="message-<?php echo $message['id']; ?>-<?php echo $type; ?>-feedback"></span>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                            <input type="hidden" name="new_message" value="1">
                                            <div class="mb-3">
                                                <label for="new_message_<?php echo $forum['id']; ?>" class="form-label">Your Reply</label>
                                                <textarea class="form-control" id="new_message_<?php echo $forum['id']; ?>" name="message" rows="3" placeholder="Type your reply..."></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="message_image_<?php echo $forum['id']; ?>" class="form-label">Image (Optional)</label>
                                                <input type="file" class="form-control" id="message_image_<?php echo $forum['id']; ?>" name="image" accept="image/jpeg,image/png">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Send Reply</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editModal<?php echo $forum['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $forum['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $forum['id']; ?>">
                                            Edit Discussion: <?php echo htmlspecialchars($forum['titre']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="editForumForm<?php echo $forum['id']; ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateEditForm(this, <?php echo $forum['id']; ?>)">
                                            <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                            <input type="hidden" name="edit_forum" value="1">
                                            <div class="mb-3">
                                                <label for="edit_titre_<?php echo $forum['id']; ?>" class="form-label">Title</label>
                                                <input type="text" class="form-control" id="edit_titre_<?php echo $forum['id']; ?>" name="titre" value="<?php echo htmlspecialchars($forum['titre']); ?>">
                                                <div class="input-error" id="edit_titreError_<?php echo $forum['id']; ?>"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_message_<?php echo $forum['id']; ?>" class="form-label">Message</label>
                                                <textarea class="form-control" id="edit_message_<?php echo $forum['id']; ?>" name="message" rows="4"><?php echo htmlspecialchars($forum['first_message'] ?? ''); ?></textarea>
                                                <div class="input-error" id="edit_messageError_<?php echo $forum['id']; ?>"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_category_<?php echo $forum['id']; ?>" class="form-label">Category</label>
                                                <input type="text" class="form-control" id="edit_category_<?php echo $forum['id']; ?>" name="category" value="<?php echo htmlspecialchars($forum['category']); ?>">
                                                <div class="input-error" id="edit_categoryError_<?php echo $forum['id']; ?>"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_image_<?php echo $forum['id']; ?>" class="form-label">Forum Image (Optional)</label>
                                                <input type="file" class="form-control" id="edit_image_<?php echo $forum['id']; ?>" name="image" accept="image/jpeg,image/png">
                                                <?php if (!empty($forum['image'])): ?>
                                                    <small>Current: <a href="<?php echo htmlspecialchars($forum['image']); ?>" target="_blank">View Image</a></small>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="table" value="forums">
                                                        <input type="hidden" name="id" value="<?php echo $forum['id']; ?>">
                                                        <input type="hidden" name="column" value="image">
                                                        <input type="hidden" name="delete_image" value="1">
                                                        <button type="submit" class="btn btn-danger btn-sm mt-2">Delete Image</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_message_image_<?php echo $forum['id']; ?>" class="form-label">Message Image (Optional)</label>
                                                <input type="file" class="form-control" id="edit_message_image_<?php echo $forum['id']; ?>" name="message_image" accept="image/jpeg,image/png">
                                                <?php if (!empty($forum['message_image'])): ?>
                                                    <small>Current: <a href="<?php echo htmlspecialchars($forum['message_image']); ?>" target="_blank">View Image</a></small>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="table" value="messages">
                                                        <input type="hidden" name="id" value="<?php echo $forum['id']; ?>">
                                                        <input type="hidden" name="column" value="image">
                                                        <input type="hidden" name="delete_image" value="1">
                                                        <button type="submit" class="btn btn-danger btn-sm mt-2">Delete Image</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer id="footer" class="footer">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> <strong>InnoConnect</strong>. All Rights Reserved.</p>
        </div>
    </footer>

    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <button id="open-chatbot-btn" class="chatbot-float-btn" title="Open Chatbot">
        <i class="bi bi-robot"></i>
    </button>
    <div id="chatbot-modal" class="chatbot-modal">
        <div class="chatbot-modal-content">
            <div class="chatbot-header">
                <div class="chatbot-avatar"><i class="bi bi-robot"></i></div>
                <span class="chatbot-title">InnoConnect Chatbot</span>
                <span class="chatbot-close" id="close-chatbot-modal" title="Close">×</span>
            </div>
            <div id="chatbot-messages" class="chatbot-messages"></div>
            <div class="chatbot-input-row">
                <textarea id="chatbot-input" class="form-control chatbot-input" rows="1" placeholder="Type your message..."></textarea>
                <button id="chatbot-send" class="btn btn-primary chatbot-send-btn" title="Send"><i class="bi bi-send"></i></button>
                <span id="chatbot-loading" class="chatbot-loading-spinner" style="display:none;"><i class="bi bi-arrow-repeat"></i></span>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        AOS.init();
        function toggleForm() {
            const formContainer = document.getElementById('create-forum');
            const toggleBtn = document.querySelector('.toggle-form-btn');
            if (formContainer.style.display === 'none' || formContainer.style.display === '') {
                formContainer.style.display = 'block';
                setTimeout(() => formContainer.classList.add('active'), 10);
                toggleBtn.classList.add('active');
                toggleBtn.querySelector('span').textContent = 'Close Form';
                toggleBtn.querySelector('i').classList.add('bi-x-lg');
                toggleBtn.querySelector('i').classList.remove('bi-plus-lg');
                formContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                formContainer.classList.remove('active');
                setTimeout(() => formContainer.style.display = 'none', 300);
                toggleBtn.classList.remove('active');
                toggleBtn.querySelector('span').textContent = 'Start a Discussion';
                toggleBtn.querySelector('i').classList.add('bi-plus-lg');
                toggleBtn.querySelector('i').classList.remove('bi-x-lg');
                document.getElementById('createForumForm').reset();
                ['titreError', 'messageError', 'categoryError'].forEach(id => {
                    const errorEl = document.getElementById(id);
                    errorEl.textContent = '';
                    errorEl.classList.remove('show');
                });
            }
        }
        window.addEventListener('scroll', () => {
            const scrollTop = document.getElementById('scroll-top');
            if (window.scrollY > 300) {
                scrollTop.classList.add('visible');
            } else {
                scrollTop.classList.remove('visible');
            }
        });

        function validateCreateForm(form) {
            let isValid = true;
            const errors = {
                titre: '',
                message: '',
                category: ''
            };

            const titre = form.titre.value.trim();
            if (!titre || titre.length < 3 || titre.length > 50 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(titre)) {
                errors.titre = 'Title must be 3-50 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            const message = form.message.value.trim();
            if (!message || message.length < 10 || message.length > 500 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(message)) {
                errors.message = 'Message must be 10-500 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            const category = form.category.value.trim();
            if (!category || category.length < 3 || category.length > 50 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(category)) {
                errors.category = 'Category must be 3-50 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            ['titre', 'message', 'category'].forEach(field => {
                const errorEl = document.getElementById(`${field}Error`);
                errorEl.textContent = errors[field];
                errorEl.classList.toggle('show', !!errors[field]);
            });

            return isValid;
        }

        function validateEditForm(form, forumId) {
            let isValid = true;
            const errors = {
                titre: '',
                message: '',
                category: ''
            };

            const titre = form.titre.value.trim();
            if (!titre || titre.length < 3 || titre.length > 50 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(titre)) {
                errors.titre = 'Title must be 3-50 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            const message = form.message.value.trim();
            if (!message || message.length < 10 || message.length > 500 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(message)) {
                errors.message = 'Message must be 10-500 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            const category = form.category.value.trim();
            if (!category || category.length < 3 || category.length > 50 || !/^[A-Za-z0-9\s.,!?\'"-]+$/.test(category)) {
                errors.category = 'Category must be 3-50 characters and contain only letters, numbers, spaces, and basic punctuation.';
                isValid = false;
            }

            ['titre', 'message', 'category'].forEach(field => {
                const errorEl = document.getElementById(`edit_${field}Error_${forumId}`);
                errorEl.textContent = errors[field];
                errorEl.classList.toggle('show', !!errors[field]);
            });

            return isValid;
        }

        $(document).ready(function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery not loaded');
                alert('jQuery failed to load. Please check your internet connection or CDN.');
                return;
            }

            // Initialize modals
            $('.edit-forum-btn').on('click', function() {
                const forumId = $(this).data('forum-id');
                console.log('Opening edit modal for forum ID: ' + forumId);
                $(`#editModal${forumId}`).modal('show');
            });

            // Handle reactions
            $('.reaction-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                const type = button.data('type');
                const target = button.data('target');
                const id = button.data('id');
                const feedback = $('#' + target + '-' + id + '-' + type + '-feedback');
                const reactionButtons = button.closest('.reaction-buttons').find('.reaction-btn');
                console.log('Sending AJAX:', { reaction_type: type, target_type: target, target_id: id });
                feedback.text(button.hasClass('active') ? 'Removing...' : 'Adding...').show();
                $.ajax({
                    url: './forumview.php',
                    method: 'POST',
                    data: { reaction_type: type, target_type: target, target_id: id },
                    success: function(response) {
                        console.log('Response:', response);
                        feedback.hide();
                        if (response.success) {
                            feedback.text(response.message).show().delay(2000).fadeOut();
                            if (response.message === 'Reaction added') {
                                reactionButtons.removeClass('active');
                                button.addClass('active');
                            } else if (response.message === 'Reaction removed') {
                                button.removeClass('active');
                            }
                            ['like', 'love', 'haha'].forEach(function(reactionType) {
                                const countSpan = button.closest('.reaction-buttons')
                                    .find(`.reaction-btn[data-type="${reactionType}"] .reaction-count`);
                                const count = response.counts[reactionType];
                                if (count > 0) {
                                    if (countSpan.length) {
                                        countSpan.text(count);
                                    } else {
                                        button.closest('.reaction-buttons')
                                            .find(`.reaction-btn[data-type="${reactionType}"]`)
                                            .append(`<span class="reaction-count">${count}</span>`);
                                    }
                                } else {
                                    countSpan.remove();
                                }
                            });
                        } else {
                            feedback.text(response.message).show().delay(2000).fadeOut();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.status, error, xhr.responseText);
                        feedback.text('Failed to process reaction: ' + error).show().delay(2000).fadeOut();
                    },
                    dataType: 'json'
                });
            });

            // Debounced search
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                const spinner = $('.loading-spinner');
                spinner.addClass('active');
                const searchValue = $(this).val();
                if (searchValue.length > 0 && searchValue.length < 3) {
                    spinner.removeClass('active');
                    return;
                }
                searchTimeout = setTimeout(() => {
                    const sortValue = $('#sortSelect').val();
                    window.location.href = `?search=${encodeURIComponent(searchValue)}&sort=${sortValue}`;
                }, 500);
                setTimeout(() => spinner.removeClass('active'), 1000);
            });

            // Handle sort change
            $('#sortSelect').on('change', function() {
                const sortValue = $(this).val();
                const searchValue = $('#searchInput').val();
                window.location.href = `?search=${encodeURIComponent(searchValue)}&sort=${sortValue}`;
            });

            $('#open-chatbot-btn').on('click', function() {
                $('#chatbot-modal').addClass('active');
            });
            $('#close-chatbot-modal').on('click', function() {
                $('#chatbot-modal').removeClass('active');
            });
            $('#chatbot-send').on('click', function() {
                sendChatbotMessage();
            });
            $('#chatbot-input').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendChatbotMessage();
                }
            });
            function sendChatbotMessage() {
                var msg = $('#chatbot-input').val().trim();
                if (!msg) return;
                $('#chatbot-messages').append('<div><b>You:</b> ' + $('<div>').text(msg).html() + '</div>');
                $('#chatbot-input').val('');
                $('#chatbot-send').prop('disabled', true);
                $('#chatbot-loading').show();
                $.ajax({
                    url: 'chatbot.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        message: msg,
                        forum_id: 0,
                        user_id: 1
                    }),
                    success: function(res) {
                        var reply = res && res.message ? res.message : 'No response.';
                        $('#chatbot-messages').append('<div><b>Bot:</b> ' + $('<div>').text(reply).html() + '</div>');
                        $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
                    },
                    error: function(xhr) {
                        $('#chatbot-messages').append('<div style="color:red"><b>Bot:</b> Error contacting chatbot.</div>');
                    },
                    complete: function() {
                        $('#chatbot-send').prop('disabled', false);
                        $('#chatbot-loading').hide();
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>