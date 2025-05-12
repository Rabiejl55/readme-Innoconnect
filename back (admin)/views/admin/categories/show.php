<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Détails de la catégorie</h6>
          <div>
            <a href="index.php?controller=category&action=edit&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-warning">Modifier</a>
            <a href="index.php?controller=category&action=index" class="btn btn-sm btn-secondary">Retour à la liste</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <div class="d-flex align-items-center mb-4">
              <div class="icon icon-shape icon-lg bg-gradient-success shadow text-center">
                <i class="ni ni-tag text-white opacity-10"></i>
              </div>
              <div class="ms-3">
                <h5 class="mb-0"><?php echo htmlspecialchars($category['nom']); ?></h5>
                <p class="text-sm mb-0">ID: <?php echo $category['id_categorie']; ?></p>
              </div>
            </div>
            
            <div class="mb-4">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder">Description</h6>
              <p class="text-sm mb-0">
                <?php echo nl2br(htmlspecialchars($category['description'] ?: 'Aucune description disponible.')); ?>
              </p>
            </div>
            
            <div class="mb-4">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder">Actions</h6>
              <div class="d-flex mt-2">
                <a href="index.php?controller=category&action=edit&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-warning me-2">
                  <i class="fas fa-edit"></i> Modifier
                </a>
                <a href="index.php?controller=category&action=delete&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i> Supprimer
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Display projects in this category if any -->
    <div class="card mt-4">
      <div class="card-header pb-0">
        <h6>Projets dans cette catégorie</h6>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <!-- For a real implementation, you'd fetch projects in this category -->
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Projet</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                <th class="text-secondary opacity-7"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Sample placeholder row -->
              <tr>
                <td colspan="5" class="text-center py-4">
                  <p class="text-sm mb-0">Aucun projet n'est actuellement associé à cette catégorie</p>
                </td>
              </tr>
              <!-- In a real implementation, you'd loop through projects -->
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