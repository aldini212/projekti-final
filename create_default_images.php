<?php
// Create default avatar
function createDefaultAvatar($path) {
    $width = 200;
    $height = 200;
    $image = imagecreatetruecolor($width, $height);
    
    // Background color
    $bg = imagecolorallocate($image, 41, 45, 62);
    imagefill($image, 0, 0, $bg);
    
    // Text color
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Add text
    $text = "Avatar";
    $font = 5; // Built-in font (1-5)
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save the image
    imagepng($image, $path);
    imagedestroy($image);
}

// Create default game images
function createDefaultGameImage($path, $gameName) {
    $width = 400;
    $height = 300;
    $image = imagecreatetruecolor($width, $height);
    
    // Background gradient
    $color1 = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 200));
    $color2 = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 200));
    
    // Create gradient
    for ($i = 0; $i < $height; $i++) {
        $r = $color1 >> 16 & 0xFF + ($color2 >> 16 & 0xFF - $color1 >> 16 & 0xFF) * $i / $height;
        $g = $color1 >> 8 & 0xFF + ($color2 >> 8 & 0xFF - $color1 >> 8 & 0xFF) * $i / $height;
        $b = $color1 & 0xFF + ($color2 & 0xFF - $color1 & 0xFF) * $i / $height;
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $i, $width, $i, $color);
    }
    
    // Add game name
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($gameName);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $textX, $textY, $gameName, $textColor);
    
    // Save the image
    imagepng($image, $path);
    imagedestroy($image);
}

// Create default hero image
function createDefaultHeroImage($path) {
    $width = 800;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);
    
    // Background gradient
    $color1 = imagecolorallocate($image, 41, 45, 62);
    $color2 = imagecolorallocate($image, 28, 31, 45);
    
    // Create gradient
    for ($i = 0; $i < $height; $i++) {
        $r = $color1 >> 16 & 0xFF + ($color2 >> 16 & 0xFF - $color1 >> 16 & 0xFF) * $i / $height;
        $g = $color1 >> 8 & 0xFF + ($color2 >> 8 & 0xFF - $color1 >> 8 & 0xFF) * $i / $height;
        $b = $color1 & 0xFF + ($color2 & 0xFF - $color1 & 0xFF) * $i / $height;
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $i, $width, $i, $color);
    }
    
    // Add some decorative elements
    $accentColor = imagecolorallocatealpha($image, 108, 92, 231, 50);
    for ($i = 0; $i < 10; $i++) {
        $size = rand(50, 200);
        $x = rand(0, $width);
        $y = rand(0, $height);
        imageellipse($image, $x, $y, $size, $size, $accentColor);
    }
    
    // Add text
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $text = "GameHub";
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save the image
    imagepng($image, $path);
    imagedestroy($image);
}

// Create house images
function createHouseImage($path, $houseName, $color) {
    $width = 200;
    $height = 200;
    $image = imagecreatetruecolor($width, $height);
    
    // Transparent background
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    imagesavealpha($image, true);
    
    // House color
    list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
    $houseColor = imagecolorallocate($image, $r, $g, $b);
    
    // Draw a simple house shape
    $points = [
        100, 20,   // Top point (roof)
        180, 100,  // Right point
        100, 180,  // Bottom point
        20, 100,   // Left point
    ];
    
    imagefilledpolygon($image, $points, 4, $houseColor);
    
    // Add house name
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($houseName);
    $textX = ($width - $textWidth) / 2;
    $textY = $height - 30;
    
    imagestring($image, $font, $textX, $textY, $houseName, $textColor);
    
    // Save the image
    imagepng($image, $path);
    imagedestroy($image);
}

// Create all default images
function createAllDefaultImages() {
    // Create default avatar if it doesn't exist
    $defaultAvatar = 'assets/images/default-avatar.png';
    if (!file_exists($defaultAvatar)) {
        createDefaultAvatar($defaultAvatar);
        echo "Created: $defaultAvatar<br>";
    }
    
    // Create hero image if it doesn't exist
    $heroImage = 'assets/images/hero-image.png';
    if (!file_exists($heroImage)) {
        createDefaultHeroImage($heroImage);
        echo "Created: $heroImage<br>";
    }
    
    // Create game images if they don't exist
    $games = [
        'trivia' => 'Trivia Challenge',
        'memory' => 'Memory Match',
        'puzzle' => 'Puzzle Quest',
        'wordsearch' => 'Word Search',
        'sudoku' => 'Sudoku'
    ];
    
    foreach ($games as $gameId => $gameName) {
        $gameImage = "assets/images/games/{$gameId}.jpg";
        if (!file_exists($gameImage)) {
            createDefaultGameImage($gameImage, $gameName);
            echo "Created: $gameImage<br>";
        }
    }
    
    // Create house images if they don't exist
    $houses = [
        'hipsters' => '6c5ce7',
        'speeders' => 'e74c3c',
        'engineers' => '3498db',
        'shadows' => '9b59b6'
    ];
    
    foreach ($houses as $houseName => $color) {
        $houseImage = "assets/images/houses/{$houseName}.png";
        if (!file_exists($houseImage)) {
            createHouseImage($houseImage, ucfirst($houseName), $color);
            echo "Created: $houseImage<br>";
        }
    }
    
    echo "<br>All default images have been created!";
}

// Run the function
createAllDefaultImages();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Default Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Default Images Generator</h1>
        <p>This script creates default images for the GameHub application.</p>
        
        <div class="output">
            <?php
            // The output will be generated by the PHP code above
            ?>
        </div>
        
        <p class="success">You can now go back to the <a href="index.php">homepage</a>.</p>
    </div>
</body>
</html>
