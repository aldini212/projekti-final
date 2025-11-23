<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['house'])) {
    echo json_encode(['success' => false, 'message' => 'No house specified']);
    exit;
}

$house = $_POST['house'];
$userId = $_SESSION['user_id'];

// Update user's house in the database
$stmt = $pdo->prepare("UPDATE users SET house = ? WHERE id = ?");
$success = $stmt->execute([$house, $userId]);

if ($success) {
    $_SESSION['user_house'] = $house;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}
