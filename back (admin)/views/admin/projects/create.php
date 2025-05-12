<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Créer un nouveau projet</h6>
          <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary">Retour à la liste</a>
        </div>
      </div>
      <div class="card-body">
        <form role="form" action="index.php?controller=project&action=store" method="post" id="projectForm">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre du projet</label>
            <input type="text" class="form-control validation-required" id="titre" name="titre" placeholder="Entrez le titre du projet">
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control validation-required" id="description" name="description" rows="5" placeholder="Entrez la description du projet"></textarea>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="id_categorie" class="form-label">Catégorie</label>
            <select class="form-control validation-required" id="id_categorie" name="id_categorie">
              <option value="">Sélectionnez une catégorie</option>
              <?php foreach($categories as $category): ?>
                <option value="<?php echo $category['id_categorie']; ?>">
                  <?php echo htmlspecialchars($category['nom']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="id_utilisateur" class="form-label">Utilisateur</label>
            <select class="form-control validation-required" id="id_utilisateur" name="id_utilisateur">
              <!-- In a real application, you would fetch users from the database -->
              <option value="">Sélectionnez un utilisateur</option>
              <option value="1">Utilisateur 1</option>
              <option value="2">Utilisateur 2</option>
              <option value="3">Utilisateur 3</option>
            </select>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="montant_demande" class="form-label">Montant demandé (€)</label>
            <input type="number" step="0.01" min="0" class="form-control validation-required validation-number" id="montant_demande" name="montant_demande" placeholder="Entrez le montant demandé">
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
            <div class="error-message-number text-danger mt-1" style="font-size: 0.875em;">Veuillez entrer un montant valide supérieur à 0.</div>
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <button type="reset" class="btn btn-light me-2">Réinitialiser</button>
            <button type="submit" class="btn btn-primary">Créer le projet</button>
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