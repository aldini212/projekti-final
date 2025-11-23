<?php
// Create the uploads directory if it doesn't exist
$uploadDir = 'assets/uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Create a simple default avatar
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Set background color (dark gray)
$bgColor = imagecolorallocate($image, 50, 50, 50);
imagefill($image, 0, 0, $bgColor);

// Set text color (white)
$textColor = imagecolorallocate($image, 255, 255, 255);

// Add text to the image
$text = 'AVATAR';
$fontSize = 20;
$font = 5; // Built-in font (1-5)

// Calculate text position to center it
$textWidth = imagefontwidth($font) * strlen($text);
$textX = ($width - $textWidth) / 2;
$textY = ($height - imagefontheight($font)) / 2;

// Add the text to the image
imagestring($image, $font, $textX, $textY, $text, $textColor);

// Save the image
$filename = $uploadDir . 'default-avatar.png';
imagepng($image, $filename);

// Free up memory
imagedestroy($image);

echo "Default avatar created successfully at: " . $filename;
?>
