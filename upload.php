<?php
session_start();
require_once 'db_config.php';
require_once 'rate_limit.php';
require_once 'csrf_token.php';

$ip = getClientIP();
if (!checkRateLimit($conn, $ip, 'FORM_ACTION', 10, 600)) {
    $error = "Too many attempts. Please wait 10 minutes.";
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin');
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $conn->prepare("SELECT is_pro, upload_limit FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_pro = (int)($user_data['is_pro'] ?? 0);
$upload_limit = (int)($user_data['upload_limit'] ?? 5);

$upload_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM image_library WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upload_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$effective_limit = $is_pro ? ($upload_limit * 2) : $upload_limit;
$remaining = max(0, $effective_limit - $upload_count);
$can_upload = $remaining > 0;

function compressImage($source, $destination, $quality = 80) {
    $image_info = getimagesize($source);
    $mime = $image_info['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            imagepng($image, $destination, 8);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            imagegif($image, $destination);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            imagewebp($image, $destination, $quality);
            break;
        default:
            return false;
    }
    imagedestroy($image);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } elseif (!$is_admin && !$can_upload) {
        $error = "Upload limit reached! You can upload up to " . $effective_limit . " images.";
    } else {
        $file = $_FILES['image'];
        $custom_name = trim($_POST['custom_name'] ?? '');
        $hashtags = trim($_POST['hashtags'] ?? '');
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size = 5 * 1024 * 1024;
        
        $hashtag_array = array_filter(explode(' ', $hashtags));
        $valid_hashtags = array_filter($hashtag_array, function($tag) {
            return str_starts_with($tag, '#') && strlen($tag) > 1;
        });
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Upload error. Please try again.";
        } elseif (!in_array($file['type'], $allowed)) {
            $error = "Only JPG, PNG, GIF, and WebP images are allowed.";
        } elseif ($file['size'] > $max_size) {
            $error = "File too large. Maximum size is 5MB.";
        } elseif (empty($custom_name)) {
            $error = "Please enter a name for your image.";
        } elseif (empty($hashtags) || count($valid_hashtags) < 5) {
            $error = "Please add at least 5 hashtags.";
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safe_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $temp_path = $upload_dir . 'temp_' . $safe_name;
            $dest = $upload_dir . $safe_name;
            
            if (move_uploaded_file($file['tmp_name'], $temp_path)) {
                $compress_quality = 75;
                $compressed = compressImage($temp_path, $dest, $compress_quality);
                if (!$compressed) {
                    rename($temp_path, $dest);
                } else {
                    unlink($temp_path);
                }
                
                $compressed_size = filesize($dest);
                $hashtag_string = implode(' ', array_unique($valid_hashtags));
                
                $stmt = $conn->prepare("INSERT INTO image_library (user_id, filename, original_name, file_path, file_size, mime_type, hashtags) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssiss", $user_id, $safe_name, $custom_name, $safe_name, $compressed_size, $file['type'], $hashtag_string);
                
                if ($stmt->execute()) {
                    $message = "Image uploaded and compressed successfully!";
                    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('✅ Image uploaded and compressed!', 'success'); });</script>";
                    $upload_count++;
                    $remaining = max(0, $effective_limit - $upload_count);
                    $can_upload = $remaining > 0;
                } else {
                    $error = "Database error. Please try again.";
                    unlink($dest);
                }
                $stmt->close();
            } else {
                $error = "Failed to save file. Check folder permissions.";
            }
        }
    }
}

