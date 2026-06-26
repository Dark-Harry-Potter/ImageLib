<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php';
require_once 'captcha_config.php';
require_once 'csrf_token.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin');

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
    $page_title = "Feedback - ImageLib";
    $page_description = "Submit feedback and earn credits on ImageLib.";
    $page_keywords = "image gallery, responsive images, free stock photos, developer tools";
    $page_image = "https://imagelib.lovestoblog.com/logo.png";
    $page_type = "website";
    require_once __DIR__ . '/header-meta.php';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="global.css">
    <script src="toast.js" defer></script>
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--bg-primary, #F0F2F5);
        }
        
        .floating-container {
            background: var(--bg-card, #FFFFFF);
            border-radius: 20px;
            box-shadow: var(--shadow, 0 2px 8px rgba(0,0,0,0.04));
            padding: 30px;
            max-width: 500px;
            width: 100%;
            border: 1px solid var(--border-color, #EDF0F3);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-header h1 {
            color: var(--text-primary, #1A2A3A);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .form-header p {
            color: var(--text-muted, #94A3B8);
            font-size: 12px;
        }
        
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color, #EDF0F3);
        }
        .user-greeting {
            color: #14B8A6;
            font-weight: 600;
            font-size: 13px;
        }
        .logout-link {
            background: #EF4444;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }
        .logout-link:hover {
            background: #DC2626;
            color: white;
        }
        
        .credit-note {
            background: var(--bg-input, #F8FAFC);
            padding: 10px;
            border-radius: 12px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
            color: #14B8A6;
            border-left: 4px solid #14B8A6;
        }
        .admin-view-note {
            background: rgba(255, 107, 74, 0.2);
            padding: 10px;
            border-radius: 12px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
            color: var(--accent-color, #FF6B4A);
            border-left: 4px solid var(--accent-color, #FF6B4A);
        }
        
        .g-recaptcha {
            margin: 15px 0;
            display: flex;
            justify-content: center;
        }
        
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .success-modal.show {
            display: flex;
        }
        .success-content {
            background: var(--bg-card, #FFFFFF);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .success-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: #14B8A6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: white;
        }
        .success-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary, #1A2A3A);
            margin-bottom: 8px;
        }
        .success-message {
            color: var(--text-muted, #94A3B8);
            font-size: 13px;
            margin-bottom: 20px;
        }
        .success-details {
            background: var(--bg-input, #F8FAFC);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
            max-height: 250px;
            overflow-y: auto;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid var(--border-color, #EDF0F3);
            font-size: 12px;
            color: var(--text-muted, #94A3B8);
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: var(--text-primary, #1A2A3A);
        }
        .detail-value {
            color: #14B8A6;
            word-break: break-all;
        }
        .close-btn {
            background: #14B8A6;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .close-btn:hover {
            filter: brightness(0.9);
        }
        
        @media (max-width: 550px) {
            .floating-container {
                padding: 20px;
            }
            .user-info {
                flex-direction: column;
                align-items: flex-end;
            }
        }
    </style>
</head>
<body class="<?= $dark_mode_class ?> <?= $theme_class ?>">

<div class="floating-container">
    <div class="user-info">
        <span class="user-greeting"><i class="fas fa-user-circle"></i> Welcome, <?= $is_admin ? 'Admin' : htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <?php if($is_admin): ?>
        <div class="admin-view-note">
            <i class="fas fa-eye"></i> <strong>Admin View Mode</strong> — You can see the form but cannot submit it.
        </div>
    <?php else: ?>
        <div class="credit-note">
            <i class="fas fa-gem"></i> <strong>+50 Credits!</strong> Submit this feedback form once per month and earn 50 credits.
        </div>
    <?php endif; ?>
    
    <div class="form-header">
        <h1>Contact Form</h1>
        <p>Please fill in all the required fields below</p>
    </div>

    <form id="contactForm" novalidate>
        <?= getCSRFField() ?>
        <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" <?= $is_admin ? 'disabled' : '' ?>>
            <div class="error-message" id="nameError">Full Name must be at least 3 characters long</div>
        </div>
        <div class="form-group">
            <label class="form-label">Mobile Number <span class="required">*</span></label>
            <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Enter 10-digit mobile number" maxlength="10" <?= $is_admin ? 'disabled' : '' ?>>
            <div class="error-message" id="mobileError">Mobile Number must be exactly 10 digits</div>
        </div>
        <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" <?= $is_admin ? 'disabled' : '' ?>>
            <div class="error-message" id="emailError">Please enter a valid email address</div>
        </div>
        <div class="form-group">
            <label class="form-label">Address <span class="required">*</span></label>
            <input type="text" class="form-control" id="address" name="address" placeholder="Enter your complete address" <?= $is_admin ? 'disabled' : '' ?>>
            <div class="error-message" id="addressError">Address must be at least 10 characters long</div>
        </div>
        <div class="form-group">
            <label class="form-label">Remarks</label>
            <input type="text" class="form-control" id="remark" name="remark" placeholder="Optional remarks" <?= $is_admin ? 'disabled' : '' ?>>
            <div class="error-message" id="remarkError">Remarks must be empty or at least 5 characters</div>
        </div>
        
        <?php if(!$is_admin): ?>
            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary" id="submitBtn" <?= $is_admin ? 'disabled' : '' ?> style="width:100%;">
            <i class="fas fa-paper-plane"></i> <?= $is_admin ? 'Admin View Only' : 'Submit Form' ?>
        </button>
    </form>
</div>

<?php if(!$is_admin): ?>
<div class="success-modal" id="successModal">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <div class="success-title">Submission Successful!</div>
        <p class="success-message">Your feedback has been recorded.</p>
        <div class="success-details">
            <div class="detail-item"><span class="detail-label">Name:</span><span class="detail-value" id="displayName"></span></div>
            <div class="detail-item"><span class="detail-label">Mobile:</span><span class="detail-value" id="displayMobile"></span></div>
            <div class="detail-item"><span class="detail-label">Email:</span><span class="detail-value" id="displayEmail"></span></div>
            <div class="detail-item"><span class="detail-label">Address:</span><span class="detail-value" id="displayAddress"></span></div>
            <div class="detail-item"><span class="detail-label">Remarks:</span><span class="detail-value" id="displayRemark"></span></div>
        </div>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>
</div>

<script>
const form = document.getElementById('contactForm');
const nameInput = document.getElementById('name');
const mobileInput = document.getElementById('mobile');
const emailInput = document.getElementById('email');
const addressInput = document.getElementById('address');
const remarkInput = document.getElementById('remark');
const successModal = document.getElementById('successModal');
const submitBtn = document.getElementById('submitBtn');

const nameError = document.getElementById('nameError');
const mobileError = document.getElementById('mobileError');
const emailError = document.getElementById('emailError');
const addressError = document.getElementById('addressError');
const remarkError = document.getElementById('remarkError');

const validationRules = {
    name: (v) => v.trim().length >= 3,
    mobile: (v) => /^\d{10}$/.test(v.trim()),
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
    address: (v) => v.trim().length >= 10,
    remark: (v) => { let t = v.trim(); return t === '' || t.length >= 5; }
};

function validateField(field, fieldName, errorElement) {
    const isValid = validationRules[fieldName](field.value);
    if (isValid) {
        field.classList.remove('error-input');
        errorElement.classList.remove('show');
    } else {
        field.classList.add('error-input');
        errorElement.classList.add('show');
    }
    return isValid;
}

function validateAllFields() {
    return validateField(nameInput, 'name', nameError) &&
           validateField(mobileInput, 'mobile', mobileError) &&
           validateField(emailInput, 'email', emailError) &&
           validateField(addressInput, 'address', addressError) &&
           validateField(remarkInput, 'remark', remarkError);
}

mobileInput.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
    validateField(mobileInput, 'mobile', mobileError);
});

nameInput.addEventListener('input', () => validateField(nameInput, 'name', nameError));
emailInput.addEventListener('input', () => validateField(emailInput, 'email', emailError));
addressInput.addEventListener('input', () => validateField(addressInput, 'address', addressError));
remarkInput.addEventListener('input', () => validateField(remarkInput, 'remark', remarkError));

form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!validateAllFields()) {
        const firstError = document.querySelector('.error-input');
        if (firstError) firstError.focus();
        return;
    }
    
    const captchaResponse = grecaptcha.getResponse();
    if (!captchaResponse) {
        alert('Please complete the CAPTCHA verification.');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    const formData = new FormData(form);
    formData.append('g-recaptcha-response', captchaResponse);
    
    fetch('process_feedback.php', { method: 'POST', body: formData })
        .then(res => {
            if (!res.headers.get('content-type')?.includes('application/json')) {
                return res.text().then(t => { throw new Error('Non-JSON response: ' + t.substring(0, 200)); });
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                if (data.toast) {
                    showToast(data.toast, 'success');
                }
                document.getElementById('displayName').textContent = data.data.name;
                document.getElementById('displayMobile').textContent = data.data.mobile;
                document.getElementById('displayEmail').textContent = data.data.email;
                document.getElementById('displayAddress').textContent = data.data.address;
                document.getElementById('displayRemark').textContent = data.data.remark || '—';
                if (data.message) {
                    document.querySelector('.success-message').innerHTML = data.message;
                }
                successModal.classList.add('show');
                grecaptcha.reset();
                setTimeout(() => {
                    form.reset();
                    document.querySelectorAll('.error-input').forEach(el => el.classList.remove('error-input'));
                    document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
                    setTimeout(() => location.reload(), 1000);
                }, 500);
            } else if (data.errors) {
                for (const [field, msg] of Object.entries(data.errors)) {
                    const inp = document.getElementById(field);
                    const err = document.getElementById(field + 'Error');
                    if (inp && err) {
                        inp.classList.add('error-input');
                        err.textContent = msg;
                        err.classList.add('show');
                    }
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
                grecaptcha.reset();
            } else {
                showToast('❌ ' + (data.message || 'Unknown error'), 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
                grecaptcha.reset();
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            showToast('❌ Error submitting form. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
            grecaptcha.reset();
        });
});

function closeModal() { 
    successModal.classList.remove('show'); 
}
successModal.addEventListener('click', (e) => { if (e.target === successModal) closeModal(); });
document.querySelector('.success-content')?.addEventListener('click', (e) => e.stopPropagation());

const userBadgeLevel = <?= $badge_level ?? 0 ?>;
const darkModeSession = <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1 ? 'true' : 'false' ?>;
</script>
<script src="global.js"></script>
<?php endif; ?>
</body>
</html>