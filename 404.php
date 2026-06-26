<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = "Page Not Found - ImageLib";
    $page_description = "The page you're looking for doesn't exist.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png"; // Update with your domain
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - ImageLib</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:#F0F2F5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .error-container{text-align:center;max-width:500px;}
        .error-code{font-size:120px;font-weight:800;color:#FF6B4A;margin-bottom:20px;line-height:1;}
        .error-title{font-size:28px;color:#1A2A3A;margin-bottom:15px;}
        .error-message{color:#6A7A8A;margin-bottom:30px;line-height:1.6;}
        .btn-home{background:#FF6B4A;color:white;padding:12px 32px;border-radius:40px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:10px;transition:0.2s;}
        .btn-home:hover{background:#E54A2E;transform:translateY(-2px);}
        .suggestions{display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin-top:30px;}
        .suggestions a{color:#6A7A8A;text-decoration:none;font-size:14px;transition:0.2s;}
        .suggestions a:hover{color:#FF6B4A;}
        hr{border-color:#EDF0F3;margin:30px 0;}
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-code">404</div>
    <h1 class="error-title">Page Not Found</h1>
    <p class="error-message">The page you are looking for doesn't exist or has been moved.</p>
    
    <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Return to Home</a>
    
    <hr>
    
    <div class="suggestions">
        <a href="gallery.php"><i class="fas fa-images"></i> Browse Gallery</a>
        <a href="upload.php"><i class="fas fa-upload"></i> Upload Images</a>
        <a href="blog.php"><i class="fas fa-blog"></i> Read Our Blog</a>
        <a href="faq.php"><i class="fas fa-question-circle"></i> Visit FAQ</a>
    </div>
</div>
</body>
</html>