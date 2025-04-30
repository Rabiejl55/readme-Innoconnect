<?php
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/Controller/utilisateurC.php';
session_start();

// Log file for debugging
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/debug.log';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    logMessage("User not logged in, redirecting to login");
    header("Location: login.php");
    exit;
}

$conn = config::getConnexion();
$userId = $_SESSION['id_utilisateur'];
$userC = new userC();
$user = $userC->getUserById($userId);

if (!$user) {
    logMessage("User not found for ID: $userId");
    header("Location: login.php?error=User not found");
    exit;
}

logMessage("User profile loaded for ID: $userId, photo_profil: " . ($user['photo_profil'] ?? 'NULL'));

// Handle the form submission for updating the profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $date_inscription = trim($_POST['date_inscription']);

    // Validate inputs
    $errors = [];
    if (empty($nom)) $errors[] = "Last name is required.";
    if (empty($prenom)) $errors[] = "First name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($date_inscription)) $errors[] = "Register date is required.";
    else {
        // Validate date format and ensure it's not in the future
        $dateObj = DateTime::createFromFormat('Y-m-d', $date_inscription);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date_inscription) {
            $errors[] = "Invalid date format for register date.";
        } else {
            $today = new DateTime();
            if ($dateObj > $today) {
                $errors[] = "Register date cannot be in the future.";
            }
        }
    }

    // Option 1: Email changes are disabled (email field is readonly)
    if ($email !== $user['email']) {
        $errors[] = "Email cannot be changed.";
    }

    if (!empty($errors)) {
        header("Location: profile.php?error=" . urlencode(implode(" ", $errors)));
        exit;
    }

    // Handle photo upload
    $photo_profil = $user['photo_profil'];
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ProjetInnoconnect/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            logMessage("Created upload directory: $uploadDir");
        }
        if (!is_writable($uploadDir)) {
            logMessage("Upload directory is not writable: $uploadDir");
            header("Location: profile.php?error=Upload directory is not writable");
            exit;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        $fileType = $_FILES['photo_profil']['type'];
        $fileSize = $_FILES['photo_profil']['size'];
        $fileTmpName = $_FILES['photo_profil']['tmp_name'];

        logMessage("Profile photo upload started: type=$fileType, size=$fileSize bytes");

        if (!in_array($fileType, $allowedTypes)) {
            logMessage("Profile photo upload failed: Invalid file type ($fileType)");
            header("Location: profile.php?error=Invalid file type. Only JPEG, PNG, and GIF are allowed");
            exit;
        }

        if ($fileSize > $maxFileSize) {
            logMessage("Profile photo upload failed: File size ($fileSize bytes) exceeds 5MB limit");
            header("Location: profile.php?error=File size exceeds 5MB limit");
            exit;
        }

        $fileExt = strtolower(pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION));
        $newFileName = $userId . '_' . time() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        logMessage("Attempting to move uploaded file to: $uploadPath");
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            logMessage("Profile photo upload failed: Unable to move file to $uploadPath");
            header("Location: profile.php?error=Failed to upload photo");
            exit;
        }

        try {
            $photo_profil = '/ProjetInnoconnect/uploads/' . $newFileName; // Correct path for database
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

    // Update profile
    try {
        // Format date if necessary (for DATETIME fields)
        $date_inscription_formatted = $date_inscription;
        if ($userC->isDateTimeField()) { // Check if date_inscription is DATETIME
            $date_inscription_formatted .= " 00:00:00";
        }

        // Call updateUser to update all fields
        $userC->updateUser($userId, $nom, $prenom, $email, $user['type'], $photo_profil, $date_inscription_formatted);
        logMessage("Profile updated successfully for user ID: $userId, date_inscription: $date_inscription_formatted");
    } catch (Exception $e) {
        logMessage("Error updating user: " . $e->getMessage());
        header("Location: profile.php?error=Error updating profile: " . urlencode($e->getMessage()));
        exit;
    }

    // Handle password update if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            header("Location: profile.php?error=Password must be at least 8 characters long");
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
        $stmt->execute([$hashed_password, $userId]); 
        logMessage("Password updated for user ID: $userId");
    }

    // Redirect to listeUser.php to reflect changes in the table
    header("Location: ../backOffice/listeUser.php?success=Profile updated successfully&highlight=$userId&changed=nom,prenom,email,date_inscription&t=" . time());
    exit;
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        // Delete the user's photo if it exists
        if ($user['photo_profil'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['photo_profil'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $user['photo_profil']);
            logMessage("Deleted profile picture: " . $user['photo_profil']);
        }
        $userC->deleteUser($userId);
        session_destroy();
        logMessage("User account deleted for ID: $userId");
        header("Location: login.php?success=Account deleted successfully");
        exit;
    } catch (Exception $e) {
        logMessage("Error deleting user: " . $e->getMessage());
        header("Location: profile.php?error=Error deleting account");
        exit;
    }
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
    <style>
        .profile-pic-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
            display: block;
        }
        .profile-pic-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #6c757d;
            border: 2px dashed #007bff;
        }
        .debug-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
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
                <li><a href="profile.php" class="active">My Profile</a></li>
                <?php if ($_SESSION['user_type'] === 'administrateur'): ?>
                    <li><a href="../backOffice/listeUser.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="profile-section">
            <h2>Welcome, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>!</h2>
            <p class="slogan">You are logged in as <?php echo htmlspecialchars($user['type']); ?>. Manage your personal information below.</p>
            <?php if (isset($_GET['success'])): ?>
                <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <div class="profile-pic-container">
                <?php
                $imagePath = '';
                $defaultImage = '../../uploads/default_profile.jpg';
                $debugMessage = '';
                if ($user['photo_profil']) {
                    $imagePath = $user['photo_profil']; // Use the path directly from the database
                    logMessage("Attempting to display profile picture: $imagePath");
                } else {
                    logMessage("No photo_profil set for user ID: $userId");
                    $debugMessage = "No profile picture set in the database.";
                }
                ?>
                <img src="<?php echo htmlspecialchars($imagePath ?: $defaultImage); ?>" alt="Profile Picture" class="profile-pic" onerror="console.log('Image failed to load: ' + this.src); this.onerror=null; this.src='<?php echo $defaultImage; ?>'; this.alt='Default Profile Picture';">
                <?php if ($debugMessage): ?>
                    <div class="debug-message"><?php echo htmlspecialchars($debugMessage); ?></div>
                <?php endif; ?>
            </div>
            <form method="POST" enctype="multipart/form-data" onsubmit="if (!validateForm()) return false; showLoader()">
                <div class="form-group input-with-icon">
                    <label for="photo_profil">Profile Picture</label>
                    <i class="fas fa-camera"></i>
                    <input type="file" id="photo_profil" name="photo_profil" accept="image/*" onchange="previewImage(event)">
                    <img id="photo-preview" style="display: none; width: 100px; height: 100px; margin-top: 10px; border-radius: 50%;">
                </div>
                <div class="form-group input-with-icon">
                    <label for="nom">Last Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" oninput="validateName('nom')">
                    <span id="nom-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="prenom">First Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" oninput="validateName('prenom')">
                    <span id="prenom-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    <span id="email-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter new password" oninput="validatePassword()">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    <span id="password-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="date_inscription">Registration Date</label>
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="date_inscription" name="date_inscription" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($user['date_inscription']))); ?>" oninput="validateDate()">
                    <span id="date_inscription-error" class="error-message" style="display: none;"></span>
                </div>
                <div class="form-group input-with-icon">
                    <label for="type">Type</label>
                    <i class="fas fa-user-tag"></i>
                    <input type="text" id="type" value="<?php echo htmlspecialchars($user['type']); ?>" disabled>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" name="update" class="btn-primary">Update</button>
                    <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</button>
                    <a href="profile.php" class="btn-danger">Cancel</a>
                </div>
            </form>
        </section>
    </main>

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

        function validatePassword() {
            const password = document.getElementById("password").value;
            const passwordError = document.getElementById("password-error");
            if (password.length > 0 && password.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                passwordError.style.display = "block";
            } else {
                passwordError.style.display = "none";
            }
        }

        function validateDate() {
            const date = document.getElementById("date_inscription").value;
            const dateError = document.getElementById("date_inscription-error");
            const today = new Date().toISOString().split("T")[0];
            if (!date) {
                dateError.textContent = "Registration date is required.";
                dateError.style.display = "block";
                return false;
            } else if (date > today) {
                dateError.textContent = "Registration date cannot be in the future.";
                dateError.style.display = "block";
                return false;
            } else {
                dateError.style.display = "none";
                return true;
            }
        }

        function validateForm() {
            const nomValid = validateName("nom");
            const prenomValid = validateName("prenom");
            validateEmail();
            validatePassword();
            const dateValid = validateDate();
            const emailError = document.getElementById("email-error").style.display === "block";
            const passwordError = document.getElementById("password-error").style.display === "block";
            return nomValid && prenomValid && dateValid && !emailError && !passwordError;
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

        function showLoader() {
            document.getElementById("loader").style.display = "flex";
        }
    </script>
</body>
</html>