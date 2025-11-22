<?php
require_once 'config/database.php';

// Check users table
$tables = ['users', 'scores', 'badges', 'user_badges'];
$structure = [];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table");
        $structure[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $structure[$table] = "Error: " . $e->getMessage();
    }
}

echo "<pre>";
print_r($structure);
echo "</pre>";
