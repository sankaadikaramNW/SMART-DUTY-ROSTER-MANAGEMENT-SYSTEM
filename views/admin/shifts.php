<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-clock"></i> Manage Duty Shifts</h2>
        <p class="text-secondary">System configurations for guard rotation shifts.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-custom btn-custom-primary" onclick="openShiftModal();">
            <i class="fas fa-plus"></i> Add New Shift
        </button>
    </div>
</div>

<div class="glass-card p-4">
    <div class="table-custom-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Shift Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration (hrs)</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shifts)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No duty shifts registered.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($shifts as $s): ?>
                        <tr>
                            <td class="fw-bold text-info"><?= htmlspecialchars($s['shift_name']) ?></td>
                            <td class="font-monospace"><?= htmlspecialchars($s['start_time']) ?></td>
                            <td class="font-monospace"><?= htmlspecialchars($s['end_time']) ?></td>
                            <td><?= number_format($s['duration_hours'], 2) ?></td>
                            <td class="small text-secondary"><?= htmlspecialchars($s['description'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $s['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $s['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $s['status'] === 'Active' ? 'success' : 'secondary' ?> px-2">
                                    <?= $s['status'] ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2" 
                                        onclick='openShiftModal(<?= json_encode($s) ?>);'>
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

<!-- Shift Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="shiftModalLabel">Configure Shift</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/shifts/save" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" id="shift_id" name="shift_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="shift_name" class="form-label text-secondary small">Shift Name</label>
                        <input type="text" class="form-control form-control-custom" id="shift_name" name="shift_name" required placeholder="e.g. Night Shift">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label text-secondary small">Start Time</label>
                            <input type="time" class="form-control form-control-custom" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label text-secondary small">End Time</label>
                            <input type="time" class="form-control form-control-custom" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="duration_hours" class="form-label text-secondary small">Duration (Hours)</label>
                        <input type="number" step="0.25" class="form-control form-control-custom" id="duration_hours" name="duration_hours" required placeholder="e.g. 8">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label text-secondary small">Description</label>
                        <textarea class="form-control form-control-custom" id="description" name="description" rows="2" placeholder="Sentry duty description..."></textarea>
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
                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom btn-custom-primary">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let modal;
    document.addEventListener('DOMContentLoaded', () => {
        modal = new bootstrap.Modal(document.getElementById('shiftModal'));
    });

    function openShiftModal(data = null) {
        document.getElementById('shift_id').value = data ? data.shift_id : '';
        document.getElementById('shift_name').value = data ? data.shift_name : '';
        document.getElementById('start_time').value = data ? data.start_time : '';
        document.getElementById('end_time').value = data ? data.end_time : '';
        document.getElementById('duration_hours').value = data ? data.duration_hours : '';
        document.getElementById('description').value = data ? (data.description || '') : '';
        document.getElementById('status').value = data ? data.status : 'Active';

        document.getElementById('shiftModalLabel').innerHTML = data ? '<i class="fas fa-pen-to-square me-2"></i> Edit Shift configuration' : '<i class="fas fa-plus me-2"></i> Register New Shift';
        modal.show();
    }
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
