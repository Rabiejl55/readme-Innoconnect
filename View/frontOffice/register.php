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
        header("Location: register.php?error=Invalid CSRF token");
        exit;
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $type = $_POST['type'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($type)) {
        header("Location: register.php?error=All fields are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=Invalid email format");
        exit;
    }

    $validTypes = ['investisseur', 'innovateur', 'administrateur'];
    if (!in_array($type, $validTypes)) {
        header("Location: register.php?error=Invalid user type");
        exit;
    }

    $userC = new userC();
    $existingUser = $userC->getUserByEmail($email);
    if ($existingUser) {
        header("Location: register.php?error=Email already exists");
        exit;
    }

    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $userC->ajouterUser($nom, $prenom, $email, $hashed_password, $type);
    header("Location: login.php?success=Registration successful! Please login.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - InnoConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="../assets2/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets2/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets2/css/bootstrap.min.css" rel="stylesheet" />
    <link id="pagestyle" href="../assets2/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
    <link href="../../styles.css" rel="stylesheet">
    <style>
        
        .strength-message {
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>

    <div class="loader" id="loader"></div>

    <header>
        <div class="logo">
            <img src="../../innoconnect.jpeg" alt="InnoConnect Logo">
        </div>
        <nav>
            <ul>
                <li><a href="../../index.html" class="active">Home</a></li>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 register-container">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Sign Up</h2>
                        <p class="slogan text-center mb-4">Join InnoConnect and Shape the Future of Innovation!</p>
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                        <?php endif; ?>
                        <form method="POST" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
                            <input type="text" style="display:none" name="fake-username">
                            <input type="password" style="display:none" name="fake-password">

                            <div class="form-group input-with-icon">
                                <label for="nom">Last Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="nom" name="nom" placeholder="Last Name" required autocomplete="off">
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="prenom">First Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="prenom" name="prenom" placeholder="First Name" required autocomplete="off">
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="email">Email</label>
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Email" required oninput="validateEmail()" autocomplete="off">
                                <span id="email-error" class="error-message" style="display: none;"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="mot_de_passe">Password</label>
                                <i class="fas fa-lock"></i>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" required autocomplete="off" oninput="checkPasswordStrength()">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
                                <span id="password-strength" class="strength-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="type">Type</label>
                                <i class="fas fa-user-tag"></i>
                                <select id="type" name="type" required autocomplete="off">
                                    <option value="" disabled selected>Type</option>
                                    <option value="investisseur">Investor</option>
                                    <option value="innovateur">Innovator</option>
                                    <option value="administrateur">Administrator</option>
                                </select>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <button type="submit" class="register-btn btn-primary">Sign Up</button>
                                <a href="../../index.html" class="btn-danger">Back</a>
                            </div>
                            <div>
                                <a href="forgot_password.php">Forgot Password?</a>
                            </div>
                        </form>
                        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
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

        function checkPasswordStrength() {
            const password = document.getElementById("mot_de_passe").value;
            const strengthMessage = document.getElementById("password-strength");
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (password.length === 0) {
                strengthMessage.textContent = "";
            } else if (strength <= 2) {
                strengthMessage.textContent = "Weak";
                strengthMessage.className = "strength-message strength-weak";
            } else if (strength === 3) {
                strengthMessage.textContent = "Medium";
                strengthMessage.className = "strength-message strength-medium";
            } else {
                strengthMessage.textContent = "Strong";
                strengthMessage.className = "strength-message strength-strong";
            }
        }

        function validateForm() {
            const nom = document.getElementById("nom").value;
            const prenom = document.getElementById("prenom").value;
            const email = document.getElementById("email").value;
            const mot_de_passe = document.getElementById("mot_de_passe").value;
            const emailError = document.getElementById("email-error");
            const nameRegex = /^[a-zA-Z\s]+$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;

            if (!nameRegex.test(nom)) {
                alert("Last name must contain only letters and spaces.");
                isValid = false;
            }
            if (!nameRegex.test(prenom)) {
                alert("First name must contain only letters and spaces.");
                isValid = false;
            }

            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            if (mot_de_passe.length < 8) {
                alert("Password must be at least 8 characters long.");
                isValid = false;
            }

            return isValid;
        }

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }

        window.onload = function() {
            document.getElementById('nom').value = '';
            document.getElementById('prenom').value = '';
            document.getElementById('email').value = '';
            document.getElementById('mot_de_passe').value = '';
            document.getElementById('type').value = '';
        };
    </script>
</body>
</html>