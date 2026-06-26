<?php
session_start();
require_once 'db_config.php';

// ============================================
// AJAX DETECTION
// ============================================
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

// Handle image deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_SESSION['user_id'])) {
    $image_id = (int)$_GET['delete'];
    $current_user_id = $_SESSION['user_id'];
    $current_credits = (float)$_SESSION['user_credits'];
    
    $stmt = $conn->prepare("SELECT filename, user_id, original_name FROM image_library WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $uploader_id = $row['user_id'];
        $filename = $row['filename'];
        $original_name = $row['original_name'];
        
        if ($uploader_id == $current_user_id) {
            if ($current_credits >= 5) {
                $penalty = 5;
                $new_credits = $current_credits - $penalty;
                $conn->query("UPDATE users SET credits = $new_credits WHERE id = $current_user_id");
                $conn->query("INSERT INTO credit_transactions (user_id, amount, reason, reference_id) VALUES ($current_user_id, -$penalty, 'Deleted your image: $original_name', $image_id)");
                $_SESSION['user_credits'] = $new_credits;
                
                $filepath = __DIR__ . '/uploads/' . $filename;
                if (file_exists($filepath)) unlink($filepath);
                $delete = $conn->prepare("DELETE FROM image_library WHERE id = ?");
                $delete->bind_param("i", $image_id);
                $delete->execute();
                $delete->close();
            } else {
                $delete_error = "Insufficient credits. You need 5 credits to delete your own image.";
            }
        } else {
            $delete_error = "You can only delete your own images.";
        }
    } else {
        $delete_error = "Image not found.";
    }
    $stmt->close();
}

// Get search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$order_by = "created_at DESC";
switch ($sort) {
    case 'oldest': $order_by = "created_at ASC"; break;
    case 'downloads': $order_by = "downloads DESC"; break;
    case 'largest': $order_by = "file_size DESC"; break;
    case 'smallest': $order_by = "file_size ASC"; break;
    case 'alphabetical': $order_by = "original_name ASC"; break;
    default: $order_by = "created_at DESC";
}

$search_where = "";
$search_params = [];
$search_types = "";
if (!empty($search)) {
    $clean_search = ltrim($search, '#');
    $search_where = "WHERE hashtags LIKE ?";
    $search_params[] = "%" . $clean_search . "%";
    $search_types .= "s";
}

