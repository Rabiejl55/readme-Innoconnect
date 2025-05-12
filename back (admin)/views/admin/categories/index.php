<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Liste des catégories</h6>
          <a href="index.php?controller=category&action=create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Ajouter une catégorie
          </a>
        </div>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($categories)): ?>
                <tr>
                  <td colspan="4" class="text-center">Aucune catégorie trouvée</td>
                </tr>
              <?php else: ?>
                <?php foreach($categories as $category): ?>
                <tr>
                  <td>
                    <div class="d-flex px-3">
                      <p class="text-xs font-weight-bold mb-0"><?php echo $category['id_categorie']; ?></p>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div>
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                          <i class="ni ni-tag text-success text-sm opacity-10"></i>
                        </div>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($category['nom']); ?></h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs text-secondary mb-0">
                      <?php echo (strlen($category['description']) > 50) ? htmlspecialchars(substr($category['description'], 0, 50)) . '...' : htmlspecialchars($category['description']); ?>
                    </p>
                  </td>
                  <td class="align-middle text-center">
                    <div class="btn-group">
                      <a href="index.php?controller=category&action=show&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-info" title="Voir">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="index.php?controller=category&action=edit&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-warning" title="Modifier">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="index.php?controller=category&action=delete&id=<?php echo $category['id_categorie']; ?>" class="btn btn-sm btn-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
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