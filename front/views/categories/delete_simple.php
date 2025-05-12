<?php
// Vérifier si une session est active, sinon en démarrer une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Supprimer une Catégorie - inoconnect (Version Simple)</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding-top: 80px;
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .card-header {
      background-color: #dc3545;
      color: white;
      font-weight: bold;
    }
    .delete-icon {
      font-size: 4rem;
      color: #dc3545;
      display: block;
      margin: 0 auto 1.5rem;
      text-align: center;
    }
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
    }
    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }
  </style>
</head>

<body>
  <header class="fixed-top bg-white shadow-sm">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <h1 class="h4 mb-0">inoconnect - Gestion des Catégories</h1>
      <nav>
        <a href="index.php?controller=category&action=index" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
      </nav>
    </div>
  </header>

  <main>
    <div class="container py-4">
      <!-- Notifications -->
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header py-3">
              <h2 class="mb-0 h5"><i class="bi bi-trash me-2"></i>Supprimer la catégorie</h2>
            </div>
            <div class="card-body p-4 text-center">
              <i class="bi bi-exclamation-triangle-fill delete-icon"></i>
              <h3 class="mb-4">Êtes-vous sûr de vouloir supprimer cette catégorie ?</h3>
              
              <div class="card mb-4 bg-light">
                <div class="card-body">
                  <h4 class="h5 mb-3"><?= htmlspecialchars($category['nom']) ?></h4>
                  <div class="row mb-2">
                    <div class="col-md-6 text-md-end fw-bold">ID :</div>
                    <div class="col-md-6 text-md-start"><?= $category['id_categorie'] ?></div>
                  </div>
                  <?php if (!empty($category['description'])): ?>
                  <div class="row mb-2">
                    <div class="col-md-6 text-md-end fw-bold">Description :</div>
                    <div class="col-md-6 text-md-start"><?= htmlspecialchars($category['description']) ?></div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <p class="text-danger mb-4">Cette action est irréversible et supprimera définitivement cette catégorie.</p>
              <p class="text-warning mb-4"><strong>Attention :</strong> La suppression de cette catégorie peut affecter les projets associés.</p>
              
              <form action="index.php?controller=category&action=destroy" method="POST" class="d-inline">
                <input type="hidden" name="id_categorie" value="<?= $category['id_categorie'] ?>">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                  <a href="index.php?controller=category&action=index" class="btn btn-secondary btn-lg px-4">
                    <i class="bi bi-x-lg me-2"></i>Annuler
                  </a>
                  <button type="submit" class="btn btn-danger btn-lg px-4">
                    <i class="bi bi-trash-fill me-2"></i>Confirmer la suppression
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-light text-center py-4 mt-5">
    <div class="container">
      <p class="mb-0">© 2024 inoconnect - Tous droits réservés</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 