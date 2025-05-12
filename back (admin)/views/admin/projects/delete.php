<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <h6>Confirmer la suppression</h6>
      </div>
      <div class="card-body px-4 pt-4">
        <div class="alert alert-warning" role="alert">
          <h4 class="alert-heading">Attention!</h4>
          <p>Vous êtes sur le point de supprimer le projet <strong>"<?php echo htmlspecialchars($project['titre']); ?>"</strong>.</p>
          <p>Cette action est irréversible. Êtes-vous sûr de vouloir continuer?</p>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Détails du projet</h6>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <strong>ID:</strong> <?php echo $project['id_projet']; ?>
              </li>
              <li class="list-group-item">
                <strong>Titre:</strong> <?php echo htmlspecialchars($project['titre']); ?>
              </li>
              <li class="list-group-item">
                <strong>Catégorie:</strong> <?php echo htmlspecialchars($project['categorie_nom']); ?>
              </li>
              <li class="list-group-item">
                <strong>Montant demandé:</strong> <?php echo number_format($project['montant_demande'], 2, ',', ' '); ?> €
              </li>
              <li class="list-group-item">
                <strong>Date de soumission:</strong> <?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?>
              </li>
              <li class="list-group-item">
                <strong>Statut:</strong> 
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
              </li>
              <li class="list-group-item">
                <strong>Créé par:</strong> <?php echo htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']); ?>
              </li>
            </ul>
          </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
          <a href="index.php?controller=project&action=index" class="btn btn-secondary me-2">Annuler</a>
          <form action="index.php?controller=project&action=destroy" method="post">
            <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?> 