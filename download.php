<?php
session_start();
require_once 'db_config.php';
require_once 'email_templates.php';

if (!isset($_SESSION['user_id'])) {
    die('Please login to download.');
}

$image_id = (int)($_GET['id'] ?? 0);
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin');

if ($image_id <= 0) {
    die('Invalid image.');
}

// Get user's pro status
$user_stmt = $conn->prepare("SELECT is_pro FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();
$is_pro = (int)($user_data['is_pro'] ?? 0);

// Credit cost: 2.5 for standard, 1 for pro
$credit_cost = $is_pro ? 1 : 2.5;

$stmt = $conn->prepare("SELECT filename, user_id FROM image_library WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Image not found.');
}
$img = $result->fetch_assoc();
$filename = $img['filename'];
$uploader_id = $img['user_id'];
$filepath = __DIR__ . '/uploads/' . $filename;

if (!file_exists($filepath)) {
    die('File missing.');
}

$user_id = $_SESSION['user_id'];
$current_credits = (float)($_SESSION['user_credits'] ?? 0);

// Admin downloads for free
if ($is_admin) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
}

if ($current_credits < $credit_cost) {
    // Send out-of-credits email
    $user_stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_data = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    
    if ($user_data) {
        sendOutOfCreditsEmail($user_id, $user_data['full_name'], $user_data['email']);
    }
    
    $cost_display = $is_pro ? '1' : '2.5';
    die('Insufficient credits. You have ' . $current_credits . '. Need ' . $cost_display . ' credits to download. <br><br> 📱 Contact admin via social media to purchase more credits.');
}

$new_credits = $current_credits - $credit_cost;
$conn->query("UPDATE users SET credits = $new_credits WHERE id = $user_id");
$conn->query("INSERT INTO credit_transactions (user_id, amount, reason, reference_id) VALUES ($user_id, -$credit_cost, 'Downloaded image ID $image_id', $image_id)");
$_SESSION['user_credits'] = $new_credits;

$conn->query("UPDATE image_library SET downloads = downloads + 1 WHERE id = $image_id");
$conn->query("UPDATE users SET downloads_received = downloads_received + 1 WHERE id = $uploader_id");

$downloads_received = $conn->query("SELECT downloads_received FROM users WHERE id = $uploader_id")->fetch_assoc()['downloads_received'];
$new_badge = 0;
if ($downloads_received >= 1000000) $new_badge = 11;
elseif ($downloads_received >= 100000) $new_badge = 10;
elseif ($downloads_received >= 50000) $new_badge = 9;
elseif ($downloads_received >= 10000) $new_badge = 8;
elseif ($downloads_received >= 5000) $new_badge = 7;
elseif ($downloads_received >= 1000) $new_badge = 6;
elseif ($downloads_received >= 500) $new_badge = 5;
elseif ($downloads_received >= 100) $new_badge = 4;
elseif ($downloads_received >= 50) $new_badge = 3;
elseif ($downloads_received >= 10) $new_badge = 2;
elseif ($downloads_received >= 1) $new_badge = 1;
$conn->query("UPDATE users SET badge_level = $new_badge WHERE id = $uploader_id");

// ✅ Update upload limit for uploader
$new_limit = getUserUploadLimit($conn, $uploader_id);
$conn->query("UPDATE users SET upload_limit = $new_limit WHERE id = $uploader_id");

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit();
?>