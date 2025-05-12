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
          <p>Vous êtes sur le point de supprimer la catégorie <strong>"<?php echo htmlspecialchars($category['nom']); ?>"</strong>.</p>
          <p>Cette action est irréversible. Êtes-vous sûr de vouloir continuer?</p>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Détails de la catégorie</h6>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <strong>ID:</strong> <?php echo $category['id_categorie']; ?>
              </li>
              <li class="list-group-item">
                <strong>Nom:</strong> <?php echo htmlspecialchars($category['nom']); ?>
              </li>
              <li class="list-group-item">
                <strong>Description:</strong> 
                <?php echo htmlspecialchars($category['description'] ?: 'Aucune description disponible.'); ?>
              </li>
            </ul>
          </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
          <a href="index.php?controller=category&action=index" class="btn btn-secondary me-2">Annuler</a>
          <form action="index.php?controller=category&action=destroy" method="post">
            <input type="hidden" name="id_categorie" value="<?php echo $category['id_categorie']; ?>">
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