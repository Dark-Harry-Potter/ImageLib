<?php
/**
 * header-meta.php – Centralized SEO Meta Tags + Google Analytics
 */

// ============================================
// SITE CONFIGURATION
// ============================================
$site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$current_url = $site_url . $_SERVER['REQUEST_URI'];

$page_title = $page_title ?? 'ImageLib – Free Responsive Images for Developers';
$page_description = $page_description ?? 'Free, responsive images for developers. Download, embed, and use high-quality images in your HTML/CSS projects.';
$page_keywords = $page_keywords ?? 'free images, responsive images, stock photos, developer tools, image library, web development';
$page_image = $page_image ?? $site_url . '/logo.png';
$page_type = $page_type ?? 'website';
$page_site_name = 'ImageLib';

// ============================================
// GOOGLE ANALYTICS (GA4)
// ============================================
$ga_measurement_id = 'G-4ZEYNK381J';

$track_analytics = true;
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
    $track_analytics = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ===== SEO META TAGS ===== -->
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= $current_url ?>">

    <!-- ===== OPEN GRAPH ===== -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:url" content="<?= $current_url ?>">
    <meta property="og:type" content="<?= $page_type ?>">
    <meta property="og:site_name" content="<?= $page_site_name ?>">
    <meta property="og:image" content="<?= $page_image ?>">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">

    <!-- ===== TWITTER CARDS ===== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="twitter:image" content="<?= $page_image ?>">

    <!-- ===== JSON-LD STRUCTURED DATA ===== -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "ImageLib",
        "url": "<?= $site_url ?>",
        "description": "Free, responsive images for developers.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?= $site_url ?>/gallery.php?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- ===== FAVICON ===== -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="apple-touch-icon" href="logo.png">

    <!-- ===== PWA ===== -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#FF6B4A">

    <!-- ===== GOOGLE ANALYTICS ===== -->
    <?php if ($track_analytics && !empty($ga_measurement_id)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga_measurement_id ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= $ga_measurement_id ?>');
    </script>
    <?php endif; ?>

    <!-- ===== GLOBAL ASSETS ===== -->
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    <script src="global.js" defer></script>
</head>