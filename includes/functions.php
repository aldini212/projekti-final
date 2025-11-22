<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to a specific URL
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require login for protected pages
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

// Sanitize user input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get user data
function getUserData($userId = null) {
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) return null;
    
    $user = fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    return $user ?: null;
}

// Format date
function formatDate($date, $format = 'F j, Y') {
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Upload file
function uploadFile($file, $targetDir = 'uploads/') {
    $targetFile = $targetDir . basename($file['name']);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetPath = $targetDir . $newFileName;
    
    // Check if file is an actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

// Add points to user
function addPoints($userId, $points, $gameId = null) {
    // Update user's total points
    query("UPDATE users SET points = points + ? WHERE id = ?", [$points, $userId]);
    
    // Log the points transaction
    if ($gameId) {
        query("INSERT INTO points_log (user_id, game_id, points, created_at) VALUES (?, ?, ?, NOW())", 
              [$userId, $gameId, $points]);
    }
    
    // Update session if it's the current user
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        $_SESSION['points'] = ($_SESSION['points'] ?? 0) + $points;
    }
    
    return true;
}

// Check if user has a specific badge
function hasBadge($userId, $badgeId) {
    $badge = fetch("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?", [$userId, $badgeId]);
    return !empty($badge);
}

// Add badge to user
function addBadge($userId, $badgeId) {
    if (!hasBadge($userId, $badgeId)) {
        query("INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?, ?, NOW())", [$userId, $badgeId]);
        return true;
    }
    return false;
}

// Get user's rank
function getUserRank($userId) {
    $rank = fetch("SELECT position FROM (
        SELECT id, ROW_NUMBER() OVER (ORDER BY points DESC) as position FROM users
    ) ranked WHERE id = ?", [$userId]);
    
    return $rank ? $rank['position'] : null;
}

// Get recent activities
function getRecentActivities($limit = 10) {
    return fetchAll("
        SELECT a.*, u.username, u.avatar, g.name as game_name 
        FROM activities a 
        JOIN users u ON a.user_id = u.id 
        LEFT JOIN games g ON a.game_id = g.id 
        ORDER BY a.created_at DESC 
        LIMIT ?
    ", [$limit]);
}

// Log activity
function logActivity($userId, $type, $data = null, $gameId = null) {
    $dataJson = $data ? json_encode($data) : null;
    query("
        INSERT INTO activities (user_id, activity_type, activity_data, game_id, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ", [$userId, $type, $dataJson, $gameId]);
}
?>
