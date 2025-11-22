<?php
require_once 'config/database.php';

try {
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Available Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table";
        
        // Show table structure
        echo "<ul>";
        $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "<li>{$column['Field']} ({$column['Type']})";
            if ($column['Key'] == 'PRI') echo " <span class='badge bg-primary'>Primary</span>";
            echo "</li>";
        }
        echo "</ul></li>";
    }
    echo "</ul>";
    
    // Check if badges and user_badges tables exist
    if (in_array('badges', $tables) && in_array('user_badges', $tables)) {
        echo "<h3>Sample Badges Data (first 5):</h3>";
        $badges = $pdo->query("SELECT * FROM badges LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($badges, true) . "</pre>";
        
        echo "<h3>Sample User Badges (first 5):</h3>";
        $userBadges = $pdo->query("SELECT * FROM user_badges LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($userBadges, true) . "</pre>";
    } else {
        echo "<div class='alert alert-warning'>Missing required tables. The following tables are required:";
        echo "<ul>";
        if (!in_array('badges', $tables)) echo "<li>badges</li>";
        if (!in_array('user_badges', $tables)) echo "<li>user_badges</li>";
        echo "</ul></div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'><strong>Database Error:</strong> " . $e->getMessage() . "</div>";
}
?>

<style>
    body { padding: 20px; font-family: Arial, sans-serif; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    .badge { font-size: 0.8em; }
</style>
