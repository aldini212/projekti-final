<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit scores']);
    exit;
}

// Validate required fields
$required = ['game_id', 'score'];
foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize input
$gameId = (int)$data['game_id'];
$score = (int)$data['score'];
$timeSpent = isset($data['time_spent']) ? (int)$data['time_spent'] : 0;

// Validate score and time
if ($score < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Score cannot be negative']);
    exit;
}

if ($timeSpent < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Time spent cannot be negative']);
    exit;
}

// Record the game completion
$result = recordGameCompletion($_SESSION['user_id'], $gameId, $score, $timeSpent);

// Return the result
if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(500);
}

echo json_encode($result);
