<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow admins to run this script
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die('Access denied. Admin privileges required.');
}

// Function to run SQL from file
function runSqlFromFile($pdo, $file) {
    if (!file_exists($file)) {
        return "Error: File not found: $file";
    }
    
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
        return "Successfully executed: " . basename($file);
    } catch (PDOException $e) {
        return "Error executing " . basename($file) . ": " . $e->getMessage();
    }
}

// Function to create house images
function createHouseImages() {
    $houses = [
        'Hipster' => '#FF6B6B',
        'Speedster' => '#4ECDC4',
        'Shadow' => '#5F4B8B',
        'Beginner' => '#FFD166'
    ];
    
    $result = [];
    $imageDir = '../assets/images/houses';
    
    // Create directory if it doesn't exist
    if (!file_exists($imageDir)) {
        mkdir($imageDir, 0777, true);
    }
    
    // Create images for each house
    foreach ($houses as $house => $color) {
        $image = imagecreatetruecolor(200, 200);
        $bgColor = imagecolorallocate($image, 
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2))
        );
        $textColor = imagecolorallocate($image, 255, 255, 255);
        
        imagefill($image, 0, 0, $bgColor);
        
        // Add house name
        $font = 5; // Built-in GD font
        $text = $house;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = (200 - $textWidth) / 2;
        $textY = 90;
        
        imagestring($image, $font, $textX, $textY, $text, $textColor);
        
        // Save the image
        $filename = $imageDir . '/' . strtolower($house) . '.png';
        if (imagepng($image, $filename)) {
            $result[] = "Created image: $filename";
        } else {
            $result[] = "Failed to create image: $filename";
        }
        
        imagedestroy($image);
    }
    
    return $result;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $results = [];
    
    // Run SQL migration
    $migrationFile = __DIR__ . '/../database/migrations/002_add_house_to_users.sql';
    $results[] = runSqlFromFile($pdo, $migrationFile);
    
    // Create house images
    $results = array_merge($results, createHouseImages());
    
    // Create default image if it doesn't exist
    $defaultImage = '../assets/images/houses/default.png';
    if (!file_exists($defaultImage)) {
        $image = imagecreatetruecolor(200, 200);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 100, 100, 100);
        imagefill($image, 0, 0, $bgColor);
        $text = "House";
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = (200 - $textWidth) / 2;
        $textY = 90;
        imagestring($image, $font, $textX, $textY, $text, $textColor);
        imagepng($image, $defaultImage);
        imagedestroy($image);
        $results[] = "Created default house image: $defaultImage";
    }
    
    // Update existing users with random houses if their house is not set
    try {
        $stmt = $pdo->query("SELECT id FROM users WHERE house IS NULL OR house = ''");
        $usersToUpdate = $stmt->rowCount();
        
        if ($usersToUpdate > 0) {
            $updateStmt = $pdo->prepare("
                UPDATE users SET house = 
                ELT(1 + FLOOR(RAND() * 4), 'Hipster', 'Speedster', 'Shadow', 'Beginner')
                WHERE id = ? AND (house IS NULL OR house = '')
            ");
            
            $stmt = $pdo->query("SELECT id FROM users WHERE house IS NULL OR house = ''");
            $updated = 0;
            while ($user = $stmt->fetch()) {
                $updateStmt->execute([$user['id']]);
                $updated++;
            }
            $results[] = "Updated $updated users with random houses.";
        } else {
            $results[] = "No users need house assignment updates.";
        }
    } catch (PDOException $e) {
        $results[] = "Error updating user houses: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House System Setup - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 20px;
        }
        .result-box {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .house-preview {
            display: inline-block;
            margin: 10px;
            text-align: center;
        }
        .house-preview img {
            border-radius: 50%;
            border: 3px solid #ddd;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>House System Setup</h3>
            </div>
            <div class="card-body">
                <p>This setup will:</p>
                <ol>
                    <li>Add a 'house' column to the users table if it doesn't exist</li>
                    <li>Assign random houses to users who don't have one</li>
                    <li>Create house images in the assets/images/houses directory</li>
                </ol>
                
                <div class="text-center mt-4">
                    <form method="post">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-gear-fill"></i> Run House System Setup
                        </button>
                    </form>
                </div>
                
                <?php if (!empty($results)): ?>
                    <div class="result-box mt-4">
                        <h4>Results:</h4>
                        <div class="alert alert-info">
                            <ul class="mb-0">
                                <?php foreach ($results as $result): ?>
                                    <li><?php echo htmlspecialchars($result); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <h4 class="mt-4">House Previews:</h4>
                        <div class="text-center">
                            <?php 
                            $houses = ['Hipster', 'Speedster', 'Shadow', 'Beginner'];
                            foreach ($houses as $house): 
                                $image = '../assets/images/houses/' . strtolower($house) . '.png';
                                if (file_exists($image)): ?>
                                    <div class="house-preview">
                                        <img src="<?php echo $image; ?>" alt="<?php echo $house; ?>" width="100" height="100">
                                        <div><?php echo $house; ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
