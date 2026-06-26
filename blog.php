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
    $page_title = "Blog - ImageLib";
    $page_description = "Read the latest articles and tutorials on responsive images and web development.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    
    <style>
        .page-header {
            margin-bottom: 40px;
            text-align: center;
        }
        .page-header h1 {
            font-size: 38px;
            color: var(--text-primary, #1A2A3A);
        }
        .page-header h1 i {
            color: var(--accent-color, #FF6B4A);
            margin-right: 12px;
        }
        .page-header p {
            color: var(--text-muted, #6A7A8A);
            margin-top: 10px;
            font-size: 16px;
        }
        
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }
        
        .blog-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 25px;
            border: 1px solid var(--border-color, #EDF0F3);
            transition: var(--transition, 0.25s ease);
            color: var(--text-primary, #1A2A3A);
        }
        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover, 0 12px 24px rgba(0,0,0,0.05));
        }
        .blog-card .blog-date {
            color: var(--accent-color, #FF6B4A);
            font-size: 13px;
            margin-bottom: 12px;
        }
        .blog-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--text-primary, #1A2A3A);
        }
        .blog-card p {
            color: var(--text-muted, #6A7A8A);
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .read-more {
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
            font-weight: 500;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        
        .featured-post {
            background: linear-gradient(135deg, rgba(255,107,74,0.08), rgba(45,156,219,0.08));
            border: 2px solid var(--accent-color, #FF6B4A);
            margin-bottom: 40px;
        }
        .featured-post .blog-date {
            color: #2D9CDB;
        }
        
        body.dark-mode .featured-post {
            background: linear-gradient(135deg, rgba(255,107,74,0.15), rgba(45,156,219,0.15));
            border-color: var(--accent-color, #FF6B4A);
        }
        
        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }
            .page-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-blog"></i> ImageLib Blog</h1>
        <p>Insights, tutorials, and news for developers</p>
    </div>
    
    <div class="blog-card featured-post">
        <div class="blog-date"><i class="fas fa-star"></i> Featured • June 12, 2026</div>
        <h3>Mastering Responsive Images in 2026</h3>
        <p>A complete guide to srcset, picture element, and modern image formats like WebP and AVIF. Learn how to make your images look perfect on any device.</p>
        <a href="blog-detail.php?id=1" class="read-more">Read More →</a>
    </div>
    
    <div class="blog-grid">
        <div class="blog-card">
            <div class="blog-date">June 10, 2026</div>
            <h3>10 Tips for Responsive Images</h3>
            <p>Learn how to make your images look great on any device – from mobile phones to 4K monitors.</p>
            <a href="blog-detail.php?id=1" class="read-more">Read More →</a>
        </div>
        <div class="blog-card">
            <div class="blog-date">June 8, 2026</div>
            <h3>Why ImageLib is the Best for Developers</h3>
            <p>Discover why thousands of developers choose ImageLib for their projects.</p>
            <a href="blog-detail.php?id=2" class="read-more">Read More →</a>
        </div>
        <div class="blog-card">
            <div class="blog-date">June 5, 2026</div>
            <h3>How to Earn Free Credits</h3>
            <p>A complete guide to maximizing your credit earnings on ImageLib.</p>
            <a href="blog-detail.php?id=3" class="read-more">Read More →</a>
        </div>
        <div class="blog-card">
            <div class="blog-date">June 1, 2026</div>
            <h3>Building a Community-Driven Image Library</h3>
            <p>The story behind ImageLib and our vision for the future.</p>
            <a href="blog-detail.php?id=4" class="read-more">Read More →</a>
        </div>
        <div class="blog-card">
            <div class="blog-date">May 28, 2026</div>
            <h3>Optimizing Images for Web Performance</h3>
            <p>Learn techniques to reduce image size without losing quality.</p>
            <a href="blog-detail.php?id=5" class="read-more">Read More →</a>
        </div>
        <div class="blog-card">
            <div class="blog-date">May 25, 2026</div>
            <h3>Understanding Image Formats: PNG vs JPG vs WebP</h3>
            <p>Which format should you use for your projects?</p>
            <a href="blog-detail.php?id=6" class="read-more">Read More →</a>
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