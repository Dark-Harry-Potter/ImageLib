<?php
session_start();
if (isset($_SESSION['user_id'])) {
    require_once 'db_config.php';
    if ($conn && $conn->ping()) {
        $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $_SESSION['user_credits'] = (float)$row['credits'];
        }
        $stmt->close();
    }
}
require_once 'db_config.php';
require_once 'email_templates.php';
require_once 'csrf_token.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$profile_message = '';
$profile_error = '';

$stmt = $conn->prepare("SELECT id, full_name, email, username, created_at, last_login, credits, dark_mode, profile_pic, badge_level, downloads_received, is_pro, pro_badge_icon FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$uploadCount = 0;
$downloadsReceived = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(downloads) as downloads FROM image_library WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$uploadCount = $row['total'] ?? 0;
$downloadsReceived = $row['downloads'] ?? 0;
$stmt->close();

$badge_thresholds = [0=>0,1=>1,2=>10,3=>50,4=>100,5=>500,6=>1000,7=>5000,8=>10000,9=>50000,10=>100000,11=>1000000];
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

$new_badge_level = 0;
foreach ($badge_thresholds as $level => $threshold) {
    if ($downloadsReceived >= $threshold) $new_badge_level = $level;
}
$old_badge_level = (int)($user['badge_level'] ?? 0);
if ($new_badge_level > $old_badge_level) {
    $conn->query("UPDATE users SET badge_level = $new_badge_level WHERE id = $user_id");
    $user['badge_level'] = $new_badge_level;
    $_SESSION['badge_level'] = $new_badge_level;
    sendBadgeUnlockedEmail($user_id, $user['full_name'], $user['email'], $new_badge_level, $badge_map[$new_badge_level]['name'], $badge_map[$new_badge_level]['emoji']);
} else {
    $new_badge_level = $old_badge_level;
    $user['badge_level'] = $old_badge_level;
    $_SESSION['badge_level'] = $old_badge_level;
}
$badge_emoji = $badge_map[$old_badge_level]['emoji'] ?? '—';
$badge_name = $badge_map[$old_badge_level]['name'] ?? 'Default';
$is_pro = (int)($user['is_pro'] ?? 0);
$pro_icon = $user['pro_badge_icon'] ?? 'fa-crown';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $max_size = 2 * 1024 * 1024;
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $profile_error = "Upload error.";
    } elseif (!in_array($file['type'], $allowed)) {
        $profile_error = "Only JPG, PNG, GIF, and WebP images are allowed.";
    } elseif ($file['size'] > $max_size) {
        $profile_error = "Profile photo too large. Maximum 2MB.";
    } else {
        $upload_dir = __DIR__ . '/uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe_name = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $dest = $upload_dir . $safe_name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            if (!empty($user['profile_pic']) && file_exists($upload_dir . $user['profile_pic'])) {
                unlink($upload_dir . $user['profile_pic']);
            }
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $safe_name, $user_id);
            if ($stmt->execute()) {
                $profile_message = "Profile photo updated!";
                $user['profile_pic'] = $safe_name;
            } else {
                $profile_error = "Failed to update profile photo.";
                unlink($dest);
            }
            $stmt->close();
        } else {
            $profile_error = "Failed to save profile photo.";
        }
    }
}

if (isset($_POST['toggle_dark_mode'])) {
    $new_dark_mode = $user['dark_mode'] ? 0 : 1;
    $stmt = $conn->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_dark_mode, $user_id);
    if ($stmt->execute()) {
        $user['dark_mode'] = $new_dark_mode;
        $_SESSION['dark_mode'] = $new_dark_mode;
        $message = "Dark mode " . ($new_dark_mode ? "enabled" : "disabled") . "!";
    } else {
        $error = "Failed to update dark mode.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!password_verify($current, $row['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $user_id);
        if ($update->execute()) {
            $message = "Password changed successfully!";
            echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('✅ Password changed!', 'success'); });</script>";
        } else {
            $error = "Failed to update password.";
        }
        $update->close();
    }
}

