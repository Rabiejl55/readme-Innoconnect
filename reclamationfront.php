<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Claims - InnoConnect</title>

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Raleway:wght@400;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f0f0;
        }

        .form-label, h2, h3 {
            color: #007bff;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
        }

        .reclamation-card {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }

        .status-pending {
            color: #ffc107;
        }

        .status-resolved {
            color: #28a745;
        }

        .status-rejected {
            color: #dc3545;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .submit-btn-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        /* Scroll to Top Button */
        #scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            display: none;
            font-size: 50px;
            color: #007bff;
            cursor: pointer;
            transition: opacity 0.3s ease-in-out;
        }

        #scroll-top:hover {
            color: #0056b3;
        }

        .modal-content {
            background-color: #f9f9f9;
        }

        .modal-header {
            border-bottom: 1px solid #ddd;
            background-color: #007bff;
            color: white;
        }

        .modal-header .btn-close {
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .character-count {
            font-size: 0.8em;
            margin-top: 5px;
            color: #666;
        }
        .character-count.exceeded {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body class="starter-page-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <h1 class="sitename">InnoConnect</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero"></a></li>
                    <li><a href="#about"></a></li>
                    <li><a href="#services"></a></li>
                    <li><a href="#admin"></a></li>
                    <li><a href="#portfolio"></a></li>
                    <li><a href="#team"></a></li>
                    <li><a href="#contact"></a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container mt-5 form-container">
            <?php
            require_once(__DIR__ . '/../../controller/ReclamationController.php');
            require_once(__DIR__ . '/../../model/Reclamation.php');

            $controller = new ReclamationController();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'add':
                            $date = date('Y-m-d H:i:s');
                            $description = $_POST['description'];
                            $etat = 'En attente';
                            
                            $reclamation = new Reclamation($date, $description, $etat);
                            
                            if ($controller->addReclamation($reclamation)) {
                                echo '<div class="alert alert-success">Réclamation ajoutée avec succès!</div>';
                            } else {
                                if (isset($_SESSION['errors'])) {
                                    echo '<div class="alert alert-danger">';
                                    foreach ($_SESSION['errors'] as $error) {
                                        echo htmlspecialchars($error) . '<br>';
                                    }
                                    echo '</div>';
                                    unset($_SESSION['errors']);
                                }
                            }
                            break;

                        case 'edit':
                            if (isset($_POST['id'], $_POST['description'])) {
                                $reclamation = $controller->getReclamationById($_POST['id']);
                                if ($reclamation) {
                                    if ($controller->updateReclamation(
                                        $_POST['id'],
                                        $reclamation['date_reclamation'],
                                        $_POST['description'],
                                        $reclamation['etat_reclamation']
                                    )) {
                                        echo '<div class="alert alert-success">Réclamation modifiée avec succès!</div>';
                                    } else {
                                        if (isset($_SESSION['errors'])) {
                                            echo '<div class="alert alert-danger">';
                                            foreach ($_SESSION['errors'] as $error) {
                                                echo htmlspecialchars($error) . '<br>';
                                            }
                                            echo '</div>';
                                            unset($_SESSION['errors']);
                                        } else {
                                            echo '<div class="alert alert-danger">Erreur lors de la modification de la réclamation.</div>';
                                        }
                                    }
                                }
                            }
                            break;

                        case 'delete':
                            if (isset($_POST['id'])) {
                                if ($controller->deleteReclamation($_POST['id'])) {
                                    echo '<div class="alert alert-success">Réclamation supprimée avec succès!</div>';
                                } else {
                                    echo '<div class="alert alert-danger">Erreur lors de la suppression de la réclamation.</div>';
                                }
                            }
                            break;
                    }
                }
            }

            $reclamations = $controller->getReclamations();
            ?>

            <h2>Manage your Claims</h2>

            <!-- Add Reclamation Form -->
            <div class="reclamation-card">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="description" class="form-label">Claim Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                placeholder="Write your claim (25 characters max)"></textarea>
                    </div>
                    <div class="submit-btn-container">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>

            <!-- List of Reclamations -->
            <div class="mt-5">
                <h3>Your Claims</h3>
                <?php foreach ($reclamations as $reclamation): ?>
                    <div class="reclamation-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">
                                    <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($reclamation['date_reclamation']))); ?>
                                </h6>
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($reclamation['description_reclamation'])); ?></p>
                            </div>
                            <div>
                                <?php
                                $statusClass = '';
                                $statusIcon = '';
                                switch(strtolower($reclamation['etat_reclamation'])) {
                                    case 'en attente':
                                        $statusClass = 'status-pending';
                                        $statusIcon = 'clock';
                                        $status = 'Pending';
                                        break;
                                    case 'résolu':
                                        $statusClass = 'status-resolved';
                                        $statusIcon = 'check-circle';
                                        $status = 'Resolved';
                                        break;
                                    case 'rejeté':
                                        $statusClass = 'status-rejected';
                                        $statusIcon = 'times-circle';
                                        $status = 'Rejected';
                                        break;
                                    default:
                                        $status = $reclamation['etat_reclamation'];
                                }
                                ?>
                                <span class="<?php echo $statusClass; ?>">
                                    <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal<?php echo $reclamation['id_reclamation']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this claim?');">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $reclamation['id_reclamation']; ?>" tabindex="-1" 
                             aria-labelledby="editModalLabel<?php echo $reclamation['id_reclamation']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $reclamation['id_reclamation']; ?>">
                                            Edit Claim
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                            <div class="mb-3">
                                                <label for="description<?php echo $reclamation['id_reclamation']; ?>" class="form-label">
                                                    Claim Description
                                                </label>
                                                <textarea class="form-control" 
                                                          id="description<?php echo $reclamation['id_reclamation']; ?>" 
                                                          name="description" 
                                                          rows="4"
                                                          placeholder="25 characters maximum"
                                                          ><?php echo htmlspecialchars($reclamation['description_reclamation']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer id="footer" class="footer accent-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-5 col-md-12 footer-about">
                    <a href="index.html" class="logo d-flex align-items-center">
                        <i class="bi bi-arrow-left" style="font-size: 60px; font-weight: bold; color: white;"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <a href="#" id="scroll-top" title="Scroll to Top">
        <i class="bi bi-arrow-up-circle-fill"></i>
    </a>

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                const textarea = form.querySelector('textarea[name="description"]');
                if (textarea) {
                    // Create character counter
                    const countDiv = document.createElement('div');
                    countDiv.className = 'character-count';
                    textarea.parentNode.insertBefore(countDiv, textarea.nextSibling);

                    // Create error message div
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-danger mt-1 small';
                    textarea.parentNode.insertBefore(errorDiv, countDiv);

                    // Update character counter
                    function updateCharacterCount() {
                        const length = textarea.value.length;
                        const remaining = 25 - length;
                        
                        if (length <= 25) {
                            countDiv.textContent = `Characters remaining: ${remaining}`;
                            countDiv.classList.remove('exceeded');
                        } else {
                            countDiv.textContent = `Exceeded by ${length - 25} character(s)`;
                            countDiv.classList.add('exceeded');
                        }
                    }

                    // Input validation
                    textarea.addEventListener('input', function() {
                        updateCharacterCount();
                        // Clear error message when user starts typing
                        errorDiv.textContent = '';
                    });
                    
                    // Initialize counter
                    updateCharacterCount();

                    // Form submission validation
                    form.addEventListener('submit', function(e) {
                        const description = textarea.value.trim();
                        
                        if (description.length === 0) {
                            e.preventDefault();
                            errorDiv.textContent = 'Description cannot be empty';
                            return false;
                        }
                        
                        if (description.length > 25) {
                            e.preventDefault();
                            errorDiv.textContent = 'Description cannot exceed 25 characters';
                            return false;
                        }
                        
                        return true;
                    });
                }
            });
        });

        // Scroll to Top functionality
        const scrollTopBtn = document.getElementById('scroll-top');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 200) {
                scrollTopBtn.style.display = 'block';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });

        scrollTopBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
