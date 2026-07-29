<?php
include __DIR__ . '/../layout/header.php';
?>

<!-- ===== WELCOME HEADER ===== -->
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-8 col-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-stamp"></i> Roster Duty Approvals</h2>
        <p class="text-secondary mb-0">Perform bulk crew reviews, inspect personnel warnings, and authorize guard duty rosters.</p>
    </div>
    <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
        <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-custom btn-custom-secondary btn-sm">
            <i class="fas fa-calendar-alt me-1"></i> Watch Calendar
        </a>
    </div>
</div>

<?php if (empty($dutyCrews)): ?>
    <div class="glass-card p-5 text-center my-4 animate-fade-in">
        <div class="text-success mb-3" style="font-size: 3.5rem;">
            <i class="fas fa-circle-check"></i>
        </div>
        <h4 class="fw-bold text-dark">No Pending Duty Rosters</h4>
        <p class="text-secondary">All submitted duty rosters have been processed.</p>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-custom btn-custom-primary mt-2 px-4 py-2">
            <i class="fas fa-chart-line me-1"></i> Go to Dashboard
        </a>
    </div>
<?php else: ?>
    <!-- ===== BULK PIPELINE CONTROL ACTION BAR ===== -->
    <div class="glass-card p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate-fade-in bg-light bg-opacity-50">
        <div class="d-flex align-items-center gap-2">
            <div class="form-check fs-6 mb-0">
                <input class="form-check-input border-secondary" type="checkbox" id="selectAllCrews">
                <label class="form-check-label text-dark fw-bold" for="selectAllCrews">Select All</label>
            </div>
            <span class="text-secondary small font-monospace" id="selectedCountBadge">(0 selected)</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm px-3 py-2" id="btnBulkApprove">
                <i class="fas fa-check-double me-1"></i> Bulk Approve
            </button>
            <button type="button" class="btn btn-danger btn-sm px-3 py-2" id="btnBulkReject">
                <i class="fas fa-circle-xmark me-1"></i> Bulk Reject
            </button>
        </div>
    </div>

    <!-- ===== DUTY ROSTER GRID TABLE ===== -->
    <div class="glass-card p-0 overflow-hidden animate-fade-in">
        <div class="table-custom-container">
            <table class="table-custom text-dark" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="width: 3%;"></th> <!-- Checkbox -->
                        <th style="width: 3%;"></th> <!-- Accordion Toggle -->
                        <th style="width: 14%;">Roster ID</th>
                        <th style="width: 10%;">Duty Date</th>
                        <th style="width: 10%;">Shift</th>
                        <th style="width: 12%;">Duty Type</th>
                        <th style="width: 10%;">Location</th>
                        <th style="width: 8%;" class="text-center">Assigned</th>
                        <th style="width: 14%;">Submitted By</th>
                        <th style="width: 10%;" class="text-center">Warnings</th>
                        <th style="width: 6%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dutyCrews as $key => $crew): ?>
                        <!-- Main Crew Row -->
                        <tr class="crew-main-row bg-white align-middle" data-crew-key="<?= htmlspecialchars($key) ?>" style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <!-- Selection Checkbox -->
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input border-secondary crew-selector" data-crew-key="<?= htmlspecialchars($key) ?>">
                            </td>
                            <!-- Accordion Toggle -->
                            <td class="text-center">
                                <button type="button" class="btn btn-xs p-1 text-primary btn-toggle-accordion" style="font-size: 0.95rem; border: none; background: transparent;">
                                    <i class="fas fa-chevron-right transition-transform" id="icon-<?= htmlspecialchars($key) ?>"></i>
                                </button>
                            </td>
                            <!-- Crew ID -->
                            <td class="font-monospace text-primary fw-bold" style="font-size:0.75rem;">ROSTER-<?= htmlspecialchars($key) ?></td>
                            <!-- Duty Date -->
                            <td class="fw-semibold text-dark"><?= date('D, d M Y', strtotime($crew['duty_date'])) ?></td>
                            <!-- Shift -->
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($crew['shift_name']) ?></span>
                                <div class="text-secondary" style="font-size:0.72rem;"><?= substr($crew['start_time'], 0, 5) ?> - <?= substr($crew['end_time'], 0, 5) ?></div>
                            </td>
                            <!-- Duty Type -->
                            <td>
                                <span class="badge d-inline-flex align-items-center gap-1 px-2.5 py-1 text-dark" style="background: <?= htmlspecialchars($crew['color_code']) ?>16; border: 1px solid <?= htmlspecialchars($crew['color_code']) ?>44; color: <?= htmlspecialchars($crew['color_code']) ?> !important; font-size:0.75rem;">
                                    <i class="<?= htmlspecialchars($crew['icon_class']) ?>"></i> <?= htmlspecialchars($crew['duty_type_name']) ?>
                                </span>
                            </td>
                            <!-- Location -->
                            <td class="text-dark"><i class="fas fa-campground text-muted me-1"></i> <?= htmlspecialchars($crew['camp_name']) ?></td>
                            <!-- Total Assigned -->
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-dark border px-2.5 py-1 rounded-pill fw-bold">
                                    <?= count($crew['personnel']) ?> Airmen
                                </span>
                            </td>
                            <!-- Submitted By -->
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($crew['creator_rank'] . ' ' . $crew['creator_name']) ?></div>
                                <div class="text-secondary font-monospace" style="font-size:0.7rem;"><?= date('d M, H:i', strtotime($crew['submission_time'])) ?></div>
                            </td>
                            <!-- Conflict Warnings -->
                            <td class="text-center">
                                <?php if ($crew['duplicate_warnings_count'] > 0): ?>
                                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 rounded animate-pulse">
                                        <i class="fas fa-triangle-exclamation"></i> <?= $crew['duplicate_warnings_count'] ?> Conflict(s)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                                        🟢 Clean
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- Current Status -->
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2.5 py-1">
                                    Pending
                                </span>
                            </td>
                        </tr>

                        <!-- Accordion Detail Row -->
                        <tr class="crew-detail-row bg-light" id="detail-<?= htmlspecialchars($key) ?>" style="display: none;">
                            <td colspan="11" class="p-3">
                                <div class="p-3 rounded border border-light bg-white bg-opacity-75 shadow-sm">
                                    <h6 class="fw-bold text-primary mb-2.5"><i class="fas fa-users-gear me-1"></i> Roster Personnel Details</h6>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-sm small mb-0 align-middle text-dark">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 12%;">Service Number</th>
                                                    <th style="width: 20%;">Rank & Full Name</th>
                                                    <th style="width: 15%;">Trade</th>
                                                    <th style="width: 15%;">Section</th>
                                                    <th style="width: 15%;">Last Guard Duty</th>
                                                    <th style="width: 12%;">Personnel Status</th>
                                                    <th style="width: 11%;">Status Warnings</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($crew['personnel'] as $p): ?>
                                                    <tr class="<?= $p['is_conflict'] ? 'table-warning bg-opacity-10' : '' ?>">
                                                        <td class="font-monospace text-muted fw-bold"><?= htmlspecialchars($p['service_number']) ?></td>
                                                        <td class="fw-bold"><?= htmlspecialchars($p['rank'] . ' ' . $p['full_name']) ?></td>
                                                        <td><?= htmlspecialchars($p['trade']) ?></td>
                                                        <td><?= htmlspecialchars($p['section'] ?: '—') ?></td>
                                                        <td class="font-monospace"><?= htmlspecialchars($p['prev_duty_date']) ?></td>
                                                        <td>
                                                            <span class="badge <?= $p['personnel_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 text-dark-50 border">
                                                                <?= htmlspecialchars($p['personnel_status']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($p['is_double_booked']): ?>
                                                                <span class="text-danger small fw-bold d-block"><i class="fas fa-triangle-exclamation"></i> Double Booked</span>
                                                            <?php endif; ?>
                                                            <?php if ($p['personnel_status'] === 'Leave'): ?>
                                                                <span class="text-danger small fw-bold d-block"><i class="fas fa-plane-departure"></i> On Leave</span>
                                                            <?php elseif ($p['personnel_status'] === 'Inactive'): ?>
                                                                <span class="text-danger small fw-bold d-block"><i class="fas fa-ban"></i> Inactive Status</span>
                                                            <?php endif; ?>
                                                            <?php if (!$p['is_conflict']): ?>
                                                                <span class="text-success small">🟢 No warnings</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if ($crew['remarks']): ?>
                                        <div class="mt-2.5 p-2 bg-light border rounded small text-secondary">
                                            <strong>SNCO Remarks:</strong> <em>"<?= htmlspecialchars($crew['remarks']) ?>"</em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- ===== INTERACTIVE BULK JS SCRIPT ===== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Accordion Toggle handlers
    document.querySelectorAll('.btn-toggle-accordion').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tr = btn.closest('.crew-main-row');
            const key = tr.getAttribute('data-crew-key');
            const detailRow = document.getElementById(`detail-${key}`);
            const icon = btn.querySelector('i');

            if (detailRow.style.display === 'none') {
                detailRow.style.display = 'table-row';
                icon.style.transform = 'rotate(90deg)';
                icon.className = 'fas fa-chevron-right text-warning';
            } else {
                detailRow.style.display = 'none';
                icon.style.transform = '';
                icon.className = 'fas fa-chevron-right';
            }
        });
    });

    // Checkbox selectors
    const selectAllCheckbox = document.getElementById('selectAllCrews');
    const crewSelectors = document.querySelectorAll('.crew-selector');
    const countBadge = document.getElementById('selectedCountBadge');

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.crew-selector:checked').length;
        if (countBadge) {
            countBadge.textContent = `(${checkedCount} selected)`;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const isChecked = selectAllCheckbox.checked;
            crewSelectors.forEach(cb => {
                cb.checked = isChecked;
            });
            updateSelectedCount();
        });
    }

    crewSelectors.forEach(cb => {
        cb.addEventListener('change', () => {
            updateSelectedCount();
            // sync selectAll state
            const allChecked = document.querySelectorAll('.crew-selector:checked').length === crewSelectors.length;
            selectAllCheckbox.checked = allChecked;
        });
    });

    // Bulk Approve action handler
    document.getElementById('btnBulkApprove')?.addEventListener('click', () => {
        const selected = Array.from(document.querySelectorAll('.crew-selector:checked')).map(cb => cb.getAttribute('data-crew-key'));

        if (selected.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select at least one duty roster to approve.',
                icon: 'warning',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        Swal.fire({
            title: 'Bulk Approve Rosters',
            text: `Are you sure you want to approve the selected ${selected.length} duty roster(s)? This will publish their watch assignments immediately.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve Selected',
            input: 'textarea',
            inputPlaceholder: 'Enter authorization directives or remarks (optional)...',
            inputAttributes: {
                'aria-label': 'Remarks'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const remarks = result.value || '';
                
                // Show loading
                Swal.fire({
                    title: 'Processing Approvals',
                    text: 'Updating watch assignments and database logs...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`${BASE_URL}/rosters/bulk-approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        crews: selected,
                        remarks: remarks,
                        csrf_token: CSRF_TOKEN
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success',
                            text: data.message || 'Duty rosters approved and published successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.error || 'Failed to process bulk approval.', 'error');
                    }
                })
                .catch(err => {
                    console.error("AJAX Error bulk approve:", err);
                    Swal.fire('Error', 'An unexpected connection failure occurred.', 'error');
                });
            }
        });
    });

    // Bulk Reject action handler
    document.getElementById('btnBulkReject')?.addEventListener('click', () => {
        const selected = Array.from(document.querySelectorAll('.crew-selector:checked')).map(cb => cb.getAttribute('data-crew-key'));

        if (selected.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select at least one duty roster to reject.',
                icon: 'warning',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        Swal.fire({
            title: 'Bulk Reject Rosters',
            text: `Are you sure you want to reject the selected ${selected.length} duty roster(s)? Rejection remarks are mandatory.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Reject Selected',
            input: 'textarea',
            inputPlaceholder: 'Enter mandatory correction directives or rejection remarks...',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'You must enter rejection remarks before proceeding!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const remarks = result.value;

                // Show loading
                Swal.fire({
                    title: 'Processing Rejections',
                    text: 'Returning watch assignments to originating SNCOs...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`${BASE_URL}/rosters/bulk-reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        crews: selected,
                        remarks: remarks,
                        csrf_token: CSRF_TOKEN
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Returned',
                            text: data.message || 'Selected duty rosters have been returned to SNCO.',
                            icon: 'info',
                            confirmButtonColor: '#0ea5e9'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.error || 'Failed to process bulk rejection.', 'error');
                    }
                })
                .catch(err => {
                    console.error("AJAX Error bulk reject:", err);
                    Swal.fire('Error', 'An unexpected connection failure occurred.', 'error');
                });
            }
        });
    });
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
