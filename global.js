(function() {
    'use strict';

    const badgeThemes = {
        0: { name: 'Default', emoji: '—', className: 'theme-default' },
        1: { name: 'First Download', emoji: '🎯', className: 'theme-first-download' },
        2: { name: 'Sprout', emoji: '🌿', className: 'theme-sprout' },
        3: { name: 'Wave', emoji: '🌊', className: 'theme-wave' },
        4: { name: 'Blossom', emoji: '🌸', className: 'theme-blossom' },
        5: { name: 'Blaze', emoji: '🔥', className: 'theme-blaze' },
        6: { name: 'Pinnacle', emoji: '⭐', className: 'theme-pinnacle' },
        7: { name: 'Champion', emoji: '🏆', className: 'theme-champion' },
        8: { name: 'Sage', emoji: '🧠', className: 'theme-sage' },
        9: { name: 'Wizard', emoji: '🧙', className: 'theme-wizard' },
        10: { name: 'Royalty', emoji: '👑', className: 'theme-royalty' },
        11: { name: 'Legend', emoji: '⚡', className: 'theme-legend' }
    };

    function applyTheme(badgeLevel) {
        const theme = badgeThemes[badgeLevel] || badgeThemes[0];
        const body = document.body;
        Object.values(badgeThemes).forEach(t => body.classList.remove(t.className));
        body.classList.add(theme.className);
        try {
            localStorage.setItem('imagelib-theme', badgeLevel);
            localStorage.setItem('imagelib-theme-name', theme.name);
        } catch(e) {}
    }

    function initTheme() {
        let badgeLevel = 0;
        if (typeof userBadgeLevel !== 'undefined') {
            badgeLevel = userBadgeLevel;
        } else {
            try {
                const stored = localStorage.getItem('imagelib-theme');
                if (stored !== null) badgeLevel = parseInt(stored) || 0;
            } catch(e) {}
        }
        applyTheme(badgeLevel);
    }

    function toggleDarkMode() {
        const body = document.body;
        body.classList.toggle('dark-mode');
        try {
            localStorage.setItem('imagelib-darkmode', body.classList.contains('dark-mode') ? 'true' : 'false');
        } catch(e) {}
        updateDarkToggle();
    }

    function updateDarkToggle() {
        const toggles = document.querySelectorAll('.toggle-switch');
        const isDark = document.body.classList.contains('dark-mode');
        toggles.forEach(toggle => toggle.classList.toggle('active', isDark));
    }

    function initDarkMode() {
        let darkMode = false;
        try {
            darkMode = localStorage.getItem('imagelib-darkmode') === 'true';
        } catch(e) {}
        if (typeof darkModeSession !== 'undefined' && darkModeSession) darkMode = true;
        if (darkMode) document.body.classList.add('dark-mode');
        updateDarkToggle();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initTheme();
        initDarkMode();
        initFAQAccordion();
        initBackToTop();
        initHamburgerMenu();
        initCopyLink();
        initLightbox();
        initGalleryActions();
    });

    function initFAQAccordion() {
        document.querySelectorAll('.faq-question').forEach(q => {
            q.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                if (answer && answer.classList.contains('faq-answer')) {
                    answer.classList.toggle('show');
                    const icon = this.querySelector('i');
                    if (icon) icon.classList.toggle('fa-chevron-down');
                }
            });
        });
    }

    function initBackToTop() {
        const backToTop = document.getElementById('backToTop');
        if (!backToTop) return;
        window.addEventListener('scroll', function() {
            backToTop.classList.toggle('show', window.scrollY > 300);
        });
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    function initHamburgerMenu() {
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        if (!hamburger || !navMenu) return;
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
        document.querySelectorAll('.navbar-right a').forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('show');
            });
        });
    }

    function initCopyLink() {
        document.querySelectorAll('.copy-link-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url || window.location.href;
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof showToast !== 'undefined') showToast('✅ Link copied!', 'success');
                }).catch(() => {
                    const input = document.createElement('input');
                    input.value = url;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    if (typeof showToast !== 'undefined') showToast('✅ Link copied!', 'success');
                });
            });
        });
    }

    function initLightbox() {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        if (!lightbox || !lightboxImg) return;
        document.querySelectorAll('.preview-btn, .preview-trigger').forEach(el => {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.image-card');
                const imgId = card ? card.dataset.id : this.dataset.id;
                if (imgId) {
                    lightboxImg.src = 'preview.php?id=' + imgId;
                    lightbox.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });
        lightbox.addEventListener('click', function(e) {
            if (e.target === this) {
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
    }

    function initGalleryActions() {
        document.querySelectorAll('.download-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('💎 This will cost 2.5 credits. Proceed?')) {
                    window.location.href = 'download.php?id=' + this.dataset.id;
                }
            });
        });
        document.querySelectorAll('.embed-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const url = this.dataset.url;
                if (confirm('💎 This will cost 2.5 credits. Generate embed code?')) {
                    fetch('embed.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'image_id=' + id
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const html = `<img src="${url}" alt="Image from ImageLib" style="max-width:100%; height:auto; border-radius:8px;">`;
                            navigator.clipboard.writeText(html);
                            if (typeof showToast !== 'undefined') showToast('✅ Embed code copied!', 'success');
                        } else {
                            if (typeof showToast !== 'undefined') showToast('❌ ' + data.message, 'error');
                        }
                    })
                    .catch(() => {
                        if (typeof showToast !== 'undefined') showToast('❌ Error generating embed.', 'error');
                    });
                }
            });
        });
    }

    window.confirmDelete = function(id, name) {
        if (confirm(`⚠️ Delete "${name}"? This will cost 5 credits. Are you sure?`)) {
            const search = document.getElementById('searchInput')?.value || '';
            const sort = document.querySelector('.sort-btn.active')?.dataset?.sort || 'newest';
            window.location.href = `?delete=${id}&search=${encodeURIComponent(search)}&sort=${encodeURIComponent(sort)}`;
        }
    };

    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('dragstart', e => e.preventDefault());
    });

})();