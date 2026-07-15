<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="login-card mx-auto text-center animate-fade-in" style="max-width: 450px; margin-top: 60px;">
    <div class="mb-4">
        <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png?v=<?= time() ?>" alt="Sri Lanka Air Force Logo" class="login-logo mb-2" style="width:110px !important;height:auto !important;display:block;margin:0 auto 12px;">
        <h2 class="login-title mb-1 text-dark fw-bold">Force Password Change</h2>
        <p class="login-subtitle mb-0 text-secondary" style="font-size: 13.5px;">Your password has been reset by the Administrator. Please configure a new secure password before accessing the system.</p>
    </div>
    
    <?php if (Session::has('error_message')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-danger text-danger mb-3 p-2.5 small text-start d-flex align-items-center justify-content-between" role="alert" style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; font-size: 12.5px; line-height: 1.3;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation text-danger flex-shrink-0"></i> 
                <span><?= htmlspecialchars(Session::get('error_message')) ?></span>
            </div>
            <button type="button" class="border-0 bg-transparent p-0 ms-2" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.95rem; color: #7f1d1d; opacity: 0.7; cursor: pointer; display: flex; align-items: center;"><i class="fas fa-xmark"></i></button>
        </div>
        <?php Session::remove('error_message'); ?>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/change-password/save" method="POST" class="text-start" id="changePasswordForm">
        <?= Security::csrfField() ?>
        
        <div class="login-form-group mb-3">
            <label for="new_password" class="form-label login-form-label text-secondary small fw-bold">New Password</label>
            <div class="input-group">
                <span class="input-group-text login-input-group-text">
                    <i class="fas fa-key"></i>
                </span>
                <input type="password" class="form-control login-input-height login-input-height-append" id="new_password" name="new_password" placeholder="Min 8 characters..." required autocomplete="new-password">
            </div>
        </div>
        
        <div class="login-form-group mb-4">
            <label for="confirm_password" class="form-label login-form-label text-secondary small fw-bold">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text login-input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control login-input-height login-input-height-append" id="confirm_password" name="confirm_password" placeholder="Retype new password..." required autocomplete="new-password">
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" id="submitBtn" class="btn btn-primary login-btn d-flex align-items-center justify-content-center gap-2 py-2.5">
                <i class="fas fa-floppy-disk"></i> Update & Log In
            </button>
        </div>
    </form>
    
    <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-danger px-3 py-1.5 small"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
    </div>
</div>

<?php
include __DIR__ . '/../layout/footer.php';
?>
