<?php
$pageTitle = 'Détail du Projet - inoconnect';
$pageHeader = $project['titre'];
$pageSubheader = 'Détails complets du projet';
$pageIcon = 'bi-folder2-open';
include_once 'views/templates/header.php';
?>

<style>
/* --- Futuristic Project Detail Enhancements --- */
body.project-detail-bg {
    background: linear-gradient(135deg, #f7f8fa 0%, #e6e6ff 100%) fixed;
    min-height: 100vh;
    position: relative;
}
.project-detail-glass {
    background: rgba(255,255,255,0.85);
    box-shadow: 0 8px 32px 0 rgba(88,70,249,0.10), 0 1.5px 8px 0 rgba(123,111,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    border: 1.5px solid rgba(88,70,249,0.10);
    transition: box-shadow 0.3s, transform 0.3s;
    position: relative;
    overflow: hidden;
}
.project-detail-glass::before {
    content: '';
    position: absolute;
    top: -40px; left: -40px; right: -40px; bottom: -40px;
    background: radial-gradient(circle at 80% 20%, rgba(123,111,255,0.08) 0%, transparent 70%);
    z-index: 0;
    pointer-events: none;
}
.project-detail-glass:hover {
    box-shadow: 0 16px 48px 0 rgba(88,70,249,0.18), 0 4px 16px 0 rgba(123,111,255,0.12);
    transform: translateY(-2px) scale(1.01);
}
.project-detail-table th {
    color: var(--primary-color);
    font-weight: 700;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 0 #f8f9fa;
}
.project-detail-table td {
    font-size: 1.08rem;
    color: #2c4964;
}
.project-detail-section-title {
    font-family: var(--heading-font, 'Raleway', sans-serif);
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0;
    text-shadow: 0 1px 0 #f8f9fa;
    z-index: 1;
}
.project-detail-section-title i {
    color: var(--secondary-color);
    font-size: 1.3em;
    filter: drop-shadow(0 1px 2px #e6e6ff);
}
.project-detail-label {
    font-weight: 600;
    color: var(--secondary-color);
    letter-spacing: 0.2px;
}
.project-detail-value {
    font-weight: 500;
    color: #2c4964;
}
.project-detail-description {
    font-size: 1.08rem;
    color: #444;
    line-height: 1.7;
    background: rgba(248,249,250,0.7);
    border-radius: 10px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 2px 8px rgba(88,70,249,0.04);
    margin-bottom: 0;
    transition: background 0.3s;
    z-index: 1;
}
.project-detail-description:hover {
    background: rgba(248,249,250,0.95);
}
.project-detail-anim {
    animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1.000) both;
}
@keyframes fadeInUp {
  0% { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: none; }
}
.project-id {
    font-family: 'Roboto Mono', monospace;
    letter-spacing: 1px;
    color: var(--info-color);
    font-size: 1.1em;
    background: linear-gradient(90deg, #e6e6ff 0%, #f7f8fa 100%);
    border-radius: 6px;
    padding: 2px 8px;
}
.budget-value {
    font-size: 1.15em;
    font-weight: 600;
    color: var(--primary-color);
    letter-spacing: 0.5px;
    background: linear-gradient(90deg, #f7f8fa 0%, #e6e6ff 100%);
    border-radius: 6px;
    padding: 2px 8px;
}
.badge {
    font-size: 1em;
    padding: 0.5em 1.1em;
    border-radius: 2em;
    box-shadow: 0 2px 8px rgba(88,70,249,0.08);
    letter-spacing: 0.5px;
    transition: transform 0.2s;
}
.badge:hover {
    transform: scale(1.08) rotate(-2deg);
}
.fab-edit {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 9999;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 64px;
    height: 64px;
    box-shadow: 0 6px 24px rgba(88,70,249,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    cursor: pointer;
    transition: box-shadow 0.2s, transform 0.2s;
    outline: none;
}
.fab-edit:hover, .fab-edit:focus {
    box-shadow: 0 12px 32px rgba(123,111,255,0.22);
    transform: scale(1.08) rotate(3deg);
    color: #fff;
}
.fab-edit[title] {
    position: fixed;
}
@media (max-width: 768px) {
    .fab-edit {
        width: 48px;
        height: 48px;
        font-size: 1.4rem;
        bottom: 16px;
        right: 16px;
    }
}
/* Futuristic animated background overlay */
.bg-animated-futuristic {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: 0;
    pointer-events: none;
    background: radial-gradient(circle at 80% 20%, rgba(123,111,255,0.10) 0%, transparent 70%),
                radial-gradient(circle at 20% 80%, rgba(88,70,249,0.10) 0%, transparent 70%);
    animation: bgMove 12s ease-in-out infinite alternate;
}
@keyframes bgMove {
    0% { background-position: 80% 20%, 20% 80%; }
    100% { background-position: 60% 40%, 40% 60%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add body class for background
    document.body.classList.add('project-detail-bg');
    // Animate on scroll (AOS)
    if (window.AOS) {
        AOS.init({ duration: 800, once: true });
    }
    // Tooltip for floating action button
    var fab = document.getElementById('fab-edit');
    if (fab && window.bootstrap) {
        new bootstrap.Tooltip(fab);
    }
});
</script>

<!-- Animated Futuristic Background Overlay -->
<div class="bg-animated-futuristic"></div>

<!-- Action Buttons -->
<div class="row mb-4 project-detail-anim" data-aos="fade-down">
    <div class="col-md-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="./index.php?controller=project&action=index" class="main-action-btn main-action-btn-outline" title="Retour à la liste" data-bs-toggle="tooltip">
            <i class="bi bi-arrow-left btn-icon"></i>Retour à la liste
        </a>
    </div>
</div>

<!-- Project Details -->
<div class="card mb-4 project-detail-glass project-detail-anim" data-aos="fade-up">
    <div class="card-header bg-white border-0">
        <h5 class="project-detail-section-title">
            <i class="bi bi-info-circle"></i>Informations générales
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <table class="table table-borderless project-detail-table">
                    <tr>
                        <th width="30%" class="project-detail-label">ID du projet:</th>
                        <td width="70%" class="project-detail-value"><span class="project-id" title="Identifiant unique du projet" data-bs-toggle="tooltip"><?php echo $project['id_projet']; ?></span></td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Titre:</th>
                        <td class="project-detail-value project-title"><strong><?php echo htmlspecialchars($project['titre']); ?></strong></td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Catégorie:</th>
                        <td class="project-detail-value"><?php echo htmlspecialchars($project['nom_categorie'] ?? 'Non catégorisé'); ?></td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Montant demandé:</th>
                        <td class="project-detail-value"><span class="budget-value" title="Montant demandé pour ce projet" data-bs-toggle="tooltip"><?php echo number_format($project['montant_demande'], 0, ',', ' ') . ' €'; ?></span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless project-detail-table">
                    <tr>
                        <th width="30%" class="project-detail-label">Statut:</th>
                        <td width="70%" class="project-detail-value">
                            <?php 
                            $statusClass = '';
                            switch(strtolower($project['statut'])) {
                                case 'accepté':
                                case 'approuvé':
                                case 'approved':
                                    $statusClass = 'badge-approved';
                                    break;
                                case 'en attente':
                                case 'pending':
                                    $statusClass = 'badge-pending';
                                    break;
                                case 'rejeté':
                                case 'rejected':
                                    $statusClass = 'badge-rejected';
                                    break;
                                default:
                                    $statusClass = 'badge-pending';
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?> shadow-sm" title="Statut du projet" data-bs-toggle="tooltip"><?php echo htmlspecialchars($project['statut']); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Date de création:</th>
                        <td class="project-detail-value"><?php echo isset($project['date_creation']) ? date('d/m/Y à H:i', strtotime($project['date_creation'])) : 'Non disponible'; ?></td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Dernière modification:</th>
                        <td class="project-detail-value"><?php echo isset($project['date_modification']) ? date('d/m/Y à H:i', strtotime($project['date_modification'])) : 'Non disponible'; ?></td>
                    </tr>
                    <tr>
                        <th class="project-detail-label">Propriétaire:</th>
                        <td class="project-detail-value"><?php echo isset($project['utilisateur_nom']) ? htmlspecialchars($project['utilisateur_prenom'] . ' ' . $project['utilisateur_nom']) : 'Non spécifié'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Project Description -->
<div class="card mb-4 project-detail-glass project-detail-anim" data-aos="fade-up" data-aos-delay="100">
    <div class="card-header bg-white border-0">
        <h5 class="project-detail-section-title">
            <i class="bi bi-file-text"></i>Description
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($project['description'])): ?>
            <p class="text-muted project-detail-description"><em>Aucune description fournie pour ce projet.</em></p>
        <?php else: ?>
            <p class="project-detail-description"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button (Edit) -->
<a href="#" id="fab-edit" class="fab-edit" title="Modifier ce projet" data-bs-toggle="tooltip" tabindex="0" aria-label="Modifier ce projet">
    <i class="bi bi-pencil"></i>
</a>

<?php include_once 'views/templates/footer.php'; ?> 