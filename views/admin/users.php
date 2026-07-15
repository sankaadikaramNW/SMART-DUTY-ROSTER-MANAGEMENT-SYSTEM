<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-user-shield"></i> Manage User Accounts</h2>
        <p class="text-secondary">Configure credentials, security levels, and access controls.</p>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-info me-2"></i> User Accounts List</h5>
        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin' || $roleName === 'Warrant Officer IC'): ?>
            <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" onclick="openUserModal();">
                <i class="fas fa-user-plus"></i> Create User Account
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <div class="table-custom-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Service Number</th>
                        <th>Username</th>
                        <th>Rank & Name</th>
                        <th>Camp/Base</th>
                        <th>System Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No user accounts registered.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($u['service_number']) ?></td>
                                <td class="text-info fw-bold"><?= htmlspecialchars($u['username'] ?? '') ?></td>
                                <td>
                                    <span class="text-info font-monospace small"><?= htmlspecialchars($u['rank'] ?? 'No Rank') ?></span> 
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($u['camp_name'] ?? 'No Location') ?></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-25 px-2.5 py-1 small rounded">
                                        <?= htmlspecialchars($u['role_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $u['status'] === 'Active' ? 'success' : 'danger' ?> bg-opacity-25 border border-<?= $u['status'] === 'Active' ? 'success' : 'danger' ?> border-opacity-25 text-<?= $u['status'] === 'Active' ? 'success' : 'danger' ?> px-2.5">
                                        <?= $u['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin' || $roleName === 'Warrant Officer IC'): ?>
                                        <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2 me-1" 
                                                onclick="openUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>);">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <form action="<?= BASE_URL ?>/users/status" method="POST" class="d-inline me-1">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <input type="hidden" name="status" value="<?= $u['status'] === 'Active' ? 'Suspended' : 'Active' ?>">
                                            <button type="submit" class="btn btn-sm btn-custom btn-custom-<?= $u['status'] === 'Active' ? 'danger' : 'success' ?> py-1 px-2 me-1">
                                                <i class="fas fa-<?= $u['status'] === 'Active' ? 'ban' : 'circle-check' ?>"></i>
                                                <?= $u['status'] === 'Active' ? 'Suspend' : 'Activate' ?>
                                            </button>
                                        </form>
                                        
                                        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                                            <button type="button" class="btn btn-sm btn-custom btn-custom-warning py-1 px-2 me-1" onclick="openResetPasswordModal(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['service_number'], ENT_QUOTES, 'UTF-8') ?>')">
                                                <i class="fas fa-key"></i> Reset
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin' || $roleName === 'Warrant Officer IC'): ?>
                                            <button type="button" class="btn btn-sm btn-custom btn-custom-danger py-1 px-2" onclick="confirmArchiveUser(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['service_number'], ENT_QUOTES, 'UTF-8') ?>')">
                                                <i class="fas fa-box-archive"></i> Archive
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-secondary small">Read-Only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/save" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" id="user_id" name="user_id">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="userModalLabel">Configure User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Link to Personnel Profile</label>
                    <!-- Search input shown when creating new user -->
                    <div id="personnel_search_wrapper">
                        <input type="text" class="form-control form-control-custom" id="personnel_search" placeholder="Type service number or name to search..." autocomplete="off">
                        <div id="searchResults" class="list-group mt-2 border border-light-subtle bg-white shadow position-absolute w-75 z-3" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    <!-- Display input shown when editing existing user -->
                    <input type="text" class="form-control form-control-custom bg-light text-muted d-none" id="personnel_display" readonly>
                    <!-- Hidden field to hold selected service number -->
                    <input type="hidden" id="service_number_val" name="service_number" required>
                    <!-- User Account exists warning message -->
                    <div id="userExistsWarning" class="text-danger small mt-1" style="display: none;"><i class="fas fa-triangle-exclamation"></i> User account already exists.</div>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label text-secondary small fw-bold">Username</label>
                    <input type="text" class="form-control form-control-custom" id="username" name="username" placeholder="Suggested username (alphanumeric only)..." required pattern="[A-Za-z0-9]+" title="Must contain only letters and numbers (no slashes or special characters)">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary small">Account Password</label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="Leave empty to keep existing password (when updating)">
                </div>

                <div class="mb-3">
                    <label for="role_id" class="form-label text-secondary small">System Role Level</label>
                    <select class="form-select form-control-custom" id="role_id" name="role_id" required>
                        <option value="" disabled selected>Select Role</option>
                        <?php foreach ($roles as $r): ?>
                            <?php 
                            // Warrant Officer IC cannot assign high privilege roles
                            if ($roleName === 'Warrant Officer IC' && ((int)$r['role_id'] === 1 || (int)$r['role_id'] === 6)) {
                                continue;
                            }
                            ?>
                            <option value="<?= $r['role_id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label text-secondary small">Account Status</label>
                    <select class="form-select form-control-custom" id="status" name="status">
                        <option value="Active">Active</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                $submitLabel = "Save User";
                $submitId = "btnSaveUser";
                $submitIcon = "fas fa-floppy-disk";
                $cancelIcon = "fas fa-xmark";
                include __DIR__ . '/../components/form-buttons.php';
                ?>
            </div>
        </form>
    </div>
</div>

<script>
    let modal;
    document.addEventListener('DOMContentLoaded', () => {
        modal = new bootstrap.Modal(document.getElementById('userModal'));

        const searchInput = document.getElementById('personnel_search');
        const resultsDiv = document.getElementById('searchResults');
        const hiddenService = document.getElementById('service_number_val');

        searchInput.addEventListener('input', () => {
            // Reset warnings
            document.getElementById('userExistsWarning').style.display = 'none';
            const saveBtn = document.getElementById('btnSaveUser');
            if (saveBtn) saveBtn.disabled = false;

            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'none';
                return;
            }

            fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action list-group-item-dark text-light border-secondary small py-2';
                            btn.innerHTML = `<strong>${item.service_number}</strong> - ${item.rank} ${item.full_name} (${item.camp_name})`;
                            btn.addEventListener('click', () => {
                                searchInput.value = `${item.rank} ${item.full_name} (${item.service_number})`;
                                hiddenService.value = item.service_number;
                                resultsDiv.innerHTML = '';
                                resultsDiv.style.display = 'none';

                                // Suggest username
                                const usernameField = document.getElementById('username');
                                if (usernameField) {
                                    usernameField.value = item.service_number.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                                }

                                // Check if user account already exists
                                const existsWarning = document.getElementById('userExistsWarning');
                                if (parseInt(item.has_user_account) > 0) {
                                    existsWarning.style.display = 'block';
                                    if (saveBtn) saveBtn.disabled = true;
                                } else {
                                    existsWarning.style.display = 'none';
                                    if (saveBtn) saveBtn.disabled = false;
                                }
                            });
                            resultsDiv.appendChild(btn);
                        });
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.innerHTML = '<div class="list-group-item list-group-item-dark text-muted border-secondary small py-2">No matches found</div>';
                        resultsDiv.style.display = 'block';
                    }
                })
                .catch(err => console.error('Error searching personnel:', err));
        });

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target !== searchInput && e.target !== resultsDiv) {
                resultsDiv.style.display = 'none';
            }
        });
    });

    function openUserModal(data = null) {
        document.getElementById('user_id').value = data ? data.user_id : '';
        
        const searchWrapper = document.getElementById('personnel_search_wrapper');
        const displayInput = document.getElementById('personnel_display');
        const hiddenService = document.getElementById('service_number_val');
        const searchInput = document.getElementById('personnel_search');

        // Reset warning state
        document.getElementById('userExistsWarning').style.display = 'none';
        const saveBtn = document.getElementById('btnSaveUser');
        if (saveBtn) saveBtn.disabled = false;

        if (data) {
            searchWrapper.classList.add('d-none');
            displayInput.classList.remove('d-none');
            displayInput.value = `${data.service_number} - ${data.rank} ${data.full_name}`;
            hiddenService.value = data.service_number;
            searchInput.removeAttribute('required');
            document.getElementById('username').value = data.username || '';
        } else {
            searchWrapper.classList.remove('d-none');
            displayInput.classList.add('d-none');
            displayInput.value = '';
            searchInput.value = '';
            hiddenService.value = '';
            document.getElementById('username').value = '';
            searchInput.setAttribute('required', 'required');
        }

        document.getElementById('password').value = '';
        document.getElementById('role_id').value = data ? data.role_id : '';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('userModalLabel').innerHTML = data ? '<i class="fas fa-user-pen me-2"></i> Edit Account credentials' : '<i class="fas fa-user-plus me-2"></i> Register New User Account';
        modal.show();
    }
