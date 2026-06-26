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
    $page_title = "Roadmap - ImageLib";
    $page_description = "ImageLib development roadmap - see what's built, what's in progress, and what's coming next.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, roadmap";
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
        .roadmap-header {
            text-align: center;
            padding: 40px 0 20px;
        }
        .roadmap-header h1 {
            font-size: 38px;
            color: var(--text-primary, #1A2A3A);
        }
        .roadmap-header h1 i {
            color: var(--accent-color, #FF6B4A);
            margin-right: 12px;
        }
        .roadmap-header p {
            color: var(--text-muted, #6A7A8A);
            font-size: 16px;
            max-width: 600px;
            margin: 10px auto 0;
        }
        
        .roadmap-timeline {
            position: relative;
            padding: 20px 0;
        }
        .roadmap-timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--border-color, #EDF0F3);
            transform: translateX(-50%);
        }
        
        .roadmap-item {
            display: flex;
            justify-content: flex-end;
            padding: 20px 0;
            position: relative;
            width: 50%;
        }
        .roadmap-item:nth-child(odd) {
            padding-right: 40px;
            text-align: right;
        }
        .roadmap-item:nth-child(even) {
            margin-left: 50%;
            padding-left: 40px;
            text-align: left;
        }
        
        .roadmap-item .badge-status {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            z-index: 2;
            border: 3px solid var(--bg-card, #FFFFFF);
            box-shadow: 0 0 0 3px var(--border-color, #EDF0F3);
        }
        .roadmap-item:nth-child(odd) .badge-status {
            right: -20px;
        }
        .roadmap-item:nth-child(even) .badge-status {
            left: -20px;
        }
        
        .badge-status.done {
            background: #20B2AA;
            color: #FFFFFF;
        }
        .badge-status.in-progress {
            background: #F59E0B;
            color: #FFFFFF;
            animation: pulse 1.5s infinite;
        }
        .badge-status.planned {
            background: var(--text-muted, #6A7A8A);
            color: #FFFFFF;
        }
        
        @keyframes pulse {
            0%, 100% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-50%) scale(1.1); }
        }
        
        .roadmap-content {
            background: var(--bg-card, #FFFFFF);
            border-radius: 16px;
            padding: 20px 24px;
            border: 1px solid var(--border-color, #EDF0F3);
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            transition: var(--transition, 0.25s ease);
            max-width: 400px;
        }
        .roadmap-content:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover, 0 12px 24px rgba(0,0,0,0.08));
        }
        .roadmap-content h3 {
            font-size: 18px;
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 6px;
        }
        .roadmap-content p {
            color: var(--text-muted, #6A7A8A);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 0;
        }
        .roadmap-content .tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tag-done {
            background: rgba(32, 178, 170, 0.15);
            color: #20B2AA;
        }
        .tag-in-progress {
            background: rgba(245, 158, 11, 0.15);
            color: #F59E0B;
        }
        .tag-planned {
            background: rgba(106, 122, 138, 0.15);
            color: #6A7A8A;
        }
        
        .roadmap-content.pro-highlight {
            border-left: 4px solid #FDE047;
        }
        
        body.dark-mode .roadmap-timeline::before {
            background: rgba(255,255,255,0.06);
        }
        body.dark-mode .roadmap-item .badge-status {
            border-color: var(--bg-card, #1A1A1A);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.06);
        }
        body.dark-mode .roadmap-content {
            background: var(--bg-card, #1A1A1A);
            border-color: rgba(255,255,255,0.06);
        }
        
        @media (max-width: 768px) {
            .roadmap-timeline::before {
                left: 20px;
            }
            .roadmap-item {
                width: 100%;
                padding-left: 60px !important;
                padding-right: 0 !important;
                text-align: left !important;
            }
            .roadmap-item:nth-child(odd) .badge-status,
            .roadmap-item:nth-child(even) .badge-status {
                left: 0 !important;
                right: auto !important;
            }
            .roadmap-item:nth-child(odd) {
                margin-left: 0;
            }
            .roadmap-item:nth-child(even) {
                margin-left: 0;
            }
            .roadmap-content {
                max-width: 100%;
            }
            .roadmap-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container">
    <div class="roadmap-header">
        <h1><i class="fas fa-map-signs"></i> ImageLib Roadmap</h1>
        <p>See what's built, what's in progress, and what's coming next.</p>
    </div>
    
    <div class="roadmap-timeline">
        
        <!-- DONE -->
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Core Platform</h3>
                <p>Gallery, upload, download, embed, authentication, and user profiles.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Badge System</h3>
                <p>12 badge levels from Default to Legend, earned through downloads.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Dynamic Theming</h3>
                <p>Each badge has its own color palette. Light mode + dark mode with 3D glow.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Upload Limits</h3>
                <p>Upload limits grow based on downloads received. Pro users get double the limit.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Credit System</h3>
                <p>100 free credits on signup. No earning — purchase more via social media.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Email Automation</h3>
                <p>Welcome emails, badge unlock, out-of-credits, and upload limit increase notifications.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status done"><i class="fas fa-check"></i></div>
            <div class="roadmap-content">
                <h3>Social Monetization</h3>
                <p>Users purchase credit packs via Twitter, Instagram, GitHub, Bluesky, Reddit, or email.</p>
                <span class="tag tag-done">Done</span>
            </div>
        </div>
        
        <!-- IN PROGRESS -->
        <div class="roadmap-item">
            <div class="badge-status in-progress"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="roadmap-content pro-highlight">
                <h3>Pro Badges</h3>
                <p>Paid users get double upload limits, reduced credit costs (1 per download/embed), and premium UI animations.</p>
                <span class="tag tag-in-progress">In Progress</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status in-progress"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="roadmap-content">
                <h3>Badge Tracker</h3>
                <p>E-commerce style timeline showing badge progress, downloads needed, and next badge preview.</p>
                <span class="tag tag-in-progress">In Progress</span>
            </div>
        </div>
        
        <!-- PLANNED -->
        <div class="roadmap-item">
            <div class="badge-status planned"><i class="fas fa-clock"></i></div>
            <div class="roadmap-content">
                <h3>User Dashboard</h3>
                <p>Central hub showing uploads, downloads, credits, badge progress, and activity history.</p>
                <span class="tag tag-planned">Planned</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status planned"><i class="fas fa-clock"></i></div>
            <div class="roadmap-content">
                <h3>Image Analytics</h3>
                <p>See which images get the most downloads, views, and embeds.</p>
                <span class="tag tag-planned">Planned</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status planned"><i class="fas fa-clock"></i></div>
            <div class="roadmap-content">
                <h3>Collections</h3>
                <p>Users can organize images into collections and share them with others.</p>
                <span class="tag tag-planned">Planned</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status planned"><i class="fas fa-clock"></i></div>
            <div class="roadmap-content">
                <h3>Community Features</h3>
                <p>Comments, likes, and user profiles to build a thriving developer community.</p>
                <span class="tag tag-planned">Planned</span>
            </div>
        </div>
        
        <div class="roadmap-item">
            <div class="badge-status planned"><i class="fas fa-clock"></i></div>
            <div class="roadmap-content">
                <h3>Admin Panel</h3>
                <p>Manage users, credits, images, and site settings from a dedicated admin interface.</p>
                <span class="tag tag-planned">Planned</span>
            </div>
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