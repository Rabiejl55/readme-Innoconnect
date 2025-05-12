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
  <title>Modifier une Catégorie - inoconnect (Version Simple)</title>
  
  <!-- Suppression immédiate des messages d'erreur par défaut -->
  <style>
    .invalid-feedback { display: none !important; }
    textarea.form-control.is-valid { background-image: none !important; }
  </style>
  
  <script>
    // Exécuté immédiatement pour cacher les messages d'erreur
    document.addEventListener('DOMContentLoaded', function() {
      // Supprimer tous les messages d'erreur
      document.querySelectorAll('.invalid-feedback').forEach(function(el) {
        el.textContent = '';
        el.style.display = 'none';
      });
      
      // Enlever toutes les classes de validation
      document.querySelectorAll('.form-control, .form-select').forEach(function(el) {
        el.classList.remove('is-valid', 'is-invalid');
      });
      
      // S'assurer que le formulaire a l'ID correct
      const form = document.querySelector('form');
      if (form && !form.id) {
        form.id = 'categoryForm';
      }
    });
  </script>
  
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
      background-color: #6f42c1;
      color: white;
      font-weight: bold;
    }
    .btn-primary {
      background-color: #6f42c1;
      border-color: #6f42c1;
    }
    .btn-primary:hover {
      background-color: #5a32a3;
      border-color: #5a32a3;
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
            <div class="card-header">
              <h5 class="card-title mb-0">Modifier la catégorie #<?= htmlspecialchars($category['id_categorie']) ?></h5>
            </div>
            <div class="card-body">
              <form action="index.php?controller=category&action=update" method="POST" id="categoryForm" class="needs-validation" novalidate>
                <input type="hidden" name="id_categorie" value="<?= $category['id_categorie'] ?>">
                
                <div class="mb-3">
                  <label for="nom" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom de la catégorie" value="<?= htmlspecialchars($category['nom']) ?>">
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control" id="description" name="description" rows="4" placeholder="Description (optionnelle)"><?= htmlspecialchars($category['description']) ?></textarea>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Enregistrer les modifications
                  </button>
                  <a href="index.php?controller=category&action=index" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Annuler
                  </a>
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

  <div>
    <!-- Bootstrap & JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Form Validation Script -->
    <script src="assets/js/form-validation.js"></script>
  </div>
</body>
</html> 