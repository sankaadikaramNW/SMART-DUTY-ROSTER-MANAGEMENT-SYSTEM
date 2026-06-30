<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-campground"></i> Manage Camps & Bases</h2>
        <p class="text-secondary">System configurations for SLAF base camp locations.</p>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-info me-2"></i> Camp Locations List</h5>
        <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" onclick="openCampModal();">
            <i class="fas fa-plus"></i> Add New Camp
        </button>
    </div>
    <div class="card-body p-4">
        <div class="table-custom-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Camp Code</th>
                        <th>Camp Name</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($camps)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No camps registered.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($camps as $c): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= htmlspecialchars($c['camp_code']) ?></td>
                                <td><?= htmlspecialchars($c['camp_name']) ?></td>
                                <td class="small text-secondary"><?= htmlspecialchars($c['address'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $c['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $c['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $c['status'] === 'Active' ? 'success' : 'secondary' ?> px-2">
                                        <?= $c['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2" 
                                            onclick="openCampModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>);">
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

<!-- Camp Modal -->
<div class="modal fade" id="campModal" tabindex="-1" aria-labelledby="campModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/camps/save" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" id="camp_id" name="camp_id">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="campModalLabel">Configure Camp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="camp_code" class="form-label text-secondary small">Camp Code</label>
                    <input type="text" class="form-control form-control-custom" id="camp_code" name="camp_code" required placeholder="e.g. SLAF-EKL">
                </div>
                <div class="mb-3">
                    <label for="camp_name" class="form-label text-secondary small">Camp Name</label>
                    <input type="text" class="form-control form-control-custom" id="camp_name" name="camp_name" required placeholder="e.g. SLAF Ekala">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label text-secondary small">Address</label>
                    <textarea class="form-control form-control-custom" id="address" name="address" rows="2" placeholder="Base physical address..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label text-secondary small">Status</label>
                    <select class="form-select form-control-custom" id="status" name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                $submitLabel = "Save Camp";
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
        modal = new bootstrap.Modal(document.getElementById('campModal'));
    });

    function openCampModal(data = null) {
        document.getElementById('camp_id').value = data ? data.camp_id : '';
        document.getElementById('camp_code').value = data ? data.camp_code : '';
        document.getElementById('camp_name').value = data ? data.camp_name : '';
        document.getElementById('address').value = data ? (data.address || '') : '';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('campModalLabel').innerHTML = data ? '<i class="fas fa-pen-to-square me-2"></i> Edit Camp configuration' : '<i class="fas fa-plus me-2"></i> Register New Camp';
        modal.show();
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
