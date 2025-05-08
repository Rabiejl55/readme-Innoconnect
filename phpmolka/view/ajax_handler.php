<?php
require_once(__DIR__ . '/../controller/ReclamationController.php');
require_once(__DIR__ . '/../controller/ReponseController.php');

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize controllers
try {
    $reclamationController = new ReclamationController();
    $reponseController = new ReponseController();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

// Get the action from POST request
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    switch ($action) {
        case 'update_response':
            if (!isset($_POST['id_reponse']) || !isset($_POST['description'])) {
                throw new Exception('Missing parameters for response update');
            }
            
            if ($reponseController->updateReponse(
                intval($_POST['id_reponse']),
                $_POST['description']
            )) {
                $response['success'] = true;
                $response['message'] = 'Response updated successfully';
            } else {
                throw new Exception('Error updating response');
            }
            break;

        case 'delete_response':
            if (!isset($_POST['id_reponse'])) {
                throw new Exception('Missing response ID');
            }
            
            if ($reponseController->deleteReponse(intval($_POST['id_reponse']))) {
                $response['success'] = true;
                $response['message'] = 'Response deleted successfully';
            } else {
                throw new Exception('Error deleting response');
            }
            break;

        case 'add_response':
            if (!isset($_POST['id_reclamation']) || !isset($_POST['description'])) {
                throw new Exception('Missing parameters for adding response');
            }
            
            $id_user = 1; // To be replaced with logged-in user ID
            $newResponseId = $reponseController->addReponse(
                intval($_POST['id_reclamation']),
                $id_user,
                $_POST['description'],
                date('Y-m-d H:i:s')
            );
            
            if ($newResponseId) {
                $response['success'] = true;
                $response['message'] = 'Response added successfully';
                $response['data'] = ['id' => $newResponseId];
            } else {
                throw new Exception('Error adding response');
            }
            break;

        default:
            throw new Exception('Unrecognized action');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Error in ajax_handler: " . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit;
?> 