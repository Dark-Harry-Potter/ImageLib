<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$posts = [
    1 => ['title' => '10 Tips for Responsive Images', 'date' => 'June 10, 2026', 'category' => 'Tutorial', 'content' => '
        <p>Responsive images are crucial for modern web development. Here are 10 tips to ensure your images look great on all devices.</p>
        <h3>1. Use srcset for Different Resolutions</h3>
        <p>The srcset attribute allows you to specify multiple image versions for different screen widths. Browsers automatically choose the best one.</p>
        <pre style="background:var(--bg-input, #F8FAFC); padding:15px; border-radius:12px; overflow-x:auto; border:1px solid var(--border-color, #EDF0F3);"><code>&lt;img src="small.jpg" srcset="medium.jpg 1000w, large.jpg 2000w" alt="Responsive image"&gt;</code></pre>
        <h3>2. Set max-width: 100% in CSS</h3>
        <p>This ensures images never overflow their container and scale proportionally.</p>
        <pre style="background:var(--bg-input, #F8FAFC); padding:15px; border-radius:12px; overflow-x:auto; border:1px solid var(--border-color, #EDF0F3);"><code>img { max-width: 100%; height: auto; }</code></pre>
        <h3>3. Use Modern Formats (WebP, AVIF)</h3>
        <p>Modern formats offer better compression than JPEG and PNG. Use the picture element for fallbacks.</p>
        <h3>4. Lazy Load Images</h3>
        <p>Add loading="lazy" to images below the fold to improve initial page load time.</p>
        <h3>5. Compress Images Before Upload</h3>
        <p>Tools like ImageOptim, TinyPNG, or Squoosh can reduce file size without visible quality loss.</p>
    '],
    2 => ['title' => 'Why ImageLib is the Best for Developers', 'date' => 'June 8, 2026', 'category' => 'News', 'content' => '
        <p>ImageLib was built by developers, for developers. Here\'s why thousands of developers choose us.</p>
        <h3>1. Truly Responsive Images</h3>
        <p>Every image on ImageLib is optimized to look great on any device – from smartphones to 4K monitors.</p>
        <h3>2. Developer-Friendly Credit System</h3>
        <p>Earn credits by uploading your own images or submitting feedback. Spend credits on downloads and embed codes. No hidden costs.</p>
        <h3>3. One-Click Embed Code</h3>
        <p>Stop manually writing img tags. Click the Embed button and paste responsive HTML code directly into your project.</p>
        <h3>4. No Attribution Required</h3>
        <p>Unlike other stock photo sites, ImageLib images are free to use without crediting the source.</p>
        <h3>5. Active Developer Community</h3>
        <p>Join thousands of developers sharing and using images. Contribute and benefit from the community.</p>
        <p>Ready to experience the difference? <a href="register.php" style="color:var(--accent-color, #FF6B4A);">Sign up today</a> and get 100 free credits!</p>
    '],
    3 => ['title' => 'How to Earn Free Credits', 'date' => 'June 5, 2026', 'category' => 'Guide', 'content' => '
        <p>ImageLib offers multiple ways to earn credits without spending money. Here\'s a complete guide.</p>
        <h3>1. Upload Images (+10 credits each)</h3>
        <p>Every image you upload earns you 10 credits. There\'s no upload limit, so you can earn as much as you want (up to your credit cap).</p>
        <h3>2. Submit Monthly Feedback (+50 credits)</h3>
        <p>Once per calendar month, submit the feedback form to earn 50 bonus credits. This resets every month.</p>
        <h3>3. Get Rewarded When Others Use Your Images (+5 credits per use)</h3>
        <p>When another developer downloads or embeds your image, you earn 5 credits. The more popular your images, the more you earn.</p>
        <h3>4. Referral Program (Coming Soon)</h3>
        <p>Invite friends to join ImageLib and earn bonus credits for each signup.</p>
        <p>Start earning today by <a href="upload.php" style="color:var(--accent-color, #FF6B4A);">uploading your first image</a>!</p>
    '],
];
$posts[4] = ['title' => 'Building a Community-Driven Image Library', 'date' => 'June 1, 2026', 'category' => 'Story', 'content' => '<p>The story behind ImageLib and our vision for the future.</p><p>ImageLib started as a small project by a developer who was tired of searching for images that actually worked on all devices. Today, we\'re proud to serve thousands of developers worldwide.</p><p>Our vision is to create the largest community-driven library of responsive images, completely free for developers. We believe that great design should be accessible to everyone.</p>'];
$posts[5] = ['title' => 'Optimizing Images for Web Performance', 'date' => 'May 28, 2026', 'category' => 'Performance', 'content' => '<p>Learn techniques to reduce image size without losing quality.</p><p>Images are often the largest assets on a webpage. Optimizing them can dramatically improve load times.</p><h3>Tools We Recommend</h3><ul><li>Squoosh – Google\'s image compression tool</li><li>TinyPNG – Great for PNG and JPG</li><li>ImageOptim – Desktop app for Mac</li></ul>'];
$posts[6] = ['title' => 'Understanding Image Formats: PNG vs JPG vs WebP', 'date' => 'May 25, 2026', 'category' => 'Education', 'content' => '<p>Which format should you use for your projects?</p><h3>JPEG</h3><p>Best for photographs with many colors. Supports lossy compression.</p><h3>PNG</h3><p>Best for graphics with transparency. Lossless compression.</p><h3>WebP</h3><p>Modern format that supports both lossy and lossless. Smaller file sizes than both JPEG and PNG.</p>'];

$post = $posts[$id] ?? $posts[1];

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
    $page_title = "Blog Post - ImageLib";
    $page_description = "Read the latest article on responsive images and web development.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
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
        .post-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        
        .post-category {
            color: var(--accent-color, #FF6B4A);
            font-size: 13px;
            margin-bottom: 10px;
        }
        .post-date {
            color: var(--text-muted, #6A7A8A);
            font-size: 14px;
            margin-bottom: 20px;
        }
        .post-card h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--text-primary, #1A2A3A);
        }
        .post-content {
            color: var(--text-muted, #6A7A8A);
            line-height: 1.8;
        }
        .post-content h3 {
            color: var(--text-primary, #1A2A3A);
            margin: 25px 0 15px;
            font-size: 20px;
        }
        .post-content pre {
            background: var(--bg-input, #F8FAFC);
            padding: 15px;
            border-radius: 12px;
            overflow-x: auto;
            margin: 20px 0;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .post-content pre code {
            color: var(--text-primary, #1A2A3A);
            font-family: 'Courier New', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .post-content code {
            color: var(--accent-color, #FF6B4A);
            font-family: 'Courier New', monospace;
            background: var(--bg-input, #F8FAFC);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
        .post-content ul {
            margin: 15px 0 15px 20px;
        }
        .post-content li {
            margin: 8px 0;
        }
        .post-content a {
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
        }
        .post-content a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--accent-color, #FF6B4A);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        
        body.dark-mode .post-content pre {
            background: var(--bg-input, #2A2A2A);
            border-color: rgba(255,255,255,0.06);
        }
        body.dark-mode .post-content pre code {
            color: var(--text-primary, #E8E8E8);
        }
        
        @media (max-width: 600px) {
            .post-card {
                padding: 25px;
            }
            .post-card h1 {
                font-size: 26px;
            }
            .post-content pre {
                font-size: 12px;
                padding: 10px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="post-card">
        <div class="post-category"><i class="fas fa-folder"></i> <?= $post['category'] ?></div>
        <div class="post-date"><i class="fas fa-calendar"></i> <?= $post['date'] ?></div>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="post-content"><?= $post['content'] ?></div>
        <a href="blog.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Blog</a>
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