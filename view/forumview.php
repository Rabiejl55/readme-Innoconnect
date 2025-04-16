    <?php
    require_once '../controller/forumc.php';
    require_once '../Model/forum.php';

    $forumC = new ForumC();
    $forums = $forumC->afficherForums();

    // Validation function
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
        if (!preg_match('/^[A-Za-z0-9\s]+$/', $data)) {
            $errors[] = "$fieldName can only contain letters, numbers, and spaces.";
            return false;
        }
        return $data;
    }

    // Ajout d’un nouveau forum
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['titre'], $_POST['message'], $_POST['category']) && !isset($_POST['forum_id'])) {
        $errors = [];
        $titre = validateInput($_POST['titre'], 50, 3, "Title", $errors);
        $message = validateInput($_POST['message'], 500, 10, "Message", $errors);
        $category = validateInput($_POST['category'], 50, 3, "Category", $errors);

        if (empty($errors)) {
            try {
                $forum = new Forum($titre, $message, $category, 1);
                $forumC->ajouterForum($forum);
                header("Location: forumview.php?success=Discussion created successfully");
                exit;
            } catch (Exception $e) {
                $errors[] = "Failed to create discussion: " . $e->getMessage();
            }
        }
        $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
    }

    // Modification d’un forum
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['titre'], $_POST['message'], $_POST['category'], $_POST['edit_forum'])) {
        $errors = [];
        $forum_id = filter_var($_POST['forum_id'], FILTER_VALIDATE_INT);
        $titre = validateInput($_POST['titre'], 50, 3, "Title", $errors);
        $message = validateInput($_POST['message'], 500, 10, "Message", $errors);
        $category = validateInput($_POST['category'], 50, 3, "Category", $errors);

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
                $forum = new Forum($titre, $message, $category, 1);
                $forum->setId($forum_id);
                $forumC->modifierForum($forum);
                header("Location: forumview.php?success=Discussion updated successfully");
                exit;
            } catch (Exception $e) {
                $errors[] = "Failed to update discussion: " . $e->getMessage();
            }
        }
        $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
    }

    // Ajout d’un message à un forum existant
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forum_id'], $_POST['message'], $_POST['new_message'])) {
        $errors = [];
        $forum_id = filter_var($_POST['forum_id'], FILTER_VALIDATE_INT);
        $message = validateInput($_POST['message'], 500, 10, "Message", $errors);

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
                $forumC->ajouterMessage($forum_id, 1, $message);
                header("Location: forumview.php?success=Reply added successfully");
                exit;
            } catch (Exception $e) {
                $errors[] = "Failed to add reply: " . $e->getMessage();
            }
        }
        $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
    }

    // Suppression d’un forum
    if (isset($_GET['delete'])) {
        $forum_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
        $errors = [];
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
                $forumC->supprimerForum($forum_id);
                header("Location: forumview.php?success=Discussion deleted successfully");
                exit;
            } catch (Exception $e) {
                $errors[] = "Failed to delete discussion: " . $e->getMessage();
            }
        }
        $errorMessage = implode('<br>', array_map('htmlspecialchars', $errors));
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forum Collaboratif - InnoConnect</title>
        <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&family=Raleway:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
        <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
        <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
        <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
        <link href="../assets/css/main.css" rel="stylesheet">
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
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
                margin: 20px 0;
                transition: transform 0.3s ease;
            }
            .form-container:hover {
                transform: translateY(-5px);
            }
            .forum-list {
                margin-top: 40px;
            }
            .forum-card {
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                padding: 20px;
                margin-bottom: 20px;
                transition: all 0.3s ease;
            }
            .forum-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            }
            .forum-card h5 {
                color: #6f42c1;
                font-weight: 600;
                margin-bottom: 10px;
            }
            .forum-card .category {
                background-color: #17a2b8;
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.9em;
            }
            .forum-card .actions {
                margin-top: 15px;
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
            .message-item {
                padding: 10px;
                background: #f8f9fa;
                border-radius: 8px;
                margin-bottom: 10px;
            }
            .message-item strong {
                color: #6f42c1;
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
            @media (max-width: 768px) {
                .forum-card {
                    padding: 15px;
                }
                .btn-sm {
                    font-size: 0.8em;
                }
                .form-container {
                    padding: 20px;
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
                        <li><a href="http://localhost/espace_comm/espace%20communotaire/view/index.html#services">Services</a></li>
                        <li><a href="#team">Team</a></li>
                        <li class="dropdown">
                            <a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                            <ul>
                                <li><a href="#">Dropdown 1</a></li>
                                <li class="dropdown">
                                    <a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                                    <ul>
                                        <li><a href="#">Deep Dropdown 1</a></li>
                                        <li><a href="#">Deep Dropdown 2</a></li>
                                        <li><a href="#">Deep Dropdown 3</a></li>
                                        <li><a href="#">Deep Dropdown 4</a></li>
                                        <li><a href="#">Deep Dropdown 5</a></li>
                                    </ul>
                                </li>
                                <li><a href="#">Dropdown 2</a></li>
                                <li><a href="#">Dropdown 3</a></li>
                                <li><a href="#">Dropdown 4</a></li>
                            </ul>
                        </li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>
            </div>
        </header>

        <main class="main" style="padding-top: 120px;">
            <div class="container">
                <!-- Feedback Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_GET['error']) || isset($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['error'] ?? $errorMessage ?? 'An error occurred.'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de création de discussion -->
                <div id="create-forum" class="form-container" data-aos="fade-up">
                    <h2 class="mb-4">Start a New Discussion</h2>
                    <form id="createForumForm" method="POST" onsubmit="return validateCreateForm(this)">
                        <div class="mb-3">
                            <label for="titre" class="form-label fw-bold">Title</label>
                            <input type="text" class="form-control" id="titre" name="titre" placeholder="Enter discussion title" data-error-id="titreError">
                            <div class="input-error" id="titreError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Share your thoughts..." data-error-id="messageError"></textarea>
                            <div class="input-error" id="messageError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold">Category</label>
                            <input type="text" class="form-control" id="category" name="category" placeholder="Enter category (e.g., General)" value="General" data-error-id="categoryError">
                            <div class="input-error" id="categoryError"></div>
                        </div>
                        <button type="submit" class="btn btn-custom">Submit Discussion</button>
                    </form>
                </div>

                <!-- Liste des forums -->
                <div class="forum-list" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="mb-4">Ongoing Discussions</h2>
                    <?php if (empty($forums)): ?>
                        <div class="alert alert-info text-center">No discussions available yet. Start one above!</div>
                    <?php else: ?>
                        <?php foreach ($forums as $forum): ?>
                            <div class="forum-card">
                                <h5><?php echo htmlspecialchars($forum['titre']); ?></h5>
                                <p><?php echo nl2br(htmlspecialchars($forum['message'])); ?></p>
                                <span class="category"><?php echo htmlspecialchars($forum['category']); ?></span>
                                <div class="actions">
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $forum['id']; ?>">
                                        <i class="bi bi-chat-dots"></i> View & Reply
                                    </button>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $forum['id']; ?>">
                                        <i class="bi bi-pencil"></i> Modify
                                    </button>
                                    <a href="?delete=<?php echo $forum['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this discussion?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>

                            <!-- Modal pour voir et ajouter un message -->
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
                                            <!-- Messages existants -->
                                            <h6 class="mb-3">Conversation</h6>
                                            <div class="existing-messages" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                                                <?php
                                                $messages = $forumC->getMessagesByForumId($forum['id']);
                                                if (empty($messages)) {
                                                    echo "<p class='text-muted'>No messages yet. Be the first to reply!</p>";
                                                } else {
                                                    foreach ($messages as $message) {
                                                        echo "<div class='message-item'>";
                                                        echo "<strong>User " . htmlspecialchars($message['user_id']) . ":</strong><br>";
                                                        echo "<p>" . nl2br(htmlspecialchars($message['message'])) . "</p>";
                                                        echo "<small class='text-muted'>" . (isset($message['created_at']) ? $message['created_at'] : 'Just now') . "</small>";
                                                        echo "</div>";
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <!-- Formulaire pour ajouter un message -->
                                            <form method="POST" action="">
                                                <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                                <input type="hidden" name="new_message" value="1">
                                                <div class="mb-3">
                                                    <label for="new_message_<?php echo $forum['id']; ?>" class="form-label fw-bold">Your Reply</label>
                                                    <textarea class="form-control" id="new_message_<?php echo $forum['id']; ?>" name="message" rows="3" placeholder="Type your reply..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Send Reply</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal pour modifier un forum -->
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
                                            <form id="editForumForm<?php echo $forum['id']; ?>" method="POST" onsubmit="return validateEditForm(this, <?php echo $forum['id']; ?>)">
                                                <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                                <input type="hidden" name="edit_forum" value="1">
                                                <div class="mb-3">
                                                    <label for="edit_titre_<?php echo $forum['id']; ?>" class="form-label fw-bold">Title</label>
                                                    <input type="text" class="form-control" id="edit_titre_<?php echo $forum['id']; ?>" name="titre" value="<?php echo htmlspecialchars($forum['titre']); ?>" data-error-id="edit_titreError_<?php echo $forum['id']; ?>">
                                                    <div class="input-error" id="edit_titreError_<?php echo $forum['id']; ?>"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_message_<?php echo $forum['id']; ?>" class="form-label fw-bold">Message</label>
                                                    <textarea class="form-control" id="edit_message_<?php echo $forum['id']; ?>" name="message" rows="4" data-error-id="edit_messageError_<?php echo $forum['id']; ?>"><?php echo htmlspecialchars($forum['message']); ?></textarea>
                                                    <div class="input-error" id="edit_messageError_<?php echo $forum['id']; ?>"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_category_<?php echo $forum['id']; ?>" class="form-label fw-bold">Category</label>
                                                    <input type="text" class="form-control" id="edit_category_<?php echo $forum['id']; ?>" name="category" value="<?php echo htmlspecialchars($forum['category']); ?>" data-error-id="edit_categoryError_<?php echo $forum['id']; ?>">
                                                    <div class="input-error" id="edit_categoryError_<?php echo $forum['id']; ?>"></div>
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
                <p>Designed by <a href="https://innoconnect.com/" class="text-blue">InnoConnect</a></p>
            </div>
        </footer>
        <!-- Scroll Top -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <!-- Scripts -->
        <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/vendor/aos/aos.js"></script>
        <script src="../assets/js/main.js"></script>
        <script src="../assets/js/forumverif.js"></script>
        <script>
            // Initialisation de AOS (animations sur défilement)
            AOS.init();
        </script>
    </body>
    </html>