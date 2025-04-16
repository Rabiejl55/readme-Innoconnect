<?php
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login.php");
    exit;
}

$conn = config::getConnexion();
$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$user = $userC->getUserById($userId);

if (!$user) {
    header("Location: login.php?error=User not found");
    exit;
}

// Handle the form submission for updating the profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];

    $userC->updateUser($userId, $nom, $prenom, $email, $user['type']);
    header("Location: profile.php?success=Profile updated successfully");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
</head>
<body>
    <!-- Global Loader -->
    <div class="loader" id="loader"></div>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="../../innoconnect.jpeg" alt="InnoConnect Logo">
        </div>
        <nav>
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="profile.php" class="active">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="profile-section">
            <h2>My Profile</h2>
            <p class="slogan">Manage your personal information</p>
            <?php if (isset($_GET['success'])): ?>
                <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <form method="POST" onsubmit="showLoader()">
                <div class="form-group input-with-icon">
                    <label for="nom">Last Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                </div>
                <div class="form-group input-with-icon">
                    <label for="prenom">First Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                </div>
                <div class="form-group input-with-icon">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required oninput="validateEmail()">
                    <span id="email-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="type">Type</label>
                    <i class="fas fa-user-tag"></i>
                    <input type="text" id="type" value="<?php echo htmlspecialchars($user['type']); ?>" disabled>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" class="btn-primary">Update</button>
                    <a href="profile.php" class="btn-danger">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>© 2025 InnoConnect. All rights reserved.</p>
    </footer>

    <!-- Scripts for validation and loader -->
    <script>
        function validateEmail() {
            const email = document.getElementById("email").value;
            const emailError = document.getElementById("email-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
            } else {
                emailError.style.display = "none";
            }
        }

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }
    </script>
</body>
</html>