<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT full_name, email, downloads_received, badge_level, is_pro, pro_badge_icon FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$downloads = (int)($user['downloads_received'] ?? 0);
$current_badge_level = (int)($user['badge_level'] ?? 0);
$is_pro = (int)($user['is_pro'] ?? 0);
$pro_icon = $user['pro_badge_icon'] ?? '';

// Badge data
$badge_data = [
    0 => ['emoji' => '—', 'name' => 'Default', 'downloads_needed' => 0],
    1 => ['emoji' => '🎯', 'name' => 'First Download', 'downloads_needed' => 1],
    2 => ['emoji' => '🌿', 'name' => 'Sprout', 'downloads_needed' => 10],
    3 => ['emoji' => '🌊', 'name' => 'Wave', 'downloads_needed' => 50],
    4 => ['emoji' => '🌸', 'name' => 'Blossom', 'downloads_needed' => 100],
    5 => ['emoji' => '🔥', 'name' => 'Blaze', 'downloads_needed' => 500],
    6 => ['emoji' => '⭐', 'name' => 'Pinnacle', 'downloads_needed' => 1000],
    7 => ['emoji' => '🏆', 'name' => 'Champion', 'downloads_needed' => 5000],
    8 => ['emoji' => '🧠', 'name' => 'Sage', 'downloads_needed' => 10000],
    9 => ['emoji' => '🧙', 'name' => 'Wizard', 'downloads_needed' => 50000],
    10 => ['emoji' => '👑', 'name' => 'Royalty', 'downloads_needed' => 100000],
    11 => ['emoji' => '⚡', 'name' => 'Legend', 'downloads_needed' => 1000000]
];

// Calculate next badge
$next_level = null;
$progress = 100;
$current_needed = 0;
$next_needed = 0;

foreach ($badge_data as $level => $data) {
    if ($downloads >= $data['downloads_needed']) {
        $current_badge_level = $level;
    } else {
        $next_level = $level;
        break;
    }
}

if ($next_level !== null) {
    $current_needed = $badge_data[$current_badge_level]['downloads_needed'];
    $next_needed = $badge_data[$next_level]['downloads_needed'];
    $progress = min(100, max(0, (($downloads - $current_needed) / ($next_needed - $current_needed)) * 100));
    $next_emoji = $badge_data[$next_level]['emoji'];
    $next_name = $badge_data[$next_level]['name'];
    $downloads_needed = $next_needed - $downloads;
} else {
    $progress = 100;
    $next_emoji = '🏁';
    $next_name = 'Max Level Reached!';
    $downloads_needed = 0;
}

$current_emoji = $badge_data[$current_badge_level]['emoji'];
$current_name = $badge_data[$current_badge_level]['name'];

