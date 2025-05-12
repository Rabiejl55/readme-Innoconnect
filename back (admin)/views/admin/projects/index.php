<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Liste des projets</h6>
          <a href="index.php?controller=project&action=create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Ajouter un projet
          </a>
        </div>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Projet</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catégorie</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($projects)): ?>
                <tr>
                  <td colspan="6" class="text-center">Aucun projet trouvé</td>
                </tr>
              <?php else: ?>
                <?php foreach($projects as $project): ?>
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div>
                        <img src="assets/img/small-logos/logo-xd.svg" class="avatar avatar-sm me-3" alt="project icon">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($project['titre']); ?></h6>
                        <p class="text-xs text-secondary mb-0">
                          <?php echo htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']); ?>
                        </p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($project['categorie_nom']); ?></p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="font-weight-bold">
                      <?php echo number_format($project['montant_demande'], 2, ',', ' '); ?> €
                    </span>
                  </td>
                  <td class="align-middle text-center">
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
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">
                      <?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?>
                    </span>
                  </td>
                  <td class="align-middle text-center">
                    <div class="btn-group">
                      <a href="index.php?controller=project&action=show&id=<?php echo $project['id_projet']; ?>" class="btn btn-sm btn-info" title="Voir">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="index.php?controller=project&action=edit&id=<?php echo $project['id_projet']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="index.php?controller=project&action=delete&id=<?php echo $project['id_projet']; ?>" class="btn btn-sm btn-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
                      
                      <?php if($project['statut'] == 'Soumis'): ?>
                      <form action="index.php?controller=project&action=changeStatus" method="post" class="d-inline">
                        <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
                        <input type="hidden" name="statut" value="Approuvé">
                        <button type="submit" class="btn btn-sm btn-success" title="Approuver">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      <form action="index.php?controller=project&action=changeStatus" method="post" class="d-inline">
                        <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
                        <input type="hidden" name="statut" value="Refusé">
                        <button type="submit" class="btn btn-sm btn-danger" title="Refuser">
                          <i class="fas fa-times"></i>
                        </button>
                      </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?> 