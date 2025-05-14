<?php
require_once '../controller/contractc.php';
require_once '../Model/contract.php';

$contractC = new ContractC();
$contracts = $contractC->afficherContracts();

// Ajout d’un nouveau contrat
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'], $_POST['description'], $_POST['type'])) {
    $contract = new Contract($_POST['nom'], $_POST['description'], $_POST['type'], 1); 
    $contractC->ajouterContract($contract);
    header("Location: contractview.php");
    exit;
}

// Suppression d’un contrat
if (isset($_GET['delete'])) {
    $contractC->supprimerContract($_GET['delete']);
    header("Location: contractview.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Contrats - InnoConnect</title>
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
        .contract-list {
            margin-top: 40px;
        }
        .contract-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .contract-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .contract-card h5 {
            color: #6f42c1;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .contract-card .type-investor {
            background-color: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .contract-card .type-innovator {
            background-color: #007bff;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .contract-card .actions {
            margin-top: 15px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9em;
            border-radius: 15px;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        footer {
            background: #6f42c1;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .contract-card {
                padding: 15px;
            }
            .btn-sm {
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body data-aos-easing="ease" data-aos-duration="1000" data-aos-delay="0">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="../view/starter-page.html" class="logo d-flex align-items-center me-auto">
            <img  src="../assets/img/innoconnect.jpg" alt="InnoConnect Logo" class="img-fluid" style="max-height: 45px;">
                <h1 class="sitename">InnoConnect</h1>
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="../view/starter-page.html">Home</a></li>
                    <li><a href="contractview.php" class="active">Contracts</a></li>
                    <li><a href="forumview.php">Forum</a></li>
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
            <!-- Formulaire d’ajout de contrat -->
            <div id="create-contract" class="form-container" data-aos="fade-up">
                <h2 class="mb-4">Add a New Contract</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="nom" class="form-label fw-bold">Name</label>
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Enter contract name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the contract..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label fw-bold">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" disabled selected>Select a type</option>
                            <option value="Investor">Investor</option>
                            <option value="Innovator">Innovator</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom">Submit Contract</button>
                </form>
            </div>

            <!-- Liste des contrats -->
            <div class="contract-list" data-aos="fade-up" data-aos-delay="200">
                <h2 class="mb-4">List of Contracts</h2>
                <?php if (empty($contracts)): ?>
                    <div class="alert alert-info text-center">No contracts available yet. Add one above!</div>
                <?php else: ?>
                    <?php foreach ($contracts as $contract): ?>
                        <div class="contract-card">
                            <h5><?php echo htmlspecialchars($contract['nom']); ?></h5>
                            <p><?php echo nl2br(htmlspecialchars($contract['description'])); ?></p>
                            <span class="type-<?php echo strtolower($contract['type']); ?>">
                                <?php echo htmlspecialchars($contract['type']); ?>
                            </span>
                            <div class="mt-2">
                                <small class="text-muted">Created on: <?php echo htmlspecialchars($contract['created_at']); ?></small>
                            </div>
                            <div class="actions">
                                <a href="?delete=<?php echo $contract['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this contract?');">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
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

    <!-- Scripts -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Initialisation de AOS (animations sur défilement)
        AOS.init();
    </script>
</body>
</html>