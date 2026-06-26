<?php
require_once 'db_config.php';
require_once 'captcha_config.php';
require_once 'mail_config.php';
require_once 'csrf_token.php';
require_once 'rate_limit.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        // Rate Limiting
        $ip = getClientIP();
        if (!checkRateLimit($conn, $ip, 'forgot_password', 5, 3600)) {
            $error = "Too many requests. Please wait an hour.";
        } else {
            $email = trim($_POST['email'] ?? '');
            $captcha_token = $_POST['g-recaptcha-response'] ?? '';
            
            if (!verifyCaptcha($captcha_token)) {
                $error = "Please complete the CAPTCHA verification.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Valid email required.";
            } else {
                global $conn;
                if (!$conn || !$conn->ping()) {
                    $error = "Database connection issue.";
                } else {
                    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        $conn->query("DELETE FROM password_resets WHERE email = '$email'");
                        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $email, $token, $expires);
                        $stmt->execute();
                        
                        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token&email=" . urlencode($email);
                        
                        $subject = "Password Reset Request - ImageLib";
                        $body = "
                            <html>
                            <head><title>Password Reset</title></head>
                            <body style='font-family: Arial, sans-serif;'>
                                <h2>Hello {$row['full_name']},</h2>
                                <p>We received a request to reset your password for your ImageLib account.</p>
                                <p>Click the link below to reset your password:</p>
                                <p><a href='$reset_link' style='background:#FF6B4A; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
                                <p>Or copy this link: <a href='$reset_link'>$reset_link</a></p>
                                <p>This link will expire in 1 hour.</p>
                                <p>If you did not request this, please ignore this email.</p>
                                <hr>
                                <p style='font-size:12px; color:#666;'>ImageLib - Free Responsive Images for Developers</p>
                            </body>
                            </html>
                        ";
                        
                        if (sendEmail($email, $subject, $body)) {
                            $message = "Password reset link has been sent to your email address.";
                            echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('📧 Password reset link sent to your email.', 'info'); });</script>";
                        } else {
                            $message = "If that email exists, a reset link has been sent.";
                        }
                    } else {
                        $message = "If that email exists, a reset link has been sent.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$badge_level = 0;
$theme_class = 'theme-default';
$dark_mode_class = '';
$pro_class = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Forgot Password - ImageLib";
    $page_description = "Reset your ImageLib account password.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .forgot-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-area img {
            height: 50px;
            margin-bottom: 15px;
        }
        .logo-area h2 {
            color: var(--text-primary, #1A2A3A);
            font-weight: 700;
        }
        .logo-area p {
            color: var(--text-muted, #6A7A8A);
            font-size: 14px;
            margin-top: 8px;
        }
        .g-recaptcha {
            margin: 15px 0;
            display: flex;
            justify-content: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
            font-size: 13px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?> <?= $pro_class ?>">

<div class="container-xs">
    <div class="forgot-card">
        <div class="logo-area">
            <img src="logo.png" alt="ImageLib Logo">
            <h2>Forgot Password?</h2>
            <p>We'll email you a reset link</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?= getCSRFField() ?>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Your registered email address" required>
            </div>
            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Send Reset Link →</button>
        </form>
        
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</div>

<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
</body>
</html>