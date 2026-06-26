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
    $page_title = "FAQ - ImageLib";
    $page_description = "Frequently asked questions about ImageLib, badges, pro features, and upload limits.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools, faq, pro badges";
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
        .faq-card {
            background: var(--bg-card, #FFFFFF);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        .faq-card h1 {
            color: var(--accent-color, #FF6B4A);
            margin-bottom: 10px;
            font-size: 32px;
        }
        .faq-card h1 i {
            margin-right: 12px;
        }
        .faq-card > p {
            color: var(--text-muted, #6A7A8A);
            margin-bottom: 30px;
        }
        .faq-category {
            color: var(--accent-color, #FF6B4A);
            font-size: 20px;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color, #EDF0F3);
            font-weight: 600;
        }
        .faq-category:first-of-type {
            margin-top: 0;
        }
        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color, #EDF0F3);
            padding-bottom: 15px;
        }
        .faq-item:last-child {
            border-bottom: none;
        }
        .faq-question {
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-primary, #1A2A3A);
            font-size: 17px;
            user-select: none;
            transition: var(--transition, 0.2s);
            padding: 5px 0;
        }
        .faq-question:hover {
            color: var(--accent-color, #FF6B4A);
        }
        .faq-question i {
            transition: var(--transition, 0.2s);
            color: var(--accent-color, #FF6B4A);
            font-size: 14px;
        }
        .faq-answer {
            color: var(--text-muted, #6A7A8A);
            margin-top: 12px;
            line-height: 1.6;
            display: none;
            padding-left: 15px;
            border-left: 3px solid var(--accent-color, #FF6B4A);
        }
        .faq-answer.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .faq-answer .pro-tag {
            display: inline-block;
            background: linear-gradient(135deg, #FDE047, #FFD700);
            color: #0A0A14;
            padding: 1px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 6px;
        }
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--accent-color, #FF6B4A);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            opacity: 0;
            transition: var(--transition, 0.2s);
            z-index: 100;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 107, 74, 0.3);
        }
        .back-to-top.show {
            opacity: 1;
        }
        .back-to-top:hover {
            filter: brightness(0.85);
            transform: translateY(-3px);
        }
        @media (max-width: 600px) {
            .faq-card {
                padding: 25px;
            }
            .faq-question {
                font-size: 15px;
            }
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 38px;
                height: 38px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">
<?php include 'navbar.php'; ?>

<div class="container-sm">
    <div class="faq-card">
        <h1><i class="fas fa-question-circle"></i> Frequently Asked Questions</h1>
        <p>Find answers to common questions about ImageLib.</p>
        
        <div class="faq-category">Getting Started</div>
        <div class="faq-item">
            <div class="faq-question">What is ImageLib? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">ImageLib is a free platform offering responsive, high-quality images for developers. You can browse, download, embed, and upload images. Earn downloads to unlock higher upload limits and better badges.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do I create an account? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Click "Sign Up" in the navigation bar. Fill in your name, email, and password. After registration, you'll receive 100 free credits.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Is ImageLib really free? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Yes! Browse and view all images for free. You get 100 free credits on signup. Once used, you can purchase more credits via social media.</div>
        </div>
        
        <div class="faq-category">Credits & Purchases</div>
        <div class="faq-item">
            <div class="faq-question">How do I get credits? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">You receive 100 free credits on signup. Once used, contact us via social media to purchase credit packs.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What are the credit packs? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer"><strong>Starter:</strong> $5 — 100 credits<br><strong>Pro:</strong> $15 — 350 credits<br><strong>Premium:</strong> $30 — 800 credits<br><strong>Enterprise:</strong> $50 — 1500 credits</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do I purchase credits? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">DM us on Twitter/X, Instagram, GitHub, Bluesky, or Reddit. We'll add credits to your account manually.</div>
        </div>
        
        <div class="faq-category">Pro Features</div>
        <div class="faq-item">
            <div class="faq-question">What is a Pro badge? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">A Pro badge is a premium status that gives you exclusive benefits. Contact us via social media to upgrade.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What are the Pro benefits? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer"><strong>✅ Double upload limit</strong> — Upload twice as many images<br><strong>✅ Reduced credit cost</strong> — Only 1 credit per download/embed (vs 2.5)<br><strong>✅ Glowing Pro badge</strong> — Visible in navbar and profile<br><strong>✅ Premium UI animations</strong> — Glitter buttons, gold card glow</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do I become a Pro user? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Contact us via social media to purchase a credit pack. Pro status is included with any purchase.</div>
        </div>
        
        <div class="faq-category">Uploads & Limits</div>
        <div class="faq-item">
            <div class="faq-question">How many images can I upload? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Your upload limit grows based on downloads your images receive. Standard users start with 5 images. Pro users get double the limit.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do I increase my upload limit? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Earn more downloads on your images. Each threshold unlocks more uploads. Pro users get double the limit.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What file types are supported? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">We support JPG, PNG, GIF, and WebP images up to 5MB per file.</div>
        </div>
        
        <div class="faq-category">Badges</div>
        <div class="faq-item">
            <div class="faq-question">What are badges? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Badges are achievements earned by receiving downloads on your uploaded images. There are 12 levels from Default to Legend.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do I earn a badge? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Upload images and earn downloads. Each badge requires a certain number of total downloads.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Where can I track my badge progress? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Click on your badge emoji in the navbar to open the Badge Tracker. You'll see your progress and the next badge.</div>
        </div>
        
        <div class="faq-category">Account</div>
        <div class="faq-item">
            <div class="faq-question">How do I change my password? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Go to your Profile page, then the "Change Password" section. Enter your current password and your new password.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">I forgot my password. How do I reset it? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Click "Forgot Password?" on the login page. Enter your email and you will receive a reset link.</div>
        </div>
        
        <div class="faq-category">Technical</div>
        <div class="faq-item">
            <div class="faq-question">Is ImageLib mobile responsive? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Yes! ImageLib is fully responsive and works seamlessly on all devices – desktops, tablets, and smartphones.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Do I need to credit ImageLib when using images? <i class="fas fa-chevron-down"></i></div>
            <div class="faq-answer">Attribution is appreciated but not required. You can use images without crediting ImageLib.</div>
        </div>
    </div>
</div>

<a href="#" id="backToTop" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            if (answer && answer.classList.contains('faq-answer')) {
                answer.classList.toggle('show');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            }
        });
    });
    
    // Back to Top
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

const userBadgeLevel = <?= $badge_level ?? 0 ?>;
const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
</body>
</html>