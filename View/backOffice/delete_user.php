<?php
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Check user type
$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$userType = $userC->getUserType($userId);

if ($userType !== 'administrateur') {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Get current admin user for display in navbar
$adminUser = $userC->getUserById($userId);
if (!$adminUser) {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id_utilisateur']) || empty($_GET['id_utilisateur'])) {
    header("Location: listeUser.php?error=User ID is missing");
    exit;
}

$userIdToDelete = (int)$_GET['id_utilisateur'];
$userToDelete = $userC->getUserById($userIdToDelete);

if (!$userToDelete) {
    header("Location: listeUser.php?error=User not found");
    exit;
}

// Handle form submission for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $userC->deleteUser($userIdToDelete);
    if ($success) {
        header("Location: listeUser.php?success=User deleted successfully");
    } else {
        header("Location: listeUser.php?error=Failed to delete user");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - InnoConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <link id="pagestyle" href="../../assets2/css/argon-dashboard.css" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1a2c42 0%, #2a3e5a 100%);
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            color: #fff;
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .sidebar .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .sidebar .logo span {
            font-size: 1.3em;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #d1d9e6;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.95em;
            font-weight: 500;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #5e72e4;
            color: #fff;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.2em;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            background: linear-gradient(90deg, #5e72e4 0%, #7b92ff 100%);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-size: 0.9em;
        }

        .navbar .breadcrumb a {
            color: #e0e0e0;
            text-decoration: none;
        }

        .navbar .breadcrumb a:hover {
            color: #fff;
        }

        .navbar .breadcrumb i {
            color: #e0e0e0;
            font-size: 0.8em;
        }

        .navbar .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar .nav-icons i {
            color: #fff;
            font-size: 1.2em;
            cursor: pointer;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .navbar .nav-icons i:hover {
            color: #e0e0e0;
            transform: scale(1.1);
        }

        .navbar .user-info {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .navbar .user-info img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .navbar .user-info span {
            color: #fff;
            font-size: 0.95em;
            font-weight: 500;
        }

        .navbar .user-info .dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 150px;
        }

        .navbar .user-info:hover .dropdown {
            display: block;
        }

        .navbar .user-info .dropdown a {
            display: block;
            padding: 10px 20px;
            color: #1a2c42;
            text-decoration: none;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .navbar .user-info .dropdown a:hover {
            background-color: #f8f9fa;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            flex-grow: 1;
            background: #f4f6f9;
            min-height: calc(100vh - 70px);
            width: calc(100% - 260px);
        }

        .page-header {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.5s ease-out;
        }

        .page-header h2 {
            color: #1a2c42;
            font-size: 1.8em;
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            color: #6c757d;
            font-size: 0.9em;
            margin: 5px 0 0;
        }

        .page-header .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #e9ecef;
            color: #5e72e4;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .page-header .back-btn i {
            margin-right: 8px;
        }

        .page-header .back-btn:hover {
            background-color: #d3d7db;
        }

        .section {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 0 auto;
            animation: slideIn 0.5s ease-out;
            text-align: center;
        }

        .section .confirmation-message {
            margin-bottom: 30px;
        }

        .section .confirmation-message p {
            color: #343a40;
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        .section .confirmation-message span {
            color: #dc3545;
            font-weight: 500;
        }

        .section .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .section .btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .section .btn:hover {
            transform: translateY(-2px);
        }

        .section .btn-primary {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .section .btn-primary:hover {
            background-color: #c82333;
        }

        .section .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .section .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dark Mode */
        .dark-mode {
            background-color: #1a2c42;
        }

        .dark-mode .sidebar {
            background: linear-gradient(180deg, #0f1a2b 0%, #1a2c42 100%);
        }

        .dark-mode .main-content {
            background: #1a2c42;
        }

        .dark-mode .page-header,
        .dark-mode .section {
            background-color: #2a3e5a;
            color: #d1d9e6;
        }

        .dark-mode .page-header h2,
        .dark-mode .section p {
            color: #d1d9e6;
        }

        .dark-mode .page-header p {
            color: #adb5bd;
        }

        .dark-mode .section span {
            color: #ff6b6b;
        }

        /* Responsive Design */
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
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
                border-bottom: 1px solid #e9ecef;
                z-index: 1000;
            }

            .sidebar.active {
                display: block;
            }

            .navbar {
                left: 0;
                flex-wrap: wrap;
                padding: 10px;
            }

            .navbar .navbar-toggler {
                display: block;
                color: #fff;
                font-size: 1.5em;
                cursor: pointer;
            }

            .navbar .breadcrumb {
                width: 100%;
                margin-bottom: 10px;
            }

            .navbar .nav-icons,
            .navbar .user-info {
                flex-grow: 1;
                justify-content: flex-end;
            }

            .main-content {
                margin-left: 0;
                margin-top: 120px;
                width: 100%;
            }

            .section {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="g-sidenav-show bg-gray-100">
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="../../innoconnect.jpeg" alt="InnoConnect Logo">
                <span>InnoConnect</span>
            </div>
            <ul>
                <li>
                    <a href="../../dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="listeUser.php" class="active">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="../frontOffice/profile.php">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="../frontOffice/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Navbar -->
        <div class="navbar">
            <div class="breadcrumb">
                <i class="fas fa-bars navbar-toggler" style="display: none;"></i>
                <a href="listeUser.php">User Management</a>
                <i class="fas fa-chevron-right"></i>
                <span>Delete User</span>
            </div>
            <div class="nav-icons">
                <i class="fas fa-bell"></i>
                <i class="fas fa-moon theme-toggle"></i>
            </div>
            <div class="user-info">
                <img src="https://via.placeholder.com/32" alt="User Avatar">
                <span><?php echo htmlspecialchars($adminUser['prenom'] . ' ' . $adminUser['nom']); ?></span>
                <div class="dropdown">
                    <a href="../frontOffice/profile.php">My Profile</a>
                    <a href="../frontOffice/logout.php">Logout</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h2>Delete User</h2>
                    <p>Confirm the deletion of the user below.</p>
                </div>
                <a href="listeUser.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <div class="section">
                <div class="confirmation-message">
                    <p>Are you sure you want to delete the user:</p>
                    <span><?php echo htmlspecialchars($userToDelete['prenom'] . ' ' . $userToDelete['nom']); ?> (<?php echo htmlspecialchars($userToDelete['email']); ?>)</span>
                    <p style="margin-top: 10px; color: #6c757d;">This action cannot be undone.</p>
                </div>
                <form method="POST" action="delete_user.php?id_utilisateur=<?php echo htmlspecialchars($userIdToDelete); ?>">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Delete User</button>
                        <a href="listeUser.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets2/js/core/popper.min.js"></script>
    <script src="../../assets2/js/core/bootstrap.min.js"></script>
    <script src="../../assets2/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets2/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets2/js/argon-dashboard.min.js?v=2.1.0"></script>
    <script>
        // Theme Toggle (Light/Dark Mode)
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            themeToggle.classList.toggle('fa-moon');
            themeToggle.classList.toggle('fa-sun');
        });

        // Sidebar Toggle for Mobile
        const sidebar = document.querySelector('.sidebar');
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Show sidebar toggler on mobile
        if (window.innerWidth <= 576) {
            document.querySelector('.navbar-toggler').style.display = 'block';
        }
    </script>
</body>
</html>