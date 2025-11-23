<?php
$houses = ['Hipster', 'Speedster', 'Shadow']; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>House Images Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .house-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }
        .house-card {
            text-align: center;
            padding: 25px;
            border: 2px solid #ddd;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 250px;
        }
        .house-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .house-img {
            width: 180px;
            height: 180px;
            object-fit: contain;
            margin: 0 auto 15px;
            display: block;
            border-radius: 50%;
            border: 3px solid #eee;
            transition: transform 0.3s ease;
        }
        .house-card:hover .house-img {
            transform: scale(1.05);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        h3 {
            color: #2c3e50;
            margin: 10px 0;
        }
        p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 5px 0;
            word-break: break-all;
        }
        .error {
            color: #e74c3c;
            background: #fde8e8;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            max-width: 600px;
            margin: 20px auto;
            border-left: 4px solid #e74c3c;
        }
        .success {
            color: #27ae60;
            background: #e8f8f0;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            max-width: 600px;
            margin: 20px auto;
            border-left: 4px solid #27ae60;
        }
    </style>
</head>
<body>
    <h1>House Images Test</h1>
    
    <?php if (!function_exists('imagecreatetruecolor')): ?>
        <div class="error">
            <strong>Error:</strong> GD library is not installed or enabled.
            <p>To fix this, please follow these steps:</p>
            <ol style="text-align: left; max-width: 500px; margin: 10px auto;">
                <li>Open <code>php.ini</code> in your XAMPP installation</li>
                <li>Find the line that says <code>;extension=gd</code></li>
                <li>Remove the semicolon at the beginning</li>
                <li>Save the file and restart Apache</li>
            </ol>
        </div>
    <?php else: ?>
        <?php if (empty($houses)): ?>
            <div class="error">No houses are configured.</div>
        <?php else: ?>
            <div class="house-container">
                <?php 
                $allImagesExist = true;
                foreach ($houses as $house): 
                    $imgPath = "assets/images/houses/" . strtolower($house) . ".png";
                    if (!file_exists($imgPath)) {
                        $allImagesExist = false;
                    }
                ?>
                    <div class="house-card">
                        <h3><?php echo htmlspecialchars($house); ?> House</h3>
                        <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                             alt="<?php echo htmlspecialchars($house); ?>" 
                             class="house-img"
                             onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,<svg width=\'200\' height=\'200\' xmlns=\'http://www.w3.org/2000/svg\'><rect width=\'100%\' height=\'100%\' fill=\'%23f0f0f0\'/><text x=\'50%\' y=\'50%\' font-family=\'Arial\' font-size=\'16\' text-anchor=\"middle\" dy=\".3em\" fill=\'%23999\'>Image not found</text></svg>'">
                        <p>File: <code><?php echo htmlspecialchars($imgPath); ?></code></p>
                        <p>Status: <?php echo file_exists($imgPath) ? '<span style="color: #27ae60;">✓ Found</span>' : '<span style="color: #e74c3c;">✗ Missing</span>'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$allImagesExist): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <p>Some house images are missing. <a href="generate_house_images.php">Click here to generate them</a>.</p>
                </div>
            <?php else: ?>
                <div class="success">
                    All house images are properly configured and loaded!
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px; font-size: 14px; color: #7f8c8d;">
        <p>If you're having issues, make sure:</p>
        <ul style="list-style: none; padding: 0;">
            <li>The <code>assets/images/houses/</code> directory exists and is writable</li>
            <li>GD library is enabled in your PHP configuration</li>
            <li>You have restarted your web server after making changes</li>
        </ul>
    </div>
</body>
</html>