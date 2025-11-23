<?php
// Create directory if it doesn't exist
$imageDir = 'assets/images/houses';
if (!file_exists($imageDir)) {
    if (!mkdir($imageDir, 0777, true)) {
        die("Error: Failed to create directory $imageDir");
    }
    echo "Created directory: $imageDir<br>";
}

// House configurations with names and colors
$houses = [
    'Hipster' => [
        'color' => '#FF6B6B',
        'text_color' => '#FFFFFF',
        'description' => 'Creativity & Style'
    ],
    'Speedster' => [
        'color' => '#4ECDC4',
        'text_color' => '#000000',
        'description' => 'Speed & Agility'
    ],
    'Shadow' => [
        'color' => '#5F4B8B',
        'text_color' => '#FFFFFF',
        'description' => 'Stealth & Strategy'
    ],
    'Beginner' => [
        'color' => '#FFD166',
        'text_color' => '#000000',
        'description' => 'Potential & Growth'
    ]
];

$width = 200;
$height = 200;

echo "<h3>Creating House Images</h3>";

foreach ($houses as $house => $data) {
    $color = $data['color'];
    $textColorHex = $data['text_color'];
    
    // Create a blank image
    $image = imagecreatetruecolor($width, $height);
    if (!$image) {
        echo "Error: Failed to create image for $house<br>";
        continue;
    }
    
    // Enable alpha channel for transparency
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Allocate colors
    $bg = imagecolorallocate($image, 
        hexdec(substr($color, 1, 2)),
        hexdec(substr($color, 3, 2)),
        hexdec(substr($color, 5, 2))
    );
    
    $textColor = imagecolorallocate($image, 
        hexdec(substr($textColorHex, 1, 2)),
        hexdec(substr($textColorHex, 3, 2)),
        hexdec(substr($textColorHex, 5, 2))
    );
    
    // Create a circular mask
    $mask = imagecreatetruecolor($width, $height);
    $black = imagecolorallocate($mask, 0, 0, 0);
    $white = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $black);
    
    // Draw a white circle
    imagefilledellipse($mask, $width/2, $height/2, $width-2, $height-2, $white);
    
    // Draw the colored circle
    imagefilledellipse($image, $width/2, $height/2, $width-10, $height-10, $bg);
    
    // Add text
    $font = 5; // Built-in GD font
    $text = $house;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = $height / 2 - imagefontheight($font) / 2;
    
    imagestring($image, $font, $textX, $textY, $text, $textColor);
    
    // Apply the circular mask
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $maskPixel = imagecolorat($mask, $x, $y);
            if ($maskPixel == 0) { // Black pixel in mask
                imagesetpixel($image, $x, $y, $transparent);
            }
        }
    }
    
    // Save the image
    $filename = strtolower($house) . '.png';
    $filepath = "$imageDir/$filename";
    
    if (imagepng($image, $filepath)) {
        echo "<div style='display:inline-block; text-align:center; margin:10px;'>";
        echo "<img src='$filepath' alt='$house' style='width:100px; height:100px; border-radius:50%; border:3px solid #ddd;'><br>";
        echo "<strong>$house</strong><br>";
        echo "<small>{$data['description']}</small>";
        echo "</div>";
    } else {
        echo "Error: Failed to save image for $house<br>";
    }
    
    imagedestroy($image);
    imagedestroy($mask);
}

// Create default image
$defaultImage = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($defaultImage, 240, 240, 240);
$textColor = imagecolorallocate($defaultImage, 100, 100, 100);
imagefill($defaultImage, 0, 0, $bgColor);
$text = "House";
$font = 5;
$textWidth = imagefontwidth($font) * strlen($text);
$textX = ($width - $textWidth) / 2;
$textY = $height / 2 - imagefontheight($font) / 2;
imagestring($defaultImage, $font, $textX, $textY, $text, $textColor);
imagepng($defaultImage, "$imageDir/default.png");
imagedestroy($defaultImage);

echo "<div style='clear:both; padding-top:20px;'>";
echo "<div class='alert alert-success'><strong>All house images created successfully!</strong></div>";
echo "<p>You can now view your profile to see your assigned house.</p>";
echo "<a href='profile.php' class='btn btn-primary'>View My Profile</a>";
echo "</div>";
?>
