<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="container-fluid py-4">
  <div class="row mt-5">
    <div class="col-lg-8 col-md-10 mx-auto">
      <div class="card z-index-0">
        <div class="card-header text-center pt-4">
          <h2 class="display-2 text-gradient text-warning">404</h2>
          <h5>Page non trouvée</h5>
          <p class="lead">Oups! La page que vous cherchez n'existe pas.</p>
        </div>
        <div class="card-body text-center">
          <div class="mb-3">
            <i class="ni ni-compass-04 text-warning display-1"></i>
          </div>
          <p>Il semble que vous vous soyez perdu.</p>
          <a href="index.php" class="btn bg-gradient-primary mt-3">Retour à l'accueil</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?> 