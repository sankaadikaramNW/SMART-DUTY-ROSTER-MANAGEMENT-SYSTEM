<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="login-card mx-auto text-center animate-fade-in">
    <div class="mb-3">
        <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="Sri Lanka Air Force Logo" class="login-logo mb-2">
        <h2 class="login-title mb-1">Welcome back</h2>
        <p class="login-subtitle mb-0">AFP Duty Roster Management System</p>
    </div>
    
    <?php if (Session::has('error_message')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-danger text-danger mb-3 p-2 small text-start d-flex align-items-center justify-content-between" role="alert" style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; font-size: 12.5px; line-height: 1.3;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation text-danger flex-shrink-0"></i> 
                <span><?= htmlspecialchars(Session::get('error_message')) ?></span>
            </div>
            <button type="button" class="border-0 bg-transparent p-0 ms-2" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.95rem; color: #7f1d1d; opacity: 0.7; cursor: pointer; display: flex; align-items: center;"><i class="fas fa-xmark"></i></button>
        </div>
        <?php Session::remove('error_message'); ?>
    <?php endif; ?>
    
    <?php if (Session::has('success_message')): ?>
        <div class="alert alert-success alert-dismissible fade show border-success text-success mb-3 p-2 small text-start d-flex align-items-center justify-content-between" role="alert" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 12.5px; line-height: 1.3;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-check text-success flex-shrink-0"></i> 
                <span><?= htmlspecialchars(Session::get('success_message')) ?></span>
            </div>
            <button type="button" class="border-0 bg-transparent p-0 ms-2" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.95rem; color: #14532d; opacity: 0.7; cursor: pointer; display: flex; align-items: center;"><i class="fas fa-xmark"></i></button>
        </div>
        <?php Session::remove('success_message'); ?>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/login" method="POST" class="text-start" id="loginForm">
        <?= Security::csrfField() ?>
        
        <div class="login-form-group mb-2">
            <label for="service_number" class="form-label login-form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text login-input-group-text">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="form-control login-input-height login-input-height-append" id="service_number" name="service_number" placeholder="Service No or admin" pattern="[Aa][Dd][Mm][Ii][Nn]|\d+" title="Must be a valid Service Number (e.g., 51837 or admin)" required autocomplete="username">
            </div>
        </div>
        
        <div class="login-form-group mb-3">
            <label for="password" class="form-label login-form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text login-input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control login-input-height login-input-height-append" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
            </div>
        </div>
        
        <!-- Offline Secure Verification Mock Widget -->
        <div class="login-captcha-section mb-3 d-flex justify-content-center">
            <div class="cf-turnstile-mock d-flex align-items-center justify-content-between p-2 rounded" id="turnstileWidget">
                <div class="d-flex align-items-center gap-2">
                    <div class="cf-status-container d-flex align-items-center justify-content-center">
                        <div class="spinner-border text-primary" role="status" id="captchaSpinner" style="width: 1.25rem; height: 1.25rem; border-width: 0.18em;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="cf-success-checkmark d-none" id="captchaSuccess">
                            <i class="fas fa-circle-check text-success fs-5 animate-scale-up"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column text-start" style="line-height: 1.2;">
                        <span class="cf-text-status" id="captchaTextStatus" style="font-size: 13px; font-weight: 500; color: #475569;">Verifying...</span>
                    </div>
                </div>
                <div class="text-end d-flex flex-column align-items-end justify-content-center" style="line-height: 1.2; padding-right: 5px;">
                    <span class="cf-brand-title" style="font-size: 8px; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">SECURE CHECK</span>
                    <span style="font-size: 7px; color: #94a3b8; font-weight: 500;">Intranet Protocol</span>
                </div>
            </div>
        </div>
        
        <div class="d-grid mb-3">
            <button type="submit" id="submitBtn" class="btn btn-primary login-btn d-flex align-items-center justify-content-center gap-2" disabled>
                <i class="fas fa-right-to-bracket"></i> Sign In
            </button>
        </div>
    </form>
    
    <div class="login-footer-notice d-flex align-items-center justify-content-center gap-1">
        <i class="fas fa-shield-halved text-secondary" style="font-size: 11px;"></i>
        <span>Unauthorized access is strictly prohibited and logged.</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const spinner = document.getElementById('captchaSpinner');
    const success = document.getElementById('captchaSuccess');
    const statusText = document.getElementById('captchaTextStatus');
    const submitBtn = document.getElementById('submitBtn');
    const widget = document.getElementById('turnstileWidget');
    
    // Simulate Cloudflare Turnstile verification flow with smooth transition
    setTimeout(() => {
        spinner.classList.add('d-none');
        success.classList.remove('d-none');
        statusText.textContent = 'Success!';
        statusText.style.color = '#15803d'; // Green-700
        statusText.style.fontWeight = '600';
        submitBtn.removeAttribute('disabled');
        widget.classList.add('verified');
    }, 600);
});
</script>
<?php
include __DIR__ . '/../layout/footer.php';
?>

