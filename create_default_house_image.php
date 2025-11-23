<?php
// Create a 200x200 image
$image = imagecreatetruecolor(200, 200);

// Allocate colors
$bgColor = imagecolorallocate($image, 240, 240, 240);
$textColor = imagecolorallocate($image, 100, 100, 100);

// Fill the background
imagefill($image, 0, 0, $bgColor);

// Add text
$text = "House";
$font = 5; // Built-in GD font
$textWidth = imagefontwidth($font) * strlen($text);
$textX = (200 - $textWidth) / 2;
$textY = 90;

imagestring($image, $font, $textX, $textY, $text, $textColor);

// Save the image
$filename = 'assets/images/houses/default.png';
imagepng($image, $filename);

// Free up memory
imagedestroy($image);

echo "Default house image created successfully at $filename\n";
?>
