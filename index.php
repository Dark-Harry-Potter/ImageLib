<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . '/db_config.php';

// Get real statistics from database
$total_images = 0;
$total_users = 0;
$total_downloads = 0;
$total_uploads_today = 0;

if ($conn && $conn->ping()) {
    $result = $conn->query("SELECT COUNT(*) as count FROM image_library");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_images = $row ? $row['count'] : 0;
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_users = $row ? $row['count'] : 0;
    }
    
    $result = $conn->query("SELECT SUM(downloads) as total FROM image_library");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_downloads = $row ? ($row['total'] ?? 0) : 0;
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM image_library WHERE DATE(created_at) = CURDATE()");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_uploads_today = $row ? $row['count'] : 0;
    }
}

// Get random images for hero background
$hero_images = [];
if ($conn && $conn->ping() && $total_images > 0) {
    $result = $conn->query("SELECT filename FROM image_library ORDER BY RAND() LIMIT 12");
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $hero_images[] = "uploads/" . ($row['filename'] ?? '');
        }
    }
}
if (empty($hero_images)) {
    $hero_images = [
        'https://picsum.photos/id/1015/400/300', 'https://picsum.photos/id/104/400/300',
        'https://picsum.photos/id/106/400/300', 'https://picsum.photos/id/107/400/300',
        'https://picsum.photos/id/116/400/300', 'https://picsum.photos/id/119/400/300',
        'https://picsum.photos/id/20/400/300', 'https://picsum.photos/id/26/400/300',
        'https://picsum.photos/id/28/400/300', 'https://picsum.photos/id/29/400/300',
        'https://picsum.photos/id/30/400/300', 'https://picsum.photos/id/36/400/300'
    ];
}

