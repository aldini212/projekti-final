<?php
// Create directory if it doesn't exist
$imageDir = 'assets/images/houses';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// House configurations
$houses = [
    'Hipster' => '#FF6B6B',
    'Speedster' => '#4ECDC4',
    'Shadow' => '#5F4B8B',
    'Beginner' => '#FFD166'
];

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
    
    // Add text
    $font = 5;
    $text = $house;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = (200 - $textWidth) / 2;
    $textY = 90;
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Save image
    $filename = "$imageDir/" . strtolower($house) . '.png';
    imagepng($image, $filename);
    imagedestroy($image);
    echo "Created: $filename<br>";
}

// Create default image
$defaultImage = imagecreatetruecolor(200, 200);
$bgColor = imagecolorallocate($defaultImage, 240, 240, 240);
$textColor = imagecolorallocate($defaultImage, 100, 100, 100);
imagefill($defaultImage, 0, 0, $bgColor);
$text = "House";
$font = 5;
$textWidth = imagefontwidth($font) * strlen($text);
$textX = (200 - $textWidth) / 2;
$textY = 90;
imagestring($defaultImage, $font, $textX, $textY, $text, $textColor);
imagepng($defaultImage, "$imageDir/default.png");
imagedestroy($defaultImage);
echo "Created: $imageDir/default.png<br>";

echo "All house images created successfully!";
?>