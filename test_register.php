<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/database.php';

echo "<h2>Registration Test</h2>";

// Test data
$test_user = [
    'username' => 'testuser_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'password' => 'Test123!',  // Will be hashed
    'confirm_password' => 'Test123!'
];

echo "<h3>1. Testing database connection...</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gamehub;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful!<br>";
} catch(PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

echo "<h3>2. Checking database structure...</h3>";
try {
    // Check if badges table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'badges'")->fetchAll();
    if (empty($tables)) {
        die("❌ The 'badges' table does not exist in the database.");
    }
    
    // Get badges table structure
    $badges_columns = $pdo->query("DESCRIBE badges")->fetchAll(PDO::FETCH_COLUMN);
    echo "Badges table columns: " . implode(', ', $badges_columns) . "<br>";
    
    // Check for required columns
    $required_columns = ['id', 'name'];
    $missing_columns = array_diff($required_columns, $badges_columns);
    if (!empty($missing_columns)) {
        echo "❌ Missing required columns in badges table: " . implode(', ', $missing_columns) . "<br>";
    } else {
        echo "✅ Badges table has all required columns<br>";
    }
    
    // List existing badges
    $badges = $pdo->query("SELECT * FROM badges LIMIT 5")->fetchAll();
    echo "Found " . count($badges) . " badges in the database<br>";
    if (!empty($badges)) {
        echo "<pre>" . print_r($badges, true) . "</pre>";
    }

} catch (Exception $e) {
    die("❌ Error checking database structure: " . $e->getMessage());
}

echo "<h3>3. Testing user creation...</h3>";
try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$test_user['username']]);
    if ($stmt->fetch()) {
        die("❌ Username already exists");
    }
    echo "✅ Username is available<br>";

    // Hash password
    $hashed_password = password_hash($test_user['password'], PASSWORD_DEFAULT);
    echo "✅ Password hashed successfully<br>";

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $test_user['username'],
        $test_user['email'],
        $hashed_password
    ]);
    
    $user_id = $pdo->lastInsertId();
    echo "✅ User created successfully! ID: $user_id<br>";

    // Create user profile
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, created_at, updated_at)
        VALUES (?, NOW(), NOW())
    ");
    $stmt->execute([$user_id]);
    echo "✅ User profile created successfully!<br>";

    // Skip badge assignment for now to test basic registration
    echo "ℹ️ Skipping badge assignment for now<br>";

    // Commit transaction
    $pdo->commit();
    echo "<h3>✅ Registration test completed successfully!</h3>";
    echo "Test user created:<br>";
    echo "Username: " . htmlspecialchars($test_user['username']) . "<br>";
    echo "Email: " . htmlspecialchars($test_user['email']) . "<br>";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h3>❌ Error during registration test:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Show all users
echo "<h3>Current Users:</h3>";
$users = $pdo->query("SELECT id, username, email, created_at FROM users")->fetchAll();
if (count($users) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created At</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found in the database.";
}
?>