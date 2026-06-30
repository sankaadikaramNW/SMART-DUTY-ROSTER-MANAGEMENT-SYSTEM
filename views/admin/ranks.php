<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-list-ol"></i> Manage Ranks</h2>
        <p class="text-secondary">System configurations for personnel ranks and hierarchy ordering.</p>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-info me-2"></i> Ranks List</h5>
        <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" onclick="openRankModal();">
            <i class="fas fa-plus"></i> Add New Rank
        </button>
    </div>
    <div class="card-body p-4">
        <div class="table-custom-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Display Order</th>
                        <th>Rank Code</th>
                        <th>Rank Name</th>
                        <th>Short / Initials Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ranks)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No ranks registered.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ranks as $rk): ?>
                            <tr>
                                <td class="fw-bold text-info"><?= (int)$rk['display_order'] ?></td>
                                <td class="font-monospace fw-semibold text-warning"><?= htmlspecialchars($rk['rank_code']) ?></td>
                                <td><?= htmlspecialchars($rk['rank_name']) ?></td>
                                <td><?= htmlspecialchars($rk['rank_short_name']) ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $rk['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $rk['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $rk['status'] === 'Active' ? 'success' : 'secondary' ?> px-2">
                                        <?= htmlspecialchars($rk['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2" 
                                            onclick="openRankModal(<?= htmlspecialchars(json_encode($rk), ENT_QUOTES, 'UTF-8') ?>);">
                                        <i class="fas fa-pen-to-square"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rank Modal -->
<div class="modal fade" id="rankModal" tabindex="-1" aria-labelledby="rankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/ranks/save" method="POST" class="modal-content glass-card bg-dark text-light border-secondary">
            <?= Security::csrfField() ?>
            <input type="hidden" id="rank_id" name="rank_id">
            
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="rankModalLabel">Configure Rank</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rank_code" class="form-label text-secondary small">Rank Code</label>
                    <input type="text" class="form-control form-control-custom" id="rank_code" name="rank_code" required placeholder="e.g. CPL">
                </div>
                <div class="mb-3">
                    <label for="rank_name" class="form-label text-secondary small">Rank Name</label>
                    <input type="text" class="form-control form-control-custom" id="rank_name" name="rank_name" required placeholder="e.g. Corporal">
                </div>
                <div class="mb-3">
                    <label for="rank_short_name" class="form-label text-secondary small">Short Name / Initials</label>
                    <input type="text" class="form-control form-control-custom" id="rank_short_name" name="rank_short_name" required placeholder="e.g. CPL">
                </div>
                <div class="mb-3">
                    <label for="display_order" class="form-label text-secondary small">Display / Sort Order</label>
                    <input type="number" class="form-control form-control-custom" id="display_order" name="display_order" required min="1" placeholder="e.g. 40">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label text-secondary small">Status</label>
                    <select class="form-select form-control-custom" id="status" name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <?php
                $submitLabel = "Save Rank";
                $submitIcon = "fas fa-floppy-disk";
                $cancelIcon = "fas fa-xmark";
                include __DIR__ . '/../components/form-buttons.php';
                ?>
            </div>
        </form>
    </div>
</div>

<script>
    let rankModalObj;
    document.addEventListener('DOMContentLoaded', () => {
        rankModalObj = new bootstrap.Modal(document.getElementById('rankModal'));
    });

    function openRankModal(data = null) {
        document.getElementById('rank_id').value = data ? data.rank_id : '';
        document.getElementById('rank_code').value = data ? data.rank_code : '';
        document.getElementById('rank_name').value = data ? data.rank_name : '';
        document.getElementById('rank_short_name').value = data ? data.rank_short_name : '';
        document.getElementById('display_order').value = data ? data.display_order : '10';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('rankModalLabel').innerHTML = data ? '<i class="fas fa-pen-to-square me-2"></i> Edit Rank Configuration' : '<i class="fas fa-plus me-2"></i> Register New Rank';
        rankModalObj.show();
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
