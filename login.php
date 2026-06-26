<?php
require_once 'db_config.php';
require_once 'captcha_config.php';
require_once 'csrf_token.php';
require_once 'rate_limit.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha_token = $_POST['g-recaptcha-response'] ?? '';
        $ip = getClientIP();
        
        if (!checkRateLimit($conn, $ip, 'login', 10, 600)) {
            $error = "Too many attempts. Please wait 10 minutes.";
        } elseif (!verifyCaptcha($captcha_token)) {
            $error = "Please complete CAPTCHA.";
        } elseif (empty($email) || empty($password)) {
            $error = "All fields are required.";
        } else {
            $stmt = $conn->prepare("SELECT id, full_name, email, password, credits, role, is_active, badge_level, dark_mode, is_pro, pro_badge_icon, email_verified FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($row['is_active'] != 1) {
                    $error = "Account inactive. Contact admin.";
                } elseif ($row['email_verified'] != 1) {
                    $error = "Please verify your email before logging in.";
                } elseif (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_name'] = $row['full_name'];
                    $_SESSION['user_email'] = $row['email'];
                    $_SESSION['user_credits'] = $row['credits'];
                    $_SESSION['user_role'] = $row['role'];
                    $_SESSION['badge_level'] = $row['badge_level'];
                    $_SESSION['dark_mode'] = $row['dark_mode'];
                    $_SESSION['is_pro'] = $row['is_pro'];
                    $_SESSION['pro_badge_icon'] = $row['pro_badge_icon'] ?? 'fa-crown';
                    $_SESSION['email_verified'] = $row['email_verified'];
                    
                    $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $row['id']);
                    header("Location: index.php?login=success");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
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
    $page_title = "Login - ImageLib";
    $page_description = "Login to your ImageLib account.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; background:var(--bg-primary,#f1f5f9); }
        .login-card { background:var(--bg-card,#FFFFFF); border-radius:24px; padding:40px; width:100%; max-width:400px; box-shadow:var(--shadow,0 2px 8px rgba(0,0,0,0.04)); border:1px solid var(--border-color,#EDF0F3); }
        .logo-area { text-align:center; margin-bottom:30px; }
        .logo-area img { height:50px; margin-bottom:15px; }
        .logo-area h2 { color:var(--text-primary,#1A2A3A); font-weight:700; }
        .logo-area p { color:var(--text-muted,#6A7A8A); font-size:14px; margin-top:8px; }
        .g-recaptcha { margin:15px 0; display:flex; justify-content:center; }
        hr { border-color:var(--border-color,#EDF0F3); margin:20px 0; }
        .auth-links { text-align:center; margin-top:20px; color:var(--text-muted,#6A7A8A); font-size:13px; }
        .auth-links a { color:var(--accent-color,#FF6B4A); text-decoration:none; }
        .auth-links a:hover { text-decoration:underline; }
        @media (max-width:480px) { .login-card { padding:25px; } }
    </style>
</head>
<body>
<div class="container-xs">
    <div class="login-card">
        <div class="logo-area">
            <img src="logo.png" alt="ImageLib Logo">
            <h2>Welcome Back</h2>
            <p>Login to your account</p>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['verified']) && $_GET['verified'] == 'success'): ?>
            <div class="alert alert-success">✅ Email verified! You can now login.</div>
        <?php endif; ?>
        <form method="POST">
            <?= getCSRFField() ?>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login →</button>
        </form>
        <hr>
        <div class="auth-links">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>
</div>
<script>
const userBadgeLevel = 0;
const darkModeSession = false;
</script>
<script src="global.js"></script>
<?php if(isset($_GET['login']) && $_GET['login'] == 'success'): ?>
<script>document.addEventListener('DOMContentLoaded', function() { showToast('✅ Welcome back!', 'success'); });</script>
<?php endif; ?>
</body>
</html>