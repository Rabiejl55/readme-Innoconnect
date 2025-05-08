<?php
session_start();
require_once(__DIR__ . '/controller/AIController.php');
require_once(__DIR__ . '/controller/ReponseController.php');

header('Content-Type: application/json');

// Get the POST data
$rawData = file_get_contents('php://input');
$data = null;

// Try to decode JSON data
if (!empty($rawData)) {
    $data = json_decode($rawData, true);
}

// If JSON decode failed, try POST data
if ($data === null) {
    $data = $_POST;
}

// Log incoming request for debugging
error_log('Received request: ' . print_r($data, true));

if (!isset($data['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No action specified',
        'data' => null
    ]);
    exit;
}

try {
    switch ($data['action']) {
        case 'generate_ai_response':
            if (!isset($data['description'])) {
                throw new Exception('No description provided');
            }

            $aiController = new AIController();
            $result = $aiController->generateResponse($data['description']);
            
            // Log the result for debugging
            error_log('AI Response result: ' . print_r($result, true));
            
            echo json_encode($result);
            break;

        case 'add_response':
            if (!isset($data['id_reclamation']) || !isset($data['description'])) {
                throw new Exception('Missing required fields for adding response');
            }

            $reponseController = new ReponseController();
            $success = $reponseController->addReponse(
                $data['id_reclamation'],
                1, // Default user ID
                $data['description'],
                date('Y-m-d')
            );

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Response added successfully' : 'Failed to add response'
            ]);
            break;

        case 'update_response':
            if (!isset($data['id_reponse']) || !isset($data['description'])) {
                throw new Exception('Missing required fields for updating response');
            }

            $reponseController = new ReponseController();
            $success = $reponseController->updateReponse(
                $data['id_reponse'],
                $data['description']
            );

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Response updated successfully' : 'Failed to update response'
            ]);
            break;

        case 'delete_response':
            if (!isset($data['id_reponse'])) {
                throw new Exception('Missing response ID for deletion');
            }

            $reponseController = new ReponseController();
            $success = $reponseController->deleteReponse($data['id_reponse']);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Response deleted successfully' : 'Failed to delete response'
            ]);
            break;

        default:
            throw new Exception('Unrecognized action: ' . $data['action']);
    }
} catch (Exception $e) {
    error_log('Error in ajax_handler: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
} 