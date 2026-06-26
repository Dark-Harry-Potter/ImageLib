<?php
session_start();
// Force refresh credits from database
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/db_config.php';
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

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$transactions = [];

$stmt = $conn->prepare("SELECT amount, reason, created_at FROM credit_transactions WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

$credits = $_SESSION['user_credits'] ?? 0;

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
    $page_title = "Credit History - ImageLib";
    $page_description = "View your ImageLib credit transaction history.";
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
        .history-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .history-card h2 {
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 8px;
            font-size: 26px;
        }
        .history-card h2 i {
            color: var(--accent-color, #FF6B4A);
            margin-right: 10px;
        }
        .history-card > p {
            color: var(--text-muted, #6A7A8A);
            font-size: 14px;
        }
        
        .current-credits {
            background: var(--bg-input, #F8FAFC);
            border-radius: 16px;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .current-credits span {
            font-size: 14px;
            color: var(--text-muted, #6A7A8A);
        }
        .current-credits strong {
            font-size: 28px;
            color: var(--accent-color, #FF6B4A);
            display: block;
            margin-top: 5px;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            text-align: left;
            padding: 12px 8px;
            color: var(--text-muted, #6A7A8A);
            font-weight: 500;
            font-size: 13px;
            border-bottom: 2px solid var(--border-color, #EDF0F3);
        }
        .table td {
            padding: 12px 8px;
            border-bottom: 1px solid var(--border-color, #EDF0F3);
            font-size: 14px;
            color: var(--text-primary, #1A2A3A);
        }
        .credit-positive {
            color: #20B2AA;
            font-weight: 600;
        }
        .credit-negative {
            color: #EF4444;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted, #6A7A8A);
        }
        .empty-state i {
            font-size: 48px;
            color: var(--border-color, #D1D9E0);
            margin-bottom: 15px;
            display: block;
        }
        .empty-state h4 {
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 10px;
        }
        
        body.dark-mode .empty-state i {
            color: rgba(255,255,255,0.1);
        }
        
        @media (max-width: 550px) {
            .history-card {
                padding: 20px;
            }
            .table th, .table td {
                padding: 8px 5px;
                font-size: 12px;
            }
            .current-credits strong {
                font-size: 22px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="history-card">
        <h2><i class="fas fa-history"></i> Credit History</h2>
        <p>Track all your credit earnings and spendings</p>
        
        <div class="current-credits">
            <span><i class="fas fa-coins"></i> Current Balance</span>
            <strong><?= (int)$credits ?> credits</strong>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($transactions)): ?>
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <h4>No transactions yet</h4>
                                    <p style="font-size:13px; color:var(--text-muted, #6A7A8A);">
                                        Upload images or submit feedback to earn credits
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($transactions as $t): ?>
                            <tr>
                                <td class="<?= $t['amount'] >= 0 ? 'credit-positive' : 'credit-negative' ?>">
                                    <?= ($t['amount'] >= 0 ? '+' : '') . number_format($t['amount'], 0) ?>
                                </td>
                                <td><?= htmlspecialchars($t['reason']) ?></td>
                                <td style="color:var(--text-muted, #6A7A8A); font-size:13px;">
                                    <?= date('M d, Y H:i', strtotime($t['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <a href="gallery.php" class="btn btn-primary" style="display:inline-flex; margin-top:20px;">
            <i class="fas fa-arrow-left"></i> Back to Gallery
        </a>
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