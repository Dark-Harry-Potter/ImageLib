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
    $page_title = "Terms of Service - ImageLib";
    $page_description = "Terms of service for using ImageLib.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, terms of service";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    
    <style>
        .legal-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .legal-card h1 {
            color: var(--accent-color, #FF6B4A);
            margin-bottom: 20px;
            font-size: 32px;
        }
        .legal-card h2 {
            color: var(--text-primary, #1A2A3A);
            margin: 25px 0 15px;
            font-size: 20px;
            font-weight: 600;
        }
        .legal-card p {
            color: var(--text-muted, #6A7A8A);
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .legal-card ul {
            color: var(--text-muted, #6A7A8A);
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .legal-card ul li {
            margin-bottom: 6px;
        }
        .legal-card a {
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
        }
        .legal-card a:hover {
            text-decoration: underline;
        }
        .last-updated {
            color: var(--text-light, #94A3B8);
            font-size: 13px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color, #EDF0F3);
        }
        .highlight-box {
            background: rgba(253, 224, 71, 0.08);
            border-left: 4px solid #FDE047;
            padding: 16px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .pro-tag {
            display: inline-block;
            background: linear-gradient(135deg, #FDE047, #FFD700);
            color: #0A0A14;
            padding: 2px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 6px;
        }
        body.dark-mode .legal-card {
            background: var(--bg-card, #1A1A1A);
            border-color: rgba(255,255,255,0.06);
        }
        body.dark-mode .legal-card h2 {
            color: var(--text-primary, #E8E8E8);
        }
        body.dark-mode .highlight-box {
            background: rgba(253, 224, 71, 0.12);
        }
        @media (max-width: 600px) {
            .legal-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="legal-card">
        <h1>Terms of Service</h1>
        <p>Welcome to ImageLib. By using our website, you agree to these terms.</p>
        
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using ImageLib, you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, please do not use our service.</p>
        
        <h2>2. User Accounts</h2>
        <p>To access certain features, you must create an account. You are responsible for maintaining the confidentiality of your login credentials and for all activities that occur under your account.</p>
        
        <h2>3. Credit System</h2>
        <p>ImageLib operates a credit-based system. New users receive 100 free credits. Credits are consumed when you download or embed images. Credits are non-transferable and have no cash value outside ImageLib. Credits can be purchased via social media channels.</p>
        
        <h2>4. Pro Features</h2>
        <p>Users who purchase credits may receive <span class="pro-tag">PRO</span> status. Pro benefits include:</p>
        <ul>
            <li><strong>Double upload limit</strong> — Upload twice as many images</li>
            <li><strong>Reduced credit cost</strong> — Only 1 credit per download/embed</li>
            <li><strong>Glowing Pro badge</strong> — Visible in navbar and profile</li>
            <li><strong>Premium UI animations</strong> — Glitter buttons, gold card glow</li>
        </ul>
        
        <h2>5. Upload Limits</h2>
        <p>Upload limits are based on the total downloads your images receive. Standard users start with 5 images. Pro users get double the limit. Limits increase as you earn more downloads.</p>
        
        <h2>6. User Content</h2>
        <p>By uploading images to ImageLib, you grant us a non-exclusive, worldwide, royalty-free license to display, distribute, and promote your images on our platform. You retain ownership of your images. You warrant that you have the right to upload the images and that they do not infringe any third-party rights.</p>
        
        <h2>7. Prohibited Content</h2>
        <p>The following content is strictly prohibited on ImageLib:</p>
        <ul>
            <li>Illegal or unlawful content</li>
            <li>Copyrighted material you do not own</li>
            <li>Obscene, pornographic, or sexually explicit content</li>
            <li>Hate speech, harassment, or bullying</li>
            <li>Violent or gory content</li>
            <li>Spam or misleading content</li>
        </ul>
        
        <h2>8. Termination</h2>
        <p>We reserve the right to suspend or terminate accounts that violate these terms. You may delete your account at any time by contacting support.</p>
        
        <h2>9. Disclaimer of Warranties</h2>
        <p>ImageLib is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted or error-free.</p>
        
        <h2>10. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, ImageLib shall not be liable for any indirect, incidental, or consequential damages arising from your use of the service.</p>
        
        <h2>11. Changes to Terms</h2>
        <p>We may update these terms from time to time. Continued use of the service constitutes acceptance of the updated terms.</p>
        
        <h2>12. Contact Us</h2>
        <p>If you have questions about these terms, please <a href="form.php">contact us</a>.</p>
        
        <div class="highlight-box">
            <strong>💳 Payment Policy:</strong> All credit purchases are handled manually via social media. No payment information is stored on our servers.
        </div>
        
        <div class="last-updated">Last updated: June 20, 2026</div>
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