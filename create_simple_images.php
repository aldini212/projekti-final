<?php
// Function to create a simple colored image with text
function createSimpleImage($path, $text, $bgColor = '6c5ce7', $textColor = 'ffffff') {
    $width = 200;
    $height = 200;
    $image = imagecreatetruecolor($width, $height);
    
    // Parse background color
    $bgR = hexdec(substr($bgColor, 0, 2));
    $bgG = hexdec(substr($bgColor, 2, 2));
    $bgB = hexdec(substr($bgColor, 4, 2));
    $bg = imagecolorallocate($image, $bgR, $bgG, $bgB);
    
    // Parse text color
    $textR = hexdec(substr($textColor, 0, 2));
    $textG = hexdec(substr($textColor, 2, 2));
    $textB = hexdec(substr($textColor, 4, 2));
    $textColor = imagecolorallocate($image, $textR, $textG, $textB);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // Add text
    $font = 5; // Built-in font
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save the image
    imagepng($image, $path);
    imagedestroy($image);
}

// Create all necessary directories
$dirs = [
    'assets/images',
    'assets/images/games',
    'assets/images/houses',
    'assets/uploads/avatars'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Create default images
$images = [
    // Default avatar
    'assets/images/default-avatar.png' => ['Avatar', '6c5ce7', 'ffffff'],
    
    // Hero image
    'assets/images/hero-image.png' => ['GameHub', '1a1a2e', 'a29bfe'],
    
    // Game images
    'assets/images/games/trivia.jpg' => ['Trivia', '6c5ce7', 'ffffff'],
    'assets/images/games/memory.jpg' => ['Memory', 'e74c3c', 'ffffff'],
    'assets/images/games/puzzle.jpg' => ['Puzzle', '3498db', 'ffffff'],
    'assets/images/games/wordsearch.jpg' => ['Word Search', '9b59b6', 'ffffff'],
    'assets/images/games/sudoku.jpg' => ['Sudoku', '2ecc71', 'ffffff'],
    
    // House images
    'assets/images/houses/hipsters.png' => ['Hipsters', '6c5ce7', 'ffffff'],
    'assets/images/houses/speeders.png' => ['Speeders', 'e74c3c', 'ffffff'],
    'assets/images/houses/engineers.png' => ['Engineers', '3498db', 'ffffff'],
    'assets/images/houses/shadows.png' => ['Shadows', '9b59b6', 'ffffff'],
];

// Create each image if it doesn't exist
foreach ($images as $path => $params) {
    if (!file_exists($path)) {
        createSimpleImage($path, $params[0], $params[1], $params[2]);
        echo "Created: $path<br>";
    } else {
        echo "Exists: $path<br>";
    }
}

echo "<br>All default images have been created!<br>";
echo "<a href='index.php'>Go to Homepage</a>";
?>
