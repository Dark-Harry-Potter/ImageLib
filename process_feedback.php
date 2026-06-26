<?php
require_once 'rate_limit.php';
$ip = getClientIP();
if (!checkRateLimit($conn, $ip, 'FORM_ACTION', 10, 600)) {
    $error = "Too many attempts. Please wait 10 minutes.";
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php';
require_once 'email_templates.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to submit feedback.']);
    exit();
}
$user_id = $_SESSION['user_id'];
$current_credits = (int)$_SESSION['user_credits'];

// Monthly limit check
if (hasFeedbackThisMonth($conn, $user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'You have already submitted feedback this month. Try again next month.']);
    exit();
}

// Validate form data
$name = trim($_POST['name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$remark = trim($_POST['remark'] ?? '');

$errors = [];
if (strlen($name) < 3) $errors['name'] = 'Full Name must be at least 3 characters';
if (!preg_match('/^\d{10}$/', $mobile)) $errors['mobile'] = 'Mobile must be 10 digits';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required';
if (strlen($address) < 10) $errors['address'] = 'Address at least 10 characters';
if ($remark !== '' && strlen($remark) < 5) $errors['remark'] = 'Remarks empty or >=5 chars';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit();
}

// Insert into loginform
$stmt = $conn->prepare("INSERT INTO " . DB_TABLE . " (name, mobile, email, address, remark) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $mobile, $email, $address, $remark);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    exit();
}
$stmt->close();

// Award credits subject to cap
$cap = getUserCreditCap($conn, $user_id);
$max_add = $cap - $current_credits;
if ($max_add <= 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Feedback submitted, but your credit limit ($cap) is reached. No credits added.',
        'data' => compact('name','mobile','email','address','remark')
    ]);
    exit();
}
$add_amount = min(50, $max_add);
$new_credits = $current_credits + $add_amount;
$conn->query("UPDATE users SET credits = $new_credits WHERE id = $user_id");
$conn->query("INSERT INTO credit_transactions (user_id, amount, reason) VALUES ($user_id, $add_amount, 'Feedback form submission')");
$_SESSION['user_credits'] = $new_credits;

// ✅ SEND FEEDBACK CONFIRMATION EMAIL
$user_stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if ($user_data) {
    sendFeedbackConfirmationEmail($user_id, $user_data['full_name'], $user_data['email'], $add_amount);
}
echo json_encode([
    'status' => 'success',
    'message' => $msg,
    'toast' => '✅ Feedback submitted!',
    'data' => compact('name','mobile','email','address','remark')
]);
if ($add_amount < 50) $msg .= " Your credit limit ($cap) was reached.";
echo json_encode([
    'status' => 'success',
    'message' => $msg,
    'data' => compact('name','mobile','email','address','remark')
]);
exit();
?>