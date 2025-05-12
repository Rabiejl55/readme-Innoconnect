<?php
// Include header
require_once 'views/admin/layout/header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Modifier le projet</h6>
          <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary">Retour à la liste</a>
        </div>
      </div>
      <div class="card-body">
        <form role="form" action="index.php?controller=project&action=update" method="post" id="projectEditForm">
          <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">
          
          <div class="mb-3">
            <label for="titre" class="form-label">Titre du projet</label>
            <input type="text" class="form-control validation-required" id="titre" name="titre" value="<?php echo htmlspecialchars($project['titre']); ?>" placeholder="Entrez le titre du projet">
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control validation-required" id="description" name="description" rows="5" placeholder="Entrez la description du projet"><?php echo htmlspecialchars($project['description']); ?></textarea>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="id_categorie" class="form-label">Catégorie</label>
            <select class="form-control validation-required" id="id_categorie" name="id_categorie">
              <option value="">Sélectionnez une catégorie</option>
              <?php foreach($categories as $category): ?>
                <option value="<?php echo $category['id_categorie']; ?>" <?php echo ($category['id_categorie'] == $project['id_categorie']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($category['nom']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-control validation-required" id="statut" name="statut">
              <option value="">Sélectionnez un statut</option>
              <option value="Soumis" <?php echo ($project['statut'] == 'Soumis') ? 'selected' : ''; ?>>Soumis</option>
              <option value="Approuvé" <?php echo ($project['statut'] == 'Approuvé') ? 'selected' : ''; ?>>Approuvé</option>
              <option value="Refusé" <?php echo ($project['statut'] == 'Refusé') ? 'selected' : ''; ?>>Refusé</option>
            </select>
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
          </div>
          
          <div class="mb-3">
            <label for="montant_demande" class="form-label">Montant demandé (€)</label>
            <input type="number" step="0.01" min="0" class="form-control validation-required validation-number" id="montant_demande" name="montant_demande" value="<?php echo $project['montant_demande']; ?>" placeholder="Entrez le montant demandé">
            <div class="error-message text-danger mt-1" style="font-size: 0.875em;">Ce champ est obligatoire.</div>
            <div class="error-message-number text-danger mt-1" style="font-size: 0.875em;">Veuillez entrer un montant valide supérieur à 0.</div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Créé par</label>
              <p class="form-control-static">
                <?php echo htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']); ?>
              </p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date de soumission</label>
              <p class="form-control-static">
                <?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?>
              </p>
            </div>
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <a href="index.php?controller=project&action=index" class="btn btn-light me-2">Annuler</a>
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