<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'internship_exemplar_1');
define('DB_CHARSET', 'utf8mb4');
define('DB_TABLE', 'loginform');

define('SHOW_ERRORS', true);
define('LOG_ERRORS', true);

$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) @mkdir($logs_dir, 0755, true);
define('ERROR_LOG_PATH', $logs_dir . '/database_errors.log');

$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) throw new Exception($conn->connect_error);
    $conn->set_charset(DB_CHARSET);
    @$conn->query("SET time_zone = '+00:00'");
} catch (Exception $e) {
    if (LOG_ERRORS) error_log("[" . date('Y-m-d H:i:s') . "] DB Error: " . $e->getMessage() . "\n", 3, ERROR_LOG_PATH);
    die("Database Connection Failed: " . $e->getMessage());
}

function get_connection() { global $conn; return $conn; }
function is_connected() { global $conn; return $conn && $conn->ping(); }
function close_connection() { global $conn; if ($conn) $conn->close(); }

function getUserCreditCap($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM image_library WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $uploadCount = $row['total'];
    $stmt->close();
    return ($uploadCount >= 15) ? 500 : 200;
}

function hasFeedbackThisMonth($conn, $user_id) {
    $firstOfMonth = date('Y-m-01 00:00:00');
    $stmt = $conn->prepare("SELECT id FROM credit_transactions WHERE user_id = ? AND reason = 'Feedback form submission' AND created_at >= ? LIMIT 1");
    $stmt->bind_param("is", $user_id, $firstOfMonth);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function getUserUploadLimit($conn, $user_id) {
    $stmt = $conn->prepare("SELECT downloads_received FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $downloads = $row['downloads_received'] ?? 0;
    $stmt->close();
    $limits = [0=>5,10=>10,25=>15,50=>20,100=>30,250=>45,500=>70,1000=>100,5000=>150];
    $base_limit = 5;
    foreach ($limits as $threshold => $value) { if ($downloads >= $threshold) $base_limit = $value; }
    $stmt = $conn->prepare("SELECT is_pro FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $is_pro = (int)($row['is_pro'] ?? 0);
    $stmt->close();
    return $is_pro ? ($base_limit * 2) : $base_limit;
}

function getUserUploadCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM image_library WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

function checkUploadLimit($conn, $user_id) {
    $limit = getUserUploadLimit($conn, $user_id);
    $count = getUserUploadCount($conn, $user_id);
    return ['limit' => $limit, 'count' => $count, 'remaining' => max(0, $limit - $count), 'can_upload' => $count < $limit];
}

register_shutdown_function('close_connection');
?>