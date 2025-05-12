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
  <title>Modifier un Projet - inoconnect (Version Simple)</title>
  
  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- AOS Animation CSS (optional, if not already loaded globally) -->
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">

  <style>
  body.futuristic-bg {
    background: linear-gradient(135deg, #f7f8fa 0%, #e6e6ff 100%) fixed;
    min-height: 100vh;
    position: relative;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding-top: 80px;
  }
  .bg-animated-futuristic {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: 0;
    pointer-events: none;
    background: radial-gradient(circle at 80% 20%, rgba(123,111,255,0.10) 0%, transparent 70%),
                radial-gradient(circle at 20% 80%, rgba(88,70,249,0.10) 0%, transparent 70%);
    animation: bgMove 12s ease-in-out infinite alternate;
  }
  @keyframes bgMove {
    0% { background-position: 80% 20%, 20% 80%; }
    100% { background-position: 60% 40%, 40% 60%; }
  }
  .futuristic-glass {
    background: rgba(255,255,255,0.85);
    box-shadow: 0 8px 32px 0 rgba(88,70,249,0.10), 0 1.5px 8px 0 rgba(123,111,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    border: 1.5px solid rgba(88,70,249,0.10);
    transition: box-shadow 0.3s, transform 0.3s;
    position: relative;
    overflow: hidden;
  }
  .futuristic-glass::before {
    content: '';
    position: absolute;
    top: -40px; left: -40px; right: -40px; bottom: -40px;
    background: radial-gradient(circle at 80% 20%, rgba(123,111,255,0.08) 0%, transparent 70%);
    z-index: 0;
    pointer-events: none;
  }
  .futuristic-glass:hover {
    box-shadow: 0 16px 48px 0 rgba(88,70,249,0.18), 0 4px 16px 0 rgba(123,111,255,0.12);
    transform: translateY(-2px) scale(1.01);
  }
  .futuristic-section-title {
    font-family: 'Raleway', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #5846f9;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0;
    text-shadow: 0 1px 0 #f8f9fa;
    z-index: 1;
  }
  .futuristic-section-title i {
    color: #7b6fff;
    font-size: 1.3em;
    filter: drop-shadow(0 1px 2px #e6e6ff);
  }
  .form-label {
    font-weight: 600;
    color: #7b6fff;
    letter-spacing: 0.2px;
  }
  .form-control, .form-select {
    border-radius: 8px;
    background: rgba(248,249,250,0.7);
    transition: box-shadow 0.2s, border-color 0.2s;
    box-shadow: 0 1px 4px rgba(88,70,249,0.04);
    border: 1.5px solid #e6e6ff;
    font-size: 1.08rem;
  }
  .form-control:focus, .form-select:focus {
    border-color: #5846f9;
    box-shadow: 0 0 0 2px #7b6fff33;
  }
  .input-group-text {
    background: #e6e6ff;
    border-radius: 8px 0 0 8px;
    border: none;
    color: #5846f9;
    font-weight: 600;
  }
  .btn-primary {
    background: linear-gradient(135deg, #5846f9, #7b6fff);
    border: none;
    font-weight: 600;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(88,70,249,0.08);
    border-radius: 8px;
    transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover, .btn-primary:focus {
    background: linear-gradient(135deg, #7b6fff, #5846f9);
    transform: scale(1.04);
    color: #fff;
  }
  .btn-secondary {
    border-radius: 8px;
  }
  .fab-save {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 9999;
    background: linear-gradient(135deg, #5846f9, #7b6fff);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 64px;
    height: 64px;
    box-shadow: 0 6px 24px rgba(88,70,249,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    cursor: pointer;
    transition: box-shadow 0.2s, transform 0.2s;
    outline: none;
  }
  .fab-save:hover, .fab-save:focus {
    box-shadow: 0 12px 32px rgba(123,111,255,0.22);
    transform: scale(1.08) rotate(3deg);
    color: #fff;
  }
  .fab-save[title] {
    position: fixed;
  }
  @media (max-width: 768px) {
    .fab-save {
      width: 48px;
      height: 48px;
      font-size: 1.4rem;
      bottom: 16px;
      right: 16px;
    }
  }
  .card-header {
    background: transparent;
    border-bottom: none;
    padding-bottom: 0;
  }
  .alert {
    border-radius: 10px;
    font-weight: 500;
    letter-spacing: 0.2px;
  }
  </style>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('futuristic-bg');
    if (window.AOS) {
      AOS.init({ duration: 800, once: true });
    }
    // Tooltip for floating action button
    var fab = document.getElementById('fab-save');
    if (fab && window.bootstrap) {
      new bootstrap.Tooltip(fab);
    }
    // Tooltips for form labels
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
      if (window.bootstrap) new bootstrap.Tooltip(el);
    });
    // Floating save button triggers form submit
    if (fab) {
      fab.addEventListener('click', function(e) {
        e.preventDefault();
        var form = document.getElementById('projectForm');
        if (form) form.submit();
      });
    }
  });
  </script>
</head>

<body>
  <!-- Animated Futuristic Background Overlay -->
  <div class="bg-animated-futuristic"></div>
  <header class="fixed-top bg-white shadow-sm">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <h1 class="h4 mb-0">inoconnect - Gestion de Projets</h1>
      <nav>
        <a href="index.php?controller=project&action=index" class="btn btn-outline-secondary" title="Retour à la liste" data-bs-toggle="tooltip">
          <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
      </nav>
    </div>
  </header>

  <main>
    <div class="container py-4">
      <!-- Notifications -->
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-down">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card futuristic-glass project-detail-anim" data-aos="fade-up">
            <div class="card-header py-3">
              <h2 class="mb-0 h5 futuristic-section-title"><i class="bi bi-pencil-square me-2"></i>Modifier le Projet (Version Simple)</h2>
            </div>
            <div class="card-body p-4">
              <form action="index.php?controller=project&action=update&id=<?= $project['id_projet'] ?>" method="POST" id="projectForm" class="needs-validation" novalidate>
                <input type="hidden" name="id_projet" value="<?= $project['id_projet'] ?>">
                <div class="mb-3">
                  <label for="titre" class="form-label" title="Titre du projet" data-bs-toggle="tooltip">Titre du projet <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($project['titre']) ?>">
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                  <label for="description" class="form-label" title="Description détaillée du projet" data-bs-toggle="tooltip">Description <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($project['description']) ?></textarea>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                  <label for="id_categorie" class="form-label" title="Catégorie du projet" data-bs-toggle="tooltip">Catégorie <span class="text-danger">*</span></label>
                  <select class="form-select" id="id_categorie" name="id_categorie">
                    <option value="" disabled>Choisir une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?= $category['id_categorie'] ?>" <?= ($project['id_categorie'] == $category['id_categorie'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($category['nom']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                  <label for="statut" class="form-label" title="Statut du projet" data-bs-toggle="tooltip">Statut <span class="text-danger">*</span></label>
                  <select class="form-select" id="statut" name="statut">
                    <option value="En attente" <?= ($project['statut'] == 'En attente' ? 'selected' : '') ?>>En attente</option>
                    <option value="En cours" <?= ($project['statut'] == 'En cours' ? 'selected' : '') ?>>En cours</option>
                    <option value="Terminé" <?= ($project['statut'] == 'Terminé' ? 'selected' : '') ?>>Terminé</option>
                    <option value="Annulé" <?= ($project['statut'] == 'Annulé' ? 'selected' : '') ?>>Annulé</option>
                  </select>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                  <label for="montant_demande" class="form-label" title="Montant demandé pour ce projet" data-bs-toggle="tooltip">Montant demandé (€) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">€</span>
                    <input type="number" class="form-control" id="montant_demande" name="montant_demande" min="0" step="100" value="<?= $project['montant_demande'] ?>">
                  </div>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                  <a href="index.php?controller=project&action=index" class="btn btn-secondary" title="Annuler et revenir à la liste" data-bs-toggle="tooltip">
                    <i class="bi bi-x"></i> Annuler
                  </a>
                  <button type="submit" class="btn btn-primary" title="Enregistrer les modifications" data-bs-toggle="tooltip">
                    <i class="bi bi-check-lg"></i> Enregistrer
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Floating Action Button (Save) -->
  <button id="fab-save" class="fab-save" title="Enregistrer les modifications" data-bs-toggle="tooltip" tabindex="0" aria-label="Enregistrer les modifications">
    <i class="bi bi-check-lg"></i>
  </button>

  <footer class="bg-light text-center py-4 mt-5">
    <div class="container">
      <p class="mb-0">© 2024 inoconnect - Tous droits réservés</p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- AOS Animation JS (optional, if not already loaded globally) -->
  <script src="assets/vendor/aos/aos.js"></script>
  <!-- Notre script de validation personnalisé -->
  <script src="assets/js/form-validation.js"></script>
</body>
</html> 