</script>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/password-reset" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" id="reset_user_id" name="user_id">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="resetPasswordModalLabel"><i class="fas fa-key me-2 text-warning"></i> Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">User Service Number</label>
                    <input type="text" class="form-control form-control-custom bg-light text-muted" id="reset_service_number" readonly>
                </div>
                <div class="mb-3">
                    <label for="temp_password" class="form-label text-secondary small fw-bold">Temporary Password</label>
                    <input type="password" class="form-control form-control-custom" id="temp_password" name="temp_password" placeholder="At least 8 characters..." required minlength="8">
                </div>
                <div class="mb-3">
                    <label for="reset_reason" class="form-label text-secondary small fw-bold">Reason for Password Reset</label>
                    <input type="text" class="form-control form-control-custom" id="reset_reason" name="reset_reason" placeholder="e.g. User request, Forgotten password..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-custom btn-custom-secondary px-3 py-2" data-bs-dismiss="modal"><i class="fas fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn btn-sm btn-custom btn-custom-warning px-3 py-2"><i class="fas fa-key"></i> Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Archive User Form (POST) -->
<form id="archiveUserForm" action="<?= BASE_URL ?>/users/archive" method="POST" style="display:none;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="user_id" id="archiveUserId">
    <input type="hidden" name="archive_reason" id="archiveUserReason">
