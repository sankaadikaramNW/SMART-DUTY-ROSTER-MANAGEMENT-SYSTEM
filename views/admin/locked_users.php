<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-user-lock"></i> Locked User Accounts</h2>
        <p class="text-secondary">View user accounts that have been locked due to excessive failed login attempts (5 consecutive attempts).</p>
    </div>
</div>

<div class="glass-card mb-4 animate-fade-in">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-ban text-danger me-2"></i> Locked Accounts List</h5>
    </div>
    <div class="card-body p-4">
        <div class="table-custom-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Service Number</th>
                        <th>Rank & Name</th>
                        <th>Camp/Base</th>
                        <th>System Role</th>
                        <th>Lock Date</th>
                        <th>Lock Reason</th>
                        <th>Failed Attempts</th>
                        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="<?= ($roleName === 'Administrator' || $roleName === 'Super Admin') ? '8' : '7' ?>" class="text-center text-secondary py-4">No user accounts are currently locked.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($u['service_number']) ?></td>
                                <td>
                                    <span class="text-info small"><?= htmlspecialchars($u['rank'] ?? 'No Rank') ?></span>
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($u['camp_name'] ?? 'No Location') ?></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-25 px-2.5 py-1 small rounded">
                                        <?= htmlspecialchars($u['role_name']) ?>
                                    </span>
                                </td>
                                <td class="small font-monospace text-danger"><?= htmlspecialchars($u['lock_date'] ? date('Y-m-d H:i', strtotime($u['lock_date'])) : '—') ?></td>
                                <td class="small text-wrap text-danger" style="max-width:180px;"><?= htmlspecialchars($u['lock_reason'] ?? '5 Consecutive failures') ?></td>
                                <td class="fw-bold text-center text-danger"><?= (int)$u['failed_attempts'] ?></td>
                                <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-custom btn-custom-success py-1 px-2" onclick="confirmUnlock(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['service_number'], ENT_QUOTES, 'UTF-8') ?>')">
                                            <i class="fas fa-lock-open"></i> Unlock
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Unlock Form (POST) -->
<form id="unlockUserForm" action="<?= BASE_URL ?>/users/unlock" method="POST" style="display:none;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="user_id" id="unlockUserId">
    <input type="hidden" name="unlock_reason" id="unlockReason">
</form>

<script>
    function confirmUnlock(userId, serviceNumber) {
        Swal.fire({
            title: 'Unlock User Account?',
            html: `Are you sure you want to unlock the login account for Service Number:<br><strong>${serviceNumber}</strong>?`,
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Enter Unlock Reason (e.g. Identity verified, password reset)...',
            inputAttributes: {
                required: 'true'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-lock-open"></i> Unlock Account',
            confirmButtonColor: '#10b981',
            cancelButtonText: '<i class="fas fa-xmark"></i> Cancel',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            customClass: {
                popup: 'glass-card text-dark',
                confirmButton: 'btn btn-success px-4 py-2 small me-2',
                cancelButton: 'btn btn-secondary px-4 py-2 small'
            },
            buttonsStyling: false,
            preConfirm: (reason) => {
                if (!reason || reason.trim() === '') {
                    Swal.showValidationMessage('An unlock reason is required.');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('unlockUserId').value = userId;
                document.getElementById('unlockReason').value = result.value;
                document.getElementById('unlockUserForm').submit();
            }
        });
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
