<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required fields are present
if (!isset($data['game'], $data['score'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'message' => 'Score could not be saved'
];

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $game = sanitizeInput($data['game']);
    $score = (int)$data['score'];
    $attempts = isset($data['attempts']) ? (int)$data['attempts'] : null;
    $time = isset($data['time']) ? (int)$data['time'] : null;
    $correct = isset($data['correct']) ? (int)$data['correct'] : null;
    $total = isset($data['total']) ? (int)$data['total'] : null;

    // Get game ID
    $stmt = $pdo->prepare("SELECT id FROM games WHERE slug = ?");
    $stmt->execute([$game]);
    $gameData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gameData) {
        // If game doesn't exist, create it
        $gameName = ucwords(str_replace('-', ' ', $game));
        $stmt = $pdo->prepare("INSERT INTO games (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$gameName, $game, 'Game added automatically']);
        $gameId = $pdo->lastInsertId();
    } else {
        $gameId = $gameData['id'];
    }

    // Save the score
    $sql = "INSERT INTO scores (user_id, game_id, score, attempts, time_taken, correct_answers, total_questions, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $userId,
        $gameId,
        $score,
        $attempts,
        $time,
        $correct,
        $total
    ]);

    if ($success) {
        $response = [
            'success' => true,
            'message' => 'Score saved successfully',
            'score_id' => $pdo->lastInsertId()
        ];
        
        // Update user's total score and level if user is logged in
        if ($userId) {
            updateUserStats($pdo, $userId, $score);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

/**
 * Update user's total score and level
 */
function updateUserStats($pdo, $userId, $score) {
    // Update total score
    $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $stmt->execute([$score, $userId]);
    
    // Get current points and level
    $stmt = $pdo->prepare("SELECT points, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check for level up (every 1000 points)
    $newLevel = floor($user['points'] / 1000) + 1;
    if ($newLevel > $user['level']) {
        $stmt = $pdo->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$newLevel, $userId]);
    }
}
?>
