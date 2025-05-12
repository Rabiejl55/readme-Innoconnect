<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $pageTitle ?? 'inoconnect Bootstrap Template'; ?></title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        /* Custom styles for project management */
        body {
            padding-top: 70px;
        }
        .main-action-btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        .btn-icon {
            margin-right: 8px;
        }
        .main-action-btn-primary {
            background-color: #5846f9;
            color: white;
        }
        .main-action-btn-primary:hover {
            background-color: #4c3dd0;
            color: white;
        }
        .main-action-btn-outline {
            border: 1px solid #5846f9;
            color: #5846f9;
        }
        .main-action-btn-outline:hover {
            background-color: #5846f9;
            color: white;
        }
        .action-btn {
            padding: 5px 8px;
            margin: 0 2px;
            border-radius: 4px;
            display: inline-block;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            color: white;
        }
        .action-btn-view { background-color: #17a2b8; }
        .action-btn-edit { background-color: #28a745; }
        .action-btn-delete { background-color: #dc3545; }
        .badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: white;
        }
        .badge-approved { background-color: #28a745; }
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-rejected { background-color: #dc3545; }
        .empty-state {
            text-align: center;
            padding: 30px 0;
        }
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
        .card {
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .project-title {
            font-weight: 600;
        }
        .project-id {
            font-family: monospace;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .budget-value {
            font-weight: 500;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .table tbody + tbody {
            border-top: 2px solid #dee2e6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(88, 70, 249, 0.05);
        }
    </style>

    <script>
        // Function to handle button clicks with confirmation
        function confirmDelete(event, message) {
            if (!confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Function to navigate to a URL
        function goToPage(url) {
            window.location.href = url;
        }
    </script>
</head>
<body>

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <h1 class="sitename">inoconnect</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#about">About</a></li>
          <li><a href="index.php#services">Services</a></li>
          <li><a href="index.php#portfolio">Portfolio</a></li>
          <li><a href="index.php#team">Team</a></li>
          <li><a href="index.php?controller=project&action=index">Projects</a></li>
          <li><a href="index.php?controller=category&action=index">Categories</a></li>
          <li><a href="index.php#contact">Contact</a></li>
                        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="#about">Get Started</a>

                </div>
  </header>

  <main class="main">
    <!-- Page Title -->
    <div class="page-title accent-background">
      <div class="container position-relative">
        <h1><?php echo $pageHeader ?? 'inoconnect'; ?></h1>
        <p><?php echo $pageSubheader ?? ''; ?></p>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current"><?php echo $pageHeader ?? 'Page'; ?></li>
          </ol>
        </nav>
            </div>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="container"> 