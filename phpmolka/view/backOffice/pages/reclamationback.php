<?php
require_once(__DIR__ . '/../../../controller/ReclamationController.php');
require_once(__DIR__ . '/../../../controller/ReponseController.php');
require_once(__DIR__ . '/../../../controller/AIController.php');
require_once(__DIR__ . '/../../../model/Reclamation.php');

// Initialiser les contrôleurs
$controller = new ReclamationController();
$reponseController = new ReponseController();
$aiController = new AIController();

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = 'reclamationback.php';
    
    if (isset($_POST['action'])) {
        $success = false;
        
        switch ($_POST['action']) {
            case 'update_status':
                if (isset($_POST['id'], $_POST['status'])) {
                    $reclamation = $controller->getReclamationById($_POST['id']);
                    if ($reclamation) {
                        $success = $controller->updateReclamation(
                            $_POST['id'],
                            $reclamation['date_reclamation'],
                            $reclamation['description_reclamation'],
                            $_POST['status']
                        );
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $success = $controller->deleteReclamation($_POST['id']);
                }
                break;

            case 'add_response':
                if (isset($_POST['id_reclamation'], $_POST['description'])) {
                    $id_user = 1; // À remplacer par l'ID de l'utilisateur connecté
                    $success = $reponseController->addReponse(
                        $_POST['id_reclamation'],
                        $id_user,
                        $_POST['description'],
                        date('Y-m-d')
                    );
                }
                break;

            case 'update_response':
                if (isset($_POST['id_reponse'], $_POST['description'])) {
                    $success = $reponseController->updateReponse(
                        $_POST['id_reponse'],
                        $_POST['description']
                    );
                }
                break;

            case 'delete_response':
                if (isset($_POST['id_reponse'])) {
                    $success = $reponseController->deleteReponse($_POST['id_reponse']);
                }
                break;
        }

        if ($success) {
            header("Location: " . $redirectUrl);
            exit();
        }
    }
}