$badge_level = $_SESSION['badge_level'] ?? 0;
$badge_map = [
    0 => 'Default', 1 => 'First Download', 2 => 'Sprout', 3 => 'Wave',
    4 => 'Blossom', 5 => 'Blaze', 6 => 'Pinnacle', 7 => 'Champion',
    8 => 'Sage', 9 => 'Wizard', 10 => 'Royalty', 11 => 'Legend'
];
$badge_name = $badge_map[$badge_level] ?? 'Default';
$theme_class = 'theme-' . strtolower(str_replace(' ', '-', $badge_name));
$dark_mode_class = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Upload Image - ImageLib";
    $page_description = "Upload your images to ImageLib.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <style>
        body { min-height:100vh; }
        .upload-card { background:var(--bg-card,#FFFFFF); border-radius:24px; padding:35px; box-shadow:var(--shadow,0 2px 8px rgba(0,0,0,0.04)); border:1px solid var(--border-color,#EDF0F3); max-width:700px; margin:0 auto; }
        .upload-card h2 { color:var(--text-primary,#1A2A3A); margin-bottom:8px; font-size:26px; }
        .upload-card h2 i { color:var(--accent-color,#FF6B4A); margin-right:10px; }
        .preview-area { text-align:center; margin-top:20px; padding:15px; background:var(--bg-input,#F8FAFC); border-radius:16px; display:none; border:1px solid var(--border-color,#EDF0F3); }
        .preview-area img { max-width:100%; max-height:200px; border-radius:12px; }
        .hashtag-hint { color:var(--text-muted,#6A7A8A); font-size:12px; margin-top:4px; }
        .hashtag-hint strong { color:var(--accent-color,#FF6B4A); }
        .upload-limit-info { background:rgba(45,156,219,0.08); border-left:4px solid #2D9CDB; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px; color:var(--text-primary,#1A2A3A); }
        .upload-limit-info strong { color:var(--accent-color,#FF6B4A); }
        .pro-badge-upload { display:inline-block; background:linear-gradient(135deg,#FDE047,#FFD700); color:#0A0A14; padding:2px 12px; border-radius:30px; font-size:12px; font-weight:700; animation:proGlow 2s infinite; margin-left:8px; }
        @keyframes proGlow { 0%,100% { box-shadow:0 0 5px rgba(253,224,71,0.3); } 50% { box-shadow:0 0 20px rgba(253,224,71,0.6); } }
        @media (max-width:600px) { .upload-card { padding:20px; } }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="upload-card">
        <h2><i class="fas fa-cloud-upload-alt"></i> Upload Image</h2>
        <?php if($is_admin): ?>
            <p class="text-muted" style="margin-bottom:25px; color:var(--accent-color,#FF6B4A);">👑 Admin mode: No limits</p>
        <?php else: ?>
            <p class="text-muted" style="margin-bottom:25px;">📸 Share your images with the community</p>
        <?php endif; ?>
        
        <?php if(!$is_admin): ?>
        <div class="upload-limit-info">
            <i class="fas fa-images"></i> 
            You have uploaded <strong><?= $upload_count ?></strong> out of <strong><?= $effective_limit ?></strong> allowed images.
            <?php if($is_pro): ?>
                <span class="pro-badge-upload"><i class="fas fa-crown"></i> PRO</span>
                <span style="display:block; font-size:12px; color:var(--text-muted,#6A7A8A); margin-top:4px;">⭐ Pro users get double the upload limit!</span>
            <?php endif; ?>
            <?php if($remaining > 0): ?>
                <br>You can upload <strong><?= $remaining ?></strong> more images.
            <?php else: ?>
                <br><strong style="color:#EF4444;">⚠️ Limit reached!</strong> 
                <?php if(!$is_pro): ?>
                    <span style="font-size:12px; color:var(--text-muted,#6A7A8A);">Upgrade to Pro for double the limit!</span>
                <?php else: ?>
                    <span style="font-size:12px; color:var(--text-muted,#6A7A8A);">Earn more downloads to increase your limit.</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <?= getCSRFField() ?>
            <div class="form-group">
                <label class="form-label">Image Name <span class="required">*</span></label>
                <input type="text" name="custom_name" class="form-control" placeholder="Enter a descriptive name for your image" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hashtags <span class="required">*</span></label>
                <input type="text" name="hashtags" id="hashtagsInput" class="form-control" placeholder="#nature #sunset #beach #ocean #vibrant" required>
                <div class="hashtag-hint"><i class="fas fa-info-circle"></i> Add at least <strong>5 hashtags</strong> (space separated)</div>
                <div id="hashtagCounter" style="font-size:12px; margin-top:5px; color:var(--text-muted,#6A7A8A);">
                    <span id="hashtagCount">0</span> / 5+ hashtags
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Select Image <span class="required">*</span></label>
                <input type="file" name="image" id="imageInput" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <div style="font-size:12px; color:var(--text-muted,#6A7A8A); margin-top:4px;">
                    <i class="fas fa-compress-alt"></i> Images will be automatically compressed for faster loading
                </div>
            </div>
            <div id="previewArea" class="preview-area">
                <p style="color:var(--text-muted,#6A7A8A); margin-bottom:10px;"><i class="fas fa-eye"></i> Preview:</p>
                <img id="imagePreview" src="#">
            </div>
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%;" <?= (!$is_admin && !$can_upload) ? 'disabled' : '' ?>>
                <i class="fas fa-cloud-upload-alt"></i> Upload Now
            </button>
            <a href="gallery.php" class="btn btn-secondary" style="width:100%; margin-top:10px; text-align:center;">← Back to Gallery</a>
        </form>
        <hr>
        <p class="text-muted" style="text-align:center;">Supported formats: JPG, PNG, GIF, WebP (max 5MB) • Auto-compressed</p>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hashtagsInput = document.getElementById('hashtagsInput');
    const hashtagCount = document.getElementById('hashtagCount');
    if (hashtagsInput && hashtagCount) {
        hashtagsInput.addEventListener('input', function() {
            const raw = this.value.trim();
            const tags = raw.split(/\s+/).filter(t => t.startsWith('#') && t.length > 1);
            const validCount = tags.length;
            hashtagCount.textContent = validCount;
            hashtagCount.style.color = validCount >= 5 ? '#20B2AA' : '#EF4444';
        });
    }
    const imageInput = document.getElementById('imageInput');
    const previewArea = document.getElementById('previewArea');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && previewArea && imagePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    previewArea.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewArea.style.display = 'none';
                imagePreview.src = '#';
            }
        });
    }
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('imageInput');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select an image to upload.');
                return;
            }
            const nameInput = document.querySelector('input[name="custom_name"]');
            if (!nameInput || nameInput.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a name for your image.');
                return;
            }
            if (hashtagsInput) {
                const raw = hashtagsInput.value.trim();
                const tags = raw.split(/\s+/).filter(t => t.startsWith('#') && t.length > 1);
                if (tags.length < 5) {
                    e.preventDefault();
                    alert('Please add at least 5 hashtags.');
                    return;
                }
            }
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading & Compressing...';
                submitBtn.disabled = true;
            }
        });
    }
});
const userBadgeLevel = <?= $badge_level ?? 0 ?>;
const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<script src="toast.js"></script>
</body>
</html>