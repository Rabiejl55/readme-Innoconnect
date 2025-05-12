<?php
// Vérifier si une session est active, sinon en démarrer une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Ajouter une Catégorie - inoconnect';
$pageHeader = 'Ajouter une Catégorie';
$pageSubheader = 'Créez une nouvelle catégorie pour organiser vos projets';
$pageIcon = 'bi-tags';
include_once 'views/templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-plus-circle me-2"></i>Nouvelle catégorie
        </h5>
    </div>
    <div class="card-body">
        <form action="index.php?controller=category&action=store" method="POST" id="categoryForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la catégorie</label>
                <input type="text" class="form-control" id="nom" name="nom">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Enregistrer
                </button>
                <a href="index.php?controller=category&action=index" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>

<!-- Form Validation JS -->
<script src="assets/js/form-validation.js"></script>

<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script> 