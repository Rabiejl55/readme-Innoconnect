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
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Détails de la Catégorie</h1>
        
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
        
        <div class="card">
            <div class="card-header">
                Catégorie #<?= htmlspecialchars($category['id']) ?>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                <?php if (!empty($category['description'])): ?>
                    <p class="card-text"><?= htmlspecialchars($category['description']) ?></p>
                <?php else: ?>
                    <p class="card-text text-muted">Aucune description disponible.</p>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="index.php?controller=category&action=edit&id=<?= $category['id'] ?>" class="btn btn-primary">Modifier</a>
                    <a href="index.php?controller=category&action=delete&id=<?= $category['id'] ?>" class="btn btn-danger">Supprimer</a>
                    <a href="index.php?controller=category&action=index" class="btn btn-secondary">Retour à la liste</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 