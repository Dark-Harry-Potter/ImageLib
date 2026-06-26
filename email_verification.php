<?php
session_start();
require_once 'db_config.php';
require_once 'email_templates.php';

$token = $_GET['token'] ?? '';
$email = urldecode($_GET['email'] ?? '');

if (empty($token) || empty($email)) {
    die('Invalid verification link.');
}

$stmt = $conn->prepare("SELECT id, email_verified, full_name FROM users WHERE email = ? AND verification_token = ? AND verification_expires > NOW()");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Invalid or expired verification link.');
}

$user = $result->fetch_assoc();

if ($user['email_verified'] == 1) {
    die('Email already verified. You can <a href="login.php">login</a>.');
}

$stmt->close();

// Verify email
$stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// Send welcome email
sendWelcomeEmail($user['id'], $user['full_name'], $email);

// Update session if logged in
if (isset($_SESSION['user_id'])) {
    $_SESSION['email_verified'] = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - ImageLib</title>
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bg-primary, #f1f5f9);
        }
        .verified-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .verified-card .icon {
            font-size: 64px;
            color: #20B2AA;
            margin-bottom: 20px;
        }
        .verified-card h1 {
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 15px;
        }
        .verified-card p {
            color: var(--text-muted, #6A7A8A);
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: var(--primary-color, #FF6B4A);
            color: #FFFFFF;
            padding: 12px 32px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            filter: brightness(0.85);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="verified-card">
        <div class="icon">✅</div>
        <h1>Email Verified!</h1>
        <p>Your email has been successfully verified. You can now access all features of ImageLib.</p>
        <a href="login.php?verified=success" class="btn">Login Now</a>
    </div>
</body>
</html>