<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-stamp"></i> Roster Duty Approvals</h2>
        <p class="text-secondary">Review and authorize pending watch duty schedules submitted by SNCOs.</p>
    </div>
</div>

<?php if (empty($pendingRosters)): ?>
    <div class="glass-card p-5 text-center my-4">
        <div class="text-success mb-3" style="font-size: 3rem;">
            <i class="fas fa-circle-check"></i>
        </div>
        <h4 class="fw-bold text-dark">No Pending Roster Approvals</h4>
        <p class="text-secondary">All submitted duty rosters have been processed and approved.</p>
        <a href="<?= BASE_URL ?>/rosters" class="btn btn-custom btn-custom-secondary mt-2">
            <i class="fas fa-calendar-days"></i> View All Rosters
        </a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <?php foreach ($pendingRosters as $r): ?>
                <div class="glass-card mb-5 p-0 overflow-hidden" style="border-top: 4px solid var(--accent-indigo);">
                    <!-- Header -->
                    <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex flex-wrap justify-content-between align-items-center bg-light bg-opacity-50">
                        <div>
                            <h4 class="fw-bold mb-1 text-primary">Duty #<?= htmlspecialchars($r['roster_name']) ?></h4>
                            <div class="small text-secondary">
                                <i class="fas fa-campground me-1"></i> Camp: <strong><?= htmlspecialchars($r['camp_name']) ?></strong> &bull; 
                                <i class="fas fa-calendar-days me-1"></i> Duration: <strong><?= date('d-M-Y', strtotime($r['start_date'])) ?> to <?= date('d-M-Y', strtotime($r['end_date'])) ?></strong>
                            </div>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 border border-warning border-opacity-25 rounded-pill">
                                <i class="fas fa-clock-rotate-left me-1"></i> Pending Approval
                            </span>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4">
                        <div class="row g-3 mb-4 text-secondary small border-bottom pb-3 border-secondary border-opacity-10">
                            <div class="col-md-4">
                                <i class="fas fa-user-pen me-1"></i> Created/Assigned By: <strong class="text-dark"><?= htmlspecialchars($r['creator_rank'] . ' ' . $r['creator_name']) ?></strong>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-clock me-1"></i> Submitted At: <strong class="text-dark"><?= date('d-M-Y H:i', strtotime($r['updated_at'])) ?></strong>
                            </div>
                        </div>

                        <h5 class="fw-bold text-secondary mb-3"><i class="fas fa-shield-halved text-info me-2"></i> Watch Duty Entries</h5>
                        
                        <?php
                        // Group assignments by Date, Shift, and Duty Type
                        $groupedAssignments = [];
                        foreach ($r['assignments'] as $as) {
                            $key = $as['duty_date'] . '_' . $as['shift_id'] . '_' . $as['duty_type_id'];
                            if (!isset($groupedAssignments[$key])) {
                                $groupedAssignments[$key] = [
                                    'duty_date' => $as['duty_date'],
                                    'shift_name' => $as['shift_name'],
                                    'start_time' => $as['start_time'],
                                    'end_time' => $as['end_time'],
                                    'duty_type_name' => $as['duty_type_name'],
                                    'remarks' => $as['remarks'],
                                    'personnel' => []
                                ];
                            }
                            $groupedAssignments[$key]['personnel'][] = [
                                'service_number' => $as['service_number'],
                                'rank' => $as['rank'],
                                'full_name' => $as['full_name']
                            ];
                        }
                        ?>

                        <div class="row g-4">
                            <?php foreach ($groupedAssignments as $group): ?>
                                <div class="col-md-6 col-12">
                                    <div class="p-3 rounded border border-secondary border-opacity-10 bg-dark bg-opacity-10 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25"><?= htmlspecialchars($group['duty_type_name']) ?></span>
                                                <span class="small text-muted font-monospace"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($group['shift_name']) ?></span>
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-calendar-day text-secondary me-1"></i>
                                                <strong class="text-dark"><?= date('D, d-M-Y', strtotime($group['duty_date'])) ?></strong>
                                                <span class="small text-muted">(<?= date('H:i', strtotime($group['start_time'])) ?> - <?= date('H:i', strtotime($group['end_time'])) ?>)</span>
                                            </div>

                                            <div class="mt-3">
                                                <div class="text-secondary small fw-medium mb-1">Personnel Assigned:</div>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($group['personnel'] as $pers): ?>
                                                        <li class="d-flex align-items-center gap-2 mb-1 p-1 bg-white bg-opacity-50 rounded">
                                                            <span class="font-monospace small text-primary fw-bold"><?= htmlspecialchars($pers['service_number']) ?></span>
                                                            <span class="text-dark small"><?= htmlspecialchars($pers['rank'] . ' ' . $pers['full_name']) ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>

                                        <?php if ($group['remarks']): ?>
                                            <div class="mt-3 p-2 bg-warning bg-opacity-10 border-start border-warning rounded-end small text-secondary">
                                                <i class="fas fa-comment-dots text-warning me-1"></i>Remarks: <?= htmlspecialchars($group['remarks']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Approval Actions Form -->
                        <form action="<?= BASE_URL ?>/rosters/action" method="POST" class="approval-form mt-4 pt-4 border-top border-secondary border-opacity-10">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="roster_id" value="<?= $r['roster_id'] ?>">
                            
                            <div class="mb-4">
                                <label for="remarks_<?= $r['roster_id'] ?>" class="form-label text-secondary small fw-medium">Workflow Review Remarks / Notes</label>
                                <textarea class="form-control form-control-custom" id="remarks_<?= $r['roster_id'] ?>" name="remarks" rows="2" placeholder="Provide decision notes or reasons if rejecting or returning..."></textarea>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <button type="submit" name="action" value="Reject" class="btn btn-danger py-2.5 px-4">
                                    <i class="fas fa-xmark me-1"></i> ✖ Reject
                                </button>
                                <button type="submit" name="action" value="Return" class="btn btn-warning py-2.5 px-4 text-dark fw-medium">
                                    <i class="fas fa-arrow-rotate-left me-1"></i> ↩ Return to Draft
                                </button>
                                <button type="submit" name="action" value="Approve" class="btn btn-success py-2.5 px-4">
                                    <i class="fas fa-check me-1"></i> ✔ Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Confirmation JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const approvalForms = document.querySelectorAll('.approval-form');
    approvalForms.forEach(form => {
        const remarksInput = form.querySelector('textarea[name="remarks"]');
        
        const btnReject = form.querySelector('button[value="Reject"]');
        const btnReturn = form.querySelector('button[value="Return"]');
        const btnApprove = form.querySelector('button[value="Approve"]');

        let selectedAction = '';

        btnReject?.addEventListener('click', () => { selectedAction = 'Reject'; });
        btnReturn?.addEventListener('click', () => { selectedAction = 'Return'; });
        btnApprove?.addEventListener('click', () => { selectedAction = 'Approve'; });

        form.addEventListener('submit', (e) => {
            if (selectedAction === 'Approve') {
                const confirmApprove = confirm('Approve this duty roster?');
                if (!confirmApprove) {
                    e.preventDefault();
                }
            } else if (selectedAction === 'Reject') {
                let reason = remarksInput.value.trim();
                if (!reason) {
                    reason = prompt('Reject this duty roster?\nReason (Required):');
                    if (reason === null) {
                        e.preventDefault();
                        return;
                    }
                    reason = reason.trim();
                    if (!reason) {
                        alert('Reason is required to reject the roster.');
                        e.preventDefault();
                        return;
                    }
                    remarksInput.value = reason;
                } else {
                    const confirmReject = confirm('Reject this duty roster?');
                    if (!confirmReject) {
                        e.preventDefault();
                    }
                }
            } else if (selectedAction === 'Return') {
                let remarks = remarksInput.value.trim();
                if (!remarks) {
                    remarks = prompt('Return this roster to Draft?\nRemarks (Required):');
                    if (remarks === null) {
                        e.preventDefault();
                        return;
                    }
                    remarks = remarks.trim();
                    if (!remarks) {
                        alert('Remarks are required to return the roster.');
                        e.preventDefault();
                        return;
                    }
                    remarksInput.value = remarks;
                } else {
                    const confirmReturn = confirm('Return this roster to Draft?');
                    if (!confirmReturn) {
                        e.preventDefault();
                    }
                }
            }
        });
    });
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
