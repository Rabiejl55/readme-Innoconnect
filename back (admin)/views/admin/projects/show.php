<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Détails du projet</h6>
          <div>
            <a href="index.php?controller=project&action=edit&id=<?php echo $project['id_projet']; ?>" class="btn btn-sm btn-warning">Modifier</a>
            <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary">Retour à la liste</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <h5 class="mb-3"><?php echo htmlspecialchars($project['titre']); ?></h5>
            
            <div class="mb-4">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder">Description</h6>
              <p class="text-sm mb-0">
                <?php echo nl2br(htmlspecialchars($project['description'])); ?>
              </p>
            </div>
            
            <div class="row mb-4">
              <div class="col-md-6">
                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Catégorie</h6>
                <p class="text-sm mb-0"><?php echo htmlspecialchars($project['categorie_nom']); ?></p>
              </div>
              <div class="col-md-6">
                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Montant demandé</h6>
                <p class="text-sm mb-0"><?php echo number_format($project['montant_demande'], 2, ',', ' '); ?> €</p>
              </div>
            </div>
            
            <div class="row mb-4">
              <div class="col-md-6">
                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Date de soumission</h6>
                <p class="text-sm mb-0"><?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?></p>
              </div>
              <div class="col-md-6">
                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Statut</h6>
                <span class="badge badge-sm bg-gradient-<?php 
                  if($project['statut'] == 'Approuvé') {
                    echo 'success';
                  } elseif($project['statut'] == 'Refusé') {
                    echo 'danger';
                  } else {
                    echo 'warning';
                  }
                  ?>">
                  <?php echo htmlspecialchars($project['statut']); ?>
                </span>
              </div>
            </div>
            
            <?php if($project['statut'] == 'Soumis'): ?>
            <div class="mb-4">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder">Actions</h6>
              <div class="d-flex mt-2">
                <form action="index.php?controller=project&action=changeStatus" method="post" class="me-2">
                  <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
                  <input type="hidden" name="statut" value="Approuvé">
                  <button type="submit" class="btn btn-sm btn-success">Approuver</button>
                </form>
                <form action="index.php?controller=project&action=changeStatus" method="post">
                  <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
                  <input type="hidden" name="statut" value="Refusé">
                  <button type="submit" class="btn btn-sm btn-danger">Refuser</button>
                </form>
              </div>
            </div>
            <?php endif; ?>
          </div>
          
          <div class="col-md-4">
            <div class="card card-profile">
              <img src="assets/img/bg-profile.jpg" alt="Image d'arrière-plan" class="card-img-top">
              <div class="row justify-content-center">
                <div class="col-4 col-lg-4 order-lg-2">
                  <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                    <a href="javascript:;">
                      <img src="assets/img/team-2.jpg" class="rounded-circle img-fluid border border-2 border-white">
                    </a>
                  </div>
                </div>
              </div>
              <div class="card-body pt-0">
                <div class="text-center mt-4">
                  <h5>
                    <?php echo htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']); ?>
                  </h5>
                  <div class="h6 font-weight-300">
                    <i class="ni location_pin mr-2"></i>Soumis le <?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?>
                  </div>
                  <div class="h6 mt-3">
                    <i class="ni business_briefcase-24 mr-2"></i>Projet <?php echo htmlspecialchars($project['statut']); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?> 