// Récupérer toutes les réclamations
$reclamations = $controller->getReclamations();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>Claims Management - InnoConnect</title>

    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- jsPDF AutoTable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    
    <style>
        /* Status colors */
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .badge.bg-success {
            background-color: #28a745 !important;
            color: #fff !important;
        }
        
        .badge.bg-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        /* Select option colors */
        .status-select option.text-warning {
            color: #ffc107;
            background-color: #fff;
        }
        
        .status-select option.text-success {
            color: #28a745;
            background-color: #fff;
        }
        
        .status-select option.text-danger {
            color: #dc3545;
            background-color: #fff;
        }

        .character-counter {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .character-counter.error {
            color: #dc3545;
        }
        
        textarea.error-border {
            border-color: #dc3545;
        }
        
        .submit-button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .character-count {
            font-size: 0.8rem;
            transition: color 0.3s ease;
        }

        .character-count.text-danger {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .response-input {
            padding-bottom: 1.5rem;
        }

        .response-input.is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="index.html">
                <img src="../assets/img/logo.png" class="navbar-brand-img h-100" alt="logo">
                <span class="ms-1 font-weight-bold">InnoConnect</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.html">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reclamationback.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Claims</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <main class="main-content position-relative border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">Claims</li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">Claims Management</h6>
                </nav>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div class="row">
                <!-- Claims List Section -->
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h6>Claims List</h6>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-danger btn-sm me-2" onclick="exportToPDF()">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                    <button class="btn btn-info btn-sm me-2" onclick="showStatistics()">
                                        <i class="fas fa-chart-pie me-1"></i> Statistics
                                    </button>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input type="text" 
                                           class="form-control form-control-sm me-2" 
                                           id="searchDescription" 
                                           placeholder="Search by description..."
                                           onkeyup="searchClaims()">
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="me-2 text-sm">Sort by date:</label>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="sortClaimsByDate('asc')">
                                        <i class="fas fa-sort-amount-up-alt"></i> Oldest
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="sortClaimsByDate('desc')">
                                        <i class="fas fa-sort-amount-down-alt"></i> Latest
                                    </button>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="me-2 text-sm">Filter by status:</label>
                                    <select class="form-select form-select-sm" id="statusFilter" onchange="filterClaims()">
                                        <option value="all">All Claims</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reclamations as $reclamation): ?>
                                            <tr>
                                                <td class="align-middle text-center">
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($reclamation['date_reclamation']))); ?>
                                                    </p>
                                                </td>
                                                <td class="align-middle" style="max-width: 400px;">
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        <?php echo nl2br(htmlspecialchars($reclamation['description_reclamation'])); ?>
                                                    </p>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <?php
                                                    $badgeClass = '';
                                                    $status = '';
                                                    switch(strtolower($reclamation['etat_reclamation'])) {
                                                        case 'en attente':
                                                        case 'pending':
                                                            $badgeClass = 'bg-warning text-dark';
                                                            $status = 'Pending';
                                                            break;
                                                        case 'résolu':
                                                        case 'resolved':
                                                            $badgeClass = 'bg-success text-white';
                                                            $status = 'Resolved';
                                                            break;
                                                        case 'rejeté':
                                                        case 'rejected':
                                                            $badgeClass = 'bg-danger text-white';
                                                            $status = 'Rejected';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-sm <?php echo $badgeClass; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#statusModal<?php echo $reclamation['id_reclamation']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <?php
                                                    $responses = $reponseController->getReponsesByReclamation($reclamation['id_reclamation']);
                                                    $hasResponses = !empty($responses);
                                                    ?>
                                                    <button type="button" 
                                                            class="btn btn-info btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewResponsesModal<?php echo $reclamation['id_reclamation']; ?>">
                                                        <i class="fas fa-comments"></i> 
                                                        View Responses (<?php echo count($responses); ?>)
                                                    </button>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Are you sure you want to delete this claim?');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Status Modal -->
                                                    <div class="modal fade" id="statusModal<?php echo $reclamation['id_reclamation']; ?>" 
                                                         tabindex="-1" aria-labelledby="statusModalLabel<?php echo $reclamation['id_reclamation']; ?>" 
                                                         aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="statusModalLabel<?php echo $reclamation['id_reclamation']; ?>">
                                                                        Edit Status
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="action" value="update_status">
                                                                        <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Description:</label>
                                                                            <p class="text-sm">
                                                                                <?php echo nl2br(htmlspecialchars($reclamation['description_reclamation'])); ?>
                                                                            </p>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="status<?php echo $reclamation['id_reclamation']; ?>" class="form-label">
                                                                                New Status
                                                                            </label>
                                                                            <select class="form-select status-select" id="status<?php echo $reclamation['id_reclamation']; ?>" 
                                                                                    name="status" required>
                                                                                <option value="Pending" class="text-warning" <?php echo $reclamation['etat_reclamation'] == 'En attente' || $reclamation['etat_reclamation'] == 'Pending' ? 'selected' : ''; ?>>
                                                                                    Pending
                                                                                </option>
                                                                                <option value="Resolved" class="text-success" <?php echo $reclamation['etat_reclamation'] == 'Résolu' || $reclamation['etat_reclamation'] == 'Resolved' ? 'selected' : ''; ?>>
                                                                                    Resolved
                                                                                </option>
                                                                                <option value="Rejected" class="text-danger" <?php echo $reclamation['etat_reclamation'] == 'Rejeté' || $reclamation['etat_reclamation'] == 'Rejected' ? 'selected' : ''; ?>>
                                                                                    Rejected
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- View Responses Modal -->
                                                    <div class="modal fade" id="viewResponsesModal<?php echo $reclamation['id_reclamation']; ?>" 
                                                         tabindex="-1" aria-labelledby="viewResponsesModalLabel<?php echo $reclamation['id_reclamation']; ?>" 
                                                         aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="viewResponsesModalLabel<?php echo $reclamation['id_reclamation']; ?>">
                                                                        Responses for Claim #<?php echo $reclamation['id_reclamation']; ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Responses Section -->
                                                                    <div class="responses-section mt-3">
                                                                        <button class="btn btn-outline-primary btn-sm mb-2" type="button" 
                                                                                onclick="toggleAddResponseForm(<?php echo $reclamation['id_reclamation']; ?>)">
                                                                            <i class="fas fa-plus"></i> Add New Response
                                                                        </button>

                                                                        <div id="responseForm_<?php echo $reclamation['id_reclamation']; ?>" style="display: none;">
                                                                            <div class="card">
                                                                                <div class="card-body">
                                                                                    <form onsubmit="submitResponse(event, <?php echo $reclamation['id_reclamation']; ?>)">
                                                                                        <div class="mb-3">
                                                                                            <label class="form-label">Response Description</label>
                                                                                            <div class="position-relative">
                                                                                                <textarea 
                                                                                                    class="form-control" 
                                                                                                    id="responseText_<?php echo $reclamation['id_reclamation']; ?>"
                                                                                                    rows="3" 
                                                                                                    required
                                                                                                    onkeyup="checkLength(this, <?php echo $reclamation['id_reclamation']; ?>)"
                                                                                                ></textarea>
                                                                                                <div class="character-count position-absolute end-0 bottom-0 small text-muted pe-2 pb-1">
                                                                                                    0/30
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="invalid-feedback">
                                                                                                Response must not exceed 30 characters
                                                                                            </div>
                                                                                            <button type="button" class="btn btn-info btn-sm mt-2" 
                                                                                                    onclick="generateAIResponse(<?php echo $reclamation['id_reclamation']; ?>, '<?php echo addslashes($reclamation['description_reclamation']); ?>')">
                                                                                                <i class="fas fa-robot"></i> Generate with AI
                                                                                            </button>
                                                                                            <div id="aiSuggestions_<?php echo $reclamation['id_reclamation']; ?>" class="mt-2">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="text-end">
                                                                                            <button type="button" class="btn btn-secondary" onclick="toggleAddResponseForm(<?php echo $reclamation['id_reclamation']; ?>)">
                                                                                                Cancel
                                                                                            </button>
                                                                                            <button type="submit" class="btn btn-primary" id="submitBtn_<?php echo $reclamation['id_reclamation']; ?>" disabled>
                                                                                                Submit Response
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <?php if (empty($responses)): ?>
                                                                            <div class="alert alert-info mt-3">
                                                                                No responses yet for this claim.
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <?php foreach ($responses as $response): ?>
                                                                                <div class="card mt-3">
                                                                                    <div class="card-body">
                                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                            <small class="text-muted">
                                                                                                <?php echo date('d/m/Y H:i', strtotime($response['date_reponse'])); ?>
                                                                                            </small>
                                                                                            <div>
                                                                                                <button type="button" class="btn btn-warning btn-sm" onclick="showEditResponseForm(<?php echo $response['id_reponse']; ?>)">
                                                                                                    <i class="fas fa-edit"></i> Edit
                                                                                                </button>
                                                                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteResponse(<?php echo $response['id_reponse']; ?>, <?php echo $reclamation['id_reclamation']; ?>)">
                                                                                                    <i class="fas fa-trash"></i> Delete
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                        
                                                                                        <!-- View Mode -->
                                                                                        <div id="viewResponse<?php echo $response['id_reponse']; ?>">
                                                                                            <p class="card-text"><?php echo nl2br(htmlspecialchars($response['description_reponse'])); ?></p>
                                                                                        </div>
                                                                                        
                                                                                        <!-- Edit Mode -->
                                                                                        <div id="editResponse<?php echo $response['id_reponse']; ?>" style="display: none;">
                                                                                            <form onsubmit="updateResponse(event, <?php echo $response['id_reponse']; ?>)">
                                                                                                <div class="mb-3">
                                                                                                    <div class="position-relative">
                                                                                                        <textarea 
                                                                                                            class="form-control response-input" 
                                                                                                            name="description" 
                                                                                                            rows="3" 
                                                                                                            required
                                                                                                            onkeyup="checkLength(this, 'edit_<?php echo $response['id_reponse']; ?>')"
                                                                                                        ><?php echo htmlspecialchars($response['description_reponse']); ?></textarea>
                                                                                                        <div class="character-count position-absolute end-0 bottom-0 small text-muted pe-2 pb-1">
                                                                                                            <?php echo strlen($response['description_reponse']); ?>/30
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Response must not exceed 30 characters
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="text-end">
                                                                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditResponseForm(<?php echo $response['id_reponse']; ?>)">
                                                                                                        Cancel
                                                                                                    </button>
                                                                                                    <button type="submit" class="btn btn-primary btn-sm submit-btn" id="submitBtn_edit_<?php echo $response['id_reponse']; ?>" <?php echo strlen($response['description_reponse']) > 30 ? 'disabled' : ''; ?>>
                                                                                                        Save Changes
                                                                                                    </button>
                                                                                                </div>
                                                                                            </form>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- All Responses Modal -->
    <div class="modal fade" id="allResponsesModal" tabindex="-1" aria-labelledby="allResponsesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allResponsesModalLabel">All Responses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php
                        // Get all responses
                        $allResponses = [];
                        foreach ($reclamations as $reclamation) {
                            $responses = $reponseController->getReponsesByReclamation($reclamation['id_reclamation']);
                            if (!empty($responses)) {
                                foreach ($responses as $response) {
                                    $response['reclamation'] = $reclamation;
                                    $allResponses[] = $response;
                                }
                            }
                        }

                        // Sort responses by date (most recent first)
                        usort($allResponses, function($a, $b) {
                            return strtotime($b['date_reponse']) - strtotime($a['date_reponse']);
                        });

                        foreach ($allResponses as $response):
                        ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($response['date_reponse'])); ?>
                                        </small>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item" onclick="showEditResponseFormAll(<?php echo $response['id_reponse']; ?>)">
                                                        <i class="fas fa-edit text-warning"></i> Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="deleteResponse(<?php echo $response['id_reponse']; ?>, <?php echo $response['reclamation']['id_reclamation']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Claim Info -->
                                    <div class="mb-3">
                                        <h6 class="card-subtitle mb-2 text-muted">Related Claim:</h6>
                                        <p class="card-text small">
                                            <?php 
                                            $claimText = $response['reclamation']['description_reclamation'];
                                            echo strlen($claimText) > 100 ? 
                                                htmlspecialchars(substr($claimText, 0, 100)) . '...' : 
                                                htmlspecialchars($claimText);
                                            ?>
                                        </p>
                                        <span class="badge <?php 
                                            switch(strtolower($response['reclamation']['etat_reclamation'])) {
                                                case 'en attente':
                                                case 'pending':
                                                    echo 'bg-warning text-dark';
                                                    break;
                                                case 'résolu':
                                                case 'resolved':
                                                    echo 'bg-success';
                                                    break;
                                                case 'rejeté':
                                                case 'rejected':
                                                    echo 'bg-danger';
                                                    break;
                                            }
                                        ?>">
                                            <?php echo $response['reclamation']['etat_reclamation']; ?>
                                        </span>
                                    </div>

                                    <!-- Response Content -->
                                    <div id="viewResponse<?php echo $response['id_reponse']; ?>_all">
                                        <h6 class="card-subtitle mb-2 text-muted">Response:</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($response['description_reponse'])); ?></p>
                                    </div>

                                    <!-- Edit Form -->
                                    <div id="editResponse<?php echo $response['id_reponse']; ?>_all" style="display: none;">
                                        <form onsubmit="updateResponse(event, <?php echo $response['id_reponse']; ?>)">
                                            <div class="mb-3">
                                                <label class="form-label">Edit Response</label>
                                                <div class="position-relative">
                                                    <textarea 
                                                        class="form-control response-input" 
                                                        name="description" 
                                                        rows="3" 
                                                        required
                                                        onkeyup="checkLength(this, 'edit_all_<?php echo $response['id_reponse']; ?>')"
                                                    ><?php echo htmlspecialchars($response['description_reponse']); ?></textarea>
                                                    <div class="character-count position-absolute end-0 bottom-0 small text-muted pe-2 pb-1">
                                                        <?php echo strlen($response['description_reponse']); ?>/30
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Response must not exceed 30 characters
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditResponseFormAll(<?php echo $response['id_reponse']; ?>)">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-sm submit-btn" id="submitBtn_edit_all_<?php echo $response['id_reponse']; ?>" <?php echo strlen($response['description_reponse']) > 30 ? 'disabled' : ''; ?>>
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div class="modal fade" id="statisticsModal" tabindex="-1" aria-labelledby="statisticsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statisticsModalLabel">Claims Statistics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <canvas id="claimsChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody id="statsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--   Core JS Files   -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize perfect scrollbar only if element exists
            if (document.querySelector('#sidenav-scrollbar')) {
                var options = {
                    damping: '0.5'
                }
                Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
            }

            // Add event listener for statistics button
            const statsButton = document.querySelector('button[onclick="showStatistics()"]');
            if (statsButton) {
                statsButton.removeAttribute('onclick');
                statsButton.addEventListener('click', showStatistics);
            }
        });

        // Function to generate AI response with improved error handling
        function generateAIResponse(claimId, description) {
            console.log('Generating AI response for claim:', claimId);
            console.log('Description:', description);

            const button = document.querySelector(`button[onclick="generateAIResponse(${claimId}, '${description.replace(/'/g, "\\'")}')"]`);
            const suggestionsDiv = document.getElementById(`aiSuggestions_${claimId}`);
            const textarea = document.getElementById(`responseText_${claimId}`);
            
            if (!button || !suggestionsDiv || !textarea) {
                console.error('Required elements not found:', {
                    button: !!button,
                    suggestionsDiv: !!suggestionsDiv,
                    textarea: !!textarea
                });
                return;
            }
            
            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            suggestionsDiv.innerHTML = '<div class="text-muted">Generating suggestions...</div>';
            
            // Make API call
            fetch('../../../ajax_handler.php', {  // Updated path to point to root
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'generate_ai_response',
                    description: description
                })
            })
            .then(async response => {
                console.log('Raw response:', response);
                const text = await response.text();
                console.log('Response text:', text);
                
                try {
                    const data = JSON.parse(text);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}, message: ${data.message || 'Unknown error'}`);
                    }
                    return data;
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    throw new Error('Invalid JSON response from server');
                }
            })
            .then(data => {
                console.log('Received data:', data);
                
                if (data.success && Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                    console.log('Valid suggestions received:', data.suggestions);
                    
                    // Create analysis UI
                    const analysis = data.analysis;
                    const analysisHtml = `
                        <div class="card mt-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie text-info me-2"></i>
                                    Analyse de la Réclamation
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Catégorie:</strong> 
                                            <span class="badge bg-primary">${analysis.category}</span>
                                        </p>
                                        <p class="mb-1"><strong>Urgence:</strong> 
                                            <span class="badge bg-${analysis.urgency === 'high' ? 'danger' : analysis.urgency === 'medium' ? 'warning' : 'success'}">
                                                ${analysis.urgency}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Sentiment:</strong> 
                                            <span class="badge bg-${analysis.sentiment === 'negative' ? 'danger' : analysis.sentiment === 'neutral' ? 'secondary' : 'success'}">
                                                ${analysis.sentiment}
                                            </span>
                                        </p>
                                        <p class="mb-1"><strong>Type:</strong> 
                                            <span class="badge bg-info">
                                                ${analysis.containsQuestion ? 'Question' : 'Déclaration'}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                    // Create suggestions UI
                    const suggestionsList = data.suggestions.map(suggestion => {
                        const escapedSuggestion = suggestion.replace(/'/g, "\\'").replace(/"/g, "&quot;");
                        return `
                            <button type="button" 
                                    class="list-group-item list-group-item-action py-2" 
                                    onclick="useSuggestion(${claimId}, '${escapedSuggestion}')">
                                ${suggestion}
                            </button>`;
                    }).join('');
                    
                    suggestionsDiv.innerHTML = analysisHtml + `
                        <div class="card mt-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    Suggestions IA
                                </h6>
                            </div>
                            <div class="list-group list-group-flush">
                                ${suggestionsList}
                            </div>
                        </div>`;
                } else {
                    console.error('Invalid data structure received:', data);
                    throw new Error('No valid suggestions received');
                }
            })
            .catch(error => {
                console.error('Error in AI response generation:', error);
                suggestionsDiv.innerHTML = `
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unable to generate suggestions. Please try again or enter your response manually.
                        <br>
                        <small class="text-muted">${error.message}</small>
                    </div>`;
            })
            .finally(() => {
                // Re-enable button and restore text
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-robot"></i> Generate with AI';
            });
        }

        // Function to use a suggestion with improved error handling
        function useSuggestion(claimId, suggestion) {
            console.log('Using suggestion for claim:', claimId);
            console.log('Suggestion:', suggestion);

            const textarea = document.getElementById(`responseText_${claimId}`);
            if (textarea) {
                textarea.value = suggestion;
                checkLength(textarea, claimId);
                
                // Scroll the textarea into view with some offset
                const offset = 100; // pixels from the top
                const elementPosition = textarea.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Focus the textarea and trigger input event
                textarea.focus();
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Visual feedback
                textarea.style.backgroundColor = '#f0f9ff';
                setTimeout(() => {
                    textarea.style.backgroundColor = '';
                }, 1000);
            } else {
                console.error('Textarea not found for claim:', claimId);
            }
        }

        // Declare chart variable in global scope
        let claimsChart = null;

        // Function to sort claims by date
        function sortClaimsByDate(order) {
            const tbody = document.querySelector('table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                // Get the date text from the first column
                const dateTextA = a.querySelector('td:first-child').textContent.trim();
                const dateTextB = b.querySelector('td:first-child').textContent.trim();
                
                // Convert date from format "dd/mm/yyyy HH:ii" to Date object
                const [dateA, timeA] = dateTextA.split(' ');
                const [dayA, monthA, yearA] = dateA.split('/');
                const [hoursA, minutesA] = timeA.split(':');
                
                const [dateB, timeB] = dateTextB.split(' ');
                const [dayB, monthB, yearB] = dateB.split('/');
                const [hoursB, minutesB] = timeB.split(':');
                
                const dateObjA = new Date(yearA, monthA - 1, dayA, hoursA, minutesA);
                const dateObjB = new Date(yearB, monthB - 1, dayB, hoursB, minutesB);
                
                return order === 'asc' ? dateObjA - dateObjB : dateObjB - dateObjA;
            });

            // Clear the table body
            tbody.innerHTML = '';
            
            // Add sorted rows back to the table
            rows.forEach(row => {
                tbody.appendChild(row);
            });

            // Highlight active sort button
            const buttons = document.querySelectorAll('.btn-outline-primary');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (
                    (order === 'asc' && btn.textContent.includes('Oldest')) ||
                    (order === 'desc' && btn.textContent.includes('Latest'))
                ) {
                    btn.classList.add('active');
                }
            });
        }

        // Function to search claims by description
        function searchClaims() {
            console.log('Search function triggered');
            const searchText = document.getElementById('searchDescription').value.toLowerCase();
            console.log('Search text:', searchText);
            
            const tbody = document.querySelector('table tbody');
            const rows = tbody.getElementsByTagName('tr');
            console.log('Number of rows found:', rows.length);
            
            Array.from(rows).forEach(row => {
                // Get the description cell (second column)
                const descriptionCell = row.cells[1];
                const statusCell = row.cells[2];
                
                if (descriptionCell && statusCell) {
                    const description = descriptionCell.textContent.toLowerCase();
                    const statusFilter = document.getElementById('statusFilter').value;
                    const statusText = statusCell.textContent.trim();
                    
                    console.log('Row description:', description);
                    console.log('Current status filter:', statusFilter);
                    
                    // Check if the description contains the search text
                    const matchesSearch = searchText === '' || description.includes(searchText);
                    // Check if the status matches the filter
                    const matchesStatus = statusFilter === 'all' || statusText.includes(statusFilter);
                    
                    // Show/hide the row based on both conditions
                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                    
                    console.log('Match result:', matchesSearch && matchesStatus);
                }
            });
        }

        // Modify existing filterClaims function to work with search
        function filterClaims() {
            console.log('Filter function triggered');
            searchClaims(); // This will handle both status and search filtering
        }

        // Add event listener when the document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            
            // Add input event listener for real-time search
            const searchInput = document.getElementById('searchDescription');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    searchClaims();
                });
            }
            
            // Initialize the status filter
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    filterClaims();
                });
            }
        });

        // Function to toggle the add response form
        function toggleAddResponseForm(claimId) {
            const formDiv = document.getElementById(`responseForm_${claimId}`);
            if (formDiv) {
                const isVisible = formDiv.style.display === 'block';
                formDiv.style.display = isVisible ? 'none' : 'block';
                
                if (!isVisible) {
                    // Reset form when showing
                    const textarea = document.getElementById(`responseText_${claimId}`);
                    if (textarea) {
                        textarea.value = '';
                        checkLength(textarea, claimId);
                        textarea.focus();
                    }
                }
            }
        }

        // Function to check text length
        function checkLength(textarea, claimId) {
            const maxLength = 30;
            const currentLength = textarea.value.length;
            const submitBtn = document.getElementById(`submitBtn_${claimId}`);
            const charCount = textarea.parentElement.querySelector('.character-count');
            
            // Update character count
            charCount.textContent = `${currentLength}/${maxLength}`;
            
            // Visual feedback
            const isValid = currentLength > 0 && currentLength <= maxLength;
            textarea.classList.toggle('is-invalid', !isValid);
            charCount.classList.toggle('text-danger', !isValid);
            
            // Enable/disable submit button
            if (submitBtn) {
                submitBtn.disabled = !isValid;
            }
        }

        // Function to submit response
        function submitResponse(event, claimId) {
            event.preventDefault();
            
            const textarea = document.getElementById(`responseText_${claimId}`);
            if (!textarea) return;
            
            const formData = new FormData();
            formData.append('action', 'add_response');
            formData.append('id_reclamation', claimId);
            formData.append('description', textarea.value);
            
            // Disable form elements during submission
            const submitBtn = document.getElementById(`submitBtn_${claimId}`);
            if (submitBtn) submitBtn.disabled = true;
            
            fetch('../../../ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error adding response');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (submitBtn) submitBtn.disabled = false;
                alert('Error adding response: ' + error.message);
            });
        }

        // Function to delete response
        function deleteResponse(responseId, claimId) {
            if (confirm('Are you sure you want to delete this response?')) {
                const formData = new FormData();
                formData.append('action', 'delete_response');
                formData.append('id_reponse', responseId);

                fetch('../../../ajax_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Error deleting response');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting response: ' + error.message);
                });
            }
        }

        // Function to show edit response form
        function showEditResponseForm(responseId) {
            const viewDiv = document.getElementById('viewResponse' + responseId);
            const editDiv = document.getElementById('editResponse' + responseId);
            if (viewDiv && editDiv) {
                viewDiv.style.display = 'none';
                editDiv.style.display = 'block';
                const textarea = editDiv.querySelector('.response-input');
                if (textarea) {
                    // Initialize character count and validation
                    checkLength(textarea, 'edit_' + responseId);
                    textarea.focus();
                    // Place cursor at the end of the text
                    const length = textarea.value.length;
                    textarea.setSelectionRange(length, length);
                }
            }
        }

        // Function to hide edit response form
        function hideEditResponseForm(responseId) {
            const viewDiv = document.getElementById('viewResponse' + responseId);
            const editDiv = document.getElementById('editResponse' + responseId);
            if (viewDiv && editDiv) {
                viewDiv.style.display = 'block';
                editDiv.style.display = 'none';
            }
        }

        // Function to update response
        function updateResponse(event, responseId) {
            event.preventDefault();
            
            const form = event.target;
            const textarea = form.querySelector('textarea[name="description"]');
            if (!textarea) return;
            
            const formData = new FormData();
            formData.append('action', 'update_response');
            formData.append('id_reponse', responseId);
            formData.append('description', textarea.value);
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            
            fetch('../../../ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error updating response');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (submitBtn) submitBtn.disabled = false;
                alert('Error updating response: ' + error.message);
            });
        }

        // Function to show edit response form in all responses modal
        function showEditResponseFormAll(responseId) {
            const viewDiv = document.getElementById(`viewResponse${responseId}_all`);
            const editDiv = document.getElementById(`editResponse${responseId}_all`);
            if (viewDiv && editDiv) {
                viewDiv.style.display = 'none';
                editDiv.style.display = 'block';
                const textarea = editDiv.querySelector('.response-input');
                if (textarea) {
                    checkLength(textarea, 'edit_all_' + responseId);
                    textarea.focus();
                    const length = textarea.value.length;
                    textarea.setSelectionRange(length, length);
                }
            }
        }

        // Function to hide edit response form in all responses modal
        function hideEditResponseFormAll(responseId) {
            const viewDiv = document.getElementById(`viewResponse${responseId}_all`);
            const editDiv = document.getElementById(`editResponse${responseId}_all`);
            if (viewDiv && editDiv) {
                viewDiv.style.display = 'block';
                editDiv.style.display = 'none';
            }
        }

        // Function to show statistics
        function showStatistics() {
            console.log('showStatistics called');
            
            // Get all rows including hidden ones
            const rows = document.querySelectorAll('table tbody tr');
            console.log('Total rows found:', rows.length);
            
            const stats = {
                'Pending': 0,
                'Resolved': 0,
                'Rejected': 0
            };
            let total = 0;

            // Count claims by status
            rows.forEach(row => {
                if (row.style.display !== 'none') { // Only count visible rows
                    const statusCell = row.querySelector('td:nth-child(3)');
                    if (statusCell) {
                        const statusText = statusCell.textContent.trim();
                        console.log('Processing status:', statusText);
                        if (statusText.includes('Pending')) stats['Pending']++;
                        else if (statusText.includes('Resolved')) stats['Resolved']++;
                        else if (statusText.includes('Rejected')) stats['Rejected']++;
                        total++;
                    }
                }
            });

            console.log('Calculated stats:', stats);
            console.log('Total claims:', total);

            // Update stats table
            const statsTableBody = document.getElementById('statsTableBody');
            if (!statsTableBody) {
                console.error('Stats table body not found!');
                return;
            }
            
            statsTableBody.innerHTML = '';
            Object.entries(stats).forEach(([status, count]) => {
                const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
                const row = `
                    <tr>
                        <td>${status}</td>
                        <td>${count}</td>
                        <td>${percentage}%</td>
                    </tr>
                `;
                statsTableBody.innerHTML += row;
            });

            // Create or update chart
            const canvas = document.getElementById('claimsChart');
            if (!canvas) {
                console.error('Canvas element not found!');
                return;
            }

            // Clear the canvas for new chart
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            
            // Destroy existing chart if it exists
            if (claimsChart) {
                claimsChart.destroy();
                claimsChart = null;
            }

            try {
                claimsChart = new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(stats),
                        datasets: [{
                            data: Object.values(stats),
                            backgroundColor: [
                                '#ffc107', // Warning color for Pending
                                '#28a745', // Success color for Resolved
                                '#dc3545'  // Danger color for Rejected
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Claims Distribution by Status',
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                });
                console.log('Chart created successfully');

                // Show the modal
                const modalElement = document.getElementById('statisticsModal');
                if (!modalElement) {
                    console.error('Modal element not found!');
                    return;
                }
                const statisticsModal = new bootstrap.Modal(modalElement);
                statisticsModal.show();
                console.log('Modal shown successfully');

            } catch (error) {
                console.error('Error creating chart:', error);
            }
        }

        // Function to export claims to PDF
        function exportToPDF() {
            // Initialize jsPDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add title
            doc.setFontSize(18);
            doc.text('Claims Report', 14, 20);

            // Add date
            doc.setFontSize(11);
            doc.text('Generated on: ' + new Date().toLocaleString(), 14, 30);

            // Get claims data
            const rows = document.querySelectorAll('table tbody tr');
            const data = [];
            
            rows.forEach(row => {
                if (row.style.display !== 'none') { // Only export visible rows
                    const date = row.cells[0].textContent.trim();
                    const description = row.cells[1].textContent.trim();
                    const status = row.cells[2].textContent.trim();
                    data.push([date, description, status]);
                }
            });

            // Create the table
            doc.autoTable({
                startY: 40,
                head: [['Date', 'Description', 'Status']],
                body: data,
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: 255,
                    fontSize: 12,
                    halign: 'center'
                },
                styles: {
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    halign: 'left'
                },
                columnStyles: {
                    0: { cellWidth: 40 }, // Date column
                    1: { cellWidth: 100 }, // Description column
                    2: { cellWidth: 30 } // Status column
                },
                margin: { top: 40 },
                theme: 'striped',
                didDrawPage: function(data) {
                    // Add page number at the bottom
                    doc.setFontSize(10);
                    doc.text('Page ' + doc.internal.getCurrentPageInfo().pageNumber, doc.internal.pageSize.width - 20, doc.internal.pageSize.height - 10);
                }
            });

            // Add statistics
            const stats = calculateStats();
            const totalClaims = Object.values(stats).reduce((a, b) => a + b, 0);
            
            doc.addPage();
            doc.setFontSize(16);
            doc.text('Claims Statistics', 14, 20);
            
            doc.setFontSize(12);
            let yPos = 40;
            Object.entries(stats).forEach(([status, count]) => {
                const percentage = ((count / totalClaims) * 100).toFixed(1);
                doc.text(`${status}: ${count} (${percentage}%)`, 14, yPos);
                yPos += 10;
            });

            // Save the PDF
            doc.save('claims-report.pdf');
        }

        // Function to calculate statistics
        function calculateStats() {
            const stats = {
                'Pending': 0,
                'Resolved': 0,
                'Rejected': 0
            };

            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const statusCell = row.querySelector('td:nth-child(3)');
                    if (statusCell) {
                        const statusText = statusCell.textContent.trim();
                        if (statusText.includes('Pending')) stats['Pending']++;
                        else if (statusText.includes('Resolved')) stats['Resolved']++;
                        else if (statusText.includes('Rejected')) stats['Rejected']++;
                    }
                }
            });

            return stats;
        }
    </script>
</body>
</html>
