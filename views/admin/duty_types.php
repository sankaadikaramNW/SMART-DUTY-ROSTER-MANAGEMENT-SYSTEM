<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-shield"></i> Manage Duty Types</h2>
        <p class="text-secondary">System configurations for sentry and guard duty task categories.</p>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-info me-2"></i> Duty Types List</h5>
        <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" onclick="openDutyTypeModal();">
            <i class="fas fa-plus"></i> Add New Duty Type
        </button>
    </div>
    <div class="card-body p-4">
        <div class="table-custom-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Display Order</th>
                        <th>Duty Code</th>
                        <th>Duty Type Name</th>
                        <th>Color</th>
                        <th>Icon</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dutyTypes)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">No duty types registered.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dutyTypes as $dt): ?>
                            <tr>
                                <td class="font-monospace text-secondary text-center"><?= (int)$dt['display_order'] ?></td>
                                <td class="font-monospace text-info small"><?= htmlspecialchars($dt['duty_code']) ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($dt['duty_type_name']) ?></td>
                                <td>
                                    <span class="d-inline-block rounded border border-secondary border-opacity-20 align-middle" style="width: 20px; height: 20px; background-color: <?= htmlspecialchars($dt['color_code']) ?>;"></span>
                                    <span class="small font-monospace text-secondary ms-1 align-middle"><?= htmlspecialchars($dt['color_code']) ?></span>
                                </td>
                                <td>
                                    <i class="<?= htmlspecialchars($dt['icon_class']) ?> text-dark fs-5 align-middle"></i>
                                    <span class="small text-secondary font-monospace ms-1 align-middle" style="font-size: 0.72rem;">(<?= htmlspecialchars($dt['icon_class']) ?>)</span>
                                </td>
                                <td class="small text-secondary"><?= htmlspecialchars($dt['description'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= $dt['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $dt['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $dt['status'] === 'Active' ? 'success' : 'secondary' ?> px-2">
                                        <?= $dt['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2" 
                                            onclick="openDutyTypeModal(<?= htmlspecialchars(json_encode($dt), ENT_QUOTES, 'UTF-8') ?>);">
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

<!-- Duty Type Modal -->
<div class="modal fade" id="dutyTypeModal" tabindex="-1" aria-labelledby="dutyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/duty-types/save" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" id="duty_type_id" name="duty_type_id">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="dutyTypeModalLabel">Configure Duty Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <div class="mb-3">
                    <label for="duty_type_name" class="form-label text-secondary small">Duty Type Name</label>
                    <input type="text" class="form-control form-control-custom" id="duty_type_name" name="duty_type_name" required placeholder="e.g. Armoury Guard">
                </div>
                <div class="mb-3">
                    <label for="duty_code" class="form-label text-secondary small">Duty Code (Unique Identifier)</label>
                    <input type="text" class="form-control form-control-custom font-monospace" id="duty_code" name="duty_code" required placeholder="e.g. ARMOURY_GUARD" style="text-transform: uppercase;">
                </div>
                <div class="mb-3">
                    <label for="color_code" class="form-label text-secondary small">Duty Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color border-secondary border-opacity-20" id="color_code" name="color_code" value="#0d6efd" title="Choose duty color" style="width: 50px; height: 38px;">
                        <input type="text" class="form-control form-control-custom font-monospace" id="color_hex" placeholder="#000000" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="icon_class" class="form-label text-secondary small">Duty Icon Class (Bootstrap Icons / Font Awesome)</label>
                    <input type="text" class="form-control form-control-custom" id="icon_class" name="icon_class" required placeholder="e.g. bi-shield-lock or fas fa-user-shield">
                    <div class="form-text text-muted" style="font-size:0.7rem;">Use Bootstrap Icons (bi-*) or Font Awesome (fas fa-*). Example: <code>bi-shield-lock</code>, <code>bi-person-check</code>, <code>bi-car-front</code></div>
                </div>
                <div class="mb-3">
                    <label for="display_order" class="form-label text-secondary small">Display Order</label>
                    <input type="number" class="form-control form-control-custom" id="display_order" name="display_order" required min="0" value="0">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label text-secondary small">Description</label>
                    <textarea class="form-control form-control-custom" id="description" name="description" rows="3" placeholder="Description of sentry duties..."></textarea>
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
                $submitLabel = "Save Duty Type";
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
        modal = new bootstrap.Modal(document.getElementById('dutyTypeModal'));
        
        // Link color picker with text hex representation
        const colorPicker = document.getElementById('color_code');
        const colorHex = document.getElementById('color_hex');
        if (colorPicker && colorHex) {
            colorHex.value = colorPicker.value;
            colorPicker.addEventListener('input', () => {
                colorHex.value = colorPicker.value;
            });
        }
    });

    function openDutyTypeModal(data = null) {
        document.getElementById('duty_type_id').value = data ? data.duty_type_id : '';
        document.getElementById('duty_type_name').value = data ? data.duty_type_name : '';
        document.getElementById('duty_code').value = data ? data.duty_code : '';
        document.getElementById('color_code').value = data ? data.color_code : '#0d6efd';
        document.getElementById('color_hex').value = data ? data.color_code : '#0d6efd';
        document.getElementById('icon_class').value = data ? data.icon_class : 'bi-shield';
        document.getElementById('display_order').value = data ? data.display_order : '0';
        document.getElementById('description').value = data ? (data.description || '') : '';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('dutyTypeModalLabel').innerHTML = data ? '<i class="fas fa-pen-to-square me-2"></i> Edit Duty Type configuration' : '<i class="fas fa-plus me-2"></i> Register New Duty Type';
        modal.show();
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
