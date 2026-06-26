<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "This email is already subscribed.";
        } else {
            $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $message = "Thank you for subscribing! You'll receive updates from ImageLib.";
            } else {
                $error = "Subscription failed. Please try again.";
            }
        }
        $stmt->close();
    }
}

$badge_level = $_SESSION['badge_level'] ?? 0;
$badge_map = [
    0 => ['name' => 'Default'],
    1 => ['name' => 'First Download'],
    2 => ['name' => 'Sprout'],
    3 => ['name' => 'Wave'],
    4 => ['name' => 'Blossom'],
    5 => ['name' => 'Blaze'],
    6 => ['name' => 'Pinnacle'],
    7 => ['name' => 'Champion'],
    8 => ['name' => 'Sage'],
    9 => ['name' => 'Wizard'],
    10 => ['name' => 'Royalty'],
    11 => ['name' => 'Legend']
];
$badge_name = $badge_map[$badge_level]['name'] ?? 'Default';
$theme_class = 'theme-' . strtolower(str_replace(' ', '-', $badge_name));
$dark_mode_class = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Newsletter - ImageLib";
    $page_description = "Subscribe to the ImageLib newsletter.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .newsletter-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            max-width: 450px;
            text-align: center;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .newsletter-card img {
            height: 60px;
            margin-bottom: 20px;
        }
        .newsletter-card h1 {
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 10px;
            font-size: 28px;
        }
        .newsletter-card > p {
            color: var(--text-muted, #6A7A8A);
            margin-bottom: 25px;
            font-size: 14px;
        }
        .newsletter-card .back-link {
            display: block;
            margin-top: 20px;
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
            font-size: 13px;
        }
        .newsletter-card .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            .newsletter-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">

<div class="container-xs">
    <div class="newsletter-card">
        <img src="logo.png" alt="ImageLib">
        <h1>ImageLib Newsletter</h1>
        <p>Get the latest images, features, and tutorials straight to your inbox.</p>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <a href="index.php" class="back-link">← Back to Home</a>
        <?php elseif($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <a href="index.php" class="back-link">← Back to Home</a>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Your email address" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Subscribe →</button>
            </form>
            <p style="font-size:11px; color:var(--text-muted, #6A7A8A); margin-top:15px;">No spam, unsubscribe anytime.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<script src="toast.js"></script></body>
</html>