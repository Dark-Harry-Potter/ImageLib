<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'db_config.php';
    if ($conn && $conn->ping()) {
        $stmt = $conn->prepare("SELECT badge_level, is_pro, pro_badge_icon, credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $_SESSION['badge_level'] = (int)$row['badge_level'];
            $_SESSION['is_pro'] = (int)$row['is_pro'];
            $_SESSION['pro_badge_icon'] = $row['pro_badge_icon'] ?? 'fa-crown';
            $_SESSION['user_credits'] = (float)$row['credits'];
        }
        $stmt->close();
    }
}

$badge_level = $_SESSION['badge_level'] ?? 0;
$is_pro = $_SESSION['is_pro'] ?? 0;
$pro_icon = $_SESSION['pro_badge_icon'] ?? 'fa-crown';

$badge_map = [
    0 => ['emoji' => '—', 'name' => 'Default'],
    1 => ['emoji' => '🎯', 'name' => 'First Download'],
    2 => ['emoji' => '🌿', 'name' => 'Sprout'],
    3 => ['emoji' => '🌊', 'name' => 'Wave'],
    4 => ['emoji' => '🌸', 'name' => 'Blossom'],
    5 => ['emoji' => '🔥', 'name' => 'Blaze'],
    6 => ['emoji' => '⭐', 'name' => 'Pinnacle'],
    7 => ['emoji' => '🏆', 'name' => 'Champion'],
    8 => ['emoji' => '🧠', 'name' => 'Sage'],
    9 => ['emoji' => '🧙', 'name' => 'Wizard'],
    10 => ['emoji' => '👑', 'name' => 'Royalty'],
    11 => ['emoji' => '⚡', 'name' => 'Legend']
];

$badge_emoji = $badge_map[$badge_level]['emoji'] ?? '—';
$badge_name = $badge_map[$badge_level]['name'] ?? 'Default';

