<?php
$pageTitle = 'Gestion des Projets - inoconnect';
$pageHeader = 'Gestion des Projets';
$pageSubheader = 'Créez et gérez vos projets techniques';
$pageIcon = 'bi-folder2-open';
include_once 'views/templates/header.php';
?>

<!-- Tour Button -->
<div class="tour-button-container">
    <button id="startTourBtn" class="tour-button">
        <i class="bi bi-info-circle-fill me-2"></i>Visite guidée
    </button>
</div>

<!-- Keyboard Shortcuts Button -->
<div class="shortcuts-button-container">
    <button id="showShortcutsBtn" class="shortcuts-button" aria-label="Afficher les raccourcis clavier">
        <i class="bi bi-keyboard-fill me-2"></i>Raccourcis
        <span class="shortcut-key">?</span>
    </button>
</div>

<!-- Action Buttons -->
<div class="row mb-4" id="actionButtonsSection" data-tour="step1">
    <div class="col-md-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" class="main-action-btn main-action-btn-outline" data-tour="step2">
                <i class="bi bi-house-door btn-icon"></i>Accueil
                <span class="shortcut-key">H</span>
            </a>
            <a href="index.php?controller=project&action=create_simple" class="main-action-btn main-action-btn-primary" data-tour="step3">
                <i class="bi bi-plus-circle btn-icon"></i>Nouveau projet
                <span class="shortcut-key">N</span>
            </a>
            <a href="index.php?controller=category&action=index" class="main-action-btn main-action-btn-outline" data-tour="step4">
                <i class="bi bi-tags btn-icon"></i>Gérer les catégories
                <span class="shortcut-key">C</span>
            </a>
        </div>
    </div>
</div>
        
