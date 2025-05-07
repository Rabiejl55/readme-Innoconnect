<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

function safeErrorLog($message, $destination = 'C:\xampp2\logs\debug.log') {
    $logDir = dirname($destination);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    if (@is_writable($destination) || @touch($destination)) {
        error_log($message . PHP_EOL, 3, $destination);
    } else {
        error_log("Custom log failed ($destination): $message");
    }
}
safeErrorLog('Starting chatbot.php');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['message'], $input['forum_id'], $input['user_id'])) {
    safeErrorLog('Invalid chatbot input: ' . print_r($input, true));
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$message = trim($input['message']);
$forum_id = filter_var($input['forum_id'], FILTER_VALIDATE_INT);
$user_id = filter_var($input['user_id'], FILTER_VALIDATE_INT);

if (empty($message) || $user_id <= 0) {
    safeErrorLog('Invalid message or user_id: message=' . $message . ', user_id=' . $user_id);
    echo json_encode(['success' => false, 'message' => 'Invalid message or user ID.']);
    exit;
}

safeErrorLog("Chatbot request: user_id=$user_id, forum_id=$forum_id, message=$message");

function callGeminiAPI($message, $apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($apiKey);
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $message]
                ]
            ]
        ]
    ];
    $payload = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        safeErrorLog('Curl error: ' . $error);
        return ['success' => false, 'message' => 'Curl error: ' . $error];
    }
    curl_close($ch);
    if ($httpCode !== 200) {
        safeErrorLog('Gemini API error: ' . $result);
        return ['success' => false, 'message' => 'Gemini API error: ' . $result];
    }
    $response = json_decode($result, true);
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return ['success' => true, 'message' => $response['candidates'][0]['content']['parts'][0]['text']];
    } else {
        safeErrorLog('Unexpected Gemini API response: ' . $result);
        return ['success' => false, 'message' => 'Unexpected Gemini API response.'];
    }
}

// Call Gemini API
$apiKey = 'AIzaSyCPFdtJ70422zFKK_6_DaXQOgeJBCDEiRM';
$response = callGeminiAPI($message, $apiKey);
safeErrorLog('Chatbot response: ' . json_encode($response));
echo json_encode($response);
exit;