$theme_class = 'theme-' . strtolower(str_replace(' ', '-', $badge_name));
$dark_mode_class = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) ? 'dark-mode' : '';
$pro_class = ($is_pro == 1) ? 'pro-theme' : '';
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .navbar {
            background: var(--navbar-bg, #4F46E5);
            padding: 12px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .navbar-logo img {
            height: 40px;
            width: auto;
        }
        .navbar-brand {
            font-size: 22px;
            font-weight: 700;
            color: var(--navbar-text, #FFFFFF) !important;
            text-decoration: none;
            transition: none !important;
        }
        .navbar-brand span {
            color: var(--accent-color, #F59E0B);
        }
        .navbar-brand:hover {
            color: var(--navbar-text, #FFFFFF) !important;
            opacity: 1 !important;
        }
        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .navbar-right a {
            color: var(--navbar-text, #FFFFFF);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            padding: 4px 0;
            transition: opacity 0.2s;
        }
        .navbar-right a:hover {
            opacity: 0.85;
        }
        .credits-badge {
            background: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 30px;
            color: var(--navbar-text, #FFFFFF);
            font-weight: 600;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
        }
        .badge-tracker-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.1);
            padding: 4px 14px 4px 10px;
            border-radius: 30px;
            text-decoration: none;
            color: var(--navbar-text, #FFFFFF) !important;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.2s;
        }
        .badge-tracker-btn:hover {
            background: rgba(255,255,255,0.2);
            opacity: 1 !important;
        }
        .badge-tracker-btn .badge-emoji {
            font-size: 18px;
        }
        .badge-tracker-btn .badge-name {
            font-size: 12px;
            font-weight: 500;
        }
        .badge-tracker-btn .pro-icon {
            font-size: 12px;
            color: #FDE047;
            animation: glowPulse 2s infinite;
        }
        .badge-tracker-btn .arrow {
            font-size: 10px;
            opacity: 0.6;
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.1); text-shadow: 0 0 15px rgba(253,224,71,0.5); }
        }
        .logout-button {
            background: rgba(239,68,68,0.2);
            color: var(--navbar-text, #FFFFFF) !important;
            padding: 6px 18px;
            border-radius: 30px;
            border: 1px solid rgba(239,68,68,0.3);
        }
        .logout-button:hover {
            background: rgba(239,68,68,0.4);
            opacity: 1 !important;
        }
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            padding: 4px;
        }
        .hamburger span {
            width: 24px;
            height: 2px;
            background: var(--navbar-text, #FFFFFF);
            border-radius: 2px;
            transition: all 0.3s;
        }
        .color-bars {
            display: flex;
            gap: 0;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1001;
        }
        .color-bar {
            height: 4px;
            flex: 1;
        }
        .bar-coral { background: #FF6B4A; }
        .bar-teal { background: #2D9CDB; }
        .bar-mint { background: #20B2AA; }
        .bar-rose { background: #EF4444; }
        .bar-amber { background: #F59E0B; }
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }
        .shape {
            position: absolute;
            opacity: 0.04;
            font-size: 200px;
            animation: floatShape 25s infinite ease-in-out;
            color: var(--accent-color, #FF6B4A);
        }
        .shape-1 { top: 5%; left: -5%; animation-duration: 28s; }
        .shape-2 { bottom: 5%; right: -5%; font-size: 280px; animation-duration: 35s; animation-delay: -5s; color: #2D9CDB; }
        .shape-3 { top: 30%; left: 85%; font-size: 140px; animation-duration: 22s; animation-delay: -10s; color: #20B2AA; }
        .shape-4 { bottom: 25%; left: -3%; font-size: 120px; animation-duration: 26s; animation-delay: -3s; color: #EF4444; }
        .shape-5 { top: 60%; left: 20%; font-size: 100px; animation-duration: 30s; animation-delay: -7s; color: #F59E0B; }
        .shape-6 { bottom: 40%; right: 15%; font-size: 90px; animation-duration: 24s; animation-delay: -12s; color: var(--accent-color, #FF6B4A); }
        @keyframes floatShape {
            0% { transform: translate(0,0) rotate(0deg); }
            50% { transform: translate(30px,20px) rotate(5deg); }
            100% { transform: translate(0,0) rotate(0deg); }
        }
        @media (max-width: 992px) {
            .navbar-right { gap: 15px; }
            .navbar-right a { font-size: 13px; }
            .badge-tracker-btn .badge-name { display: none; }
        }
        @media (max-width: 768px) {
            .navbar { padding: 12px 20px; }
            .hamburger { display: flex; }
            .navbar-right {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 12px;
                padding: 20px 0 10px 0;
                margin-top: 15px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
            .navbar-right.show { display: flex; }
            .navbar-right a { width: 100%; text-align: center; padding: 8px 0; }
            .credits-badge { text-align: center; width: fit-content; margin: 0 auto; }
            .badge-tracker-btn { justify-content: center; width: fit-content; margin: 0 auto; }
            .badge-tracker-btn .badge-name { display: inline; }
            .logout-button { text-align: center; }
            .navbar-brand { font-size: 18px; }
            .navbar-logo img { height: 32px; }
        }
        @media (max-width: 480px) {
            .navbar { padding: 10px 15px; }
            .navbar-brand { font-size: 16px; }
            .navbar-logo img { height: 28px; }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?> <?= $pro_class ?>">

<div class="color-bars">
    <div class="color-bar bar-coral"></div>
    <div class="color-bar bar-teal"></div>
    <div class="color-bar bar-mint"></div>
    <div class="color-bar bar-rose"></div>
    <div class="color-bar bar-amber"></div>
</div>

<div class="bg-shapes">
    <div class="shape shape-1">◧</div>
    <div class="shape shape-2">◈</div>
    <div class="shape shape-3">◉</div>
    <div class="shape shape-4">◍</div>
    <div class="shape shape-5">✦</div>
    <div class="shape shape-6">⬟</div>
</div>

<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-left">
            <a href="index.php" class="navbar-logo">
                <img src="logo.png" alt="ImageLib Logo">
                <span class="navbar-brand">Image<span>Lib</span></span>
            </a>
        </div>
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="navbar-right" id="navMenu">
            <a href="index.php">Home</a>
            <a href="gallery.php">Gallery</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="upload.php">Upload</a>
                <?php if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
                    <a href="form.php">Feedback</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <span class="credits-badge">💎 <?= (int)($_SESSION['user_credits'] ?? 0) ?></span>
                <a href="badge_tracker.php" class="badge-tracker-btn" title="View your badge progress">
                    <span class="badge-emoji"><?= $badge_emoji ?></span>
                    <span class="badge-name"><?= $badge_name ?></span>
                    <?php if($is_pro): ?>
                        <span class="pro-icon"><i class="fas <?= $pro_icon ?>"></i></span>
                    <?php endif; ?>
                    <span class="arrow">→</span>
                </a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin/index.php" style="color:#FDE047;">⚙️ Admin</a>
                    <a href="admin/analytics.php" style="color:#60A5FA;">📊 Analytics</a>
                <?php endif; ?>
                <a href="logout.php" class="logout-button">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
    document.querySelectorAll('.navbar-right a').forEach(link => {
        link.addEventListener('click', function() {
            if (navMenu.classList.contains('show')) {
                navMenu.classList.remove('show');
            }
        });
    });
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
</body>
</html>