<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="glass-card p-5 animate-fade-in text-center">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle p-3 mb-3 text-primary" style="width: 80px; height: 80px;">
                    <i class="fas fa-shield-halved fs-1 text-info"></i>
                </div>
                <h2 class="fw-bold mb-1 gradient-text">SLAF Smart Duty Roster</h2>
                <p class="text-secondary">Provost Duty Management Portal</p>
            </div>
            
            <form action="<?= BASE_URL ?>/login" method="POST" class="text-start">
                <?= Security::csrfField() ?>
                
                <div class="mb-4">
                    <label for="service_number" class="form-label text-secondary small fw-medium">Service Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-secondary" style="border-right: none;">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input type="text" class="form-control form-control-custom" id="service_number" name="service_number" placeholder="51837 or admin" pattern="[Aa][Dd][Mm][Ii][Nn]|\d+" title="Must be a valid Service Number (e.g., 51837 or admin)" required style="border-left: none;">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label text-secondary small fw-medium">Security Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-secondary" style="border-right: none;">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="••••••••" required style="border-left: none;">
                    </div>
                </div>
                
                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-custom btn-custom-primary justify-content-center py-3">
                        <i class="fas fa-right-to-bracket"></i> Authenticate Account
                    </button>
                </div>
            </form>
            
            <div class="mt-4 pt-3 border-top border-secondary text-secondary small">
                <span>Authorized Access Only &bull; IP Logged</span>
            </div>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
?>
