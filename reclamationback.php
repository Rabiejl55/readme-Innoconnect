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
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Claims List</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <?php
                                require_once(__DIR__ . '/../../../controller/ReclamationController.php');
                                require_once(__DIR__ . '/../../../model/Reclamation.php');

                                $controller = new ReclamationController();

                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    if (isset($_POST['action'])) {
                                        switch ($_POST['action']) {
                                            case 'update_status':
                                                if (isset($_POST['id'], $_POST['status'])) {
                                                    $reclamation = $controller->getReclamationById($_POST['id']);
                                                    if ($reclamation) {
                                                        if ($controller->updateReclamation(
                                                            $_POST['id'],
                                                            $reclamation['date_reclamation'],
                                                            $reclamation['description_reclamation'],
                                                            $_POST['status']
                                                        )) {
                                                            echo '<div class="alert alert-success mx-4">Claim status updated successfully!</div>';
                                                        } else {
                                                            echo '<div class="alert alert-danger mx-4">Error updating claim status.</div>';
                                                        }
                                                    }
                                                }
                                                break;

                                            case 'delete':
                                                if (isset($_POST['id'])) {
                                                    if ($controller->deleteReclamation($_POST['id'])) {
                                                        echo '<div class="alert alert-success mx-4">Claim deleted successfully!</div>';
                                                    } else {
                                                        echo '<div class="alert alert-danger mx-4">Error deleting claim.</div>';
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }

                                $reclamations = $controller->getReclamations();
                                ?>

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
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Are you sure you want to delete this claim?');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                    <!-- Modal -->
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

    <!--   Core JS Files   -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>
</body>
</html>