<!-- Project Grid View -->
<div class="card mb-4" data-tour="step5">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i>Projets
        </h5>
        <span class="badge bg-primary rounded-pill" data-tour="step6"><?php echo count($projects); ?> projet(s)</span>
    </div>
    <div class="card-body">
        <?php if (count($projects) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($projects as $project): ?>
                    <?php 
                        // Set badge class based on status
                        $statusClass = '';
                        switch(strtolower($project['statut'])) {
                            case 'accepté':
                            case 'approuvé':
                            case 'approved':
                                $statusClass = 'bg-success';
                                break;
                            case 'en attente':
                            case 'pending':
                                $statusClass = 'bg-warning text-dark';
                                break;
                            case 'rejeté':
                            case 'rejected':
                                $statusClass = 'bg-danger';
                                break;
                            default:
                                $statusClass = 'bg-secondary';
                        }
                        $budget = number_format($project['montant_demande'], 0, ',', ' ') . ' €';
                        
                        // Prepare text for narration
                        $projectDescription = empty($project['description']) ? 'Aucune description' : $project['description'];
                        $narrationText = "Projet " . $project['id_projet'] . ". Titre: " . $project['titre'] . 
                                        ". Catégorie: " . ($project['nom_categorie'] ?? 'Non catégorisé') . 
                                        ". Statut: " . $project['statut'] . 
                                        ". Budget: " . $budget . 
                                        ". Description: " . $projectDescription;
                        $narrationText = htmlspecialchars($narrationText, ENT_QUOTES);
                    ?>
                    <div class="col <?php echo $project === reset($projects) ? 'tour-first-project' : ''; ?>">
                        <div class="card h-100 project-card-futuristic border-0 <?php echo $project === reset($projects) ? 'tour-first-project-card' : ''; ?>" data-tour="<?php echo $project === reset($projects) ? 'step7' : ''; ?>">
                            <img src="assets/img/project.jpg" alt="Project" class="project-card-img-top mb-3">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="status-badge-futuristic <?php echo $statusClass; ?> me-2" <?php echo $project === reset($projects) ? 'data-tour="step8"' : ''; ?>><?php echo htmlspecialchars($project['statut']); ?></span>
                                    <span class="project-id-futuristic ms-auto"><i class="bi bi-hash"></i> <?php echo $project['id_projet']; ?></span>
                                </div>
                                <h5 class="card-title-futuristic mb-2"><i class="bi bi-file-earmark-text me-2"></i><?php echo htmlspecialchars($project['titre']); ?></h5>
                                <div class="mb-2">
                                    <span class="badge badge-category-futuristic" <?php echo $project === reset($projects) ? 'data-tour="step9"' : ''; ?>><i class="bi bi-tag me-1"></i> <?php echo htmlspecialchars($project['nom_categorie'] ?? 'Non catégorisé'); ?></span>
                                </div>
                                <div class="mb-2">
                                    <span class="fw-semibold budget-futuristic" <?php echo $project === reset($projects) ? 'data-tour="step10"' : ''; ?>><i class="bi bi-cash-coin me-1"></i> <?php echo $budget; ?></span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <span class="text-muted">Propriétaire :</span> <span><?php echo isset($project['utilisateur_nom']) ? htmlspecialchars($project['utilisateur_prenom'] . ' ' . $project['utilisateur_nom']) : 'Non spécifié'; ?></span>
                                </div>
                                <p class="card-text-futuristic flex-grow-1 mt-2" title="<?php echo htmlspecialchars($project['description']); ?>">
                                    <?php if (empty($project['description'])): ?>
                                        <em class="text-muted">Aucune description</em>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars(substr($project['description'], 0, 80) . (strlen($project['description']) > 80 ? '...' : '')); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-end gap-3 mt-3" <?php echo $project === reset($projects) ? 'data-tour="step11"' : ''; ?>>
                                    <!-- Narration Button -->
                                    <button class="action-btn-futuristic narrate" type="button" 
                                            title="Écouter les détails du projet" 
                                            aria-label="Écouter les détails du projet"
                                            data-narration="<?php echo $narrationText; ?>"
                                            data-project-id="<?php echo $project['id_projet']; ?>"
                                            data-bs-toggle="tooltip">
                                        <i class="bi bi-volume-up-fill"></i>
                                        <span class="audio-wave"></span>
                                    </button>
                                    <a href="index.php?controller=project&action=show&id=<?php echo $project['id_projet']; ?>" class="action-btn-futuristic view" title="Voir les détails" <?php echo $project === reset($projects) ? 'data-tour="step12"' : ''; ?>>
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="index.php?controller=project&action=edit_simple&id=<?php echo $project['id_projet']; ?>" class="action-btn-futuristic edit" title="Modifier le projet" <?php echo $project === reset($projects) ? 'data-tour="step13"' : ''; ?>>
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="javascript:void(0)" class="action-btn-futuristic delete" title="Supprimer le projet" data-project-id="<?php echo $project['id_projet']; ?>" data-project-title="<?php echo htmlspecialchars($project['titre']); ?>" data-project-category="<?php echo htmlspecialchars($project['nom_categorie'] ?? 'Non catégorisé'); ?>" data-project-budget="<?php echo number_format($project['montant_demande'], 0, ',', ' ') . ' €'; ?>" data-project-status="<?php echo htmlspecialchars($project['statut']); ?>" data-toggle="delete-modal" <?php echo $project === reset($projects) ? 'data-tour="step14"' : ''; ?>>
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state text-center py-5" data-tour="step15">
                <i class="bi bi-folder-x display-1 text-muted"></i>
                <h4 class="mt-3">Aucun projet trouvé</h4>
                <p>Créez votre premier projet en cliquant sur le bouton "Nouveau projet"</p>
                <a href="index.php?controller=project&action=create_simple" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Nouveau projet
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Keyboard Shortcuts Modal -->
<div class="shortcuts-modal-overlay" id="shortcutsModal">
    <div class="shortcuts-modal-container">
        <div class="shortcuts-modal-header">
            <div class="shortcuts-modal-icon">
                <i class="bi bi-keyboard"></i>
            </div>
            <h3 class="shortcuts-modal-title">Raccourcis clavier</h3>
            <button class="shortcuts-close-btn" id="closeShortcutsBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="shortcuts-modal-content">
            <div class="shortcuts-section">
                <h4 class="shortcuts-section-title">Navigation</h4>
                <div class="shortcuts-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">H</span>
                        </div>
                        <div class="shortcut-desc">Aller à l'accueil</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">C</span>
                        </div>
                        <div class="shortcut-desc">Gérer les catégories</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">?</span>
                        </div>
                        <div class="shortcut-desc">Afficher cette aide</div>
                    </div>
                </div>
            </div>
            
            <div class="shortcuts-section">
                <h4 class="shortcuts-section-title">Actions globales</h4>
                <div class="shortcuts-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">N</span>
                        </div>
                        <div class="shortcut-desc">Créer un nouveau projet</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">F</span>
                        </div>
                        <div class="shortcut-desc">Rechercher (à venir)</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">T</span>
                        </div>
                        <div class="shortcut-desc">Démarrer la visite guidée</div>
                    </div>
                </div>
            </div>
            
            <div class="shortcuts-section">
                <h4 class="shortcuts-section-title">Projets sélectionnés</h4>
                <div class="shortcuts-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">1</span>-<span class="shortcut-key">9</span>
                        </div>
                        <div class="shortcut-desc">Sélectionner un projet (par numéro)</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">V</span>
                        </div>
                        <div class="shortcut-desc">Voir le projet sélectionné</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">E</span>
                        </div>
                        <div class="shortcut-desc">Modifier le projet sélectionné</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">S</span>
                        </div>
                        <div class="shortcut-desc">Lire (speech) le projet sélectionné</div>
                    </div>
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <span class="shortcut-key">⌘</span>+<span class="shortcut-key">⌫</span>
                        </div>
                        <div class="shortcut-desc">Supprimer le projet sélectionné</div>
                    </div>
                </div>
            </div>
            
            <div class="shortcuts-footer">
                <p>Ces raccourcis sont conçus pour améliorer votre productivité.<br>Appuyez sur <span class="shortcut-key">Esc</span> pour fermer cette fenêtre.</p>
            </div>
        </div>
    </div>
</div>

<!-- Keyboard Shortcut Notification -->
<div class="shortcut-notification" id="shortcutNotification">
    <div class="shortcut-notification-icon">
        <i class="bi bi-keyboard"></i>
    </div>
    <div class="shortcut-notification-text" id="shortcutNotificationText">
        Raccourci activé
    </div>
</div>

<!-- Active Project Selection Indicator -->
<div class="active-project-indicator" id="activeProjectIndicator">
    <i class="bi bi-cursor-fill"></i>
</div>

<?php include_once 'views/templates/footer.php'; ?>

<!-- Delete Project Modal -->
<div class="delete-modal-overlay" id="deleteModal">
    <div class="delete-modal-container">
        <div class="delete-modal-header">
            <div class="delete-modal-icon">
                <i class="bi bi-trash"></i>
            </div>
            <h3 class="delete-modal-title">Supprimer le projet</h3>
        </div>
        
        <div class="delete-modal-content">
            <div class="delete-warning-icon">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <h4 class="delete-confirm-text">Êtes-vous sûr de vouloir supprimer ce projet ?</h4>
            
            <div class="project-info-card">
                <div class="project-info-item">
                    <span class="project-info-label">ID :</span>
                    <span class="project-info-value" id="deleteProjectId"></span>
                </div>
                <div class="project-info-item">
                    <span class="project-info-label">Catégorie :</span>
                    <span class="project-info-value" id="deleteProjectCategory"></span>
                </div>
                <div class="project-info-item">
                    <span class="project-info-label">Budget :</span>
                    <span class="project-info-value" id="deleteProjectBudget"></span>
                </div>
                <div class="project-info-item">
                    <span class="project-info-label">Statut :</span>
                    <span class="project-info-value" id="deleteProjectStatus"></span>
                </div>
            </div>
            
            <div class="delete-warning-text">
                Cette action est irréversible et supprimera définitivement ce projet.
            </div>
            
            <div class="delete-modal-actions">
                <button class="delete-cancel-btn" id="cancelDeleteBtn">
                    <i class="bi bi-x"></i> Annuler
                </button>
                <form id="deleteProjectForm" action="index.php?controller=project&action=destroy" method="POST">
                    <input type="hidden" name="id_projet" id="deleteProjectIdInput">
                    <button type="submit" class="delete-confirm-btn">
                        <i class="bi bi-trash-fill"></i> Confirmer la suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.project-card-futuristic {
    background: linear-gradient(135deg, rgba(120, 63, 255, 0.18) 0%, rgba(0, 212, 255, 0.10) 100%);
    box-shadow: 0 4px 32px 0 rgba(120, 63, 255, 0.10), 0 1.5px 6px rgba(80, 80, 120, 0.10);
    border-radius: 1.5em;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1.5px solid rgba(120, 63, 255, 0.18);
    transition: box-shadow 0.25s, transform 0.25s;
    margin-bottom: 1.5rem;
}
.project-card-futuristic:hover {
    box-shadow: 0 8px 48px 0 rgba(120, 63, 255, 0.22), 0 2px 12px rgba(0, 212, 255, 0.18);
    transform: scale(1.035) translateY(-4px);
    z-index: 3;
    border-color: #7c3fff;
}
.status-badge-futuristic {
    display: inline-block;
    padding: 0.45em 1.2em;
    border-radius: 2em;
    font-size: 1em;
    font-weight: 700;
    background: linear-gradient(90deg, #7c3fff 0%, #00d4ff 100%);
    color: #fff;
    box-shadow: 0 0 8px 0 rgba(124, 63, 255, 0.18);
    letter-spacing: 0.03em;
    border: none;
    text-shadow: 0 1px 4px rgba(60,0,80,0.10);
    transition: filter 0.2s;
}
.status-badge-futuristic.bg-success {
    background: linear-gradient(90deg, #00c896 0%, #00d4ff 100%);
}
.status-badge-futuristic.bg-warning {
    background: linear-gradient(90deg, #ffb347 0%, #ffcc80 100%);
    color: #333;
}
.status-badge-futuristic.bg-danger {
    background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%);
}
.status-badge-futuristic.bg-secondary {
    background: linear-gradient(90deg, #bdbdbd 0%, #e0e0e0 100%);
    color: #333;
}
.project-id-futuristic {
    font-family: 'Orbitron', 'Segoe UI', Arial, sans-serif;
    font-size: 1.1em;
    color: #7c3fff;
    letter-spacing: 0.04em;
    font-weight: 700;
    opacity: 0.85;
}
.card-title-futuristic {
    font-size: 1.25em;
    font-weight: 800;
    color: #2d1a4a;
    letter-spacing: 0.01em;
    margin-bottom: 0.5em;
    display: flex;
    align-items: center;
}
.badge-category-futuristic {
    background: linear-gradient(90deg, #00d4ff 0%, #7c3fff 100%);
    color: #fff;
    font-weight: 600;
    border-radius: 1em;
    font-size: 0.98em;
    padding: 0.4em 1em;
    box-shadow: 0 0 6px 0 rgba(0,212,255,0.10);
}
.budget-futuristic {
    color: #7c3fff;
    font-size: 1.08em;
    font-weight: 700;
}
.card-text-futuristic {
    color: #2d1a4a;
    font-size: 1em;
    opacity: 0.92;
    margin-bottom: 0.5em;
}
.action-btn-futuristic {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8fafc 0%, #e9e4f0 100%);
    box-shadow: 0 2px 8px rgba(124, 63, 255, 0.08);
    color: #7c3fff;
    font-size: 1.25em;
    border: none;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.2s;
    margin-left: 0.1em;
}
.action-btn-futuristic.view {
    background: linear-gradient(135deg, #7c3fff 0%, #00d4ff 100%);
    color: #fff;
}
.action-btn-futuristic.edit {
    background: linear-gradient(135deg, #00c896 0%, #00d4ff 100%);
    color: #fff;
}
.action-btn-futuristic.delete {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: #fff;
}
.action-btn-futuristic:hover, .action-btn-futuristic:focus {
    box-shadow: 0 4px 16px 0 rgba(124, 63, 255, 0.18);
    transform: scale(1.12);
    filter: brightness(1.08);
    color: #fff;
    outline: none;
    z-index: 2;
}
.project-card-img-top {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-top-left-radius: 1.5em;
    border-top-right-radius: 1.5em;
    box-shadow: 0 2px 12px 0 rgba(124, 63, 255, 0.10);
    background: #2d1a4a;
    margin-top: -1px;
}
@media (max-width: 767px) {
    .project-card-futuristic {
        border-radius: 1em;
        padding: 0.5em;
    }
    .card-title-futuristic {
        font-size: 1.1em;
    }
    .action-btn-futuristic {
        width: 36px;
        height: 36px;
        font-size: 1.1em;
    }
    .project-card-img-top {
        height: 90px;
        border-top-left-radius: 1em;
        border-top-right-radius: 1em;
    }
}

/* Delete Modal Styles */
.delete-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.delete-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.delete-modal-container {
    width: 90%;
    max-width: 500px;
    background: linear-gradient(135deg, rgba(15, 14, 22, 0.95) 0%, rgba(48, 30, 103, 0.88) 100%);
    border-radius: 1.5rem;
    box-shadow: 0 15px 50px rgba(124, 63, 255, 0.3), 0 0 0 1px rgba(124, 63, 255, 0.15);
    overflow: hidden;
    transform: translateY(30px) scale(0.95);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.delete-modal-overlay.active .delete-modal-container {
    transform: translateY(0) scale(1);
}

.delete-modal-header {
    background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.delete-modal-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.delete-modal-title {
    color: white;
    margin: 0;
    font-weight: 700;
    font-size: 1.4rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.delete-modal-content {
    padding: 2rem;
    color: white;
    text-align: center;
}

.delete-warning-icon {
    font-size: 3.5rem;
    color: #ff416c;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
    text-shadow: 0 0 15px rgba(255, 65, 108, 0.7);
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.delete-confirm-text {
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    color: rgba(255, 255, 255, 0.95);
    font-weight: 600;
}

.project-info-card {
    background: rgba(255, 255, 255, 0.07);
    border-radius: 1rem;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.project-info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.7rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.project-info-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.project-info-label {
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
}

.project-info-value {
    font-weight: 700;
    color: rgba(255, 255, 255, 0.95);
}

.delete-warning-text {
    color: #ff416c;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.delete-modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.delete-cancel-btn, .delete-confirm-btn {
    padding: 0.8rem 1.5rem;
    border-radius: 2rem;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.delete-cancel-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.15);
}

.delete-confirm-btn {
    background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 65, 108, 0.4);
}

.delete-cancel-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.delete-confirm-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 20px rgba(255, 65, 108, 0.5);
    filter: brightness(1.1);
}

@media (max-width: 767px) {
    .delete-modal-container {
        width: 95%;
    }
    
    .delete-modal-header {
        padding: 1.2rem;
    }
    
    .delete-modal-content {
        padding: 1.5rem;
    }
    
    .delete-confirm-text {
        font-size: 1.1rem;
    }
    
    .delete-modal-actions {
        flex-direction: column;
    }
}

/* Tour Button Styles */
.tour-button-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.tour-button {
    background: linear-gradient(135deg, #7c3fff 0%, #00d4ff 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(124, 63, 255, 0.25);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.tour-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 25px rgba(124, 63, 255, 0.4);
}

.tour-button i {
    font-size: 1.2em;
}

/* Intro.js Custom Styles */
.introjs-tooltip {
    background: linear-gradient(135deg, rgba(15, 14, 22, 0.95) 0%, rgba(48, 30, 103, 0.88) 100%);
    color: white;
    border-radius: 15px;
    box-shadow: 0 15px 50px rgba(124, 63, 255, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    max-width: 400px;
}

.introjs-tooltiptext {
    padding: 15px;
    font-size: 1rem;
}

.introjs-helperLayer {
    background-color: rgba(124, 63, 255, 0.12);
    border: 2px solid rgba(124, 63, 255, 0.6);
    box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.6);
}

.introjs-arrow.top, .introjs-arrow.top-middle, .introjs-arrow.top-right {
    border-bottom-color: rgba(48, 30, 103, 0.88);
}

.introjs-arrow.right, .introjs-arrow.right-bottom {
    border-left-color: rgba(48, 30, 103, 0.88);
}

.introjs-arrow.bottom, .introjs-arrow.bottom-middle, .introjs-arrow.bottom-right {
    border-top-color: rgba(15, 14, 22, 0.95);
}

.introjs-arrow.left, .introjs-arrow.left-bottom {
    border-right-color: rgba(48, 30, 103, 0.88);
}

.introjs-button {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 25px;
    padding: 6px 15px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: none;
    text-shadow: none;
    transition: all 0.2s;
}

.introjs-button:hover, .introjs-button:focus {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    box-shadow: none;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.introjs-button.introjs-nextbutton {
    background: linear-gradient(90deg, #7c3fff 0%, #00d4ff 100%);
    border: none;
}

.introjs-button.introjs-nextbutton:hover {
    box-shadow: 0 5px 15px rgba(124, 63, 255, 0.3);
    filter: brightness(1.1);
}

.introjs-button.introjs-skipbutton {
    margin-right: 8px;
}

.introjs-tooltipbuttons {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.introjs-disabled, .introjs-disabled:hover, .introjs-disabled:focus {
    opacity: 0.5;
    cursor: not-allowed;
}

.tour-highlight {
    position: relative;
    z-index: 9999;
}

@media (max-width: 767px) {
    .tour-button-container {
        bottom: 20px;
        right: 20px;
    }
    
    .tour-button {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
    
    .introjs-tooltip {
        max-width: 280px;
    }
}

/* Audio Narration Button Styles */
.action-btn-futuristic.narrate {
    background: linear-gradient(135deg, #00d4ff 0%, #00b4d8 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.action-btn-futuristic.narrate:hover, 
.action-btn-futuristic.narrate:focus {
    box-shadow: 0 4px 16px 0 rgba(0, 212, 255, 0.28);
    transform: scale(1.12);
    filter: brightness(1.08);
    color: #fff;
}
.action-btn-futuristic.narrate.speaking {
    animation: pulse-glow 1.5s infinite alternate;
}
.audio-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0);
    transition: all 0.3s;
    transform: scaleY(0);
    transform-origin: bottom;
}
.action-btn-futuristic.narrate.speaking .audio-wave {
    background: rgba(255, 255, 255, 0.7);
    transform: scaleY(1);
    animation: audio-wave 0.6s ease-in-out infinite alternate;
}
@keyframes audio-wave {
    0% { transform: scaleY(0.3); opacity: 0.5; }
    100% { transform: scaleY(1); opacity: 0.9; }
}
@keyframes pulse-glow {
    0% { box-shadow: 0 0 5px 0 rgba(0, 212, 255, 0.5); }
    100% { box-shadow: 0 0 12px 5px rgba(0, 212, 255, 0.7); }
}

/* Shortcuts Button Styles */
.shortcuts-button-container {
    position: fixed;
    bottom: 30px;
    left: 30px;
    z-index: 1000;
}

.shortcuts-button {
    background: linear-gradient(135deg, #7c3fff 0%, #00d4ff 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(124, 63, 255, 0.25);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.shortcuts-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 25px rgba(124, 63, 255, 0.4);
}

.shortcuts-button i {
    font-size: 1.2em;
}

/* Shortcut Key Indicator */
.shortcut-key {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    padding: 0 4px;
    font-size: 0.8em;
    font-weight: 700;
    margin-left: 8px;
    font-family: 'Roboto Mono', monospace;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.main-action-btn .shortcut-key {
    background: rgba(255, 255, 255, 0.2);
    color: inherit;
}

.main-action-btn-outline .shortcut-key {
    background: rgba(88, 70, 249, 0.1);
    color: inherit;
}

/* Keyboard Shortcuts Modal */
.shortcuts-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.shortcuts-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.shortcuts-modal-container {
    width: 90%;
    max-width: 800px;
    background: linear-gradient(135deg, rgba(15, 14, 22, 0.95) 0%, rgba(48, 30, 103, 0.88) 100%);
    border-radius: 1.5rem;
    box-shadow: 0 15px 50px rgba(124, 63, 255, 0.3), 0 0 0 1px rgba(124, 63, 255, 0.15);
    overflow: hidden;
    transform: translateY(30px) scale(0.95);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.shortcuts-modal-overlay.active .shortcuts-modal-container {
    transform: translateY(0) scale(1);
}

.shortcuts-modal-header {
    background: linear-gradient(90deg, #7c3fff 0%, #00d4ff 100%);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
}

.shortcuts-modal-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.shortcuts-modal-title {
    color: white;
    margin: 0;
    font-weight: 700;
    font-size: 1.4rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    flex-grow: 1;
}

.shortcuts-close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s;
}

.shortcuts-close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

.shortcuts-modal-content {
    padding: 2rem;
    color: white;
    max-height: 70vh;
    overflow-y: auto;
}

.shortcuts-section {
    margin-bottom: 2rem;
}

.shortcuts-section-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #00d4ff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 0.5rem;
}

.shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.shortcut-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.8rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.2s;
}

.shortcut-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.shortcut-keys {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.shortcut-keys .shortcut-key {
    background: rgba(124, 63, 255, 0.3);
    min-width: 28px;
    height: 28px;
    padding: 0 5px;
    margin: 0;
    font-size: 0.9rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.shortcut-desc {
    flex-grow: 1;
    font-size: 0.95rem;
}

.shortcuts-footer {
    margin-top: 2rem;
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.shortcuts-footer .shortcut-key {
    background: rgba(124, 63, 255, 0.3);
    min-width: 28px;
    height: 22px;
    display: inline-flex;
    vertical-align: middle;
}

/* Shortcut Notification */
.shortcut-notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    background: linear-gradient(135deg, rgba(15, 14, 22, 0.95) 0%, rgba(48, 30, 103, 0.88) 100%);
    border-radius: 50px;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 9998;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    opacity: 0;
}

.shortcut-notification.active {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.shortcut-notification-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(90deg, #7c3fff 0%, #00d4ff 100%);
    border-radius: 50%;
    font-size: 1.2rem;
}

.shortcut-notification-text {
    font-weight: 600;
}

/* Active Project Indicator */
.active-project-indicator {
    position: absolute;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(90deg, #7c3fff 0%, #00d4ff 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 5px 15px rgba(124, 63, 255, 0.4);
    pointer-events: none;
    z-index: 9990;
    opacity: 0;
    transform: scale(0.5);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s;
}

.active-project-indicator.active {
    transform: scale(1);
    opacity: 1;
}

/* Responsive Styles */
@media (max-width: 767px) {
    .shortcuts-grid {
        grid-template-columns: 1fr;
    }
    
    .shortcuts-button-container {
        bottom: 20px;
        left: 20px;
    }
    
    .shortcuts-button {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete modal functionality
    const deleteButtons = document.querySelectorAll('[data-toggle="delete-modal"]');
    const deleteModal = document.getElementById('deleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const deleteProjectIdEl = document.getElementById('deleteProjectId');
    const deleteProjectCategoryEl = document.getElementById('deleteProjectCategory');
    const deleteProjectBudgetEl = document.getElementById('deleteProjectBudget');
    const deleteProjectStatusEl = document.getElementById('deleteProjectStatus');
    const deleteProjectIdInput = document.getElementById('deleteProjectIdInput');
    
    // Open modal
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const projectId = this.getAttribute('data-project-id');
            const projectTitle = this.getAttribute('data-project-title');
            const projectCategory = this.getAttribute('data-project-category');
            const projectBudget = this.getAttribute('data-project-budget');
            const projectStatus = this.getAttribute('data-project-status');
            
            deleteProjectIdEl.textContent = projectId;
            deleteProjectCategoryEl.textContent = projectCategory;
            deleteProjectBudgetEl.textContent = projectBudget;
            deleteProjectStatusEl.textContent = projectStatus;
            deleteProjectIdInput.value = projectId;
            
            deleteModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close modal
    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Close on outside click
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Text-to-speech functionality for project narration
    const narrateButtons = document.querySelectorAll('.action-btn-futuristic.narrate');
    let currentlySpeaking = null;
    
    // Check if browser supports speech synthesis
    const speechSupported = 'speechSynthesis' in window;
    
    if (speechSupported) {
        // Create a speech synthesis instance
        const synth = window.speechSynthesis;
        const voices = synth.getVoices();
        
        // Find a French voice if available, otherwise use default
        let frenchVoice = null;
        
        // Get voices (initial load or when voices change)
        function loadVoices() {
            const availableVoices = synth.getVoices();
            // Try to find a French voice
            frenchVoice = availableVoices.find(voice => 
                voice.lang.includes('fr') && voice.localService
            ) || availableVoices.find(voice => 
                voice.lang.includes('fr')
            );
        }
        
        // Load voices when available
        if (synth.onvoiceschanged !== undefined) {
            synth.onvoiceschanged = loadVoices;
        }
        loadVoices();
        
        // Function to stop all speech
        function stopSpeaking() {
            synth.cancel();
            if (currentlySpeaking) {
                currentlySpeaking.classList.remove('speaking');
                currentlySpeaking = null;
            }
        }
        
        // Add click handlers to all narrate buttons
        narrateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const projectId = this.getAttribute('data-project-id');
                const narrationText = this.getAttribute('data-narration');
                
                // If this project is already speaking, stop it
                if (currentlySpeaking === this) {
                    stopSpeaking();
                    return;
                }
                
                // If another project is speaking, stop it first
                if (currentlySpeaking) {
                    stopSpeaking();
                }
                
                // Mark this button as speaking
                this.classList.add('speaking');
                currentlySpeaking = this;
                
                // Create a new utterance
                const utterance = new SpeechSynthesisUtterance(narrationText);
                
                // Set language and voice
                utterance.lang = 'fr-FR';
                if (frenchVoice) {
                    utterance.voice = frenchVoice;
                }
                
                // Configure voice parameters
                utterance.rate = 1.0;  // Speed of speech
                utterance.pitch = 1.0; // Pitch of voice
                utterance.volume = 1.0; // Volume
                
                // When speech ends, reset the button
                utterance.onend = function() {
                    if (currentlySpeaking) {
                        currentlySpeaking.classList.remove('speaking');
                        currentlySpeaking = null;
                    }
                };
                
                // When speech is interrupted by error
                utterance.onerror = function() {
                    if (currentlySpeaking) {
                        currentlySpeaking.classList.remove('speaking');
                        currentlySpeaking = null;
                    }
                };
                
                // Speak!
                synth.speak(utterance);
            });
        });
        
        // Stop speaking when navigating away from the page
        window.addEventListener('beforeunload', stopSpeaking);
    } else {
        // If speech synthesis is not supported, show a message and disable the buttons
        narrateButtons.forEach(button => {
            button.setAttribute('disabled', 'disabled');
            button.setAttribute('title', 'La synthèse vocale n\'est pas prise en charge par votre navigateur');
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
        });
    }
    
    // Load the Intro.js library dynamically
    function loadIntroJs() {
        // Check if Intro.js is already loaded
        if (typeof introJs !== 'undefined') {
            startTour();
            return;
        }
        
        // Load CSS
        const cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = 'https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css';
        document.head.appendChild(cssLink);
        
        // Load JavaScript
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/intro.min.js';
        script.onload = function() {
            startTour();
        };
        document.head.appendChild(script);
    }
    
    // Define the tour steps and start the tour
    function startTour() {
        const intro = introJs();
        
        // Configure steps
        intro.setOptions({
            steps: [
                {
                    element: document.querySelector('[data-tour="step1"]'),
                    intro: "Bienvenue dans la visite guidée ! Cette page vous permet de gérer tous vos projets.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step2"]'),
                    intro: "Ce bouton vous permet de retourner à la page d'accueil du système.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step3"]'),
                    intro: "Pour créer un nouveau projet, cliquez sur ce bouton. Vous pourrez alors remplir tous les détails nécessaires.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step4"]'),
                    intro: "Ce bouton vous permet d'accéder à la gestion des catégories de projets.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step5"]'),
                    intro: "Voici la liste de tous vos projets, présentée sous forme de cartes.",
                    position: 'top'
                },
                {
                    element: document.querySelector('[data-tour="step6"]'),
                    intro: "Ce compteur indique le nombre total de projets actuellement dans le système.",
                    position: 'left'
                }
            ]
        });
        
        // Add steps for project card if projects exist
        const firstProjectCard = document.querySelector('[data-tour="step7"]');
        if (firstProjectCard) {
            intro.addSteps([
                {
                    element: firstProjectCard,
                    intro: "Chaque carte représente un projet distinct avec toutes ses informations essentielles.",
                    position: 'right'
                },
                {
                    element: document.querySelector('[data-tour="step8"]'),
                    intro: "Le statut du projet est clairement indiqué par cette étiquette colorée (Accepté, En attente, Rejeté, etc.).",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step9"]'),
                    intro: "Cette étiquette indique la catégorie à laquelle appartient le projet.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step10"]'),
                    intro: "Le montant demandé pour le financement du projet est affiché ici.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('[data-tour="step11"]'),
                    intro: "Ces boutons vous permettent d'interagir avec le projet.",
                    position: 'left'
                },
                {
                    element: document.querySelector('[data-tour="step12"]'),
                    intro: "Ce bouton vous permet de voir tous les détails du projet.",
                    position: 'top'
                },
                {
                    element: document.querySelector('[data-tour="step13"]'),
                    intro: "Pour modifier les informations du projet, utilisez ce bouton d'édition.",
                    position: 'top'
                },
                {
                    element: document.querySelector('[data-tour="step14"]'),
                    intro: "Si vous souhaitez supprimer le projet, utilisez ce bouton avec précaution.",
                    position: 'top'
                }
            ]);
        } else {
            // If no projects exist, show the empty state step
            const emptyState = document.querySelector('[data-tour="step15"]');
            if (emptyState) {
                intro.addStep({
                    element: emptyState,
                    intro: "Vous n'avez pas encore de projets. Cliquez sur le bouton 'Nouveau projet' pour commencer !",
                    position: 'top'
                });
            }
        }
        
        // Add final step
        intro.addStep({
            element: document.querySelector('.tour-button-container'),
            intro: "La visite guidée est terminée ! Vous pouvez la relancer à tout moment en cliquant sur ce bouton.",
            position: 'left'
        });
        
        // Change button text
        intro.setOption('prevLabel', 'Précédent');
        intro.setOption('nextLabel', 'Suivant');
        intro.setOption('doneLabel', 'Terminer');
        intro.setOption('skipLabel', 'Passer');
        
        // Make tour dismissible
        intro.setOption('exitOnOverlayClick', true);
        intro.setOption('showStepNumbers', false);
        intro.setOption('scrollToElement', true);
        intro.setOption('disableInteraction', true);
        
        // Start the tour
        intro.start();
    }
    
    // Add event listener to the tour button
    const tourButton = document.getElementById('startTourBtn');
    if (tourButton) {
        tourButton.addEventListener('click', loadIntroJs);
    }
    
    // Check if tour should start automatically (e.g., via URL parameter)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tour') === 'start') {
        loadIntroJs();
    }

    // Keyboard Shortcuts Modal
    const showShortcutsBtn = document.getElementById('showShortcutsBtn');
    const closeShortcutsBtn = document.getElementById('closeShortcutsBtn');
    const shortcutsModal = document.getElementById('shortcutsModal');
    const shortcutNotification = document.getElementById('shortcutNotification');
    const shortcutNotificationText = document.getElementById('shortcutNotificationText');
    const activeProjectIndicator = document.getElementById('activeProjectIndicator');
    const projectCards = document.querySelectorAll('.project-card-futuristic');
    
    let activeProjectIndex = -1;
    
    // Open shortcuts modal
    function openShortcutsModal() {
        shortcutsModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Close shortcuts modal
    function closeShortcutsModal() {
        shortcutsModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Show shortcut notification
    function showNotification(message) {
        shortcutNotificationText.textContent = message;
        shortcutNotification.classList.add('active');
        
        setTimeout(() => {
            shortcutNotification.classList.remove('active');
        }, 2000);
    }
    
    // Set active project
    function setActiveProject(index) {
        // Remove active state from previous project
        if (activeProjectIndex >= 0 && activeProjectIndex < projectCards.length) {
            projectCards[activeProjectIndex].classList.remove('active-project');
        }
        
        activeProjectIndex = index;
        
        // If valid index, highlight the new active project
        if (index >= 0 && index < projectCards.length) {
            const card = projectCards[index];
            card.classList.add('active-project');
            
            // Show the indicator briefly
            const rect = card.getBoundingClientRect();
            activeProjectIndicator.style.top = (rect.top + window.scrollY + 20) + 'px';
            activeProjectIndicator.style.left = (rect.left + window.scrollX + 20) + 'px';
            activeProjectIndicator.classList.add('active');
            
            setTimeout(() => {
                activeProjectIndicator.classList.remove('active');
            }, 1500);
            
            // Ensure the card is visible
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            showNotification(`Projet ${index + 1} sélectionné`);
        } else if (index >= 0) {
            showNotification(`Projet ${index + 1} non disponible`);
        }
    }
    
    // Perform action on active project
    function performProjectAction(action) {
        if (activeProjectIndex < 0 || activeProjectIndex >= projectCards.length) {
            showNotification('Aucun projet sélectionné');
            return;
        }
        
        const card = projectCards[activeProjectIndex];
        
        switch (action) {
            case 'view':
                const viewBtn = card.querySelector('.action-btn-futuristic.view');
                if (viewBtn) {
                    showNotification('Affichage du projet...');
                    viewBtn.click();
                }
                break;
            case 'edit':
                const editBtn = card.querySelector('.action-btn-futuristic.edit');
                if (editBtn) {
                    showNotification('Modification du projet...');
                    editBtn.click();
                }
                break;
            case 'delete':
                const deleteBtn = card.querySelector('.action-btn-futuristic.delete');
                if (deleteBtn) {
                    showNotification('Suppression du projet...');
                    deleteBtn.click();
                }
                break;
            case 'speak':
                const narrateBtn = card.querySelector('.action-btn-futuristic.narrate');
                if (narrateBtn) {
                    showNotification('Lecture du projet...');
                    narrateBtn.click();
                }
                break;
        }
    }
    
    // Event listeners
    if (showShortcutsBtn) {
        showShortcutsBtn.addEventListener('click', openShortcutsModal);
    }
    
    if (closeShortcutsBtn) {
        closeShortcutsBtn.addEventListener('click', closeShortcutsModal);
    }
    
    // Close on outside click
    if (shortcutsModal) {
        shortcutsModal.addEventListener('click', function(e) {
            if (e.target === shortcutsModal) {
                closeShortcutsModal();
            }
        });
    }
    
    // Global keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Don't trigger shortcuts when typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        // Shortcuts
        switch (e.key.toLowerCase()) {
            case '?': // Show shortcuts
                openShortcutsModal();
                break;
                
            case 'escape': // Close modals
                closeShortcutsModal();
                // Also close delete modal if it exists and is active
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal && deleteModal.classList.contains('active')) {
                    deleteModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
                break;
                
            case 'h': // Home
                showNotification('Retour à l\'accueil...');
                window.location.href = 'index.php';
                break;
                
            case 'n': // New project
                showNotification('Création d\'un nouveau projet...');
                window.location.href = 'index.php?controller=project&action=create_simple';
                break;
                
            case 'c': // Categories
                showNotification('Gestion des catégories...');
                window.location.href = 'index.php?controller=category&action=index';
                break;
                
            case 't': // Start tour
                const tourBtn = document.getElementById('startTourBtn');
                if (tourBtn) {
                    showNotification('Démarrage de la visite guidée...');
                    tourBtn.click();
                }
                break;
                
            // Select project by number (1-9)
            case '1': case '2': case '3': case '4': case '5': 
            case '6': case '7': case '8': case '9':
                const projectIndex = parseInt(e.key) - 1;
                setActiveProject(projectIndex);
                break;
                
            // Actions on selected project
            case 'v': // View
                performProjectAction('view');
                break;
                
            case 'e': // Edit
                performProjectAction('edit');
                break;
                
            case 's': // Speak
                performProjectAction('speak');
                break;
        }
        
        // Delete shortcut (Cmd+Backspace or Ctrl+Backspace)
        if ((e.metaKey || e.ctrlKey) && e.key === 'Backspace') {
            performProjectAction('delete');
            e.preventDefault(); // Prevent browser back
        }
    });
});
</script> 