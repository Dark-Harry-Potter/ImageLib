<?php
session_start();
require_once 'db_config.php';

error_reporting(0);
ini_set('display_errors', 0);

$image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($image_id <= 0) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid image ID');
}

$stmt = $conn->prepare("SELECT filename, user_id FROM image_library WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('HTTP/1.0 404 Not Found');
    die('Image not found');
}
$row = $result->fetch_assoc();
$filename = $row['filename'];
$uploader_id = $row['user_id'];
$filepath = __DIR__ . '/uploads/' . $filename;

if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    die('File not found: ' . $filepath);
}

$uploader_name = 'ImageLib User';
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $uploader_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $uploader_name = $row['full_name'];
}
$stmt->close();

$image_info = getimagesize($filepath);
$mime = $image_info['mime'];

switch ($mime) {
    case 'image/jpeg': $image = imagecreatefromjpeg($filepath); break;
    case 'image/png': $image = imagecreatefrompng($filepath); imagealphablending($image, true); imagesavealpha($image, true); break;
    case 'image/gif': $image = imagecreatefromgif($filepath); break;
    case 'image/webp': $image = imagecreatefromwebp($filepath); break;
    default: header('HTTP/1.0 415 Unsupported Media Type'); die('Unsupported image type: ' . $mime);
}

if (!$image) die('Failed to create image resource');

$width = imagesx($image);
$height = imagesy($image);

$spacing = 10;
$cyan = imagecolorallocatealpha($image, 0, 255, 255, 35);
$cyan_light = imagecolorallocatealpha($image, 0, 255, 255, 20);

for ($y = $spacing; $y < $height; $y += $spacing) imageline($image, 0, $y, $width, $y, $cyan);
for ($x = $spacing; $x < $width; $x += $spacing) imageline($image, $x, 0, $x, $height, $cyan);
for ($i = -$height; $i < $width + $height; $i += $spacing) imageline($image, $i, 0, $i + $height, $height, $cyan_light);
for ($i = -$height; $i < $width + $height; $i += $spacing) imageline($image, $i, $height, $i + $height, 0, $cyan_light);

$font_paths = [
    'C:/Windows/Fonts/arial.ttf',
    'C:/Windows/Fonts/Arial.ttf',
    '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
    '/System/Library/Fonts/Helvetica.ttc',
];
$font = null;
foreach ($font_paths as $path) {
    if (file_exists($path)) { $font = $path; break; }
}

$font_size = max(16, min(24, $width / 40));
$name = $uploader_name . ' ©';
$text_color = imagecolorallocatealpha($image, 255, 200, 50, 90);
$shadow_color = imagecolorallocatealpha($image, 0, 0, 0, 60);

$orientations = [0, 1, 2, 3];
shuffle($orientations);
$selected_orientations = array_slice($orientations, 0, rand(3, 4));

foreach ($selected_orientations as $orientation) {
    $grid_x = rand(2, floor($width / $spacing) - 2) * $spacing;
    $grid_y = rand(2, floor($height / $spacing) - 2) * $spacing;
    if ($grid_x < 50) $grid_x = 50;
    if ($grid_y < 50) $grid_y = 50;
    if ($grid_x > $width - 100) $grid_x = $width - 100;
    if ($grid_y > $height - 50) $grid_y = $height - 50;
    
    if ($orientation == 0) {
        if ($font) {
            imagettftext($image, $font_size, 0, $grid_x + 2, $grid_y + $font_size + 2, $shadow_color, $font, $name);
            imagettftext($image, $font_size, 0, $grid_x, $grid_y + $font_size, $text_color, $font, $name);
        }
    } elseif ($orientation == 1) {
        if ($font) {
            imagettftext($image, $font_size, -90, $grid_x + $font_size + 2, $grid_y + 2, $shadow_color, $font, $name);
            imagettftext($image, $font_size, -90, $grid_x + $font_size, $grid_y, $text_color, $font, $name);
        }
    } elseif ($orientation == 2) {
        if ($font) {
            imagettftext($image, $font_size, 45, $grid_x + 2, $grid_y + $font_size + 2, $shadow_color, $font, $name);
            imagettftext($image, $font_size, 45, $grid_x, $grid_y + $font_size, $text_color, $font, $name);
        }
    } elseif ($orientation == 3) {
        if ($font) {
            imagettftext($image, $font_size, -45, $grid_x + 2, $grid_y + $font_size + 2, $shadow_color, $font, $name);
            imagettftext($image, $font_size, -45, $grid_x, $grid_y + $font_size, $text_color, $font, $name);
        }
    }
}

$preview_color = imagecolorallocatealpha($image, 255, 107, 74, 60);
$preview_shadow = imagecolorallocatealpha($image, 0, 0, 0, 50);
if ($font) {
    imagettftext($image, 16, 0, 17, 37, $preview_shadow, $font, '🔒 PREVIEW');
    imagettftext($image, 16, 0, 15, 35, $preview_color, $font, '🔒 PREVIEW');
    $bottom_text = 'PREVIEW - Download for unmarked original';
    $bottom_x = $width - (strlen($bottom_text) * 9) - 15;
    imagettftext($image, 13, 0, $bottom_x + 2, $height - 13, $preview_shadow, $font, $bottom_text);
    imagettftext($image, 13, 0, $bottom_x, $height - 15, $preview_color, $font, $bottom_text);
}

header('Content-Type: ' . $mime);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

switch ($mime) {
    case 'image/jpeg': imagejpeg($image, null, 85); break;
    case 'image/png': imagepng($image, null, 8); break;
    case 'image/gif': imagegif($image); break;
    case 'image/webp': imagewebp($image, null, 80); break;
}
imagedestroy($image);
exit;
?>