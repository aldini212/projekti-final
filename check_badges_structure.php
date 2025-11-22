<?php
require_once 'config/database.php';

try {
    // Check badges table
    echo "=== Badges Table ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM badges");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns) . "\n\n";
    
    // Check user_badges table
    echo "=== User Badges Table ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM user_badges");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns) . "\n\n";
    
    // Get sample data from badges
    echo "=== Sample Badge ===\n";
    $sample = $pdo->query("SELECT * FROM badges LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo "Sample badge data: " . print_r($sample, true) . "\n\n";
    
    // Get sample data from user_badges
    echo "=== Sample User Badge ===\n";
    $sample = $pdo->query("SELECT * FROM user_badges LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo "Sample user_badge data: " . print_r($sample, true) . "\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
