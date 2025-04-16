<?php
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

if (!isset($_GET['id_utilisateur']) || empty($_GET['id_utilisateur'])) {
    header("Location: listeUser.php?error=User ID is missing");
    exit;
}

$userIdToEdit = (int)$_GET['id_utilisateur'];

$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    header("Location: ../frontOffice/login.php");
    exit;
}


$userToEdit = $userC->getUserById($userIdToEdit);
if (!$userToEdit) {
    header("Location: listeUser.php?error=User not found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['type'])) {
        header("Location: edit_user.php?id_utilisateur=$userIdToEdit&error=All fields are required");
        exit;
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $type = $_POST['type'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: edit_user.php?id_utilisateur=$userIdToEdit&error=Invalid email");
        exit;
    }

    $validTypes = ['administrateur', 'investisseur', 'innovateur'];
    if (!in_array($type, $validTypes)) {
        header("Location: edit_user.php?id_utilisateur=$userIdToEdit&error=Invalid user type");
        exit;
    }

    try {
        $success = $userC->updateUser($userIdToEdit, $nom, $prenom, $email, $type);
        if ($success) {
            header("Location: listeUser.php?success=User updated successfully");
        } else {
            header("Location: edit_user.php?id_utilisateur=$userIdToEdit&error=Error during update");
        }
        exit;
    } catch (Exception $e) {
        error_log("Error updating user: " . $e->getMessage());
        header("Location: edit_user.php?id_utilisateur=$userIdToEdit&error=Error during update");
        exit;
    }
}

$user = $userC->getUserById($userId);
if (!$user) {
    header("Location: ../frontOffice/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - InnoConnect</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="../assets2/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets2/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../../assets2/css/bootstrap.min.css" rel="stylesheet" />
    <link id="pagestyle" href="../../assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; 
        }

        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            background-color: #fff;
            padding: 20px;
            border-right: 1px solid #e9ecef;
            position: fixed;
            top: 0; 
            left: 0;
            height: 100vh; 
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000; 
        }

        .sidebar img {
            width: 150px;
            margin-bottom: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .sidebar h2 {
            font-size: 1.1em;
            color: #1A2C42;
            margin-bottom: 25px;
            text-align: center;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #5e72e4;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #5e72e4;
            color: #fff;
        }

        .sidebar ul li a:hover i,
        .sidebar ul li a.active i {
            color: #fff;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.1em;
            color: #5e72e4;
            transition: color 0.3s ease;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 250px; 
            right: 0;
            background-color: #1A2C42;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar .search-bar {
            display: flex;
            align-items: center;
            background-color: #fff;
            border-radius: 20px;
            padding: 8px 15px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar .search-bar i {
            color: #adb5bd;
            margin-right: 10px;
            font-size: 1em;
        }

        .navbar .search-bar input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.9em;
            background: transparent;
        }

        .navbar .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar .notification {
            position: relative;
        }

        .navbar .notification .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: #fff;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7em;
            font-weight: 600;
        }

        .navbar .nav-icons i {
            color: #fff;
            font-size: 1.2em;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .navbar .nav-icons i:hover {
            color: #5e72e4;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .user-info img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .navbar .user-info span {
            color: #fff;
            font-size: 0.95em;
            font-weight: 500;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px; /* Match the sidebar width */
            margin-top: 70px; /* Match the navbar height */
            padding: 30px;
            flex-grow: 1;
            background-color: #f8f9fa;
            min-height: calc(100vh - 70px);
            width: calc(100% - 250px); /* Ensure it takes remaining width */
        }

        .section {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 0 auto;
        }

        .section h2 {
            color: #1A2C42;
            font-size: 1.6em;
            margin-bottom: 30px;
            font-weight: 600;
        }

        /* Form Styles */
        .section form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section .form-group {
            position: relative;
        }

        .section label {
            font-size: 0.95em;
            color: #5e72e4;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .section input,
        .section select {
            padding: 12px 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95em;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .section input:focus,
        .section select:focus {
            outline: none;
            border-color: #5e72e4;
            box-shadow: 0 0 8px rgba(94, 114, 228, 0.2);
        }

        .section select {
            appearance: none;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyLjI4MjggNC4yODI4M0w4LjAwMDEgOC41NjU2N0wzLjcxNzQzIDQuMjgyODNIMTIuMjgyOFoiIGZpbGw9IiM1ZTcyZTQiLz4KPC9zdmc+') no-repeat right 15px center;
            background-size: 12px;
        }

        .section .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .section .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .section .btn-primary {
            background-color: #5e72e4;
            color: #fff;
            border: none;
        }

        .section .btn-primary:hover {
            background-color: #4a5db5;
        }

        .section .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .section .btn-danger:hover {
            background-color: #c82333;
        }

        .alert {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.95em;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .navbar {
                left: 200px;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .navbar .search-bar {
                width: 200px;
            }

            .section {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e9ecef;
                z-index: 1000;
            }

            .navbar {
                left: 0;
                flex-wrap: wrap;
                padding: 10px;
            }

            .main-content {
                margin-left: 0;
                margin-top: 120px; 
                width: 100%;
            }

            .navbar .search-bar {
                width: 100%;
                margin-bottom: 10px;
            }

            .navbar .nav-icons,
            .navbar .user-info {
                flex-grow: 1;
                justify-content: flex-end;
            }

            .section {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="g-sidenav-show bg-gray-100">
    <div class="container">
        <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
            <div class="sidenav-header">
                <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
                <a class="navbar-brand m-0" href="#">
                    <img src="../../innoconnect.jpeg" width="26px" height="26px" class="navbar-brand-img h-100" alt="main_logo">
                    <span class="ms-1 font-weight-bold">Innoconnect</span>
                </a>
            </div>
            <hr class="horizontal dark mt-0">
            <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../../dashboard.php">
                            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="listeUser.php">
                            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">User Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../frontOffice/profile.php">
                            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../frontOffice/logout.php">
                            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-key-25 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="navbar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            <div class="nav-icons">
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">9</span>
                </div>
                <i class="fas fa-moon theme-toggle"></i>
            </div>
            <div class="user-info">
                <img src="https://via.placeholder.com/30" alt="User Avatar">
                <span><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
            </div>
        </div>

        <div class="main-content">
            <div class="section">
                <h2>Edit User</h2>
                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                }
                if (isset($_GET['success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
                }
                ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="nom">Last Name:</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($userToEdit['nom']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">First Name:</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($userToEdit['prenom']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userToEdit['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Type:</label>
                        <select id="type" name="type" required>
                            <option value="administrateur" <?php if ($userToEdit['type'] === 'administrateur') echo 'selected'; ?>>Administrator</option>
                            <option value="investisseur" <?php if ($userToEdit['type'] === 'investisseur') echo 'selected'; ?>>Investor</option>
                            <option value="innovateur" <?php if ($userToEdit['type'] === 'innovateur') echo 'selected'; ?>>Innovator</option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="listeUser.php" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"></i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">InnoConnect Configurator</h5>
                    <p>Customize your dashboard.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default" onclick="sidebarType(this)">Dark</button>
                </div>
                <div class="d-flex my-3">
                    <h6 class="mb-0">Fixed Navbar</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets2/js/core/popper.min.js"></script>
    <script src="../../assets2/js/core/bootstrap.min.js"></script>
    <script src="../../assets2/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets2/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets2/js/argon-dashboard.min.js?v=2.1.0"></script>
</body>
</html>