$count_sql = "SELECT COUNT(*) as total FROM image_library $search_where";
$count_stmt = $conn->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param($search_types, ...$search_params);
}
$count_stmt->execute();
$total_images = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$sql = "SELECT * FROM image_library $search_where ORDER BY $order_by LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$bind_types = $search_types . "ii";
$bind_params = array_merge($search_params, [$limit, $offset]);
if (!empty($search_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$images = [];
while($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

$uploader_names = [];
$uploader_pro_status = [];
foreach ($images as $img) {
    if (!isset($uploader_names[$img['user_id']])) {
        $stmt = $conn->prepare("SELECT full_name, is_pro FROM users WHERE id = ?");
        $stmt->bind_param("i", $img['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $uploader_names[$img['user_id']] = $row['full_name'];
            $uploader_pro_status[$img['user_id']] = (int)$row['is_pro'];
        }
        $stmt->close();
    }
}

// ============================================
// AJAX RESPONSE: ONLY IMAGE CARDS
// ============================================
if ($is_ajax) {
    foreach($images as $img) {
        $img_url = "uploads/" . htmlspecialchars($img['filename']);
        $is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']);
        $has_credits = (isset($_SESSION['user_credits']) && $_SESSION['user_credits'] >= 5);
        $uploader_name = $uploader_names[$img['user_id']] ?? 'A User';
        $is_uploader_pro = $uploader_pro_status[$img['user_id']] ?? 0;
        $pro_class = $is_uploader_pro ? 'pro-card' : '';
        ?>
        <div class="image-card <?= $pro_class ?>" data-id="<?= $img['id'] ?>" data-url="<?= $img_url ?>" data-name="<?= htmlspecialchars($img['original_name']) ?>">
            <div class="image-container preview-trigger" data-id="<?= $img['id'] ?>">
                <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($img['original_name']) ?>" loading="lazy">
                <span class="status-badge">
                    <?php if($is_owner): ?>
                        ⭐ Your Image
                    <?php elseif($img['downloads'] > 200): ?>
                        🔥 Trending
                    <?php elseif(strtotime($img['created_at']) > strtotime('-7 days')): ?>
                        🆕 New
                    <?php else: ?>
                        ✨ Fresh
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="image-title"><?= htmlspecialchars(mb_strimwidth($img['original_name'],0,45,'...')) ?></div>
                <div class="image-meta">
                    <span>📏 <?= round($img['file_size']/1024) ?> KB</span>
                    <span>📥 <?= $img['downloads'] ?> downloads</span>
                    <span>📅 <?= date('M d, Y', strtotime($img['created_at'])) ?></span>
                </div>
                <div class="owner-badge <?= $is_uploader_pro ? 'pro-owner-badge' : '' ?>">
                    👤 <?= htmlspecialchars($uploader_name) ?>
                    <?php if($is_uploader_pro): ?>
                        <i class="fas fa-crown" style="color:#FDE047; margin-left:4px;"></i>
                    <?php endif; ?>
                </div>
                
                <div class="button-group">
                    <button class="circular-btn preview-btn" data-id="<?= $img['id'] ?>">
                        <div class="icon-circle">👁️</div>
                        <span class="btn-label">Preview</span>
                    </button>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="circular-btn download-btn" data-id="<?= $img['id'] ?>">
                            <div class="icon-circle">📥</div>
                            <span class="btn-label">Download</span>
                        </button>
                        <button class="circular-btn embed-btn" data-id="<?= $img['id'] ?>" data-url="<?= $img_url ?>">
                            <div class="icon-circle">🔗</div>
                            <span class="btn-label">Embed</span>
                        </button>
                        <?php if($is_owner): ?>
                            <?php if($has_credits): ?>
                                <button class="circular-btn delete-btn" onclick="confirmDelete(<?= $img['id'] ?>, '<?= addslashes(htmlspecialchars($img['original_name'])) ?>')">
                                    <div class="icon-circle">🗑️</div>
                                    <span class="btn-label">Delete</span>
                                </button>
                            <?php else: ?>
                                <button class="circular-btn delete-btn" disabled style="opacity:0.5; cursor:not-allowed;">
                                    <div class="icon-circle">🗑️</div>
                                    <span class="btn-label">Need 5 credits</span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="circular-btn" disabled style="opacity:0.5; cursor:not-allowed;">
                            <div class="icon-circle">🔒</div>
                            <span class="btn-label">Login</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    exit;
}

// ============================================
// FULL PAGE
// ============================================

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
$pro_class = (isset($_SESSION['is_pro']) && $_SESSION['is_pro'] == 1) ? 'pro-theme' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Image Gallery - ImageLib";
    $page_description = "Browse and download thousands of responsive images for your HTML/CSS projects.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "logo.png";
    $page_type = "website";
    require_once 'header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .container-custom { max-width:1400px; margin:0 auto; padding:20px; }
        .page-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:25px; }
        .page-header h1 { font-size:28px; font-weight:700; color:var(--text-primary,#1A2A3A); }
        .page-header h1 i { color:var(--accent-color,#FF6B4A); margin-right:10px; }
        .stats-badge { background:var(--bg-card,#FFFFFF); padding:8px 18px; border-radius:30px; font-size:14px; border:1px solid var(--border-color,#EDF0F3); color:var(--text-primary,#1A2A3A); }
        .stats-badge i { color:var(--accent-color,#FF6B4A); margin-right:8px; }
        .search-sort-bar { background:var(--bg-card,#FFFFFF); border-radius:60px; padding:12px 25px; margin-bottom:30px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:15px; border:1px solid var(--border-color,#EDF0F3); }
        .search-wrapper { display:flex; flex:1; min-width:200px; gap:10px; align-items:center; position:relative; }
        .search-wrapper input { flex:1; border:none; padding:8px 12px; border-radius:30px; background:var(--bg-input,#F0F2F5); font-size:14px; outline:none; color:var(--text-primary,#1A2A3A); }
        .search-wrapper input:focus { background:var(--border-color,#E8EAED); }
        .search-wrapper button { background:transparent; border:none; color:var(--text-muted,#6A7A8A); cursor:pointer; font-size:18px; }
        .sort-wrapper { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .sort-label { font-weight:500; color:var(--text-muted,#6A7A8A); font-size:13px; }
        .sort-btn { background:var(--bg-input,#F0F2F5); color:var(--text-primary,#1A2A3A); border:none; padding:6px 16px; border-radius:30px; font-size:12px; text-decoration:none; transition:var(--transition,0.2s); cursor:pointer; }
        .sort-btn:hover, .sort-btn.active { background:var(--primary-color,#FF6B4A); color:#FFFFFF; }
        .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:25px; }
        .image-card { background:var(--bg-card,#FFFFFF); border-radius:20px; overflow:hidden; box-shadow:var(--shadow,0 2px 8px rgba(0,0,0,0.04)); transition:var(--transition,0.25s ease); border:1px solid var(--border-color,#EDF0F3); position:relative; }
        .image-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-hover,0 12px 24px rgba(0,0,0,0.08)); }
        .image-card.pro-card { border-color:rgba(253,224,71,0.3); }
        .image-card.pro-card:hover { box-shadow:0 0 30px rgba(253,224,71,0.12),0 0 60px rgba(253,224,71,0.05),var(--shadow-hover,0 12px 24px rgba(0,0,0,0.08)); }
        .image-container { position:relative; cursor:pointer; overflow:hidden; background:var(--bg-input,#F5F7FA); }
        .image-container img { width:100%; height:220px; object-fit:cover; transition:transform 0.3s ease; -webkit-user-select:none; user-select:none; }
        .image-container:hover img { transform:scale(1.03); }
        .status-badge { position:absolute; top:12px; left:12px; padding:4px 12px; border-radius:30px; font-size:11px; font-weight:600; background:var(--bg-card,#FFFFFF); box-shadow:0 2px 6px rgba(0,0,0,0.1); color:var(--text-primary,#1A2A3A); }
        .card-body { padding:16px; }
        .image-title { font-weight:600; font-size:15px; color:var(--text-primary,#1A2A3A); margin-bottom:4px; }
        .image-meta { font-size:12px; color:var(--text-muted,#6A7A8A); margin-bottom:12px; display:flex; gap:12px; flex-wrap:wrap; }
        .owner-badge { font-size:10px; background:rgba(32,178,170,0.1); color:#20B2AA; padding:2px 8px; border-radius:20px; display:inline-block; }
        .pro-owner-badge { background:linear-gradient(135deg,#FDE047,#FFD700); color:#0A0A14; font-weight:700; animation:proGlow 2s infinite; }
        @keyframes proGlow { 0%,100% { box-shadow:0 0 5px rgba(253,224,71,0.3); } 50% { box-shadow:0 0 15px rgba(253,224,71,0.6); } }
        .button-group { display:flex; justify-content:space-around; margin-top:12px; padding-top:12px; border-top:1px solid var(--border-color,#EDF0F3); }
        .circular-btn { display:flex; flex-direction:column; align-items:center; gap:6px; background:none; border:none; cursor:pointer; transition:var(--transition,0.2s); padding:5px; border-radius:12px; }
        .circular-btn:hover { transform:translateY(-2px); }
        .circular-btn .icon-circle { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; transition:var(--transition,0.2s); }
        .circular-btn .btn-label { font-size:10px; font-weight:500; color:var(--text-muted,#6A7A8A); }
        .preview-btn .icon-circle { background:var(--bg-input,#F0F2F5); color:var(--text-primary,#1A2A3A); }
        .preview-btn:hover .icon-circle { background:var(--border-color,#E0E4E9); }
        .download-btn .icon-circle { background:rgba(255,107,74,0.15); color:#FF6B4A; }
        .download-btn:hover .icon-circle { background:#FF6B4A; color:#FFFFFF; }
        .download-btn .btn-label { color:#FF6B4A; }
        .embed-btn .icon-circle { background:rgba(45,156,219,0.15); color:#2D9CDB; }
        .embed-btn:hover .icon-circle { background:#2D9CDB; color:#FFFFFF; }
        .embed-btn .btn-label { color:#2D9CDB; }
        .delete-btn .icon-circle { background:rgba(239,68,68,0.15); color:#EF4444; }
        .delete-btn:hover .icon-circle { background:#EF4444; color:#FFFFFF; }
        .delete-btn .btn-label { color:#EF4444; }
        .circular-btn:disabled { opacity:0.5; cursor:not-allowed; }
        .circular-btn:disabled:hover { transform:none; }
        .lightbox { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:9999; justify-content:center; align-items:center; cursor:pointer; }
        .lightbox.active { display:flex; flex-direction:column; }
        .lightbox-container { position:relative; max-width:90%; max-height:85%; display:flex; justify-content:center; align-items:center; }
        .lightbox-container img { max-width:100%; max-height:85vh; border-radius:12px; object-fit:contain; }
        .lightbox-close { position:absolute; top:25px; right:35px; color:#FFFFFF; font-size:40px; cursor:pointer; z-index:20; transition:var(--transition,0.2s); }
        .lightbox-close:hover { color:var(--accent-color,#FF6B4A); }
        .watermark-note { position:absolute; bottom:15px; left:0; right:0; text-align:center; color:rgba(255,255,255,0.4); font-size:11px; pointer-events:none; z-index:15; }
        #infinite-loader { text-align:center; padding:40px 0; display:none; grid-column:1/-1; }
        #infinite-loader .spinner { display:inline-block; width:40px; height:40px; border:4px solid var(--border-color,#EDF0F3); border-top-color:var(--primary-color,#FF6B4A); border-radius:50%; animation:spin 0.8s linear infinite; }
        #infinite-loader p { color:var(--text-muted,#6A7A8A); margin-top:10px; font-size:14px; }
        #end-message { text-align:center; padding:30px 0; color:var(--text-muted,#6A7A8A); font-size:14px; display:none; grid-column:1/-1; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .autocomplete-dropdown { position:absolute; top:100%; left:0; right:0; background:var(--bg-card,#FFFFFF); border:1px solid var(--border-color,#EDF0F3); border-radius:12px; box-shadow:var(--shadow-hover,0 12px 24px rgba(0,0,0,0.08)); z-index:1000; max-height:200px; overflow-y:auto; display:none; margin-top:4px; }
        .autocomplete-dropdown.show { display:block; }
        .autocomplete-item { padding:10px 16px; cursor:pointer; color:var(--text-primary,#1A2A3A); transition:all 0.2s ease; font-size:14px; }
        .autocomplete-item:hover { background:var(--bg-input,#F0F2F5); }
        .autocomplete-item .highlight { color:var(--accent-color,#FF6B4A); font-weight:600; }
        .autocomplete-item .tag-icon { margin-right:8px; opacity:0.6; }
        .no-results { padding:10px 16px; color:var(--text-muted,#6A7A8A); font-size:13px; }
        body.dark-mode .container-custom { background:transparent !important; }
        body.dark-mode .page-header h1 { color:var(--text-primary,#E8E8E8) !important; }
        body.dark-mode .stats-badge { background:var(--bg-card,#1A1A1A) !important; color:var(--text-primary,#E8E8E8) !important; border-color:rgba(255,255,255,0.06) !important; }
        body.dark-mode .search-sort-bar { background:var(--bg-card,#1A1A1A) !important; border-color:rgba(255,255,255,0.06) !important; }
        body.dark-mode .search-wrapper input { background:var(--bg-input,#2A2A2A) !important; color:var(--text-primary,#E8E8E8) !important; }
        body.dark-mode .sort-btn { background:var(--bg-input,#2A2A2A) !important; color:var(--text-primary,#E8E8E8) !important; }
        body.dark-mode .sort-btn:hover, body.dark-mode .sort-btn.active { background:var(--primary-color,#FF6B4A) !important; color:#FFFFFF !important; }
        body.dark-mode .image-card { background:var(--bg-card,#1A1A1A) !important; border-color:rgba(255,255,255,0.06) !important; }
        body.dark-mode .image-card .image-title { color:var(--text-primary,#E8E8E8) !important; }
        body.dark-mode .image-card .image-meta { color:var(--text-muted,#94A3B8) !important; }
        body.dark-mode .image-card .owner-badge { color:#20B2AA !important; }
        body.dark-mode .image-card .button-group { border-top-color:rgba(255,255,255,0.06) !important; }
        body.dark-mode .image-card .circular-btn .btn-label { color:var(--text-muted,#94A3B8) !important; }
        body.dark-mode .image-card .status-badge { background:var(--bg-card,#1A1A1A) !important; color:var(--text-primary,#E8E8E8) !important; }
        body.dark-mode .alert { background:var(--bg-card,#1A1A1A) !important; border-color:rgba(255,255,255,0.06) !important; }
        body.dark-mode .alert-success { background:rgba(32,178,170,0.15) !important; border-color:rgba(32,178,170,0.3) !important; color:#20B2AA !important; }
        body.dark-mode .alert-danger { background:rgba(239,68,68,0.15) !important; border-color:rgba(239,68,68,0.3) !important; color:#EF4444 !important; }
        body.dark-mode .alert-info { background:rgba(45,156,219,0.15) !important; border-color:rgba(45,156,219,0.3) !important; color:#2D9CDB !important; }
        @media (max-width:1000px) { .gallery-grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:640px) { .container-custom { padding:15px; } .gallery-grid { grid-template-columns:1fr; } .search-sort-bar { flex-direction:column; border-radius:30px; align-items:stretch; } .sort-wrapper { justify-content:center; } .button-group { gap:5px; } }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?> <?= $pro_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-custom">
    <div class="page-header">
        <h1><i class="fas fa-images"></i> Image Gallery</h1>
        <div class="stats-badge"><i class="fas fa-chart-simple"></i> <span id="totalImages"><?= $total_images ?></span> images available</div>
    </div>
    
    <div class="search-sort-bar">
        <div class="search-wrapper">
            <i class="fas fa-search" style="color:var(--text-muted,#6A7A8A);"></i>
            <input type="text" id="searchInput" placeholder="Search by hashtag (e.g., #ocean)" value="<?= htmlspecialchars($search) ?>">
            <button id="clearSearch" style="display:<?= !empty($search) ? 'block' : 'none' ?>;"><i class="fas fa-times"></i></button>
            <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
        </div>
        <div class="sort-wrapper">
            <span class="sort-label"><i class="fas fa-arrow-down-wide-short"></i> Sort:</span>
            <a href="#" class="sort-btn <?= $sort=='newest'?'active':'' ?>" data-sort="newest">Newest</a>
            <a href="#" class="sort-btn <?= $sort=='oldest'?'active':'' ?>" data-sort="oldest">Oldest</a>
            <a href="#" class="sort-btn <?= $sort=='downloads'?'active':'' ?>" data-sort="downloads">Most Downloaded</a>
            <a href="#" class="sort-btn <?= $sort=='largest'?'active':'' ?>" data-sort="largest">Largest</a>
            <a href="#" class="sort-btn <?= $sort=='smallest'?'active':'' ?>" data-sort="smallest">Smallest</a>
            <a href="#" class="sort-btn <?= $sort=='alphabetical'?'active':'' ?>" data-sort="alphabetical">A-Z</a>
        </div>
    </div>
    
    <?php if(isset($delete_success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($delete_success) ?></div>
    <?php elseif(isset($delete_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($delete_error) ?></div>
    <?php endif; ?>
    
    <div class="gallery-grid" id="galleryGrid">
        <?php if(empty($images)): ?>
            <div class="alert alert-info" style="grid-column:1/-1; text-align:center; padding:60px;">
                <i class="fas fa-search" style="font-size:48px; margin-bottom:15px; display:block; color:var(--text-muted,#6A7A8A);"></i>
                <h3 style="color:var(--text-primary,#1A2A3A);">No images found</h3>
                <p style="color:var(--text-muted,#6A7A8A);">Try a different search term or <a href="upload.php" style="color:var(--accent-color,#FF6B4A);">upload an image</a>!</p>
            </div>
        <?php else: ?>
            <?php foreach($images as $img): 
                $img_url = "uploads/" . htmlspecialchars($img['filename']);
                $is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']);
                $has_credits = (isset($_SESSION['user_credits']) && $_SESSION['user_credits'] >= 5);
                $uploader_name = $uploader_names[$img['user_id']] ?? 'A User';
                $is_uploader_pro = $uploader_pro_status[$img['user_id']] ?? 0;
                $pro_class = $is_uploader_pro ? 'pro-card' : '';
            ?>
            <div class="image-card <?= $pro_class ?>" data-id="<?= $img['id'] ?>" data-url="<?= $img_url ?>" data-name="<?= htmlspecialchars($img['original_name']) ?>">
                <div class="image-container preview-trigger" data-id="<?= $img['id'] ?>">
                    <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($img['original_name']) ?>" loading="lazy">
                    <span class="status-badge">
                        <?php if($is_owner): ?>
                            ⭐ Your Image
                        <?php elseif($img['downloads'] > 200): ?>
                            🔥 Trending
                        <?php elseif(strtotime($img['created_at']) > strtotime('-7 days')): ?>
                            🆕 New
                        <?php else: ?>
                            ✨ Fresh
                        <?php endif; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="image-title"><?= htmlspecialchars(mb_strimwidth($img['original_name'],0,45,'...')) ?></div>
                    <div class="image-meta">
                        <span>📏 <?= round($img['file_size']/1024) ?> KB</span>
                        <span>📥 <?= $img['downloads'] ?> downloads</span>
                        <span>📅 <?= date('M d, Y', strtotime($img['created_at'])) ?></span>
                    </div>
                    <div class="owner-badge <?= $is_uploader_pro ? 'pro-owner-badge' : '' ?>">
                        👤 <?= htmlspecialchars($uploader_name) ?>
                        <?php if($is_uploader_pro): ?>
                            <i class="fas fa-crown" style="color:#FDE047; margin-left:4px;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="button-group">
                        <button class="circular-btn preview-btn" data-id="<?= $img['id'] ?>">
                            <div class="icon-circle">👁️</div>
                            <span class="btn-label">Preview</span>
                        </button>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <button class="circular-btn download-btn" data-id="<?= $img['id'] ?>">
                                <div class="icon-circle">📥</div>
                                <span class="btn-label">Download</span>
                            </button>
                            <button class="circular-btn embed-btn" data-id="<?= $img['id'] ?>" data-url="<?= $img_url ?>">
                                <div class="icon-circle">🔗</div>
                                <span class="btn-label">Embed</span>
                            </button>
                            <?php if($is_owner): ?>
                                <?php if($has_credits): ?>
                                    <button class="circular-btn delete-btn" onclick="confirmDelete(<?= $img['id'] ?>, '<?= addslashes(htmlspecialchars($img['original_name'])) ?>')">
                                        <div class="icon-circle">🗑️</div>
                                        <span class="btn-label">Delete</span>
                                    </button>
                                <?php else: ?>
                                    <button class="circular-btn delete-btn" disabled style="opacity:0.5; cursor:not-allowed;">
                                        <div class="icon-circle">🗑️</div>
                                        <span class="btn-label">Need 5 credits</span>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="circular-btn" disabled style="opacity:0.5; cursor:not-allowed;">
                                <div class="icon-circle">🔒</div>
                                <span class="btn-label">Login</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Infinite Scroll Loader -->
        <div id="infinite-loader">
            <div class="spinner"></div>
            <p>Loading more images...</p>
        </div>
        <div id="end-message">
            <i class="fas fa-check-circle" style="color:#20B2AA; font-size:24px; display:block; margin-bottom:10px;"></i>
            You've reached the end of the gallery! 🎉
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close">&times;</span>
    <div class="lightbox-container">
        <img id="lightboxImg" src="">
    </div>
    <div class="watermark-note">🔒 Watermarked preview — download for unmarked original</div>
</div>

<?php include 'footer.php'; ?>

<script>
    const userBadgeLevel = <?= $badge_level ?? 0 ?>;
    const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // LIGHTBOX PREVIEW - EVENT DELEGATION
    // Works for ALL cards (including infinite scroll)
    // ============================================

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');

    // Attach listener to the parent grid (delegation)
    document.getElementById('galleryGrid').addEventListener('click', function(e) {
        // Find if the clicked element or its parent is a preview trigger
        const target = e.target.closest('.preview-btn') || e.target.closest('.preview-trigger');
        
        if (target) {
            e.stopPropagation();
            const card = target.closest('.image-card');
            const imgId = card ? card.dataset.id : target.dataset.id;
            
            if (imgId) {
                lightboxImg.src = 'preview.php?id=' + imgId;
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                const url = target.dataset.url || target.querySelector('img')?.src;
                if (url) {
                    lightboxImg.src = url;
                    lightbox.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }
        }
    });

    // Close lightbox
    document.querySelector('.lightbox-close').addEventListener('click', function() {
        lightbox.classList.remove('active');
        lightboxImg.src = '';
        document.body.style.overflow = '';
    });

    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            lightbox.classList.remove('active');
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }
    });
    
    // ============================================
    // SEARCH + SORT
    // ============================================
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const autocompleteDropdown = document.getElementById('autocompleteDropdown');
    var autocompleteTimeout = null;

    function updateGalleryUrl() {
        var search = searchInput?.value?.trim() || '';
        var sort = document.querySelector('.sort-btn.active')?.dataset?.sort || 'newest';
        var url = 'gallery.php';
        var params = [];
        if (search) params.push('search=' + encodeURIComponent(search));
        if (sort && sort !== 'newest') params.push('sort=' + encodeURIComponent(sort));
        if (params.length) url += '?' + params.join('&');
        window.location.href = url;
    }

    // Autocomplete
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var term = this.value.trim();
            
            if (autocompleteTimeout) {
                clearTimeout(autocompleteTimeout);
            }
            
            if (term.length < 2) {
                autocompleteDropdown.classList.remove('show');
                return;
            }
            
            autocompleteTimeout = setTimeout(function() {
                fetch('search_autocomplete.php?term=' + encodeURIComponent(term))
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        autocompleteDropdown.innerHTML = '';
                        
                        if (data.length === 0) {
                            autocompleteDropdown.innerHTML = '<div class="no-results">No hashtags found</div>';
                            autocompleteDropdown.classList.add('show');
                            return;
                        }
                        
                        data.forEach(function(tag) {
                            var item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            
                            var index = tag.toLowerCase().indexOf(term.toLowerCase());
                            var before = tag.substring(0, index);
                            var match = tag.substring(index, index + term.length);
                            var after = tag.substring(index + term.length);
                            
                            item.innerHTML = '<span class="tag-icon">#</span>' + 
                                before + 
                                '<span class="highlight">' + match + '</span>' + 
                                after;
                            
                            item.addEventListener('click', function() {
                                searchInput.value = tag;
                                autocompleteDropdown.classList.remove('show');
                                var event = new KeyboardEvent('keydown', { key: 'Enter' });
                                searchInput.dispatchEvent(event);
                            });
                            
                            autocompleteDropdown.appendChild(item);
                        });
                        
                        autocompleteDropdown.classList.add('show');
                    })
                    .catch(function() {
                        autocompleteDropdown.classList.remove('show');
                    });
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                autocompleteDropdown.classList.remove('show');
            }
            if (e.key === 'Escape') {
                autocompleteDropdown.classList.remove('show');
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
                autocompleteDropdown.classList.remove('show');
            }
        });
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            updateGalleryUrl();
        });
    }
    
    document.querySelectorAll('.sort-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sort-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            updateGalleryUrl();
        });
    });
    
    // Delete confirmation
    window.confirmDelete = function(id, name) {
        if (confirm('⚠️ Delete "' + name + '"? This will cost you 5 credits.\n\nThis action cannot be undone.\n\nAre you sure?')) {
            window.location.href = '?delete=' + id + '&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>';
        }
    };

    // ============================================
    // INFINITE SCROLL
    // ============================================
    var pageNum = 2;
    var loading = false;
    var noMore = false;
    var total = <?= $total_images ?>;
    var loaded = document.querySelectorAll('.image-card').length;

    if (loaded >= total) {
        noMore = true;
        document.getElementById('end-message').style.display = 'block';
    }

    function loadNextPage() {
        if (loading || noMore) return;
        
        loading = true;
        document.getElementById('infinite-loader').style.display = 'block';
        
        var search = document.getElementById('searchInput')?.value || '';
        var sort = document.querySelector('.sort-btn.active')?.dataset?.sort || 'newest';
        
        fetch('gallery.php?page=' + pageNum + '&limit=12&search=' + encodeURIComponent(search) + '&sort=' + encodeURIComponent(sort) + '&ajax=1')
            .then(function(response) { return response.text(); })
            .then(function(html) {
                document.getElementById('infinite-loader').style.display = 'none';
                
                if (html.trim() === '' || html.includes('No images found')) {
                    noMore = true;
                    document.getElementById('end-message').style.display = 'block';
                    loading = false;
                    return;
                }
                
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var cards = temp.querySelectorAll('.image-card');
                
                if (cards.length === 0) {
                    noMore = true;
                    document.getElementById('end-message').style.display = 'block';
                    loading = false;
                    return;
                }
                
                var grid = document.getElementById('galleryGrid');
                cards.forEach(function(card) {
                    grid.insertBefore(card, document.getElementById('infinite-loader'));
                });
                
                pageNum++;
                loading = false;
                
                var newLoaded = document.querySelectorAll('.image-card').length;
                if (newLoaded >= total) {
                    noMore = true;
                    document.getElementById('end-message').style.display = 'block';
                }
            })
            .catch(function() {
                document.getElementById('infinite-loader').style.display = 'none';
                loading = false;
            });
    }

    // Scroll listener
    window.addEventListener('scroll', function() {
        var scrollY = window.scrollY;
        var windowHeight = window.innerHeight;
        var documentHeight = document.documentElement.scrollHeight;
        
        if (scrollY + windowHeight >= documentHeight - 300) {
            loadNextPage();
        }
    });
});
</script>
</body>
</html>