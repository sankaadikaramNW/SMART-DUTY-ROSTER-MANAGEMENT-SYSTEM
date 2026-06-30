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
                                    'duty_date'      => $as['duty_date'],
                                    'shift_name'     => $as['shift_name'],
                                    'start_time'     => $as['start_time'],
                                    'end_time'       => $as['end_time'],
                                    'duty_type_name' => $as['duty_type_name'],
                                    'remarks'        => $as['remarks'],
                                    'personnel'      => []
                                ];
                            }
                            $groupedAssignments[$key]['personnel'][] = [
                                'assignment_id'  => $as['assignment_id'],
                                'service_number' => $as['service_number'],
                                'rank'           => $as['rank'],
                                'full_name'      => $as['full_name'],
                                'status'         => $as['status'],
                                'supervisor_remarks' => $as['supervisor_remarks'] ?? ''
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
                                                        <li class="d-flex align-items-center justify-content-between gap-2 mb-2 p-2 bg-white bg-opacity-50 rounded">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="font-monospace small text-primary fw-bold"><?= htmlspecialchars($pers['service_number']) ?></span>
                                                                <span class="text-dark small"><?= htmlspecialchars($pers['rank'] . ' ' . $pers['full_name']) ?></span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                                <?php if ($pers['status'] === 'Pending'): ?>
                                                                    <!-- Approve Button (direct form submit) -->
                                                                    <form action="<?= BASE_URL ?>/rosters/assignment-action" method="POST" class="d-inline">
                                                                        <?= Security::csrfField() ?>
                                                                        <input type="hidden" name="assignment_id" value="<?= (int)$pers['assignment_id'] ?>">
                                                                        <input type="hidden" name="roster_id" value="<?= (int)$r['roster_id'] ?>">
                                                                        <input type="hidden" name="status" value="Approved">
                                                                        <input type="hidden" name="supervisor_remarks" value="">
                                                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2" style="font-size: 0.72rem;" title="Approve this duty assignment">
                                                                            <i class="fas fa-check"></i> Approve
                                                                        </button>
                                                                    </form>
                                                                    <!-- Reject Button (triggers modal) -->
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger py-1 px-2"
                                                                        style="font-size: 0.72rem;"
                                                                        title="Reject this duty assignment"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#rejectModal"
                                                                        data-assignment-id="<?= (int)$pers['assignment_id'] ?>"
                                                                        data-roster-id="<?= (int)$r['roster_id'] ?>"
                                                                        data-person="<?= htmlspecialchars($pers['rank'] . ' ' . $pers['full_name']) ?>">
                                                                        <i class="fas fa-xmark"></i> Reject
                                                                    </button>
                                                                <?php else: ?>
                                                                    <?php 
                                                                    $badgeClass = $pers['status'] === 'Approved' ? 'bg-success' : 'bg-danger';
                                                                    ?>
                                                                    <span class="badge <?= $badgeClass ?> px-2 py-1 rounded small" style="font-size: 0.65rem;">
                                                                        <?= htmlspecialchars($pers['status']) ?>
                                                                    </span>
                                                                    <?php if ($pers['supervisor_remarks']): ?>
                                                                        <span class="small text-muted" style="font-size: 0.7rem;" title="<?= htmlspecialchars($pers['supervisor_remarks']) ?>">
                                                                            <i class="fas fa-comment-dots text-warning"></i>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
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
                    </div>
                    <!-- Card Footer: Roster Workflow Actions -->
                    <div class="card-footer border-top border-secondary border-opacity-10 bg-dark bg-opacity-25 p-4">
                        <form action="<?= BASE_URL ?>/rosters/action" method="POST" class="w-100">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="roster_id" value="<?= $r['roster_id'] ?>">
                            
                            <div class="row align-items-end g-3">
                                <div class="col-lg-6 col-md-12">
                                    <label for="remarks_<?= $r['roster_id'] ?>" class="form-label text-secondary small">
                                        <i class="fas fa-comment-dots text-warning me-1"></i> OCPROVST Remarks / Feedback (Required for Rejection/Return)
                                    </label>
                                    <textarea class="form-control form-control-custom" id="remarks_<?= $r['roster_id'] ?>" name="remarks" rows="2" placeholder="Enter remarks or instructions for the SNCO..."></textarea>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="d-flex flex-wrap justify-content-sm-end gap-2">
                                        <button type="submit" name="action" value="Reject" class="btn btn-custom btn-custom-danger flex-grow-1 flex-sm-grow-0">
                                            <i class="fas fa-circle-xmark"></i> Reject
                                        </button>
                                        <button type="submit" name="action" value="Return" class="btn btn-custom btn-custom-warning flex-grow-1 flex-sm-grow-0">
                                            <i class="fas fa-rotate-left"></i> Return Draft
                                        </button>
                                        <button type="submit" name="action" value="Approve" class="btn btn-custom btn-custom-success flex-grow-1 flex-sm-grow-0">
                                            <i class="fas fa-circle-check"></i> Approve
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/rosters/assignment-action" method="POST" id="rejectForm" class="modal-content glass-card bg-dark text-light border-secondary">
            <?= Security::csrfField() ?>
            <input type="hidden" name="assignment_id" id="rejectAssignmentId" value="">
            <input type="hidden" name="roster_id" id="rejectRosterId" value="">
            <input type="hidden" name="status" value="Rejected">
            <div class="modal-header border-secondary bg-danger bg-opacity-25">
                <h5 class="modal-title fw-bold text-white" id="rejectModalLabel">
                    <i class="fas fa-xmark-circle me-2 text-danger"></i> Reject Duty Assignment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-secondary mb-1">Rejecting assignment for:</p>
                    <p class="fw-bold text-light" id="rejectPersonName"></p>
                </div>
                <div class="mb-3">
                    <label for="rejectReasonInput" class="form-label text-secondary small">
                        Reason for Rejection <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control form-control-custom" name="supervisor_remarks" id="rejectReasonInput" rows="3" required placeholder="Describe the reason for rejecting this watch duty assignment..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <?php
                $submitLabel = "Confirm Rejection";
                $submitClass = "btn-custom-danger";
                $submitId = "confirmRejectBtn";
                $submitIcon = "fas fa-circle-xmark";
                $cancelIcon = "fas fa-xmark";
                include __DIR__ . '/../components/form-buttons.php';
                ?>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Populate reject modal with assignment details
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const assignmentId = button.getAttribute('data-assignment-id');
            const rosterId     = button.getAttribute('data-roster-id');
            const personName   = button.getAttribute('data-person');

            document.getElementById('rejectAssignmentId').value = assignmentId;
            document.getElementById('rejectRosterId').value     = rosterId;
            document.getElementById('rejectPersonName').textContent = personName;
            document.getElementById('rejectReasonInput').value  = '';
        });
    }

    // Validate reject form before submit
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', (e) => {
            const reason = document.getElementById('rejectReasonInput').value.trim();
            if (!reason) {
                e.preventDefault();
                document.getElementById('rejectReasonInput').classList.add('is-invalid');
                return;
            }
            document.getElementById('rejectReasonInput').classList.remove('is-invalid');
            document.getElementById('confirmRejectBtn').disabled = true;
            document.getElementById('confirmRejectBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Rejecting...';
        });

        document.getElementById('rejectReasonInput').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
