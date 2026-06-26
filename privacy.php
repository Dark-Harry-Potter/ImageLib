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
    $page_title = "Privacy Policy - ImageLib";
    $page_description = "Privacy policy for ImageLib.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, privacy policy";
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
        <h1>Privacy Policy</h1>
        <p>Your privacy is important to us. This policy explains how ImageLib collects, uses, and protects your information.</p>
        
        <h2>1. Information We Collect</h2>
        <p><strong>Account Information:</strong> When you register, we collect your name, email address, and password (hashed).</p>
        <p><strong>User Content:</strong> Images you upload, along with metadata like file names and upload timestamps.</p>
        <p><strong>Usage Data:</strong> We collect information about how you interact with our service, including downloads, embeds, and page views.</p>
        <p><strong>Cookies:</strong> We use session cookies to keep you logged in and optional analytics cookies to improve our service.</p>
        <p><strong>Pro Status:</strong> If you purchase credits and become a <span class="pro-tag">PRO</span> user, we store your pro status and badge icon preference.</p>
        
        <h2>2. How We Use Your Information</h2>
        <ul>
            <li>To provide and maintain our service</li>
            <li>To manage your account, credits, and upload limits</li>
            <li>To improve and personalize your experience</li>
            <li>To respond to your inquiries and support requests</li>
            <li>To prevent fraud and abuse</li>
            <li>To award badges based on your activity</li>
        </ul>
        
        <h2>3. Data Sharing</h2>
        <p>We do not sell your personal information. We may share data with:</p>
        <ul>
            <li>Service providers who assist us (e.g., hosting, email delivery)</li>
            <li>Law enforcement when required by law</li>
        </ul>
        
        <h2>4. Your Rights</h2>
        <p>You have the right to access, correct, or delete your personal information. You can do this through your profile page or by contacting us.</p>
        
        <h2>5. Data Security</h2>
        <p>We implement industry-standard security measures to protect your data. However, no method of transmission over the internet is 100% secure.</p>
        
        <h2>6. Children's Privacy</h2>
        <p>ImageLib is not intended for children under 13. We do not knowingly collect information from children under 13.</p>
        
        <h2>7. Changes to This Policy</h2>
        <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>
        
        <h2>8. Contact Us</h2>
        <p>If you have questions about this privacy policy, please <a href="form.php">contact us</a>.</p>
        
        <div class="highlight-box">
            <strong>💳 Credit & Pro Information:</strong> We do not store payment information. All credit purchases are handled manually via social media. Your Pro status is stored only to provide you with premium features.
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