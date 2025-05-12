<?php
// Vérifier si une session est active, sinon en démarrer une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Gestion des Catégories - inoconnect';
$pageHeader = 'Gestion des Catégories';
$pageSubheader = 'Créez et gérez des catégories pour organiser vos projets';
$pageIcon = 'bi-tags';
include_once 'views/templates/header.php';
?>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" class="main-action-btn main-action-btn-outline">
                <i class="bi bi-house-door btn-icon"></i>Accueil
            </a>
            <a href="index.php?controller=category&action=create" class="main-action-btn main-action-btn-primary">
                <i class="bi bi-plus-circle btn-icon"></i>Nouvelle catégorie
            </a>
            <a href="index.php?controller=project&action=index" class="main-action-btn main-action-btn-outline">
                <i class="bi bi-folder2-open btn-icon"></i>Voir les projets
            </a>
        </div>
                </div>
            </div>

<!-- Categories List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-ul me-2"></i>Liste des catégories
        </h5>
        <span class="badge bg-primary rounded-pill"><?php echo count($categories); ?> catégorie(s)</span>
                </div>
                    <div class="card-body">
                        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="25%">Nom</th>
                        <th width="40%">Description</th>
                        <th width="25%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><span class="category-id"><?php echo $category['id_categorie']; ?></span></td>
                                <td><span class="category-name"><?php echo htmlspecialchars($category['nom']); ?></span></td>
                                <td class="description-cell" title="<?php echo htmlspecialchars($category['description']); ?>">
                                    <?php if (empty($category['description'])): ?>
                                        <em class="text-muted">Aucune description</em>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="action-btns">
                                    <a href="index.php?controller=category&action=show&id=<?php echo $category['id_categorie']; ?>" class="action-btn action-btn-view" title="Voir les détails">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="index.php?controller=category&action=edit_simple&id=<?php echo $category['id_categorie']; ?>" class="action-btn action-btn-edit" title="Modifier la catégorie">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="index.php?controller=category&action=delete_simple&id=<?php echo $category['id_categorie']; ?>" class="action-btn action-btn-delete" title="Supprimer la catégorie">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-tags"></i>
                                    <h4>Aucune catégorie trouvée</h4>
                                    <p>Créez votre première catégorie en cliquant sur le bouton "Nouvelle catégorie"</p>
                                    <a href="index.php?controller=category&action=create" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle me-2"></i>Nouvelle catégorie
                                    </a>
                                </div>
                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

<?php include_once 'views/templates/footer.php'; ?> 