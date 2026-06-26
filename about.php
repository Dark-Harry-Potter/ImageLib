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
    $page_title = "About Us - ImageLib";
    $page_description = "Learn about ImageLib – free responsive images for developers, with badges, pro features, and upload limits.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, about, pro badges";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    
    <style>
        .about-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .about-card h1 {
            font-size: 42px;
            background: linear-gradient(135deg, var(--text-primary, #1F2937), var(--primary-color, #3B82F6));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .about-card h2 {
            color: var(--text-primary, #1F2937);
            margin: 30px 0 15px;
            font-size: 24px;
        }
        .about-card p {
            color: var(--text-muted, #6B7280);
            line-height: 1.7;
            margin-bottom: 15px;
        }
        .about-card ul {
            color: var(--text-muted, #6B7280);
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .about-card ul li {
            margin-bottom: 6px;
        }
        .mission-box {
            background: var(--bg-input, #F3F4F6);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
            border: 1px solid var(--primary-color, #3B82F6);
        }
        .mission-box h2 {
            margin: 0 0 10px 0;
            color: var(--text-primary, #1F2937);
        }
        .mission-box p {
            margin: 0;
            color: var(--text-muted, #6B7280);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color, #3B82F6);
        }
        .stat-label {
            color: var(--text-muted, #6B7280);
            font-size: 14px;
        }
        .feature-highlight {
            background: rgba(253, 224, 71, 0.08);
            border-left: 4px solid #FDE047;
            padding: 16px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .feature-highlight strong {
            color: var(--text-primary, #1A2A3A);
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
        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .about-card {
                padding: 25px;
            }
            .about-card h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="about-card">
        <h1>About ImageLib</h1>
        <p>ImageLib was created to solve a simple problem: developers need reliable, responsive images that just work. No attribution required, no complicated licenses, no watermark.</p>
        
        <div class="mission-box">
            <h2>Our Mission</h2>
            <p>Empower developers with free, high-quality, responsive images that make building websites faster and easier.</p>
        </div>
        
        <h2>Our Story</h2>
        <p>Founded in 2026, ImageLib started as a small project by a developer who was tired of searching for images that actually worked on all devices. Today, we're proud to serve thousands of developers worldwide.</p>
        
        <div class="stats-grid">
            <div><div class="stat-number">5000+</div><div class="stat-label">Images</div></div>
            <div><div class="stat-number">1000+</div><div class="stat-label">Users</div></div>
            <div><div class="stat-number">24/7</div><div class="stat-label">Access</div></div>
        </div>
        
        <h2>Why Choose ImageLib?</h2>
        <p>Unlike other stock photo sites, ImageLib is built specifically for developers. Our images are responsive by default, embed codes are one click away, and our credit system rewards community participation.</p>
        
        <h2>Our Values</h2>
        <p>We believe in transparency, fairness, and putting developers first. No hidden fees, no bait-and-switch pricing, just a free platform that works.</p>
        
        <h2>What Makes ImageLib Special?</h2>
        
        <div class="feature-highlight">
            <strong>🏅 Badge System</strong> — Earn badges as you receive downloads on your images. 12 levels from Default to Legend. Each badge unlocks a new theme!
        </div>
        
        <div class="feature-highlight">
            <strong>📈 Upload Limits</strong> — Your upload limit grows with your popularity. More downloads = more uploads. <span class="pro-tag">PRO</span> users get double the limit.
        </div>
        
        <div class="feature-highlight">
            <strong>👑 Pro Features</strong> — <span class="pro-tag">PRO</span> users enjoy double upload limits, reduced credit costs (1 per download/embed), glowing Pro badge, and premium UI animations.
        </div>
        
        <div class="feature-highlight">
            <strong>💎 Credit System</strong> — Start with 100 free credits. Purchase more via social media when you run out. No hidden fees, no automatic charges.
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<script src="toast.js"></script>
</body>
</html>