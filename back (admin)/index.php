<?php
session_start();

// Include controllers
require_once 'controllers/ProjectController.php';
require_once 'controllers/CategoryController.php';

// Helper function to display flash messages
function displayMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error']);
    }
}

// Get controller and action from URL parameters
$controller = $_GET['controller'] ?? 'project';
$action = $_GET['action'] ?? 'dashboard';
$id = $_GET['id'] ?? null;

// Available controllers and their actions
$availableControllers = [
    'project' => ['dashboard', 'index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'destroy', 'changeStatus', 'export'],
    'category' => ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'destroy']
];

// Check if the controller exists
if (!array_key_exists($controller, $availableControllers)) {
    require_once 'views/admin/error/404.php';
    exit();
}

// Check if the action exists for the controller
if (!in_array($action, $availableControllers[$controller])) {
    require_once 'views/admin/error/404.php';
    exit();
}

// Instantiate the appropriate controller
switch ($controller) {
    case 'project':
        $controllerInstance = new ProjectController();
        break;
    case 'category':
        $controllerInstance = new CategoryController();
        break;
    default:
        require_once 'views/admin/error/404.php';
        exit();
}

// Call the appropriate action
try {
    if (method_exists($controllerInstance, $action)) {
        if ($id !== null) {
            $controllerInstance->$action($id);
        } else {
            if (in_array($action, ['store', 'update', 'destroy', 'changeStatus'])) {
                $controllerInstance->$action();
            } else {
                $controllerInstance->$action();
            }
        }
    } else {
        // Action doesn't exist in the controller
        require_once 'views/admin/error/404.php';
    }
} catch (Exception $e) {
    // Handle exceptions
    $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
    header('Location: index.php');
}
?> 