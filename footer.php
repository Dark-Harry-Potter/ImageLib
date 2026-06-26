<?php
// footer.php - Complete fixed version
?>
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 mb-4">
                <h4>
                    <img src="logo.png" alt="ImageLib" style="height:30px; margin-right:8px; vertical-align:middle;">
                    Image<span style="color:var(--accent-color,#FF6B4A);">Lib</span>
                </h4>
                <p>Free, responsive images for developers. Download, embed, and build faster.</p>
                <div style="display:flex; gap:12px; margin-top:15px;">
                    <a href="https://x.com/Uncensored_41" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/uncensored_41/" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://github.com/Dark-Harry-Potter" target="_blank"><i class="fab fa-github"></i></a>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-4">
                <h5>Quick Links</h5>
                <ul><li><a href="index.php">Home</a></li><li><a href="gallery.php">Gallery</a></li><li><a href="upload.php">Upload</a></li><li><a href="blog.php">Blog</a></li></ul>
            </div>
            <div class="col-md-2 col-sm-6 mb-4">
                <h5>Support</h5>
                <ul><li><a href="faq.php">FAQ</a></li><li><a href="form.php">Contact</a></li><li><a href="roadmap.php">Roadmap</a></li><li><a href="sitemap.php">Sitemap</a></li></ul>
            </div>
            <div class="col-md-2 col-sm-6 mb-4">
                <h5>Legal</h5>
                <ul><li><a href="terms.php">Terms</a></li><li><a href="privacy.php">Privacy</a></li><li><a href="disclaimer.php">Disclaimer</a></li></ul>
            </div>
            <div class="col-md-2 col-sm-6 mb-4">
                <h5>Newsletter</h5>
                <form method="POST" action="newsletter.php">
                    <input type="email" name="email" placeholder="Your email">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <p style="font-size:11px; margin-top:8px;">No spam, unsubscribe anytime.</p>
            </div>
        </div>
        <hr>
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <p>&copy; <?= date('Y') ?> ImageLib. All rights reserved.</p>
            <div style="display:flex; gap:15px;">
                <a href="privacy.php">Privacy</a>
                <a href="terms.php">Terms</a>
                <a href="sitemap.php">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<style>
footer { background:var(--bg-primary,#f1f5f9) !important; border-top:1px solid var(--border-color,#EDF0F3) !important; padding:60px 20px 30px !important; margin-top:40px !important; }
footer h4, footer h5 { color:var(--text-primary,#1A2A3A) !important; font-weight:700 !important; margin-bottom:15px !important; }
footer p { color:var(--text-muted,#4A5A6A) !important; font-size:14px !important; line-height:1.6 !important; }
footer ul { list-style:none !important; padding:0 !important; }
footer ul li { margin-bottom:8px !important; }
footer ul li a { color:var(--text-muted,#4A5A6A) !important; text-decoration:none !important; font-size:14px !important; transition:all 0.2s ease !important; }
footer ul li a:hover { color:var(--accent-color,#FF6B4A) !important; }
footer a { color:var(--text-muted,#4A5A6A) !important; text-decoration:none !important; transition:all 0.2s ease !important; }
footer a:hover { color:var(--accent-color,#FF6B4A) !important; }
footer .fa-twitter, footer .fa-instagram, footer .fa-github { color:var(--text-muted,#4A5A6A) !important; font-size:20px !important; transition:all 0.2s ease !important; }
footer .fa-twitter:hover, footer .fa-instagram:hover, footer .fa-github:hover { color:var(--accent-color,#FF6B4A) !important; }
footer hr { border-color:var(--border-color,#EDF0F3) !important; margin:30px 0 20px !important; }
footer form { display:flex !important; gap:8px !important; }
footer form input { flex:1 !important; padding:8px 12px !important; border-radius:30px !important; border:1px solid var(--border-color,#EDF0F3) !important; background:var(--bg-input,#F8FAFC) !important; color:#1A2A3A !important; font-size:13px !important; outline:none !important; }
footer form input:focus { border-color:var(--primary-color,#FF6B4A) !important; }
footer form button { padding:8px 16px !important; border-radius:30px !important; border:none !important; background:var(--primary-color,#FF6B4A) !important; color:#FFFFFF !important; font-weight:600 !important; font-size:13px !important; cursor:pointer !important; transition:all 0.2s ease !important; }
footer form button:hover { filter:brightness(0.85) !important; }
footer .container > div:last-child p { color:#4A5A6A !important; font-size:13px !important; }
footer .container > div:last-child a { color:#4A5A6A !important; font-size:12px !important; }
footer .container > div:last-child a:hover { color:var(--accent-color,#FF6B4A) !important; }

body.dark-mode footer { background:#0A0A0A !important; border-top-color:rgba(255,255,255,0.06) !important; }
body.dark-mode footer h4, body.dark-mode footer h5 { color:#FFFFFF !important; }
body.dark-mode footer p { color:#C0C0C0 !important; }
body.dark-mode footer ul li a, body.dark-mode footer a { color:#C0C0C0 !important; }
body.dark-mode footer ul li a:hover, body.dark-mode footer a:hover { color:var(--accent-color,#FF6B4A) !important; }
body.dark-mode footer .fa-twitter, body.dark-mode footer .fa-instagram, body.dark-mode footer .fa-github { color:#C0C0C0 !important; }
body.dark-mode footer .fa-twitter:hover, body.dark-mode footer .fa-instagram:hover, body.dark-mode footer .fa-github:hover { color:var(--accent-color,#FF6B4A) !important; }
body.dark-mode footer hr { border-color:rgba(255,255,255,0.06) !important; }
body.dark-mode footer form input { background:#2A2A2A !important; color:#FFFFFF !important; border-color:rgba(255,255,255,0.06) !important; }
body.dark-mode footer form input::placeholder { color:#888888 !important; }
body.dark-mode footer form button { background:var(--primary-color,#FF6B4A) !important; color:#FFFFFF !important; }
body.dark-mode footer form button:hover { filter:brightness(0.85) !important; }
body.dark-mode footer .container > div:last-child p { color:#C0C0C0 !important; }
body.dark-mode footer .container > div:last-child a { color:#C0C0C0 !important; }
body.dark-mode footer .container > div:last-child a:hover { color:var(--accent-color,#FF6B4A) !important; }

@media (max-width:768px) { footer { padding:40px 20px 20px !important; } footer .row { flex-direction:column !important; gap:30px !important; } footer form { flex-wrap:wrap !important; } footer form input { min-width:100% !important; } }
</style>