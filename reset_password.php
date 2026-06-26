<?php
require_once 'db_config.php';
require_once 'csrf_token.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$email = urldecode($_GET['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $token = trim($_POST['token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (strlen($new) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($new !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            global $conn;
            if (!$conn || !$conn->ping()) {
                $error = "Database connection issue.";
            } else {
                $stmt = $conn->prepare("SELECT email FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
                $stmt->bind_param("ss", $email, $token);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 1) {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $update->bind_param("ss", $hashed, $email);
                    $update->execute();
                    $conn->query("DELETE FROM password_resets WHERE email = '$email'");
                    $success = "Password changed successfully! <a href='login.php'>Login now</a>";
                    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('✅ Password changed successfully!', 'success'); });</script>";
                } else {
                    $error = "Invalid or expired reset link.";
                }
                $stmt->close();
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
    $page_title = "Reset Password - ImageLib";
    $page_description = "Set a new password for your ImageLib account.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        .reset-card {
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
        .alert-success a {
            color: #20B2AA;
            text-decoration: underline;
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?> <?= $pro_class ?>">

<div class="container-xs">
    <div class="reset-card">
        <div class="logo-area">
            <img src="logo.png" alt="ImageLib Logo">
            <h2>Set New Password</h2>
            <p>Create a new secure password</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!$success && (($token && $email) || $_SERVER['REQUEST_METHOD'] === 'POST')): ?>
        <form method="POST">
            <?= getCSRFField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <div class="form-group">
                <input type="password" name="new_password" class="form-control" placeholder="New password (min 6 characters)" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Reset Password →</button>
        </form>
        <?php endif; ?>
        
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