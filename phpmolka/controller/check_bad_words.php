<?php
require_once('BadWordsController.php');

header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No text provided']);
    exit;
}

$badWordsController = new BadWordsController();
$result = $badWordsController->containsBadWords($data['text']);

echo json_encode($result); 