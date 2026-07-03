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
        <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" onclick="openUserModal();">
            <i class="fas fa-user-plus"></i> Create User Account
        </button>
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No user accounts registered.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($u['service_number']) ?></td>
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
                                    <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2 me-1" 
                                            onclick="openUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>);">
                                        <i class="fas fa-key"></i> Edit
                                    </button>
                                    
                                    <form action="<?= BASE_URL ?>/users/status" method="POST" class="d-inline">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                        <input type="hidden" name="status" value="<?= $u['status'] === 'Active' ? 'Suspended' : 'Active' ?>">
                                        <button type="submit" class="btn btn-sm btn-custom btn-custom-<?= $u['status'] === 'Active' ? 'danger' : 'success' ?> py-1 px-2">
                                            <i class="fas fa-<?= $u['status'] === 'Active' ? 'ban' : 'circle-check' ?>"></i>
                                            <?= $u['status'] === 'Active' ? 'Suspend' : 'Activate' ?>
                                        </button>
                                    </form>
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

        if (data) {
            searchWrapper.classList.add('d-none');
            displayInput.classList.remove('d-none');
            displayInput.value = `${data.service_number} - ${data.rank} ${data.full_name}`;
            hiddenService.value = data.service_number;
            searchInput.removeAttribute('required');
        } else {
            searchWrapper.classList.remove('d-none');
            displayInput.classList.add('d-none');
            displayInput.value = '';
            searchInput.value = '';
            hiddenService.value = '';
            searchInput.setAttribute('required', 'required');
        }

        document.getElementById('password').value = '';
        document.getElementById('role_id').value = data ? data.role_id : '';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('userModalLabel').innerHTML = data ? '<i class="fas fa-user-pen me-2"></i> Edit Account credentials' : '<i class="fas fa-user-plus me-2"></i> Register New User Account';
        modal.show();
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
