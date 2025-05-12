<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Modifier la catégorie</h6>
          <a href="index.php?controller=category&action=index" class="btn btn-sm btn-secondary">Retour à la liste</a>
        </div>
      </div>
      <div class="card-body">
        <form role="form" action="index.php?controller=category&action=update" method="post" id="categoryEditForm">
          <input type="hidden" name="id_categorie" value="<?php echo $category['id_categorie']; ?>">
          
          <div class="mb-3">
            <label for="nom" class="form-label">Nom de la catégorie</label>
            <input type="text" class="form-control validation-required" id="nom" name="nom" value="<?php echo htmlspecialchars($category['nom']); ?>" placeholder="Entrez le nom de la catégorie">
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Entrez la description de la catégorie"><?php echo htmlspecialchars($category['description']); ?></textarea>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <a href="index.php?controller=category&action=index" class="btn btn-light me-2">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?> 