$badge_level = 0;
if (isset($_SESSION['user_id']) && $conn && $conn->ping()) {
    $stmt = $conn->prepare("SELECT badge_level FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $badge_level = $row['badge_level'];
    }
    $stmt->close();
}
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
    $page_title = "ImageLib – Free Responsive Images for Developers";
    $page_description = "Free, responsive images for developers. Download, embed, and use high-quality images in your HTML/CSS projects.";
    $page_keywords = "free images, responsive images, stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <style>
        /* Hero Section */
        .hero {
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        
        .hero-bg-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 8px;
            padding: 15px;
            opacity: 0.15;
            pointer-events: none;
        }
        
        .hero-bg-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            z-index: 1;
            transition: background 0.4s ease;
        }

        body.dark-mode .hero-overlay {
            background: rgba(10, 10, 10, 0.85) !important;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-title {
            font-size: clamp(42px, 8vw, 72px);
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--text-primary, #1A2A3A);
        }
        
        .hero-title span {
            color: var(--accent-color, #FF6B4A);
        }
        
        body.dark-mode .hero-title {
            color: #FFFFFF !important;
        }
        body.dark-mode .hero-title span {
            color: var(--accent-color, #FFD700) !important;
        }
        
        .hero-subtitle {
            font-size: clamp(16px, 4vw, 20px);
            color: var(--text-muted, #6A7A8A);
            margin-bottom: 28px;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.4;
        }
        
        body.dark-mode .hero-subtitle {
            color: #C0C0C0 !important;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 50px 20px;
            background: var(--bg-primary, #f1f5f9);
            border-top: 1px solid var(--border-color, #EDF0F3);
            border-bottom: 1px solid var(--border-color, #EDF0F3);
        }
        
        body.dark-mode .stats-section {
            background: #1A1A1A !important;
            border-top-color: rgba(255, 255, 255, 0.06) !important;
            border-bottom-color: rgba(255, 255, 255, 0.06) !important;
        }
        
        .stats-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 30px;
            text-align: center;
        }
        
        .stat-item {
            flex: 1;
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color, #FF6B4A);
            display: block;
        }
        
        body.dark-mode .stat-number {
            color: var(--primary-color, #FF6B4A) !important;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--text-muted, #6A7A8A);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        body.dark-mode .stat-label {
            color: #C0C0C0 !important;
        }
        
        /* General Sections */
        .section {
            padding: 70px 20px;
        }
        
        .section-light {
            background: transparent;
        }
        
        body.dark-mode .section-light {
            background: transparent !important;
        }
        
        .section-title {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-primary, #1A2A3A);
        }
        
        body.dark-mode .section-title {
            color: #FFFFFF !important;
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--text-muted, #6A7A8A);
            margin-bottom: 45px;
            font-size: 16px;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
        }
        
        body.dark-mode .section-subtitle {
            color: #C0C0C0 !important;
        }
        
        /* Feature Cards */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            transition: var(--transition, 0.25s ease);
            border: 1px solid var(--border-color, #EDF0F3);
            position: relative;
            overflow: hidden;
        }
        
        body.dark-mode .feature-card {
            background: #1A1A1A !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover, 0 12px 24px rgba(0,0,0,0.08));
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
        }
        
        .feature-card:nth-child(1)::before { background: var(--primary-color, #FF6B4A); }
        .feature-card:nth-child(2)::before { background: #2D9CDB; }
        .feature-card:nth-child(3)::before { background: #20B2AA; }
        
        .feature-icon {
            font-size: 44px;
            margin-bottom: 18px;
            color: var(--primary-color, #FF6B4A);
        }
        
        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--text-primary, #1A2A3A);
        }
        
        body.dark-mode .feature-card h3 {
            color: #FFFFFF !important;
        }
        
        .feature-card p {
            color: var(--text-muted, #6A7A8A);
            line-height: 1.5;
            font-size: 14px;
        }
        
        body.dark-mode .feature-card p {
            color: #C0C0C0 !important;
        }
        
        /* Steps Grid */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .step-card {
            text-align: center;
            padding: 20px;
        }
        
        body.dark-mode .step-card h3 {
            color: #FFFFFF !important;
        }
        body.dark-mode .step-card p {
            color: #C0C0C0 !important;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary-color, #FF6B4A);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
            margin: 0 auto 18px;
        }
        
        body.dark-mode .step-number {
            background: var(--primary-color, #FF6B4A) !important;
            color: #FFFFFF !important;
        }
        
        /* Pricing Cards */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .pricing-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            border: 1px solid var(--border-color, #EDF0F3);
            transition: var(--transition, 0.25s ease);
        }
        
        body.dark-mode .pricing-card {
            background: #1A1A1A !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }
        
        .pricing-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover, 0 12px 24px rgba(0,0,0,0.05));
        }
        
        .pricing-card.featured {
            border: 2px solid var(--primary-color, #FF6B4A);
        }
        
        body.dark-mode .pricing-card.featured {
            border-color: var(--primary-color, #FF6B4A) !important;
        }
        
        .pricing-card.featured::after {
            content: '⭐ Best Value';
            position: absolute;
            top: -12px;
            right: 20px;
            background: var(--primary-color, #FF6B4A);
            color: #FFFFFF;
            padding: 2px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .price {
            font-size: 42px;
            font-weight: 800;
            color: var(--primary-color, #FF6B4A);
            margin: 15px 0;
        }
        
        body.dark-mode .price {
            color: var(--primary-color, #FF6B4A) !important;
        }
        
        .pricing-card h3 {
            color: var(--text-primary, #1A2A3A);
        }
        
        body.dark-mode .pricing-card h3 {
            color: #FFFFFF !important;
        }
        
        .pricing-card p {
            color: var(--text-muted, #6A7A8A);
        }
        
        body.dark-mode .pricing-card p {
            color: #C0C0C0 !important;
        }
        
        .pricing-card ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            text-align: left;
        }
        
        .pricing-card ul li {
            padding: 6px 0;
            color: var(--text-muted, #6A7A8A);
            font-size: 13px;
        }
        
        body.dark-mode .pricing-card ul li {
            color: #C0C0C0 !important;
        }
        
        .pricing-card ul li::before {
            content: '✓ ';
            color: #20B2AA;
            font-weight: 700;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                min-height: 70vh;
            }
            .stats-container {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            .section {
                padding: 50px 20px;
            }
            .section-title {
                font-size: 28px;
            }
            .hero-bg-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(6, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .hero-bg-grid {
                grid-template-columns: 1fr;
                grid-template-rows: repeat(12, 1fr);
            }
            .hero {
                min-height: 60vh;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include __DIR__ . '/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-bg-grid">
        <?php foreach($hero_images as $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="bg" loading="lazy">
        <?php endforeach; ?>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Image<span>Lib</span></h1>
        <p class="hero-subtitle">Responsive, high-quality images for your HTML/CSS projects. Download, embed, and build faster.</p>
        <div class="hero-buttons">
            <a href="gallery.php" class="btn btn-primary"><i class="fas fa-images"></i> Browse Gallery</a>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> Join Free</a>
            <?php else: ?>
                <a href="upload.php" class="btn btn-outline"><i class="fas fa-upload"></i> Upload Images</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item"><span class="stat-number" data-target="<?= $total_images ?>">0</span><span class="stat-label">Total Images</span></div>
        <div class="stat-item"><span class="stat-number" data-target="<?= $total_users ?>">0</span><span class="stat-label">Active Users</span></div>
        <div class="stat-item"><span class="stat-number" data-target="<?= $total_downloads ?>">0</span><span class="stat-label">Total Downloads</span></div>
        <div class="stat-item"><span class="stat-number" data-target="100">0<span>%</span></span><span class="stat-label">Responsive</span></div>
    </div>
</section>

<!-- FEATURE CARDS SECTION -->
<section class="section">
    <h2 class="section-title">Why Developers Love ImageLib</h2>
    <p class="section-subtitle">Built by developers, for developers</p>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
            <h3>Fully Responsive</h3>
            <p>Images scale perfectly on any device – from 4K monitors to mobile screens.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-code"></i></div>
            <h3>Easy Embed Code</h3>
            <p>One click to copy responsive HTML/CSS code for any image.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-download"></i></div>
            <h3>Earn While You Upload</h3>
            <p>Upload images to earn credits. Spend on downloads or embed codes.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS SECTION -->
<section class="section section-light">
    <h2 class="section-title">How It Works</h2>
    <p class="section-subtitle">Get started in 4 simple steps</p>
    <div class="steps-grid">
        <div class="step-card"><div class="step-number">1</div><h3>Sign Up Free</h3><p>Create your account and get 100 free credits</p></div>
        <div class="step-card"><div class="step-number">2</div><h3>Browse or Upload</h3><p>Discover thousands of images or share your own</p></div>
        <div class="step-card"><div class="step-number">3</div><h3>Download or Embed</h3><p>Use credits to download or copy embed code</p></div>
        <div class="step-card"><div class="step-number">4</div><h3>Build Faster</h3><p>Focus on your code – we handle the images</p></div>
    </div>
</section>

<!-- PRICING CARDS -->
<section class="section">
    <h2 class="section-title">Credit Plans</h2>
    <p class="section-subtitle">Start free, upgrade anytime. No hidden fees.</p>
    <div class="pricing-grid">
        <div class="pricing-card">
            <h3>Free</h3>
            <div class="price">100</div>
            <p>Free credits on signup</p>
            <ul>
                <li>Basic images access</li>
                <li>Embed code</li>
                <li>Monthly feedback bonus</li>
            </ul>
        </div>
        <div class="pricing-card featured">
            <h3>Pro</h3>
            <div class="price">500</div>
            <p>$9.99 / month</p>
            <ul>
                <li>All free features</li>
                <li>Unlimited downloads</li>
                <li>Priority support</li>
                <li>✨ Pro badge</li>
            </ul>
        </div>
        <div class="pricing-card">
            <h3>Enterprise</h3>
            <div class="price">Custom</div>
            <p>Contact us</p>
            <ul>
                <li>Everything in Pro</li>
                <li>API access</li>
                <li>Dedicated support</li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>

<!-- Counter Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stat-number');
                    counters.forEach(function(counter) {
                        const target = parseInt(counter.getAttribute('data-target'));
                        let current = 0;
                        const increment = Math.max(1, Math.ceil(target / 60));
                        const updateCount = function() {
                            current += increment;
                            if (current > target) current = target;
                            counter.innerText = current;
                            if (current < target) {
                                setTimeout(updateCount, 20);
                            }
                        };
                        updateCount();
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        observer.observe(statsSection);
    }
});
</script>

<!-- Pass PHP variables to JavaScript -->
<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<script src="toast.js"></script>
</body>
</html>