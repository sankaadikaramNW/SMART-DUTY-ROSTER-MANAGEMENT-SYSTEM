<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-user-lock"></i> Archived Users</h2>
        <p class="text-secondary">View system user account credentials that have been archived. (Read-Only for Warrant Officer I/C)</p>
    </div>
</div>

<div class="glass-card mb-4 animate-fade-in">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-info me-2"></i> Archived User Accounts</h5>
    </div>
    <div class="card-body p-4">
        <!-- Live Search Bar -->
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" id="archivedUsersSearch" class="form-control form-control-custom" placeholder="Filter by username, service number, rank, name or role...">
            </div>
        </div>

        <!-- Table -->
        <div class="table-custom-container">
            <table class="table-custom" id="archivedUsersTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Service Number</th>
                        <th>Rank & Name</th>
                        <th>System Role</th>
                        <th>Previous Camp</th>
                        <th>Archive Date</th>
                        <th>Archive Reason</th>
                        <th>Archived By</th>
                        <th>Account Status</th>
                        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="<?= ($roleName === 'Administrator' || $roleName === 'Super Admin') ? '10' : '9' ?>" class="text-center text-secondary py-4">No archived user accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="user-row">
                                <td class="fw-bold"><?= htmlspecialchars($u['service_number']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($u['service_number']) ?></td>
                                <td>
                                    <span class="text-info small"><?= htmlspecialchars($u['rank'] ?? 'No Rank') ?></span>
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-25 px-2.5 py-1 small rounded">
                                        <?= htmlspecialchars($u['role_name']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($u['camp_name'] ?? 'No Location') ?></td>
                                <td class="small font-monospace"><?= htmlspecialchars($u['archived_at'] ? date('Y-m-d H:i', strtotime($u['archived_at'])) : '—') ?></td>
                                <td class="small text-wrap" style="max-width:180px;"><?= htmlspecialchars($u['archive_reason'] ?? '—') ?></td>
                                <td class="small text-info"><?= htmlspecialchars($u['archived_by'] ?? '—') ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 text-secondary px-2.5 py-1 small rounded-pill">
                                        Archived
                                    </span>
                                </td>
                                <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-custom btn-custom-success py-1 px-2" onclick="confirmRestoreUser(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['service_number'], ENT_QUOTES, 'UTF-8') ?>')">
                                            <i class="fas fa-circle-check"></i> Restore
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

<!-- Restore User Form (POST) -->
<form id="restoreUserForm" action="<?= BASE_URL ?>/users/restore" method="POST" style="display:none;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="user_id" id="restoreUserId">
    <input type="hidden" name="restore_reason" id="restoreUserReason">
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Live client-side search filtering
        const searchInput = document.getElementById('archivedUsersSearch');
        const rows = document.querySelectorAll('#archivedUsersTable tbody tr.user-row');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    function confirmRestoreUser(userId, serviceNumber) {
        Swal.fire({
            title: 'Restore User Account?',
            html: `Are you sure you want to restore the login account for Service Number:<br><strong>${serviceNumber}</strong>?`,
            icon: 'question',
            input: 'text',
            inputPlaceholder: 'Enter Reason for Restore (e.g. Account reactivated)...',
            inputAttributes: {
                required: 'true'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-circle-check"></i> Restore Account',
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
                    Swal.showValidationMessage('A restore reason is required.');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restoreUserId').value = userId;
                document.getElementById('restoreUserReason').value = result.value;
                document.getElementById('restoreUserForm').submit();
            }
        });
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
