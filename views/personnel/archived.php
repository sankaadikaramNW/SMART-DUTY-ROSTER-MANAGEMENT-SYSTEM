<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-box-archive"></i> Archived Personnel</h2>
        <p class="text-secondary">View service personnel profiles that have been archived. (Read-Only for Warrant Officer I/C)</p>
    </div>
</div>

<div class="glass-card mb-4 animate-fade-in">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-info me-2"></i> Archived Personnel Registry</h5>
    </div>
    <div class="card-body p-4">
        <!-- Live Search Bar -->
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" id="archivedSearchInput" class="form-control form-control-custom" placeholder="Filter by service no, rank, name or trade...">
            </div>
        </div>

        <!-- Table -->
        <div class="table-custom-container">
            <table class="table-custom" id="archivedTable">
                <thead>
                    <tr>
                        <th>Service Number</th>
                        <th>Rank & Name</th>
                        <th>Previous Camp</th>
                        <th>Archive Date</th>
                        <th>Archive Reason</th>
                        <th>Archived By</th>
                        <th>Status</th>
                        <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personnelList)): ?>
                        <tr>
                            <td colspan="<?= ($roleName === 'Administrator' || $roleName === 'Super Admin') ? '8' : '7' ?>" class="text-center text-secondary py-4">No archived personnel profiles found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($personnelList as $p): ?>
                            <tr class="personnel-row">
                                <td class="fw-bold search-cell-service"><?= htmlspecialchars($p['service_number']) ?></td>
                                <td class="search-cell-name">
                                    <span class="text-info fw-medium"><?= htmlspecialchars($p['rank'] ?? 'No Rank') ?></span> 
                                    <?= htmlspecialchars($p['initials'] . ' ' . $p['full_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($p['camp_name'] ?? 'No Location') ?></td>
                                <td class="small font-monospace"><?= htmlspecialchars($p['archived_at'] ? date('Y-m-d H:i', strtotime($p['archived_at'])) : '—') ?></td>
                                <td class="small text-wrap" style="max-width:200px;"><?= htmlspecialchars($p['archive_reason'] ?? '—') ?></td>
                                <td class="small text-info"><?= htmlspecialchars($p['archived_by'] ?? '—') ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 text-secondary px-2.5 py-1 small rounded-pill">
                                        Archived
                                    </span>
                                </td>
                                <?php if ($roleName === 'Administrator' || $roleName === 'Super Admin'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-custom btn-custom-success py-1 px-2" onclick="confirmRestore('<?= htmlspecialchars($p['service_number'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($p['rank'] . ' ' . $p['initials'] . ' ' . $p['full_name'], ENT_QUOTES, 'UTF-8') ?>')">
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

<!-- Restore Form (POST) -->
<form id="restoreForm" action="<?= BASE_URL ?>/personnel/restore" method="POST" style="display:none;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="service_number" id="restoreServiceNumber">
    <input type="hidden" name="restore_reason" id="restoreReason">
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Live client-side search filtering
        const searchInput = document.getElementById('archivedSearchInput');
        const rows = document.querySelectorAll('#archivedTable tbody tr.personnel-row');

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

    function confirmRestore(serviceNumber, name) {
        Swal.fire({
            title: 'Restore Personnel Profile?',
            html: `Are you sure you want to restore the personnel profile for:<br><strong>${serviceNumber} - ${name}</strong>?`,
            icon: 'question',
            input: 'text',
            inputPlaceholder: 'Enter Reason for Restore (e.g. Returned from Leave, Error)...',
            inputAttributes: {
                required: 'true'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-circle-check"></i> Restore Profile',
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
                document.getElementById('restoreServiceNumber').value = serviceNumber;
                document.getElementById('restoreReason').value = result.value;
                document.getElementById('restoreForm').submit();
            }
        });
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
