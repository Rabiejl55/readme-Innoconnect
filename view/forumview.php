<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log('Starting forumview.php', 3, 'C:\xampp2\logs\debug.log');

// Set default timezone (adjust to your server's timezone if needed)
date_default_timezone_set('UTC');

require_once '../controller/forumc.php';
require_once '../Model/forum.php';

$forumC = new ForumC();
$forums = $forumC->afficherForums();
$errorMessage = '';

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
    // Allow letters, numbers, spaces, and common punctuation
    if (!preg_match('/^[A-Za-z0-9\s.,!?\'"-]+$/', $data)) {
        $errors[] = "$fieldName contains invalid characters.";
        return false;
    }
    return $data;
}

function validateImage($file, $fieldName, &$errors) {
    if ($file['size'] == 0 || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return null; // No new image uploaded, preserve existing
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
        error_log("Uploaded $fieldName to $targetPath", 3, 'C:\xampp2\logs\debug.log');
        return $targetPath;
    }
    $errors[] = "Failed to upload $fieldName.";
    return false;
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
                header("Location: forumview.php");
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
    error_log("Create forum POST: " . print_r($_POST, true), 3, 'C:\xampp2\logs\debug.log');
    error_log("Create forum FILES: " . print_r($_FILES, true), 3, 'C:\xampp2\logs\debug.log');
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
            header("Location: forumview.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to create discussion: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle edit forum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['titre'], $_POST['message'], $_POST['category'], $_POST['edit_forum'])) {
    error_log("Edit forum POST: " . print_r($_POST, true), 3, 'C:\xampp2\logs\debug.log');
    error_log("Edit forum FILES: " . print_r($_FILES, true), 3, 'C:\xampp2\logs\debug.log');
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
            // Preserve existing images if no new ones uploaded
            $finalImage = $image !== false ? $image : $existingForum['image'];
            $finalMessageImage = $message_image !== false ? $message_image : $existingForum['message_image'];
            $forum = new Forum($titre, $message, $category, 1, $finalImage, $finalMessageImage);
            $forum->setId($forum_id);
            $forumC->modifierForum($forum);
            header("Location: forumview.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to update discussion: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['message'], $_POST['new_message'])) {
    error_log("New message POST: " . print_r($_POST, true), 3, 'C:\xampp2\logs\debug.log');
    error_log("New message FILES: " . print_r($_FILES, true), 3, 'C:\xampp2\logs\debug.log');
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
            header("Location: forumview.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to add reply: " . $e->getMessage();
        }
    }
    $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
}

// Handle delete image
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_image'], $_POST['table'], $_POST['id'], $_POST['column'])) {
    error_log("Delete image POST: " . print_r($_POST, true), 3, 'C:\xampp2\logs\debug.log');
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
                header("Location: forumview.php");
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
    error_log("Reaction request: user_id=$user_id, target_type=$target_type, target_id=$target_id, reaction_type=$reaction_type", 3, 'C:\xampp2\logs\debug.log');

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
            error_log("Reaction response: " . json_encode($response), 3, 'C:\xampp2\logs\debug.log');
            echo json_encode($response);
        } catch (Exception $e) {
            $errors[] = "Server error: " . $e->getMessage();
            $response = ['success' => false, 'message' => implode(', ', $errors)];
            error_log("Reaction error: " . json_encode($response), 3, 'C:\xampp2\logs\debug.log');
            echo json_encode($response);
        }
    } else {
        $response = ['success' => false, 'message' => implode(', ', $errors)];
        error_log("Reaction validation error: " . json_encode($response), 3, 'C:\xampp2\logs\debug.log');
        echo json_encode($response);
    }
    exit;
}

