<?php
// Set directory for house images
$imageDir = 'assets/images/houses';

// Create directory if it doesn't exist
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// House configurations - Only Hipster, Speedster, and Shadow
$houses = [
    'Hipster' => '#FF6B6B',
    'Speedster' => '#4ECDC4',
    'Shadow' => '#5F4B8B'
];

// Check if GD is installed
if (!function_exists('imagecreatetruecolor')) {
    die('Error: GD library is not installed or enabled. Please enable the GD extension in php.ini');
}

// Create images for each house
foreach ($houses as $house => $color) {
    $image = imagecreatetruecolor(200, 200);
    
    // Allocate colors
    $bg = imagecolorallocate($image, 
        hexdec(substr($color, 1, 2)),
        hexdec(substr($color, 3, 2)),
        hexdec(substr($color, 5, 2))
    );
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Fill the background
    imagefill($image, 0, 0, $bg);
    
    // Add text
    $font = 5; // Built-in font
    $text = $house;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = (200 - $textWidth) / 2;
    $textY = 90;
    
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save the image
    $filename = strtolower($house) . '.png';
    $filepath = "$imageDir/$filename";
    imagepng($image, $filepath);
    imagedestroy($image);
    
    echo "Created: $filepath<br>";
}

echo "<h3>All house images created successfully!</h3>";
echo "<p><a href='test_house_images.php'>View Test Page</a></p>";
?>