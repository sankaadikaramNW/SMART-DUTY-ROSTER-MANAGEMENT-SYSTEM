<?php
include __DIR__ . '/../layout/header.php';

$fromCampId = (int)$transfer['from_camp_id'];
$toCampId = (int)$transfer['to_camp_id'];
$status = $transfer['status'];
$userId = (int)Session::get('user_id');
$userCampId = (int)Session::get('camp_id');

// Determine what actions are available based on role and status
$canEdit = ($roleName === 'Administrator' || (($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') && $userCampId === $fromCampId)) 
           && ($status === 'Draft' || $status === 'Returned for Correction');

$canCancel = ($roleName === 'Administrator' || (($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') && $userCampId === $fromCampId)) 
             && ($status === 'Draft' || $status === 'Returned for Correction' || $status === 'Pending Origin Approval');

$canOriginApprove = ($roleName === 'Administrator' || ($roleName === 'OCPROVST' && $userCampId === $fromCampId)) 
                    && ($status === 'Pending Origin Approval');

$canDestinationSubmit = ($roleName === 'Administrator' || (($roleName === 'SNCO' || $roleName === 'Warrant Officer IC') && $userCampId === $toCampId)) 
                       && ($status === 'Pending Destination Review');

$canDestinationApprove = ($roleName === 'Administrator' || ($roleName === 'OCPROVST' && $userCampId === $toCampId)) 
                        && ($status === 'Pending Destination Approval');

$canAdminOverride = ($roleName === 'Administrator' && !in_array($status, ['Transfer Completed', 'Rejected', 'Cancelled']));

$badgeColor = 'secondary';
switch ($status) {
    case 'Draft': $badgeColor = 'secondary'; break;
    case 'Pending Origin Approval': $badgeColor = 'warning'; break;
    case 'Origin Approved': $badgeColor = 'info'; break;
    case 'Pending Destination Review': $badgeColor = 'info'; break;
    case 'Pending Destination Approval': $badgeColor = 'primary'; break;
    case 'Transfer Completed': $badgeColor = 'success'; break;
    case 'Returned for Correction': $badgeColor = 'dark'; break;
    case 'Rejected': $badgeColor = 'danger'; break;
    case 'Cancelled': $badgeColor = 'secondary'; break;
}
?>

<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-circle-info"></i> Transfer Request Details</h2>
        <p class="text-secondary">Review request details, workflow history, and perform approvals.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="<?= BASE_URL ?>/transfers" class="btn btn-custom btn-custom-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Details & Actions -->
    <div class="col-lg-8">
        
        <!-- Details Card -->
        <div class="glass-card mb-4 animate-fade-in">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-id-card text-primary me-2"></i> Personnel & Transfer Info</h5>
                <span class="badge rounded-pill bg-<?= $badgeColor ?> bg-opacity-25 border border-<?= $badgeColor ?> border-opacity-25 text-<?= $badgeColor ?> px-3 py-2 fs-7">
                    <?= $status ?>
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Service Number</span>
                        <span class="fw-bold fs-5 text-dark"><?= htmlspecialchars($transfer['service_number']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Rank & Name</span>
                        <span class="fw-bold fs-5 text-dark">
                            <span class="text-info"><?= htmlspecialchars($transfer['rank_short_name']) ?></span> 
                            <?= htmlspecialchars($transfer['initials'] . ' ' . $transfer['full_name']) ?>
                        </span>
                    </div>
                    
                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Origin Camp (Current Base)</span>
                        <span class="fw-semibold text-dark"><i class="fas fa-campground text-secondary me-2"></i> <?= htmlspecialchars($transfer['from_camp_name']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Destination Camp (New Base)</span>
                        <span class="fw-bold text-primary"><i class="fas fa-campground text-primary me-2"></i> <?= htmlspecialchars($transfer['to_camp_name']) ?></span>
                    </div>

                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Transfer Effective Date</span>
                        <span class="fw-medium text-dark font-monospace"><i class="fas fa-calendar-day me-2"></i> <?= date('Y-m-d', strtotime($transfer['effective_date'])) ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-secondary small d-block">Supporting Document</span>
                        <?php if ($transfer['supporting_documents']): ?>
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($transfer['supporting_documents']) ?>" target="_blank" class="btn btn-sm btn-custom btn-custom-secondary mt-1 py-1">
                                <i class="fas fa-file-pdf text-danger"></i> View Document
                            </a>
                        <?php else: ?>
                            <span class="text-muted small italic mt-1 d-inline-block">No supporting document uploaded</span>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-12">
                        <span class="text-secondary small d-block">Reason for Transfer</span>
                        <div class="p-3 bg-dark bg-opacity-5 border border-secondary border-opacity-10 rounded text-dark fs-6 mt-1">
                            <?= nl2br(htmlspecialchars($transfer['reason'])) ?>
                        </div>
                    </div>

                    <?php if ($transfer['remarks']): ?>
                    <div class="col-md-12">
                        <span class="text-secondary small d-block">Initiator Remarks</span>
                        <span class="text-dark small"><?= htmlspecialchars($transfer['remarks']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Edit Draft / Returned Form (If Authorized) -->
        <?php if ($canEdit): ?>
        <div class="glass-card mb-4 animate-fade-in" style="border-left: 4px solid var(--accent-indigo);">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-pen-to-square text-indigo me-2"></i> Modify Transfer Details & Submit</h5>
            </div>
            <form action="<?= BASE_URL ?>/transfers/edit" method="POST" enctype="multipart/form-data" class="card-body p-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="transfer_id" value="<?= $transfer['transfer_id'] ?>">
                <input type="hidden" id="submit_action_val" name="submit_action" value="save_draft">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="to_camp_id" class="form-label text-secondary small fw-bold">Destination Camp/Base</label>
                        <select class="form-select form-control-custom" id="to_camp_id" name="to_camp_id" required>
                            <?php foreach ($camps as $c): ?>
                                <option value="<?= $c['camp_id'] ?>" <?= $c['camp_id'] === $toCampId ? 'selected' : '' ?>><?= htmlspecialchars($c['camp_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="effective_date" class="form-label text-secondary small fw-bold">Transfer Effective Date</label>
                        <input type="date" class="form-control form-control-custom" id="effective_date" name="effective_date" value="<?= date('Y-m-d', strtotime($transfer['effective_date'])) ?>" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="supporting_document" class="form-label text-secondary small fw-bold">Replace Supporting Document (Optional)</label>
                        <input type="file" class="form-control form-control-custom" id="supporting_document" name="supporting_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>

                    <div class="col-md-12">
                        <label for="reason" class="form-label text-secondary small fw-bold">Reason for Transfer</label>
                        <textarea class="form-control form-control-custom" id="reason" name="reason" rows="3" required><?= htmlspecialchars($transfer['reason']) ?></textarea>
                    </div>

                    <div class="col-md-12">
                        <label for="remarks" class="form-label text-secondary small fw-bold">Initiator Remarks / Correction Notes</label>
                        <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="2" placeholder="Any correction details..."><?= htmlspecialchars($transfer['remarks']) ?></textarea>
                    </div>

                    <div class="col-md-12 d-flex justify-content-between mt-4">
                        <button type="submit" onclick="document.getElementById('submit_action_val').value='save_draft';" class="btn btn-custom btn-custom-secondary">
                            <i class="fas fa-floppy-disk text-info"></i> Update Draft
                        </button>
                        <button type="submit" onclick="document.getElementById('submit_action_val').value='submit_request';" class="btn btn-custom btn-custom-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Approval / Action Forms -->
        <?php if ($canOriginApprove || $canDestinationApprove): ?>
        <div class="glass-card mb-4 animate-fade-in" style="border-left: 4px solid var(--accent-indigo);">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-stamp text-indigo me-2"></i> 
                    <?= $canOriginApprove ? 'Origin Camp Approval Review' : 'Destination Camp Final Approval Review' ?>
                </h5>
            </div>
            <form action="<?= BASE_URL ?>/transfers/action" method="POST" class="card-body p-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="transfer_id" value="<?= $transfer['transfer_id'] ?>">
                
                <div class="mb-3">
                    <label for="remarks" class="form-label text-secondary small fw-bold">Reviewer Remarks</label>
                    <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="3" placeholder="Provide notes for this decision..." required></textarea>
                </div>

                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <button type="submit" name="action" value="Return" class="btn btn-custom btn-custom-secondary bg-opacity-25 text-dark border-dark">
                        <i class="fas fa-arrow-rotate-left"></i> Return for Correction
                    </button>
                    <button type="submit" name="action" value="Reject" class="btn btn-custom btn-custom-secondary bg-opacity-25 text-danger border-danger">
                        <i class="fas fa-circle-xmark"></i> Reject Transfer
                    </button>
                    <button type="submit" name="action" value="Approve" class="btn btn-custom btn-custom-primary">
                        <i class="fas fa-circle-check"></i> Approve Transfer
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Destination SNCO Review & Submit Form -->
        <?php if ($canDestinationSubmit): ?>
        <div class="glass-card mb-4 animate-fade-in" style="border-left: 4px solid var(--accent-indigo);">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-circle-nodes text-indigo me-2"></i> Review Incoming Transfer & Forward</h5>
            </div>
            <form action="<?= BASE_URL ?>/transfers/action" method="POST" class="card-body p-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="transfer_id" value="<?= $transfer['transfer_id'] ?>">
                <input type="hidden" name="action" value="Submit">
                
                <p class="text-secondary small mb-3">You have received this incoming transfer request. Please verify the personnel record, add any destination base remarks, and forward it to the <strong>OCPROVST of <?= htmlspecialchars($transfer['to_camp_name']) ?></strong> for final authorization.</p>

                <div class="mb-3">
                    <label for="remarks" class="form-label text-secondary small fw-bold">Destination SNCO Review Remarks</label>
                    <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="3" placeholder="E.g., Verified duty strength, accommodation, and trade requirements at Ekala. Forwarded for approval." required></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-custom btn-custom-primary">
                        <i class="fas fa-share-from-square"></i> Forward to OCPROVST
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Admin Override Actions -->
        <?php if ($canAdminOverride): ?>
        <div class="glass-card mb-4 animate-fade-in" style="border-left: 4px solid var(--accent-indigo);">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-shield text-indigo me-2"></i> Administrative Workflow Override</h5>
            </div>
            <form action="<?= BASE_URL ?>/transfers/action" method="POST" class="card-body p-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="transfer_id" value="<?= $transfer['transfer_id'] ?>">
                
                <p class="text-secondary small mb-3">As system administrator, you can perform administrative overrides. The actions will bypass the remaining workflow stages and immediately execute in the system database.</p>

                <div class="mb-3">
                    <label for="remarks" class="form-label text-secondary small fw-bold">Override Justification</label>
                    <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="2" placeholder="Administrative override rationale..." required></textarea>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" name="action" value="Cancel" class="btn btn-custom btn-custom-secondary text-secondary">
                        <i class="fas fa-ban"></i> Cancel Request
                    </button>
                    <button type="submit" name="action" value="Override" class="btn btn-custom btn-custom-primary bg-danger border-danger">
                        <i class="fas fa-bolt"></i> Bypass Workflow & Complete
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Cancel Request (SNCO / Creator) -->
        <?php if ($canCancel && !$canEdit): ?>
        <div class="glass-card mb-4 p-4 d-flex justify-content-between align-items-center animate-fade-in" style="border-left: 4px solid #ef4444;">
            <div>
                <h6 class="fw-bold text-dark mb-1">Cancel Transfer Request</h6>
                <p class="text-secondary mb-0 small">This request is still pending review. You can cancel it to remove it from the workflow.</p>
            </div>
            <form action="<?= BASE_URL ?>/transfers/cancel" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="transfer_id" value="<?= $transfer['transfer_id'] ?>">
                <button type="submit" class="btn btn-sm btn-custom btn-custom-secondary text-danger border-danger" onclick="return confirm('Are you sure you want to cancel this transfer request?');">
                    <i class="fas fa-ban"></i> Cancel Request
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Column: Workflow History Timeline -->
    <div class="col-lg-4">
        <div class="glass-card animate-fade-in h-100">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-timeline text-indigo me-2"></i> Workflow History</h5>
            </div>
            <div class="card-body p-4">
                <div class="workflow-timeline position-relative">
                    <?php if (empty($approvals)): ?>
                        <p class="text-muted small">No workflow actions recorded yet.</p>
                    <?php else: ?>
                        <?php foreach ($approvals as $idx => $a): ?>
                            <?php
                            $actionColor = 'secondary';
                            switch ($a['action']) {
                                case 'Submit': $actionColor = 'primary'; break;
                                case 'Approve': $actionColor = 'success'; break;
                                case 'Reject': $actionColor = 'danger'; break;
                                case 'Return': $actionColor = 'warning'; break;
                                case 'Cancel': $actionColor = 'dark'; break;
                                case 'Override': $actionColor = 'danger'; break;
                            }
                            ?>
                            <div class="timeline-item mb-4 position-relative ps-4" style="border-left: 2px solid rgba(255,255,255,0.08); padding-bottom: 2px;">
                                <!-- Timeline icon/dot -->
                                <div class="timeline-dot position-absolute bg-<?= $actionColor ?>" style="width: 12px; height: 12px; border-radius: 50%; left: -7px; top: 6px;"></div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge rounded-pill bg-<?= $actionColor ?> bg-opacity-25 text-<?= $actionColor ?> border border-<?= $actionColor ?> border-opacity-25 px-2 py-0.5 small" style="font-size: 0.65rem;">
                                        <?= $a['action'] ?>
                                    </span>
                                    <span class="small text-muted font-monospace" style="font-size: 0.7rem;"><?= date('M d, H:i', strtotime($a['created_at'])) ?></span>
                                </div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.85rem;">
                                    <?= htmlspecialchars($a['rank_short_name'] . ' ' . $a['initials'] . ' ' . $a['full_name']) ?>
                                </div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    Role: <?= htmlspecialchars($a['action_role']) ?>
                                </div>
                                <?php if ($a['remarks']): ?>
                                    <div class="mt-1 small bg-dark bg-opacity-5 p-2 rounded text-dark italic border border-secondary border-opacity-5" style="font-size:0.8rem; font-style: italic;">
                                        &ldquo;<?= htmlspecialchars($a['remarks']) ?>&rdquo;
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Adjust timeline borders */
.workflow-timeline::before {
    content: '';
    position: absolute;
    top: 6px;
    bottom: 6px;
    left: -1px;
    width: 2px;
    background: rgba(255,255,255,0.1);
    z-index: 0;
}
.timeline-item {
    position: relative;
    z-index: 1;
}
</style>

<?php
include __DIR__ . '/../layout/footer.php';
?>