$user_images = [];
$stmt = $conn->prepare("SELECT id, filename, original_name, downloads, created_at FROM image_library WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $user_images[] = $row;
$stmt->close();

$profile_pic_path = !empty($user['profile_pic']) ? 'uploads/profiles/' . $user['profile_pic'] : 'data:image/svg+xml,' . urlencode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="#E2E8F0" rx="50"/><text x="50" y="50" text-anchor="middle" dy=".3em" font-size="40" font-family="Arial" fill="#64748B">'. substr($user['full_name'], 0, 1) .'</text></svg>');

$badge_level_theme = $user['badge_level'] ?? 0;
$badge_map_theme = [0=>'Default',1=>'First Download',2=>'Sprout',3=>'Wave',4=>'Blossom',5=>'Blaze',6=>'Pinnacle',7=>'Champion',8=>'Sage',9=>'Wizard',10=>'Royalty',11=>'Legend'];
$badge_name_theme = $badge_map_theme[$badge_level_theme] ?? 'Default';
$theme_class = 'theme-' . strtolower(str_replace(' ', '-', $badge_name_theme));
$dark_mode_class = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "My Profile - ImageLib";
    $page_description = "View and manage your ImageLib profile.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <style>
        .profile-wrapper { display:grid; grid-template-columns:1fr 1fr; gap:30px; }
        .profile-card { background:var(--bg-card,#FFFFFF); border-radius:24px; padding:30px; box-shadow:var(--shadow,0 2px 8px rgba(0,0,0,0.04)); border:1px solid var(--border-color,#EDF0F3); }
        .profile-card h3 { color:var(--text-primary,#1A2A3A); margin-bottom:20px; font-size:20px; border-bottom:2px solid var(--accent-color,#FF6B4A); padding-bottom:12px; display:inline-block; }
        .profile-header { text-align:center; padding:20px 0; }
        .profile-avatar { width:120px; height:120px; border-radius:50%; margin:0 auto 15px; object-fit:cover; border:4px solid var(--accent-color,#FF6B4A); background:#E2E8F0; overflow:hidden; }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; }
        .profile-badge-emoji { font-size:48px; display:block; margin-top:5px; }
        .profile-name { font-size:24px; font-weight:700; margin-top:10px; color:var(--text-primary,#1A2A3A); }
        .profile-username { color:var(--text-muted,#6A7A8A); font-size:14px; }
        .profile-badge-name { display:inline-block; padding:4px 16px; border-radius:30px; font-size:13px; font-weight:600; background:rgba(255,107,74,0.2); color:var(--accent-color,#FF6B4A); border:1px solid var(--accent-color,#FF6B4A); margin-top:8px; }
        .pro-badge-display { display:inline-block; background:linear-gradient(135deg,#FDE047,#FFD700); color:#0A0A14; padding:4px 14px; border-radius:30px; font-size:12px; font-weight:700; margin-left:8px; animation:glowPulse 2s infinite; }
        @keyframes glowPulse { 0%,100% { box-shadow:0 0 10px rgba(253,224,71,0.3); } 50% { box-shadow:0 0 25px rgba(253,224,71,0.6); } }
        .upload-photo-btn { background:var(--accent-color,#FF6B4A); color:#FFFFFF; border:none; padding:6px 16px; border-radius:30px; font-size:12px; font-weight:600; cursor:pointer; margin-top:8px; transition:var(--transition,0.2s); }
        .upload-photo-btn:hover { filter:brightness(0.85); transform:translateY(-2px); }
        .upload-photo-form { display:inline; }
        .upload-photo-form input[type="file"] { display:none; }
        .info-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border-color,#EDF0F3); }
        .info-label { font-weight:500; color:var(--text-muted,#6A7A8A); }
        .info-value { color:var(--text-primary,#1A2A3A); font-weight:500; }
        .user-images-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:15px; margin-top:15px; }
        .user-image-item { background:var(--bg-input,#F8FAFC); border-radius:12px; overflow:hidden; border:1px solid var(--border-color,#EDF0F3); transition:var(--transition,0.2s); }
        .user-image-item:hover { transform:translateY(-3px); border-color:var(--accent-color,#FF6B4A); }
        .user-image-item img { width:100%; height:100px; object-fit:cover; }
        .user-image-item .img-name { font-size:10px; padding:4px 8px; text-align:center; color:var(--text-muted,#6A7A8A); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .toggle-card { display:flex; align-items:center; gap:15px; padding:15px; background:var(--bg-input,#F8FAFC); border-radius:12px; cursor:pointer; transition:var(--transition,0.2s); border:1px solid var(--border-color,#EDF0F3); color:var(--text-primary,#1A2A3A); }
        .toggle-card:hover { background:var(--border-color,#EDF0F3); }
        .toggle-switch { width:44px; height:24px; background:#ccc; border-radius:30px; position:relative; transition:0.3s; flex-shrink:0; }
        .toggle-switch.active { background:var(--accent-color,#FF6B4A); }
        .toggle-switch::after { content:''; width:18px; height:18px; background:#fff; border-radius:50%; position:absolute; top:3px; left:3px; transition:0.3s; }
        .toggle-switch.active::after { left:23px; }
        @media (max-width:768px) { .profile-wrapper { grid-template-columns:1fr; gap:20px; } .profile-card { padding:20px; } }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>
<div class="container">
    <?php if($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($profile_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($profile_message) ?></div>
    <?php endif; ?>
    <?php if($profile_error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($profile_error) ?></div>
    <?php endif; ?>
    
    <div class="profile-wrapper">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?= $profile_pic_path ?>" alt="Profile Photo" id="profileAvatar">
                </div>
                <div style="display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:wrap;">
                    <span class="profile-badge-emoji"><?= $badge_emoji ?></span>
                    <span class="profile-badge-name"><?= $badge_name ?></span>
                    <?php if($is_pro): ?>
                        <span class="pro-badge-display"><i class="fas <?= $pro_icon ?>"></i> PRO</span>
                    <?php endif; ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
                <div style="margin-top:10px;">
                    <button class="upload-photo-btn" onclick="document.getElementById('photoUpload').click();">
                        <i class="fas fa-camera"></i> Change Photo
                    </button>
                    <form class="upload-photo-form" id="photoForm" method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_pic" id="photoUpload" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit();">
                    </form>
                </div>
            </div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
            <div class="info-row"><span class="info-label">Member Since</span><span class="info-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span></div>
            <div class="info-row"><span class="info-label">Last Login</span><span class="info-value"><?= $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'Never' ?></span></div>
            <div class="info-row"><span class="info-label">Images Uploaded</span><span class="info-value"><?= $uploadCount ?></span></div>
            <div class="info-row"><span class="info-label">Downloads Received</span><span class="info-value"><?= $downloadsReceived ?></span></div>
            <div class="info-row"><span class="info-label">Current Credits</span><span class="info-value"><?= $user['credits'] ?> <a href="credit_history.php" style="color:var(--accent-color,#FF6B4A); font-size:13px;">(history)</a></span></div>
            <div style="margin-top:20px;">
                <form method="POST">
                    <div class="toggle-card" onclick="this.querySelector('input[type=hidden]').form.submit();">
                        <span style="font-weight:500;">🌙 Dark Mode</span>
                        <div class="toggle-switch <?= $user['dark_mode'] ? 'active' : '' ?>" id="darkToggle"></div>
                        <input type="hidden" name="toggle_dark_mode" value="1">
                    </div>
                </form>
            </div>
        </div>
        <div class="profile-card">
            <h3><i class="fas fa-key"></i> Change Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label>New Password (min 6 characters)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
            </form>
            <hr style="border-color:var(--border-color,#EDF0F3); margin:25px 0;">
            <h3><i class="fas fa-images"></i> Your Recent Uploads</h3>
            <?php if(empty($user_images)): ?>
                <p style="color:var(--text-muted,#6A7A8A); font-size:13px;">You haven't uploaded any images yet. <a href="upload.php" style="color:var(--accent-color,#FF6B4A);">Upload now</a></p>
            <?php else: ?>
                <div class="user-images-grid">
                    <?php foreach($user_images as $img): ?>
                        <div class="user-image-item">
                            <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" alt="<?= htmlspecialchars($img['original_name']) ?>">
                            <div class="img-name"><?= htmlspecialchars(mb_strimwidth($img['original_name'],0,20,'...')) ?></div>
                            <div style="font-size:9px; text-align:center; color:var(--text-light,#94A3B8); padding-bottom:4px;"><?= $img['downloads'] ?> downloads</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if($uploadCount > 20): ?>
                    <p style="text-align:center; margin-top:10px;"><a href="gallery.php?user=<?= $user_id ?>" style="color:var(--accent-color,#FF6B4A); font-size:13px;">View all <?= $uploadCount ?> images →</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script>
document.querySelectorAll('.toggle-card').forEach(card => {
    card.addEventListener('click', function() {
        const form = this.querySelector('form');
        if (form) form.submit();
    });
});
document.getElementById('photoUpload')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.match('image.*')) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('profileAvatar').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
const userBadgeLevel = <?= $badge_level_theme ?? 0 ?>;
const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<script src="toast.js"></script>
</body>
</html>