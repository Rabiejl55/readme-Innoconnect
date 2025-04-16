<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $userC = new userC();
    if (method_exists($userC, 'connexionUser')) {
        $user = $userC->connexionUser($email, $mot_de_passe);

        if ($user) {
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            if ($user['type'] === 'administrateur') {
                header("Location: ../../dashboard.php");
            } else {
                header("Location: ../../index.html"); 
            }
            exit;
        } else {
            header("Location: login.php?error=Incorrect email or password");
            exit;
        }
    } else {
        header("Location: login.php?error=Login method not defined");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../styles.css" rel="stylesheet">
</head>
<body>
    <div class="loader" id="loader"></div>
    <header>
        <div class="logo">
            <img src="../../innoconnect.jpeg" alt="InnoConnect Logo">
        </div>
        <nav>
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php" class="active">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="register-container">
        <h2>Login</h2>
        <p class="slogan">Join InnoConnect and Shape the Future of Innovation!</p>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <form method="POST" onsubmit="showLoader()" autocomplete="off">
            <input type="text" style="display:none" name="fake-username">
            <input type="password" style="display:none" name="fake-password">

            <div class="form-group input-with-icon">
                <label for="email">Email</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" required oninput="validateEmail()" autocomplete="off">
                <span id="email-error" class="error-message" style="display: none;"></span>
            </div>
            <div class="form-group input-with-icon">
                <label for="mot_de_passe">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" required autocomplete="off">
                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <button type="submit" class="register-btn btn-primary">Login</button>
                <a href="../../index.html" class="btn-danger">Back</a>
            </div>
            <div>
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </form>
        <p>Don’t have an account? <a href="register.php">Sign Up</a></p>
    </div>

    <footer>
        <p>© 2025 InnoConnect. All rights reserved.</p>
    </footer>

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

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }

        window.onload = function() {
            document.getElementById('email').value = '';
            document.getElementById('mot_de_passe').value = '';
        };
    </script>
</body>
</html>