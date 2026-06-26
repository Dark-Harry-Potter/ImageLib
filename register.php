<?php
require_once 'db_config.php';
require_once 'captcha_config.php';
require_once 'email_templates.php';
require_once 'csrf_token.php';
require_once 'rate_limit.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $captcha_token = $_POST['g-recaptcha-response'] ?? '';
        $ip = getClientIP();
        
        if (!checkRateLimit($conn, $ip, 'register', 5, 600)) {
            $error = "Too many attempts. Please wait 10 minutes.";
        } elseif (!verifyCaptcha($captcha_token)) {
            $error = "Please complete CAPTCHA.";
        } elseif (empty($fullname) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (strlen($fullname) < 3) {
            $error = "Full name must be at least 3 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email already registered. Please login.";
            } else {
                $username = explode('@', $email)[0];
                $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $checkUser->bind_param("s", $username);
                $checkUser->execute();
                if ($checkUser->get_result()->num_rows > 0) {
                    $username = $username . rand(100, 999);
                }
                $checkUser->close();
                
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role, is_active, credits) VALUES (?, ?, ?, ?, 'user', 1, 100)");
                $stmt->bind_param("ssss", $username, $fullname, $email, $hashed);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    $conn->query("INSERT INTO credit_transactions (user_id, amount, reason) VALUES ($user_id, 100, 'Welcome bonus')");
                    
                    $verification_token = bin2hex(random_bytes(32));
                    $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $conn->query("UPDATE users SET verification_token = '$verification_token', verification_expires = '$verification_expires' WHERE id = $user_id");
                    
                    $verify_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/email_verification.php?token=$verification_token&email=" . urlencode($email);
                    sendVerificationEmail($fullname, $email, $verify_link);
                    
                    $success = "Registration successful! Please check your email to verify your account.";
                    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('✅ Registration successful! Check your email to verify.', 'success'); });</script>";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Sign Up - ImageLib";
    $page_description = "Create your free ImageLib account.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; background:var(--bg-primary,#f1f5f9); }
        .register-card { background:var(--bg-card,#FFFFFF); border-radius:24px; padding:40px; width:100%; max-width:450px; box-shadow:var(--shadow,0 2px 8px rgba(0,0,0,0.04)); border:1px solid var(--border-color,#EDF0F3); }
        .logo-area { text-align:center; margin-bottom:30px; }
        .logo-area img { height:50px; margin-bottom:15px; }
        .logo-area h2 { color:var(--text-primary,#1A2A3A); font-weight:700; }
        .logo-area p { color:var(--text-muted,#6A7A8A); font-size:14px; margin-top:8px; }
        .g-recaptcha { margin:15px 0; display:flex; justify-content:center; }
        hr { border-color:var(--border-color,#EDF0F3); margin:20px 0; }
        .auth-links { text-align:center; margin-top:20px; color:var(--text-muted,#6A7A8A); font-size:13px; }
        .auth-links a { color:var(--accent-color,#FF6B4A); text-decoration:none; }
        .auth-links a:hover { text-decoration:underline; }
        @media (max-width:480px) { .register-card { padding:25px; } }
    </style>
</head>
<body>
<div class="container-xs">
    <div class="register-card">
        <div class="logo-area">
            <img src="logo.png" alt="ImageLib Logo">
            <h2>Create Account</h2>
            <p>Join ImageLib for free</p>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <?= getCSRFField() ?>
            <div class="form-group">
                <input type="text" name="fullname" class="form-control" placeholder="Full Name" required autofocus>
            </div>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password (min 6 characters)" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Create Account →</button>
        </form>
        <hr>
        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>
<script>
const userBadgeLevel = 0;
const darkModeSession = false;
</script>
<script src="global.js"></script>
</body>
</html>