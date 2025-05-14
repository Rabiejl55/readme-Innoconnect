<?php
session_start();

require_once '../../config/config.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: collaborativespace.php?error=Invalid forum ID");
    exit;
}

$forum_id = $_GET['id'];
$pdo = config::getConnexion();

// Load approved forums (for legacy compatibility)
$approved_file = '../../config/approved_forums.json';
$approved_forums = file_exists($approved_file) ? json_decode(file_get_contents($approved_file), true) : [];

try {
    // Fetch forum details, including status
    $stmt = $pdo->prepare("
        SELECT f.id, f.titre, f.category, f.date_creation, f.status,
               COALESCE(u.prenom, '') AS prenom, COALESCE(u.nom, '') AS nom 
        FROM forums f 
        LEFT JOIN utilisateur u ON f.user_id = u.id_utilisateur 
        WHERE f.id = :id
    ");
    $stmt->execute(['id' => $forum_id]);
    $forum = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$forum) {
        header("Location: collaborativespace.php?error=Forum not found");
        exit;
    }

    // Fetch messages
    $stmt = $pdo->prepare("
        SELECT m.message, m.date_creation, COALESCE(u.prenom, '') AS prenom, 
               COALESCE(u.nom, '') AS nom 
        FROM messages m 
        LEFT JOIN utilisateur u ON m.user_id = u.id_utilisateur 
        WHERE m.forum_id = :id 
        ORDER BY m.date_creation ASC
    ");
    $stmt->execute(['id' => $forum_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching forum: " . $e->getMessage());
    header("Location: collaborativespace.php?error=Failed to load forum");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets2/img/apple-icon.png">
    <link rel="icon" type="image/jpg" href="../../assets/img/innoconnect.jpg">
    <title>View Forum - Innoconnect</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link id="pagestyle" href="../../assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <style>
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .message-item { padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
        .message-item strong { color: #5e72e4; }
        .badge { padding: 6px 10px; font-size: 12px; }
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="http://localhost/espace_comm/espace%20communotaire/config/index.html" target="_blank">
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
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">View Forum</li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">View Forum</h6>
                </div>
            </nav>
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6>Forum Details</h6>
                                <p class="text-sm">Title: <?php echo htmlspecialchars($forum['titre']); ?></p>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="px-4">
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($forum['category'] ?: 'General'); ?></p>
                                    <p><strong>Creator:</strong> <?php echo htmlspecialchars(trim($forum['prenom'] . ' ' . $forum['nom']) ?: 'Anonyme'); ?></p>
                                    <p><strong>Date Created:</strong> <?php echo htmlspecialchars($forum['date_creation'] ?: 'N/A'); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge badge-<?php echo $forum['status'] === 'rejected' ? 'rejected' : ($forum['status'] === 'approved' ? 'approved' : 'pending'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($forum['status'] ?: 'pending')); ?>
                                        </span>
                                    </p>
                                    <div class="mt-3">
                                        <?php if ($forum['status'] !== 'approved' && $forum['status'] !== 'rejected'): ?>
                                            <a href="approve_forum.php?id=<?php echo htmlspecialchars($forum['id']); ?>" 
                                               class="btn btn-sm btn-success me-2" 
                                               data-toggle="tooltip" 
                                               data-original-title="Approuver">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($forum['status'] !== 'rejected'): ?>
                                            <a href="reject_forum.php?id=<?php echo htmlspecialchars($forum['id']); ?>" 
                                               class="btn btn-sm btn-danger me-2" 
                                               data-toggle="tooltip" 
                                               data-original-title="Reject">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <a href="delete_forum.php?id=<?php echo htmlspecialchars($forum['id']); ?>" 
                                           class="btn btn-sm btn-danger me-2" 
                                           data-toggle="tooltip" 
                                           data-original-title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <a href="collaborativespace.php" 
                                           class="btn btn-sm btn-secondary" 
                                           data-toggle="tooltip" 
                                           data-original-title="Back to Dashboard">
                                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                                <hr class="horizontal dark my-4">
                                <h6 class="px-4">Messages</h6>
                                <div class="px-4">
                                    <?php if (empty($messages)): ?>
                                        <p class="text-sm text-muted">No messages in this forum.</p>
                                    <?php else: ?>
                                        <?php foreach ($messages as $message): ?>
                                            <div class="message-item">
                                                <strong><?php echo htmlspecialchars(trim($message['prenom'] . ' ' . $message['nom']) ?: 'Anonyme'); ?>:</strong>
                                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                                <small class="text-muted"><?php echo htmlspecialchars($message['date_creation'] ?: 'N/A'); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
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
                            <a href="http://localhost/espace_comm/espace%20communotaire/config/index.html" class="font-weight-bold" target="_blank">Innoconnect</a>
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
        <script src="../../assets2/js/argon-dashboard.min.js?v=2.1.0"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>
    </body>
</html>