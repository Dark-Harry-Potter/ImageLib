<?php
/**
 * cron/check_badges.php – Daily Badge Check & Email Notification
 * Run this script daily via cron job
 * 
 * Setup: Add to crontab
 * 0 2 * * * php /path/to/your/project/cron/check_badges.php
 */

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../email_templates.php';

// ============================================
// BADGE MAPPING
// ============================================
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

// ============================================
// CHECK USERS WHO EARNED NEW BADGES
// ============================================
$stmt = $conn->prepare("
    SELECT u.id, u.full_name, u.email, u.badge_level 
    FROM users u
    LEFT JOIN badge_notifications bn ON u.id = bn.user_id 
    WHERE u.badge_level > 0 
    AND (bn.last_notified_level IS NULL OR u.badge_level > bn.last_notified_level)
");

if (!$stmt) {
    error_log("check_badges.php: Failed to prepare statement");
    exit(1);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No new badges today
    exit(0);
}

// ============================================
// SEND EMAILS FOR EACH NEW BADGE
// ============================================
while ($user = $result->fetch_assoc()) {
    $level = $user['badge_level'];
    $badge = $badge_map[$level] ?? $badge_map[0];
    
    // Send email
    $email_sent = sendBadgeUnlockedEmail(
        $user['id'],
        $user['full_name'],
        $user['email'],
        $level,
        $badge['name'],
        $badge['emoji']
    );
    
    if ($email_sent) {
        // Update notification tracking
        $update = $conn->prepare("
            INSERT INTO badge_notifications (user_id, last_notified_level) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE last_notified_level = VALUES(last_notified_level)
        ");
        $update->bind_param("ii", $user['id'], $level);
        $update->execute();
        $update->close();
        
        error_log("Badge email sent to: " . $user['email'] . " - Badge: " . $badge['name']);
    } else {
        error_log("Failed to send badge email to: " . $user['email']);
    }
}

$stmt->close();
$conn->close();

echo "✅ Badge check completed.\n";
exit(0);
?>