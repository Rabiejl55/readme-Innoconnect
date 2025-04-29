<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

// Générer un jeton CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php?error=Invalid CSRF token");
        exit;
    }

    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Server-side validation
    if (empty($email) || empty($mot_de_passe)) {
        header("Location: login.php?error=Email and password are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=Invalid email format");
        exit;
    }

    if (strlen($mot_de_passe) < 8) {
        header("Location: login.php?error=Password must be at least 8 characters long");
        exit;
    }

    $userC = new userC();
    if (method_exists($userC, 'connexionUser')) {
        $user = $userC->connexionUser($email, $mot_de_passe);

        if ($user) {
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['user_type'] = $user['type']; // Stocker le type d'utilisateur dans la session
            // Rediriger tous les utilisateurs vers profile.php
            header("Location: profile.php");
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
        <form method="POST" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" style="display:none" name="fake-username">
            <input type="password" style="display:none" name="fake-password">

            <div class="form-group input-with-icon">
                <label for="email">Email</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" oninput="validateEmail()" autocomplete="off">
                <span id="email-error" class="error-message" style="display: none;"></span>
            </div>
            <div class="form-group input-with-icon">
                <label for="mot_de_passe">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" oninput="validatePassword()" autocomplete="off">
                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
                <span id="password-error" class="error-message" style="display: none;"></span>
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

        function validatePassword() {
            const password = document.getElementById("mot_de_passe").value;
            const passwordError = document.getElementById("password-error");

            if (password.length === 0) {
                passwordError.textContent = "Password is required.";
                passwordError.style.display = "block";
            } else if (password.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                passwordError.style.display = "block";
            } else {
                passwordError.style.display = "none";
            }
        }

        function validateForm() {
            const email = document.getElementById("email").value;
            const password = document.getElementById("mot_de_passe").value;
            const emailError = document.getElementById("email-error");
            const passwordError = document.getElementById("password-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;

            // Validate Email
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            // Validate Password
            if (password.length === 0) {
                passwordError.textContent = "Password is required.";
                passwordError.style.display = "block";
                isValid = false;
            } else if (password.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                passwordError.style.display = "block";
                isValid = false;
            } else {
                passwordError.style.display = "none";
            }

            return isValid;
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