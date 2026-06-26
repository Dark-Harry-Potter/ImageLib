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
    $page_title = "Disclaimer - ImageLib";
    $page_description = "Disclaimer for ImageLib.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, disclaimer";
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
        body.dark-mode .legal-card {
            background: var(--bg-card, #1A1A1A);
            border-color: rgba(255,255,255,0.06);
        }
        body.dark-mode .legal-card h2 {
            color: var(--text-primary, #E8E8E8);
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
        <h1>Disclaimer</h1>
        
        <h2>General Information</h2>
        <p>The information provided on ImageLib is for general informational purposes only. All information on the site is provided in good faith, however we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the site.</p>
        
        <h2>Image Content</h2>
        <p>Images on ImageLib are uploaded by users. While we moderate content, we do not guarantee that all images are free from copyright or other restrictions. Users are responsible for ensuring they have the right to use any image they download or embed.</p>
        
        <h2>External Links</h2>
        <p>Our website may contain links to external websites that are not provided or maintained by us. We do not guarantee the accuracy, relevance, timeliness, or completeness of any information on these external websites.</p>
        
        <h2>Professional Advice</h2>
        <p>The information on this site is not intended to be a substitute for professional advice. You should not rely solely on information from this website without consulting appropriate professionals.</p>
        
        <h2>Limitation of Liability</h2>
        <p>In no event shall ImageLib be liable for any loss or damage arising from the use of this website or any content therein.</p>
        
        <h2>No Guarantee of Service</h2>
        <p>We do not guarantee that our service will be uninterrupted, timely, secure, or error-free. We reserve the right to modify, suspend, or discontinue any part of our service at any time.</p>
        
        <h2>Credit & Pro Features Disclaimer</h2>
        <p>Credits purchased through social media channels are final. No refunds will be issued. Pro status is granted at the time of purchase and remains active as long as the user's account is active.</p>
        
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