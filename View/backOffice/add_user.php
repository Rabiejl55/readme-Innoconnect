<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
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
$user = $userC->getUserById($userId);
if (!$user) {
    header("Location: ../frontOffice/login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['mot_de_passe']) || empty($_POST['type']) || empty($_POST['date_inscription'])) {
        header("Location: add_user.php?error=All fields are required");
        exit;
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = password_hash(trim($_POST['mot_de_passe']), PASSWORD_DEFAULT); // Hash the password
    $type = $_POST['type'];
    $date_inscription = $_POST['date_inscription'];

    // Handle photo upload
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/uploads/';
        $photoName = uniqid() . '-' . basename($_FILES['photo']['name']);
        $photoPath = $uploadDir . $photoName;

        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (in_array($_FILES['photo']['type'], $allowedTypes) && $_FILES['photo']['size'] <= $maxSize) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                $photo = '/ProjetInnoconnect/uploads/' . $photoName;
            } else {
                header("Location: add_user.php?error=Failed to upload photo");
                exit;
            }
        } else {
            header("Location: add_user.php?error=Invalid photo format or size");
            exit;
        }
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: add_user.php?error=Invalid email");
        exit;
    }

    // Validate user type
    $validTypes = ['administrateur', 'investisseur', 'innovateur'];
    if (!in_array($type, $validTypes)) {
        header("Location: add_user.php?error=Invalid user type");
        exit;
    }

    // Check if email already exists
    $existingUser = $userC->emailExists($email);
    if ($existingUser) {
        header("Location: add_user.php?error=Email already exists");
        exit;
    }

    try {
        // Add the user and get the new user's ID
        $newUserId = $userC->ajouterUser($nom, $prenom, $email, $mot_de_passe, $type, $date_inscription, $photo);
        if ($newUserId) {
            header("Location: listeUser.php?success=User added successfully&highlight=$newUserId");
        } else {
            header("Location: listeUser.php?success=User added successfully");
        }
        exit;
    } catch (Exception $e) {
        error_log("Error adding user: " . $e->getMessage());
        header("Location: add_user.php?error=Error adding user");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - InnoConnect</title>
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
            max-width: 800px;
            margin: 0 auto;
            animation: slideIn 0.5s ease-out;
        }

        /* Form Styles */
        .section form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .section .form-group {
            position: relative;
        }

        .section .form-group.full-width {
            grid-column: span 2;
        }

        .section label {
            font-size: 0.9em;
            color: #343a40;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .section .input-wrapper {
            position: relative;
        }

        .section .input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1em;
        }

        .section input,
        .section select {
            padding: 10px 15px 10px 40px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95em;
            width: 100%;
            box-sizing: border-box;
            background-color: #f8f9fa;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .section input[type="file"] {
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95em;
            width: 100%;
            box-sizing: border-box;
            background-color: #f8f9fa;
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
            padding-right: 40px;
        }

        .section .btn-group {
            grid-column: span 2;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
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
            background-color: #5e72e4;
            color: #fff;
            border: none;
        }

        .section .btn-primary:hover {
            background-color: #4a5db5;
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

        .alert {
            grid-column: span 2;
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
        .dark-mode .section label {
            color: #d1d9e6;
        }

        .dark-mode .page-header p {
            color: #adb5bd;
        }

        .dark-mode .section input,
        .dark-mode .section select {
            background-color: #3a4e6a;
            color: #d1d9e6;
            border-color: #4a5db5;
        }

        .dark-mode .section .input-wrapper i {
            color: #adb5bd;
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

            .section form {
                grid-template-columns: 1fr;
            }

            .section .form-group.full-width,
            .section .btn-group,
            .alert {
                grid-column: span 1;
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
                <span>Add User</span>
            </div>
            <div class="nav-icons">
                <i class="fas fa-bell"></i>
                <i class="fas fa-moon theme-toggle"></i>
            </div>
            <div class="user-info">
                <img src="https://via.placeholder.com/32" alt="User Avatar">
                <span><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
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
                    <h2>Add User</h2>
                    <p>Create a new user by filling out the details below.</p>
                </div>
                <a href="listeUser.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <div class="section">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <form method="POST" action="add_user.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nom">Last Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="prenom">First Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="prenom" name="prenom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="type">User Type</label>
                        <div class="input-wrapper">
                            <i class="fas fa-users"></i>
                            <select id="type" name="type" required>
                                <option value="administrateur">Administrator</option>
                                <option value="investisseur">Investor</option>
                                <option value="innovateur">Innovator</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="photo">Profile Photo (Optional)</label>
                        <div class="input-wrapper">
                            <input type="file" id="photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="date_inscription">Registration Date</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" id="date_inscription" name="date_inscription" required>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Add User</button>
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