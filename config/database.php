<?php
// Database configuration
$host = 'localhost';
$dbname = 'gamehub';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to execute a query with parameters
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Function to fetch a single row
function fetch($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

// Function to fetch all rows
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

// Function to get the last inserted ID
function lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}
?>