function timeAgo($datetime) {
    try {
        $date = new DateTime($datetime);
        // Format as "Month Day, Year, HH:MM" (e.g., "April 26, 2025, 14:30")
        return $date->format('F j, Y, H:i');
    } catch (Exception $e) {
        error_log("timeAgo error: " . $e->getMessage(), 3, 'C:\xampp2\logs\debug.log');
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .forum-post:hover {
            transform: translateY(-5px);
        }
        .forum-post .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .forum-post .post-header .user-info {
            font-weight: 600;
            color: #6f42c1;
        }
        .forum-post .post-header .timestamp {
            color: #666;
            font-size: 0.9em;
            margin-left: 10px;
            white-space: nowrap;
        }
        .forum-post .post-title {
            font-size: 1.25em;
            font-weight: 600;
            color: #6f42c1;
            margin-bottom: 5px;
        }
        .forum-post .category {
            background-color: #17a2b8;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9em;
            display: inline-block;
            margin-bottom: 10px;
        }
        .forum-post .post-content {
            margin-bottom: 10px;
        }
        .forum-post .post-image {
            max-width: 100%;
            border-radius: 10px;
            margin: 10px 0;
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
        @media (max-width: 768px) {
            .forum-post, .message-post {
                padding: 15px;
            }
            .btn-sm {
                font-size: 0.8em;
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
        }
    </style>
</head>
<body data-aos-easing="ease" data-aos-duration="1000" data-aos-delay="0">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="../view/starter-page.html" class="logo d-flex align-items-center me-auto">
                <img src="../assets/img/innoconnect.jpg" alt="InnoConnect Logo" class="img-fluid" style="max-height: 45px;">
                <h1 class="sitename">InnoConnect</h1>
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="../view/starter-page.html">Home</a></li>
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

            <div class="forum-list" data-aos="fade-up" data-aos-delay="200">
                <h2 class="mb-4">Ongoing Discussions</h2>
                <?php if (empty($forums)): ?>
                    <div class="alert alert-info text-center">No discussions available yet. Start one using the button above!</div>
                <?php else: ?>
                    <?php foreach ($forums as $forum): ?>
                        <div class="forum-post">
                            <div class="post-header">
                                <span class="user-info">User <?php echo htmlspecialchars($forum['user_id'] ?? 'Unknown'); ?></span>
                                <span class="timestamp"><?php echo timeAgo($forum['date_creation']); ?></span>
                            </div>
                            <div class="post-title"><?php echo htmlspecialchars($forum['titre']); ?></div>
                            <span class="category"><?php echo htmlspecialchars($forum['category']); ?></span>
                            <div class="post-content"><?php echo nl2br(htmlspecialchars($forum['message'] ?? '')); ?></div>
                            <?php if (!empty($forum['image'])): ?>
                                <img src="<?php echo htmlspecialchars($forum['image']); ?>" class="post-image" alt="Forum Image">
                            <?php endif; ?>
                            <?php if (!empty($forum['message_image'])): ?>
                                <img src="<?php echo htmlspecialchars($forum['message_image']); ?>" class="post-image" alt="Message Image">
                            <?php endif; ?>
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
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $forum['id']; ?>">
                                    <i class="bi bi-chat-dots"></i> Reply
                                </button>
                                <button class="btn btn-primary btn-sm edit-forum-btn" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $forum['id']; ?>" data-forum-id="<?php echo $forum['id']; ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="?delete=<?php echo $forum['id']; ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
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
                                                <textarea class="form-control" id="edit_message_<?php echo $forum['id']; ?>" name="message" rows="4"><?php echo htmlspecialchars($forum['message'] ?? ''); ?></textarea>
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
                                                <?php
                                                $messages = $forumC->getMessagesByForumId($forum['id']);
                                                $firstMessage = !empty($messages) ? $messages[0] : null;
                                                if ($firstMessage && !empty($firstMessage['image'])): ?>
                                                    <small>Current: <a href="<?php echo htmlspecialchars($firstMessage['image']); ?>" target="_blank">View Image</a></small>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="table" value="messages">
                                                        <input type="hidden" name="id" value="<?php echo $firstMessage['id']; ?>">
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
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>