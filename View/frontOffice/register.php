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

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log file for debugging
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logMessage("Starting registration process");

    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logMessage("CSRF token validation failed");
        header("Location: register.php?error=Invalid CSRF token");
        exit;
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $type = $_POST['type'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($type)) {
        logMessage("Validation failed: Missing required fields");
        header("Location: register.php?error=All fields are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logMessage("Validation failed: Invalid email format");
        header("Location: register.php?error=Invalid email format");
        exit;
    }

    $validTypes = ['investisseur', 'innovateur', 'administrateur'];
    if (!in_array($type, $validTypes)) {
        logMessage("Validation failed: Invalid user type");
        header("Location: register.php?error=Invalid user type");
        exit;
    }

    $userC = new userC();
    $existingUser = $userC->emailExists($email);
    if ($existingUser) {
        logMessage("Validation failed: Email already exists");
        header("Location: register.php?error=Email already exists");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // Set the registration date
    $date_inscription = date('Y-m-d H:i:s');

    // Set photo_profil to null initially
    $photo_profil = null;

    // Add the user to the database
    $userId = null;
    try {
        logMessage("Attempting to add user: nom=$nom, email=$email");
        $db = config::getConnexion();
        $db->beginTransaction();
        $userC->ajouterUser($nom, $prenom, $email, $hashed_password, $type, $date_inscription, $photo_profil);
        $userId = $db->lastInsertId();
        $db->commit();
        logMessage("User added successfully with ID: $userId");
    } catch (Exception $e) {
        $db->rollBack();
        logMessage("Error during user insertion: " . $e->getMessage());
        header("Location: register.php?error=" . urlencode($e->getMessage()));
        exit;
    }

    // Handle photo upload in a separate step
    $uploadPath = null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        $fileType = $_FILES['photo_profil']['type'];
        $fileSize = $_FILES['photo_profil']['size'];
        $fileTmpName = $_FILES['photo_profil']['tmp_name'];

        logMessage("Photo upload started: type=$fileType, size=$fileSize bytes");

        if (!in_array($fileType, $allowedTypes)) {
            logMessage("File upload failed: Invalid file type ($fileType)");
            header("Location: login.php?success=Registration successful! Please login. Note: Invalid file type for photo (JPEG, PNG, GIF only).");
            exit;
        }

        if ($fileSize > $maxFileSize) {
            logMessage("File upload failed: File size ($fileSize bytes) exceeds 5MB limit");
            header("Location: login.php?success=Registration successful! Please login. Note: Photo exceeds 5MB limit.");
            exit;
        }

        // Check if upload directory exists and is writable
        if (!is_dir($uploadDir)) {
            logMessage("Upload directory does not exist: $uploadDir");
            header("Location: login.php?success=Registration successful! Please login. Note: Server error - upload directory not found.");
            exit;
        }
        if (!is_writable($uploadDir)) {
            logMessage("Upload directory is not writable: $uploadDir");
            header("Location: login.php?success=Registration successful! Please login. Note: Server error - upload directory not writable.");
            exit;
        }

        $fileExt = strtolower(pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION));
        $newFileName = $userId . '_' . time() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        logMessage("Attempting to move uploaded file to: $uploadPath");
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            logMessage("File upload failed: Unable to move file to $uploadPath");
            header("Location: login.php?success=Registration successful! Please login. Note: Failed to upload photo.");
            exit;
        }

        // Update the user with the photo path
        try {
            $photo_profil = '' . $newFileName;
            $db = config::getConnexion();
            logMessage("Updating user with photo_profil: $photo_profil");
            $stmt = $db->prepare("UPDATE utilisateur SET photo_profil = :photo_profil WHERE id_utilisateur = :id_utilisateur");
            $stmt->execute([':photo_profil' => $photo_profil, ':id_utilisateur' => $userId]);
            logMessage("Photo uploaded and user updated with photo_profil: $photo_profil");
        } catch (Exception $e) {
            if ($uploadPath && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            logMessage("Error updating user with photo: " . $e->getMessage());
            header("Location: login.php?success=Registration successful! Please login. Note: Failed to save photo due to database error.");
            exit;
        }
    }

    logMessage("Registration completed successfully");
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
        .error-message { color: #dc3545; font-size: 0.85em; margin-top: 5px; display: none; }
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
                        <form method="POST" enctype="multipart/form-data" onsubmit="if (!validateForm()) return false; showLoader()" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
                            <input type="text" style="display:none" name="fake-username">
                            <input type="password" style="display:none" name="fake-password">

                            <div class="form-group input-with-icon">
                                <label for="nom">Last Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="nom" name="nom" placeholder="Last Name" autocomplete="off" oninput="validateName('nom')">
                                <span id="nom-error" class="error-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="prenom">First Name</label>
                                <i class="fas fa-user"></i>
                                <input type="text" id="prenom" name="prenom" placeholder="First Name" autocomplete="off" oninput="validateName('prenom')">
                                <span id="prenom-error" class="error-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="email">Email</label>
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Email" oninput="validateEmail()" autocomplete="off">
                                <span id="email-error" class="error-message" style="display: none;"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="mot_de_passe">Password</label>
                                <i class="fas fa-lock"></i>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Password" autocomplete="off" oninput="checkPasswordStrength()">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('mot_de_passe')"></i>
                                <span id="password-strength" class="strength-message"></span>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="type">Type</label>
                                <i class="fas fa-user-tag"></i>
                                <select id="type" name="type" autocomplete="off">
                                    <option value="" disabled selected>Type</option>
                                    <option value="investisseur">Investor</option>
                                    <option value="innovateur">Innovator</option>
                                    <option value="administrateur">Administrator</option>
                                </select>
                            </div>
                            <div class="form-group input-with-icon">
                                <label for="photo_profil">Profile Picture (Optional)</label>
                                <i class="fas fa-camera"></i>
                                <input type="file" id="photo_profil" name="photo_profil" accept="image/*" onchange="previewImage(event)">
                                <img id="photo-preview" style="display: none; width: 100px; height: 100px; margin-top: 10px; border-radius: 50%;">
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
        function validateName(fieldId) {
            const field = document.getElementById(fieldId);
            const value = field.value.trim();
            const errorElement = document.getElementById(`${fieldId}-error`);
            const nameRegex = /^[a-zA-Z\s]+$/;

            if (value === "") {
                errorElement.textContent = fieldId === "nom" ? "Last name is required." : "First name is required.";
                errorElement.style.display = "block";
                return false;
            } else if (!nameRegex.test(value)) {
                errorElement.textContent = fieldId === "nom" ? "Last name must contain only letters and spaces." : "First name must contain only letters and spaces.";
                errorElement.style.display = "block";
                return false;
            } else {
                errorElement.style.display = "none";
                return true;
            }
        }

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

        function previewImage(event) {
            const preview = document.getElementById('photo-preview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
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
            const type = document.getElementById("type").value;
            const emailError = document.getElementById("email-error");
            const nomError = document.getElementById("nom-error");
            const prenomError = document.getElementById("prenom-error");
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;

            // Validate Last Name
            if (!validateName("nom")) {
                isValid = false;
            }

            // Validate First Name
            if (!validateName("prenom")) {
                isValid = false;
            }

            // Validate Email
            if (!emailRegex.test(email)) {
                emailError.textContent = "Please enter a valid email.";
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            // Validate Password
            if (mot_de_passe.length < 8) {
                alert("Password must be at least 8 characters long.");
                isValid = false;
            }

            // Validate Type
            if (type === "") {
                alert("Please select a user type.");
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