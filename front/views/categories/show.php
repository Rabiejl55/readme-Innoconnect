<?php
// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the category exists
if (!isset($category) || empty($category)) {
    $_SESSION['error'] = "Catégorie non trouvée";
    header('Location: index.php?controller=category&action=index');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Catégorie</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
        }
        .header .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .header .nav-link {
            color: rgba(255, 255, 255, 0.85);
            margin: 0 0.8rem;
            transition: color 0.3s;
        }
        .header .nav-link:hover {
            color: white;
        }
        .btn-action {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        .btn-back {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        .btn-back:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-edit {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-edit:hover {
            background-color: #5649c8;
        }
        .btn-delete {
            background-color: transparent;
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }
        .btn-delete:hover {
            background-color: #e74c3c;
            color: white;
        }
        .section-title {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }
        .details-card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .category-subtitle {
            color: #6c757d;
            font-weight: normal;
        }
        .category-title {
            font-weight: bold;
            color: var(--dark-color);
        }
        .description-container {
            min-height: 100px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">inoconnect</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">About</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Portfolio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Team</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Projects</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Contact</a>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-light ms-3">Get Started</a>
                    </div>
                </div>
            </nav>
            <div class="mt-4">
                <h1>Détails de la Catégorie</h1>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <!-- Success and Error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="mb-4">
            <a href="index.php?controller=category&action=index" class="btn btn-action btn-back">
                <i class="fas fa-arrow-left me-2"></i> Retour à la liste
            </a>
            <a href="index.php?controller=category&action=edit&id=<?= $category['id_categorie'] ?>" class="btn btn-action btn-edit ms-2">
                <i class="fas fa-edit me-2"></i> Modifier
            </a>
            <a href="index.php?controller=category&action=delete&id=<?= $category['id_categorie'] ?>" class="btn btn-action btn-delete ms-2">
                <i class="fas fa-trash me-2"></i> Supprimer
            </a>
        </div>
        
        <!-- General Information -->
        <div class="card details-card">
            <div class="card-header bg-white">
                <h2 class="section-title mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Informations générales
                </h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="category-subtitle mb-1">ID de la catégorie:</p>
                        <p class="category-title"><?= htmlspecialchars($category['id_categorie'] ?? '') ?></p>
                    </div>
                    <div class="col-md-8">
                        <p class="category-subtitle mb-1">Nom:</p>
                        <p class="category-title"><?= htmlspecialchars($category['nom'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description -->
        <div class="card details-card">
            <div class="card-header bg-white">
                <h2 class="section-title mb-0">
                    <i class="fas fa-file-alt me-2 text-primary"></i> Description
                </h2>
            </div>
            <div class="card-body">
                <div class="description-container">
                    <?php if (!empty($category['description'])): ?>
                        <p><?= htmlspecialchars($category['description']) ?></p>
                    <?php else: ?>
                        <p class="text-muted">Aucune description disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2023 inoconnect. Tous droits réservés.</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
</html> 