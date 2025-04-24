<?php
session_start();

// Inclure la connexion à la base de données
require_once __DIR__ . '/../../config/config.php';

// Get the PDO connection
$pdo = config::getConnexion();

// Load approved forums
$approved_file = __DIR__ . '/../../config/approved_forums.json';
$approved_forums = file_exists($approved_file) ? json_decode(file_get_contents($approved_file), true) : [];

// Récupérer les forums actifs
$discussions = $pdo->query("SELECT * FROM forums")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer des posts pour la file de modération
try {
    $query = "
        SELECT f.id, f.titre, f.category, f.date_creation AS date, f.status, COALESCE(u.prenom, '') AS prenom, COALESCE(u.nom, '') AS nom 
        FROM forums f 
        LEFT JOIN utilisateur u ON f.user_id = u.id_utilisateur 
        ORDER BY f.date_creation DESC 
        LIMIT 5
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $moderationQueue = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $moderationQueue = [];
    error_log("Erreur récupération moderation queue: " . $e->getMessage());
}

// Récupérer des tâches fictives
$tasks = $pdo->query("
    SELECT id, titre AS title, 'Admin' AS responsible, 
           DATE_ADD(NOW(), INTERVAL 2 DAY) AS deadline
    FROM forums 
    LIMIT 2
")->fetchAll(PDO::FETCH_ASSOC);

// Mark tasks completed from session
$_SESSION['completed_tasks'] = $_SESSION['completed_tasks'] ?? [];
foreach ($tasks as &$task) {
    $task['status'] = in_array($task['id'], $_SESSION['completed_tasks']) ? 'Completed' : 'En attente';
}
unset($task);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets2/img/apple-icon.png">
    <link rel="icon" type="image/jpg" href="../../assets/img/innoconnect.jpg">
    <title>Collaborative Space - Innoconnect</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link id="pagestyle" href="../../assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .custom-table { border-collapse: separate; border-spacing: 0 10px; }
        .custom-table thead th {
            background-color: #f8f9fa; padding: 12px 15px; border-bottom: 2px solid #dee2e6; transition: background-color 0.3s ease;
        }
        .custom-table tbody tr {
            background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease; animation: fadeIn 0.5s ease-in-out;
            animation-delay: calc(0.1s * var(--row-index));
        }
        .custom-table tbody tr:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); }
        .custom-table td { padding: 15px; vertical-align: middle; }
        .custom-table .btn { padding: 6px 12px; font-size: 12px; transition: all 0.3s ease; }
        .custom-table .btn:hover { transform: scale(1.05); }
        .custom-table .btn-success { background-color: #28a745; border-color: #28a745; }
        .custom-table .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .custom-table .btn-info { background-color: #17a2b8; border-color: #17a2b8; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .badge { padding: 6px 10px; font-size: 12px; transition: transform 0.2s ease, background-color 0.3s ease, box-shadow 0.3s ease; }
        .badge:hover { transform: scale(1.1); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); }
        .list-group-item { transition: background-color 0.3s ease; }
        .list-group-item:hover { background-color: #f8f9fa; }
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="../index.html" target="_blank">
                <img src="../../assets/img/innoconnect.jpg" width="26px" height="26px" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Innoconnect</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="collaborativespace.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-credit-card text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Collaborative Space</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account pages</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../frontOffice/profile.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../frontOffice/logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-key-25 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">Collaborative Space</li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">Collaborative Space</h6>
                </div>
            </nav>
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">ACTIVE FORUMS</p>
                                            <h5 class="font-weight-bolder"><?php echo count($discussions); ?></h5>
                                            <p class="mb-0">Actives</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">MODERATION QUEUE</p>
                                            <h5 class="font-weight-bolder"><?php echo count($moderationQueue); ?></h5>
                                            <p class="mb-0">Posts to Review</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                            <i class="ni ni-bell-55 text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-lg-7 mb-lg-0 mb-4">
                        <div class="card z-index-2 h-100">
                            <div class="card-header pb-0 pt-3 bg-transparent">
                                <h6 class="text-capitalize">Forum Statistics</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart">
                                    <canvas id="forumStats" class="chart-canvas" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header pb-0 p-3">
                                <h6 class="mb-0">Summary</h6>
                            </div>
                            <div class="card-body p-3">
                                <ul class="list-group">
                                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                                <i class="ni ni-mobile-button text-white opacity-10"></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-1 text-dark text-sm">ACTIVE FORUMS</h6>
                                                <span class="text-xs"><?php echo count($discussions); ?> discussions</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                                <i class="ni ni-bell-55 text-white opacity-10"></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-1 text-dark text-sm">MODERATION QUEUE</h6>
                                                <span class="text-xs"><?php echo count($moderationQueue); ?> posts to review</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contributions Récentes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6>Recent Contributions</h6>
                                <p class="text-sm">Latest Forum Activity</p>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <ul class="list-group list-group-flush">
                                    <?php foreach (array_slice($discussions, 0, 5) as $discussion): ?>
                                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                                    <i class="ni ni-chat-round text-white opacity-10"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1 text-dark text-sm"><?php echo htmlspecialchars($discussion['titre']); ?></h6>
                                                    <span class="text-xs text-secondary">Ajouté le <?php echo htmlspecialchars($discussion['date_creation'] ?: 'N/A'); ?></span>
                                                </div>
                                            </div>
                                            <a href="view_forum.php?id=<?php echo htmlspecialchars($discussion['id']); ?>" 
                                               class="btn btn-link text-dark px-2 py-0" 
                                               data-toggle="tooltip" 
                                               data-original-title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (empty($discussions)): ?>
                                    <div class="text-center py-4">
                                        <p class="text-sm text-muted">Aucune contribution récente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Forum Moderation Queue -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6>Forum Moderation Queue</h6>
                                <p class="text-sm">Review and manage forum posts</p>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0 custom-table">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Titre</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catégorie</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Créateur</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                                <th class="text-center text-uppercase text-xxs font-weight-bolder opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rowIndex = 0; foreach ($moderationQueue as $post): $rowIndex++; ?>
                                                <tr style="--row-index: <?php echo $rowIndex; ?>;">
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($post['id']); ?></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($post['titre']); ?></p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($post['category'] ?: 'General'); ?></p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars(trim($post['prenom'] . ' ' . $post['nom']) ?: 'Anonyme'); ?></p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($post['date'] ?: 'N/A'); ?></p>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $post['status'] === 'rejected' ? 'rejected' : ($post['status'] === 'approved' ? 'approved' : 'pending'); ?>">
                                                            <?php echo htmlspecialchars(ucfirst($post['status'] ?: 'pending')); ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <?php if ($post['status'] !== 'approved' && $post['status'] !== 'rejected'): ?>
                                                            <a href="approve_forum.php?id=<?php echo htmlspecialchars($post['id']); ?>" 
                                                               class="btn btn-sm btn-success me-2" 
                                                               data-toggle="tooltip" 
                                                               data-original-title="Approuver">
                                                                <i class="fas fa-check"></i> Approve
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="reject_forum.php?id=<?php echo htmlspecialchars($post['id']); ?>" 
                                                           class="btn btn-sm btn-danger me-2" 
                                                           data-toggle="tooltip" 
                                                           data-original-title="Reject">
                                                            <i class="fas fa-times"></i> Reject
                                                        </a>
                                                        <a href="view_forum.php?id=<?php echo htmlspecialchars($post['id']); ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           data-toggle="tooltip" 
                                                           data-original-title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (empty($moderationQueue)): ?>
                                    <div class="text-center py-4">
                                        <p class="text-sm text-muted">No posts awaiting moderation.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer pt-3">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            © <script>document.write(new Date().getFullYear())</script>,
                            made with <i class="fa fa-heart"></i> by
                            <a href="../index.html" class="font-weight-bold" target="_blank">Innoconnect</a>
                            for a better web.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <script src="../../assets2/js/core/popper.min.js"></script>
        <script src="../../assets2/js/core/bootstrap.min.js"></script>
        <script src="../../assets2/js/plugins/perfect-scrollbar.min.js"></script>
        <script src="../../assets2/js/plugins/smooth-scrollbar.min.js"></script>
        <script src="../../assets2/js/plugins/chartjs.min.js"></script>
        <script>
            const ctx = document.getElementById('forumStats').getContext('2d');
            const forumStats = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                    datasets: [{
                        label: 'Discussions Actives',
                        tension: 0.4,
                        borderWidth: 0,
                        pointRadius: 0,
                        borderColor: "#5e72e4",
                        borderWidth: 3,
                        fill: true,
                        data: [12, 19, 3, 5, 2],
                        maxBarThickness: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        y: {
                            grid: { drawBorder: false, display: true, drawOnChartArea: true, drawTicks: false, borderDash: [5, 5] },
                            ticks: { display: true, padding: 10, color: '#fbfbfb', font: { size: 11, family: "Open Sans", style: 'normal', lineHeight: 2 } }
                        },
                        x: {
                            grid: { drawBorder: false, display: false, drawOnChartArea: false, drawTicks: false, borderDash: [5, 5] },
                            ticks: { display: true, color: '#ccc', padding: 20, font: { size: 11, family: "Open Sans", style: 'normal', lineHeight: 2 } }
                        }
                    }
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>
        <script>
            var win = navigator.platform.indexOf('Win') > -1;
            if (win && document.querySelector('#sidenav-scrollbar')) {
                var options = { damping: '0.5' };
                Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
            }
        </script>
        <script async defer src="https://buttons.github.io/buttons.js"></script>
        <script src="../../assets2/js/argon-dashboard.min.js?v=2.1.0"></script>
    </body>
</html>