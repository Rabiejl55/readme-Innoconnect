<?php
// Application configuration

// Base URL - adjust based on your server configuration
define('BASE_URL', 'http://localhost/Techie2/Techie');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Error display (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Helper function to redirect
function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit();
}

// Flash message helper
function setFlashMessage($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlashMessage($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// Debugging function
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?> 