</form>

<script>
    let resetModal;
    document.addEventListener('DOMContentLoaded', () => {
        resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    });

    function openResetPasswordModal(userId, serviceNumber) {
        document.getElementById('reset_user_id').value = userId;
        document.getElementById('reset_service_number').value = serviceNumber;
        document.getElementById('temp_password').value = '';
        document.getElementById('reset_reason').value = '';
        resetModal.show();
    }

    function confirmArchiveUser(userId, serviceNumber) {
        Swal.fire({
            title: 'Archive User Account?',
            html: `Are you sure you want to archive the login account for Service Number:<br><strong>${serviceNumber}</strong>?<br><br>This will suspend their access and hide the account from the active users list.`,
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Enter Archive Reason (e.g. Posted Out, Retired)...',
            inputAttributes: {
                required: 'true'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-box-archive"></i> Archive Account',
            confirmButtonColor: '#ef4444',
            cancelButtonText: '<i class="fas fa-xmark"></i> Cancel',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            customClass: {
                popup: 'glass-card text-dark',
                confirmButton: 'btn btn-danger px-4 py-2 small me-2',
                cancelButton: 'btn btn-secondary px-4 py-2 small'
            },
            buttonsStyling: false,
            preConfirm: (reason) => {
                if (!reason || reason.trim() === '') {
                    Swal.showValidationMessage('An archive reason is required.');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('archiveUserId').value = userId;
                document.getElementById('archiveUserReason').value = result.value;
                document.getElementById('archiveUserForm').submit();
            }
        });
    }
</script>

<?php if (isset($_GET['create_for'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const createFor = <?= json_encode($_GET['create_for']) ?>;
        fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(createFor)}`)
            .then(res => res.json())
            .then(data => {
                const item = data.find(p => p.service_number === createFor);
                if (item) {
                    // Open the modal!
                    openUserModal();
                    
                    const searchInput = document.getElementById('personnel_search');
                    const hiddenService = document.getElementById('service_number_val');
                    
                    searchInput.value = `${item.rank} ${item.full_name} (${item.service_number})`;
                    hiddenService.value = item.service_number;
                    
                    // Suggest username
                    const usernameInput = document.getElementById('username');
                    if (usernameInput) {
                        usernameInput.value = item.service_number.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    }

                    // Check if user account already exists
                    const warning = document.getElementById('userExistsWarning');
                    const submitBtn = document.getElementById('btnSaveUser');
                    if (parseInt(item.has_user_account) > 0) {
                        warning.style.display = 'block';
                        if (submitBtn) {
                            submitBtn.disabled = true;
                        }
                    } else {
                        warning.style.display = 'none';
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                    }
                }
            });
    });
</script>
<?php endif; ?>

<?php
include __DIR__ . '/../layout/footer.php';
?>
