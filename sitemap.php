<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    $page_title = "Sitemap - ImageLib";
    $page_description = "ImageLib sitemap.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, sitemap";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    
    <style>
        .sitemap-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .sitemap-card h1 {
            color: var(--accent-color, #FF6B4A);
            margin-bottom: 30px;
            font-size: 32px;
        }
        .sitemap-card h1 i {
            margin-right: 12px;
        }
        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
        }
        .sitemap-col h3 {
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent-color, #FF6B4A);
            display: inline-block;
            font-size: 18px;
        }
        .sitemap-col ul {
            list-style: none;
            padding: 0;
        }
        .sitemap-col li {
            margin-bottom: 12px;
        }
        .sitemap-col a {
            color: var(--text-muted, #6A7A8A);
            text-decoration: none;
            transition: var(--transition, 0.2s);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sitemap-col a:hover {
            color: var(--accent-color, #FF6B4A);
            padding-left: 5px;
        }
        .sitemap-col a i {
            width: 20px;
            color: var(--accent-color, #FF6B4A);
            opacity: 0.6;
            text-align: center;
        }
        .new-badge {
            display: inline-block;
            background: linear-gradient(135deg, #FDE047, #FFD700);
            color: #0A0A14;
            padding: 0px 8px;
            border-radius: 30px;
            font-size: 8px;
            font-weight: 700;
            margin-left: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer-note {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color, #EDF0F3);
            color: var(--text-muted, #6A7A8A);
            font-size: 13px;
        }
        .footer-note a {
            color: var(--accent-color, #FF6B4A);
        }
        
        body.dark-mode .sitemap-card {
            background: var(--bg-card, #1A1A1A);
            border-color: rgba(255,255,255,0.06);
        }
        body.dark-mode .sitemap-col a {
            color: var(--text-muted, #94A3B8);
        }
        body.dark-mode .sitemap-col a:hover {
            color: var(--accent-color, #FFD700);
        }
        
        @media (max-width: 600px) {
            .sitemap-card {
                padding: 25px;
            }
            .sitemap-grid {
                gap: 25px;
            }
            .sitemap-col h3 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container">
    <div class="sitemap-card">
        <h1><i class="fas fa-sitemap"></i> Sitemap</h1>
        
        <div class="sitemap-grid">
            <div class="sitemap-col">
                <h3>Main Pages</h3>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="upload.php"><i class="fas fa-upload"></i> Upload</a></li>
                    <li><a href="form.php"><i class="fas fa-comment"></i> Feedback</a></li>
                    <li><a href="blog.php"><i class="fas fa-blog"></i> Blog</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="roadmap.php"><i class="fas fa-map-signs"></i> Roadmap <span class="new-badge">NEW</span></a></li>
                </ul>
            </div>
            <div class="sitemap-col">
                <h3>Account</h3>
                <ul>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.php"><i class="fas fa-user-plus"></i> Sign Up</a></li>
                    <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                    <li><a href="badge_tracker.php"><i class="fas fa-trophy"></i> Badge Tracker <span class="new-badge">NEW</span></a></li>
                    <li><a href="credit_history.php"><i class="fas fa-history"></i> Credit History</a></li>
                    <li><a href="forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                </ul>
            </div>
            <div class="sitemap-col">
                <h3>Legal</h3>
                <ul>
                    <li><a href="terms.php"><i class="fas fa-file-contract"></i> Terms of Service</a></li>
                    <li><a href="privacy.php"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    <li><a href="disclaimer.php"><i class="fas fa-exclamation-triangle"></i> Disclaimer</a></li>
                    <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                </ul>
            </div>
            <div class="sitemap-col">
                <h3>Support</h3>
                <ul>
                    <li><a href="form.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    <li><a href="sitemap.php"><i class="fas fa-sitemap"></i> Sitemap</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-note">
            <p><i class="fas fa-search"></i> Looking for something? Try our <a href="gallery.php">Gallery</a>, <a href="badge_tracker.php">Badge Tracker</a>, <a href="roadmap.php">Roadmap</a>, or <a href="faq.php">FAQ</a>.</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
</body>
</html>