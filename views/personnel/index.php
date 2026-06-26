<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-users-line"></i> Personnel Profiles</h2>
        <p class="text-secondary">Manage and view active service personnel in the database.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
            <button type="button" class="btn btn-custom btn-custom-primary" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                <i class="fas fa-user-plus"></i> Add New Personnel
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="glass-card p-4">
    <!-- Live Search Bar -->
    <div class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
            <input type="text" id="personnelSearchInput" class="form-control form-control-custom" placeholder="Search by service no, rank, name or trade...">
        </div>
    </div>

    <!-- Table -->
    <div class="table-custom-container">
        <table class="table-custom" id="personnelTable">
            <thead>
                <tr>
                    <th>Service Number</th>
                    <th>Rank & Name</th>
                    <th>Trade</th>
                    <th>Squadron</th>
                    <th>Camp/Base</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($personnelList)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No personnel profiles found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($personnelList as $p): ?>
                        <tr class="personnel-row">
                            <td class="fw-bold search-cell-service"><?= htmlspecialchars($p['service_number']) ?></td>
                            <td class="search-cell-name">
                                <span class="text-info fw-medium"><?= htmlspecialchars($p['rank']) ?></span> 
                                <?= htmlspecialchars($p['initials'] . ' ' . $p['full_name']) ?>
                            </td>
                            <td class="search-cell-trade"><?= htmlspecialchars($p['trade']) ?></td>
                            <td><?= htmlspecialchars($p['squadron']) ?></td>
                            <td><?= htmlspecialchars($p['camp_name']) ?></td>
                            <td>
                                <?php
                                $status = $p['status'];
                                $badgeClass = 'bg-secondary';
                                if ($status === 'Active') $badgeClass = 'bg-success';
                                elseif ($status === 'Leave') $badgeClass = 'bg-warning';
                                elseif ($status === 'Temporary Duty') $badgeClass = 'bg-info';
                                ?>
                                <span class="badge <?= $badgeClass ?> bg-opacity-25 border border-<?= substr($badgeClass, 3) ?> border-opacity-25 text-<?= substr($badgeClass, 3) === 'warning' ? 'warning' : (substr($badgeClass, 3) === 'success' ? 'success' : 'info') ?> px-2 py-1 small rounded-pill">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/personnel/view?service_number=<?= urlencode($p['service_number']) ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Personnel Modal -->
<?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
<div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-labelledby="addPersonnelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content glass-card bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="addPersonnelModalLabel"><i class="fas fa-user-plus me-2"></i> Register New Personnel Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/personnel/add" method="POST">
                <?= Security::csrfField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="service_number" class="form-label text-secondary small">Service Number</label>
                            <input type="text" class="form-control form-control-custom" id="service_number" name="service_number" placeholder="e.g. 51837 or admin" pattern="[Aa][Dd][Mm][Ii][Nn]|\d+" title="Must be a valid Service Number (e.g., 51837 or admin)" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rank_id" class="form-label text-secondary small">Rank</label>
                            <select class="form-select form-control-custom" id="rank_id" name="rank_id" required>
                                <option value="" disabled selected>Select Rank</option>
                                <?php foreach ($ranks as $rk): ?>
                                    <option value="<?= $rk['rank_id'] ?>"><?= htmlspecialchars($rk['rank_name']) ?> (<?= htmlspecialchars($rk['rank_short_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="initials" class="form-label text-secondary small">Initials</label>
                            <input type="text" class="form-control form-control-custom" id="initials" name="initials" placeholder="e.g. A.B." required>
                        </div>
                        <div class="col-md-8">
                            <label for="full_name" class="form-label text-secondary small">Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" placeholder="e.g. Silva J.A." required>
                        </div>
                        <div class="col-md-6">
                            <label for="trade" class="form-label text-secondary small">Trade / Specialty</label>
                            <input type="text" class="form-control form-control-custom" id="trade" name="trade" placeholder="e.g. Provost Guard" required>
                        </div>
                        <div class="col-md-6">
                            <label for="squadron" class="form-label text-secondary small">Squadron</label>
                            <input type="text" class="form-control form-control-custom" id="squadron" name="squadron" placeholder="e.g. Provost Squadron" required>
                        </div>
                        <div class="col-md-6">
                            <label for="camp_id" class="form-label text-secondary small">Assigned Camp / Base</label>
                            <select class="form-select form-control-custom" id="camp_id" name="camp_id" required>
                                <option value="" disabled selected>Select Camp/Base</option>
                                <?php foreach ($camps as $c): ?>
                                    <?php 
                                    // SNCO constraint
                                    $restrictedCampId = LocationMiddleware::getCampConstraint();
                                    if ($restrictedCampId !== null && (int)$c['camp_id'] !== $restrictedCampId) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?= $c['camp_id'] ?>"><?= htmlspecialchars($c['camp_name']) ?> (<?= htmlspecialchars($c['camp_code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label text-secondary small">Personnel Status</label>
                            <select class="form-select form-control-custom" id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Leave">On Leave</option>
                                <option value="Temporary Duty">Temporary Duty (TDY)</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label text-secondary small">Email Address</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="e.g. user@slaf.lk" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label text-secondary small">Contact Number</label>
                            <input type="text" class="form-control form-control-custom" id="contact_number" name="contact_number" placeholder="e.g. +94771234567">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom btn-custom-primary">Register Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // AJAX Personnel Search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('personnelSearchInput');
        const tbody = document.querySelector('#personnelTable tbody');

        // Store the original HTML content to restore it when search is cleared
        const originalHtml = tbody.innerHTML;

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            
            // If empty, restore original table content
            if (query.length === 0) {
                tbody.innerHTML = originalHtml;
                return;
            }

            // Perform fetch search
            fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(p => {
                            const tr = document.createElement('tr');
                            tr.className = 'personnel-row';
                            
                            let badgeClass = 'bg-secondary';
                            if (p.status === 'Active') badgeClass = 'bg-success';
                            else if (p.status === 'Leave') badgeClass = 'bg-warning';
                            else if (p.status === 'Temporary Duty') badgeClass = 'bg-info';
                            
                            const textBadgeClass = badgeClass.replace('bg-', 'text-');
                            const borderBadgeClass = badgeClass.replace('bg-', 'border-');

                            tr.innerHTML = `
                                <td class="fw-bold search-cell-service">${escapeHtml(p.service_number)}</td>
                                <td class="search-cell-name">
                                    <span class="text-info fw-medium">${escapeHtml(p.rank)}</span> 
                                    ${escapeHtml(p.initials + ' ' + p.full_name)}
                                </td>
                                <td class="search-cell-trade">${escapeHtml(p.trade)}</td>
                                <td>${escapeHtml(p.squadron)}</td>
                                <td>${escapeHtml(p.camp_name)}</td>
                                <td>
                                    <span class="badge ${badgeClass} bg-opacity-25 border ${borderBadgeClass} border-opacity-25 ${textBadgeClass} px-2 py-1 small rounded-pill">
                                        ${escapeHtml(p.status)}
                                    </span>
                                </td>
                                <td>
                                    <a href="${BASE_URL}/personnel/view?service_number=${encodeURIComponent(p.service_number)}" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                        <i class="fas fa-user"></i> View Profile
                                    </a>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">No matching personnel found.</td>
                            </tr>
                        `;
                    }
                })
                .catch(err => {
                    console.error('Error searching personnel:', err);
                });
        });

        function escapeHtml(string) {
            if (!string) return '';
            return String(string)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