$badge_level = $current_badge_level;
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
    $page_title = "Badge Tracker - ImageLib";
    $page_description = "Track your badge progress and see how many downloads you need for the next level.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, badge tracker";
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
        .tracker-header {
            text-align: center;
            padding: 20px 0 10px;
        }
        .tracker-header h1 {
            font-size: 32px;
            color: var(--text-primary, #1A2A3A);
        }
        .tracker-header h1 i {
            color: var(--accent-color, #FF6B4A);
            margin-right: 10px;
        }
        .tracker-header p {
            color: var(--text-muted, #6A7A8A);
            font-size: 14px;
        }
        
        .current-badge-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 30px;
            text-align: center;
            border: 1px solid var(--border-color, #EDF0F3);
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            margin-bottom: 30px;
        }
        .current-badge-card .big-emoji {
            font-size: 64px;
            display: block;
            margin-bottom: 10px;
        }
        .current-badge-card .badge-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary, #1A2A3A);
        }
        .current-badge-card .badge-level {
            font-size: 14px;
            color: var(--text-muted, #6A7A8A);
        }
        .current-badge-card .downloads-count {
            font-size: 16px;
            color: var(--text-muted, #6A7A8A);
            margin-top: 8px;
        }
        .current-badge-card .downloads-count strong {
            color: var(--accent-color, #FF6B4A);
            font-size: 20px;
        }
        
        .progress-section {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 30px;
            border: 1px solid var(--border-color, #EDF0F3);
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            margin-bottom: 30px;
        }
        .progress-section .next-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .progress-section .next-badge .label {
            font-size: 14px;
            color: var(--text-muted, #6A7A8A);
        }
        .progress-section .next-badge .target {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary, #1A2A3A);
        }
        .progress-section .next-badge .target span {
            color: var(--accent-color, #FF6B4A);
        }
        
        .progress-bar-track {
            width: 100%;
            height: 12px;
            background: var(--bg-input, #F0F2F5);
            border-radius: 30px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar-track .fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color, #FF6B4A), var(--accent-color, #F59E0B));
            border-radius: 30px;
            transition: width 0.8s ease;
            width: 0%;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted, #6A7A8A);
            margin-top: 6px;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .badge-timeline {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 30px;
            border: 1px solid var(--border-color, #EDF0F3);
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
        }
        .badge-timeline h3 {
            font-size: 18px;
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 20px;
        }
        .badge-timeline h3 i {
            color: var(--accent-color, #FF6B4A);
            margin-right: 8px;
        }
        
        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
        }
        .timeline-item {
            background: var(--bg-input, #F8FAFC);
            border-radius: 16px;
            padding: 12px;
            text-align: center;
            border: 2px solid var(--border-color, #EDF0F3);
            transition: var(--transition, 0.25s ease);
            position: relative;
        }
        .timeline-item:hover {
            transform: translateY(-3px);
        }
        .timeline-item .emoji {
            font-size: 28px;
            display: block;
        }
        .timeline-item .name {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted, #6A7A8A);
            margin-top: 4px;
        }
        .timeline-item .req {
            font-size: 9px;
            color: var(--text-light, #94A3B8);
        }
        .timeline-item.unlocked {
            border-color: #20B2AA;
            background: rgba(32, 178, 170, 0.08);
        }
        .timeline-item.unlocked .name {
            color: #20B2AA;
        }
        .timeline-item.current {
            border-color: var(--accent-color, #FF6B4A);
            background: rgba(255, 107, 74, 0.08);
            box-shadow: 0 0 20px rgba(255, 107, 74, 0.1);
        }
        .timeline-item.current .name {
            color: var(--accent-color, #FF6B4A);
        }
        .timeline-item.locked {
            opacity: 0.5;
        }
        .timeline-item .check-mark {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #20B2AA;
            color: #FFFFFF;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pro-badge-display {
            display: inline-block;
            background: linear-gradient(135deg, #FDE047, #FFD700);
            color: #0A0A14;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 8px;
            animation: glowPulse 2s infinite;
        }
        @keyframes glowPulse {
            0%, 100% { box-shadow: 0 0 10px rgba(253, 224, 71, 0.3); }
            50% { box-shadow: 0 0 25px rgba(253, 224, 71, 0.6); }
        }
        
        body.dark-mode .timeline-item {
            background: var(--bg-input, #2A2A2A);
            border-color: rgba(255,255,255,0.06);
        }
        body.dark-mode .timeline-item.unlocked {
            border-color: #20B2AA;
            background: rgba(32, 178, 170, 0.15);
        }
        body.dark-mode .timeline-item.current {
            border-color: var(--accent-color, #FF6B4A);
            background: rgba(255, 107, 74, 0.15);
        }
        
        @media (max-width: 600px) {
            .timeline-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
            .timeline-item .emoji {
                font-size: 20px;
            }
            .current-badge-card .big-emoji {
                font-size: 48px;
            }
            .current-badge-card .badge-name {
                font-size: 22px;
            }
            .progress-section .next-badge .target {
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container">
    <div class="tracker-header">
        <h1><i class="fas fa-trophy"></i> Badge Tracker</h1>
        <p>Track your progress and see what's next.</p>
    </div>
    
    <!-- Current Badge -->
    <div class="current-badge-card">
        <span class="big-emoji"><?= $current_emoji ?></span>
        <div class="badge-name">
            <?= $current_name ?>
            <?php if($is_pro): ?>
                <span class="pro-badge-display">
                    <i class="fas <?= $pro_icon ?: 'fa-crown' ?>"></i> PRO
                </span>
            <?php endif; ?>
        </div>
        <div class="badge-level">Level <?= $current_badge_level ?> of 11</div>
        <div class="downloads-count">
            📥 <strong><?= number_format($downloads) ?></strong> downloads received
        </div>
    </div>
    
    <!-- Progress to Next Badge -->
    <div class="progress-section">
        <div class="next-badge">
            <span class="label">Next Badge</span>
            <span class="target">
                <?php if ($next_level !== null): ?>
                    <?= $next_emoji ?> <span><?= $next_name ?></span>
                <?php else: ?>
                    🏁 <span>Max Level Reached!</span>
                <?php endif; ?>
            </span>
        </div>
        
        <?php if ($next_level !== null): ?>
            <div class="progress-bar-track">
                <div class="fill" style="width: <?= round($progress) ?>%;"></div>
            </div>
            <div class="progress-label">
                <span><?= number_format($downloads) ?> downloads</span>
                <span><?= number_format($downloads_needed) ?> more needed</span>
                <span><?= number_format($next_needed) ?> required</span>
            </div>
        <?php else: ?>
            <div class="progress-bar-track">
                <div class="fill" style="width: 100%;"></div>
            </div>
            <div class="progress-label">
                <span>🏆 You've reached the highest badge!</span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Full Badge Timeline -->
    <div class="badge-timeline">
        <h3><i class="fas fa-list-ul"></i> All Badges</h3>
        <div class="timeline-grid">
            <?php foreach ($badge_data as $level => $data): 
                $is_unlocked = $downloads >= $data['downloads_needed'];
                $is_current = $level == $current_badge_level;
                $is_locked = !$is_unlocked;
                $classes = '';
                if ($is_unlocked && !$is_current) $classes .= ' unlocked';
                if ($is_current) $classes .= ' current';
                if ($is_locked) $classes .= ' locked';
            ?>
            <div class="timeline-item <?= $classes ?>">
                <?php if ($is_unlocked && !$is_current): ?>
                    <span class="check-mark"><i class="fas fa-check"></i></span>
                <?php endif; ?>
                <span class="emoji"><?= $data['emoji'] ?></span>
                <div class="name"><?= $data['name'] ?></div>
                <div class="req"><?= number_format($data['downloads_needed']) ?></div>
            </div>
            <?php endforeach; ?>
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