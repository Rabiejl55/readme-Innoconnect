<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Error - inoconnect</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .error-container {
            text-align: center;
            padding: 80px 20px;
            max-width: 800px;
            margin: 100px auto 0;
        }
        
        .error-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            color: #dc3545;
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            color: #666;
        }
        
        .btn-home {
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s;
            color: #fff;
            background: var(--color-primary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-home:hover {
            background: #742ecc;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <h1 class="sitename">inoconnect</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="index.html#about">About</a></li>
                    <li><a href="index.html#services">Services</a></li>
                    <li><a href="index.html#portfolio">Portfolio</a></li>
                    <li><a href="index.html#team">Team</a></li>
                    <li><a href="index.php?controller=project&action=index">Projects</a></li>
                    <li><a href="index.php?controller=category&action=index">Catégories</a></li>
                    <li><a href="index.html#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>
    
    <main class="main">
        <section class="section">
            <div class="error-container" data-aos="fade-up">
                <i class="bi bi-exclamation-triangle-fill error-icon"></i>
                <h1 class="error-title"><?php echo isset($errorTitle) ? $errorTitle : 'Une erreur est survenue'; ?></h1>
                <p class="error-message"><?php echo isset($errorMessage) ? $errorMessage : 'Veuillez réessayer plus tard.'; ?></p>
                <a href="index.html" class="btn-getstarted">
                    <i class="bi bi-house-door me-2"></i>Retourner à l'accueil
                </a>
            </div>
        </section>
    </main>
    
    <footer id="footer" class="footer accent-background">
        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">inoconnect</strong> <span>All Rights Reserved</span> <?php echo date('Y'); ?></p>
            <div class="credits">
                Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
            </div>
        </div>
    </footer>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>
</html> 