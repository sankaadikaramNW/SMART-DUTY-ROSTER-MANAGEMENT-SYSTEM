<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-calendar-check"></i> Roster Watch Details</h2>
        <p class="text-secondary">Roster: <strong class="text-dark"><?= htmlspecialchars($roster['roster_name']) ?></strong> &bull; Base: <?= htmlspecialchars($roster['camp_name']) ?></p>
    </div>
    <div class="col-md-6 text-md-end d-print-none">
        <a href="<?= BASE_URL ?>/rosters" class="btn btn-custom btn-custom-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Lists
        </a>
        <button type="button" class="btn btn-custom btn-custom-secondary" onclick="window.print();">
            <i class="fas fa-print"></i> Print Roster
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Main content: Assignments table -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-list text-success me-2"></i> Watch Duty Assignments</h5>
            
            <?php if (empty($assignments)): ?>
                <p class="text-secondary my-3">No assignments found for this roster schedule.</p>
            <?php else: ?>
                <div class="table-custom-container">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sentry Guard Details</th>
                                <th>Duty Type</th>
                                <th>Shift Watch</th>
                                <th>Priority</th>
                                <th>Status/Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $as): ?>
                                <tr>
                                    <td class="fw-bold"><?= date('D, M d, Y', strtotime($as['duty_date'])) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($as['rank'] . ' ' . $as['full_name']) ?></div>
                                        <span class="small text-secondary font-monospace"><?= htmlspecialchars($as['service_number']) ?></span>
                                    </td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-25 px-2.5 py-1 rounded"><?= htmlspecialchars($as['duty_type_name']) ?></span></td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($as['shift_name']) ?></div>
                                        <span class="small text-muted"><?= htmlspecialchars(date('H:i', strtotime($as['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($as['end_time']))) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $prio = $as['priority_level'];
                                        $pClass = 'bg-secondary';
                                        if ($prio === 'High') $pClass = 'bg-danger';
                                        elseif ($prio === 'Medium') $pClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?= $pClass ?> px-2 py-1 rounded small"><?= $prio ?></span>
                                    </td>
                                    <td>
                                        <?php if ($roleName === 'OCPROVST' && $roster['status'] === 'Submitted' && $as['status'] === 'Pending'): ?>
                                            <!-- Approve: direct hidden-input form submit -->
                                            <form action="<?= BASE_URL ?>/rosters/assignment-action" method="POST" class="d-inline">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="assignment_id" value="<?= (int)$as['assignment_id'] ?>">
                                                <input type="hidden" name="roster_id" value="<?= (int)$roster['roster_id'] ?>">
                                                <input type="hidden" name="status" value="Approved">
                                                <input type="hidden" name="supervisor_remarks" value="">
                                                <button type="submit" class="btn btn-sm btn-success py-1 px-2 me-1" title="Approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <!-- Reject: triggers Bootstrap modal -->
                                            <button type="button"
                                                class="btn btn-sm btn-danger py-1 px-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal"
                                                data-assignment-id="<?= (int)$as['assignment_id'] ?>"
                                                data-roster-id="<?= (int)$roster['roster_id'] ?>"
                                                data-person="<?= htmlspecialchars($as['rank'] . ' ' . $as['full_name']) ?>"
                                                title="Reject">
                                                <i class="fas fa-xmark"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($as['status'] === 'Approved') $badgeClass = 'bg-success';
                                            elseif ($as['status'] === 'Rejected') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?> px-2.5 py-1 rounded small"><?= htmlspecialchars($as['status']) ?></span>
                                            <?php if ($as['supervisor_remarks']): ?>
                                                <div class="small text-muted mt-1 font-italic" style="font-size:0.75rem;">Reason: <?= htmlspecialchars($as['supervisor_remarks']) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar workflow/history -->
    <div class="col-lg-4 d-print-none">
        <!-- Status Card -->
        <div class="glass-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar text-primary me-2"></i> Current Status</h5>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small">Roster Status:</span>
                <span class="badge-custom badge-<?= strtolower($roster['status']) ?>">
                    <i class="fas fa-circle small"></i> <?= $roster['status'] ?>
                </span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary small">Creation Date:</span>
                <span class="text-dark small"><?= date('F d, Y', strtotime($roster['created_at'])) ?></span>
            </div>
            <?php if (($roleName === 'SNCO' || $roleName === 'Administrator') && ($roster['status'] === 'Draft' || $roster['status'] === 'Rejected')): ?>
                <div class="border-top pt-3 mt-3 d-grid gap-2">
                    <a href="<?= BASE_URL ?>/rosters/create?id=<?= $roster['roster_id'] ?>" class="btn btn-custom btn-custom-warning justify-content-center">
                        <i class="fas fa-pen-to-square"></i> Edit Roster Draft
                    </a>
                    <form action="<?= BASE_URL ?>/rosters/action" method="POST" class="d-grid w-100">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="roster_id" value="<?= $roster['roster_id'] ?>">
                        <input type="hidden" name="action" value="Submit">
                        <button type="submit" class="btn btn-custom btn-custom-primary justify-content-center w-100">
                            <i class="fas fa-paper-plane"></i> Submit to OCPROVST
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- OCPROVST Decision Panel -->
        <?php if ($roleName === 'OCPROVST' && $roster['status'] === 'Submitted'): ?>
            <div class="glass-card p-4 mb-4 border border-warning border-opacity-25 animate-fade-in">
                <h5 class="fw-bold mb-3 text-warning"><i class="fas fa-clipboard-check me-2"></i> Roster Decision</h5>
                <form action="<?= BASE_URL ?>/rosters/action" method="POST" id="rosterApprovalForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="roster_id" value="<?= $roster['roster_id'] ?>">
                    <div class="mb-3">
                        <label for="remarks" class="form-label text-secondary small">Supervisor Remarks / Feedback</label>
                        <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="3" placeholder="Provide feedback or instructions here..."></textarea>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="action" value="Approve" class="btn btn-custom btn-custom-success w-100 justify-content-center">
                            <i class="fas fa-circle-check"></i> Approve & Publish
                        </button>
                        <button type="submit" name="action" value="Return" class="btn btn-custom btn-custom-warning w-100 justify-content-center">
                            <i class="fas fa-rotate-left"></i> Return to Draft
                        </button>
                        <button type="submit" name="action" value="Reject" class="btn btn-custom btn-custom-danger w-100 justify-content-center">
                            <i class="fas fa-circle-xmark"></i> Reject & Archive
                        </button>
                    </div>
                </form>
            </div>
            <!-- Reject Modal for individual assignment actions -->
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
                const rejectModal = document.getElementById('rejectModal');
                if (rejectModal) {
                    rejectModal.addEventListener('show.bs.modal', (event) => {
                        const button = event.relatedTarget;
                        document.getElementById('rejectAssignmentId').value = button.getAttribute('data-assignment-id');
                        document.getElementById('rejectRosterId').value     = button.getAttribute('data-roster-id');
                        document.getElementById('rejectPersonName').textContent = button.getAttribute('data-person');
                        document.getElementById('rejectReasonInput').value  = '';
                    });
                }
                const rejectForm = document.getElementById('rejectForm');
                if (rejectForm) {
                    rejectForm.addEventListener('submit', (e) => {
                        const reason = document.getElementById('rejectReasonInput').value.trim();
                        if (!reason) {
                            e.preventDefault();
                            document.getElementById('rejectReasonInput').classList.add('is-invalid');
                            return;
                        }
                        document.getElementById('confirmRejectBtn').disabled = true;
                        document.getElementById('confirmRejectBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Rejecting...';
                    });
                    document.getElementById('rejectReasonInput').addEventListener('input', function() {
                        if (this.value.trim()) this.classList.remove('is-invalid');
                    });
                }
            });
            </script>
        <?php endif; ?>

        <!-- Approval Logs history -->
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-history text-warning me-2"></i> Roster Actions Log</h5>
            <?php if (empty($approvals)): ?>
                <p class="text-secondary small my-2">No workflow actions registered.</p>
            <?php else: ?>
                <div class="timeline-container small">
                    <?php foreach ($approvals as $app): ?>
                        <div class="mb-3 pb-3 border-bottom border-secondary border-opacity-20 last-border-none">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($app['rank'] . ' ' . $app['full_name']) ?></span>
                                <?php
                                $act = $app['action'];
                                $badge = 'bg-secondary';
                                if ($act === 'Approve') $badge = 'bg-success';
                                elseif ($act === 'Submit') $badge = 'bg-primary';
                                elseif ($act === 'Reject') $badge = 'bg-danger';
                                elseif ($act === 'Return') $badge = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badge ?> px-2 py-0.5 rounded font-monospace" style="font-size: 0.65rem;"><?= $act ?></span>
                            </div>
                            <div class="text-muted small mb-1"><?= date('M d, Y H:i', strtotime($app['created_at'])) ?></div>
                            <?php if ($app['remarks']): ?>
                                <div class="text-secondary bg-dark bg-opacity-25 p-2 rounded mt-1 border border-secondary border-opacity-10">
                                    &ldquo;<?= htmlspecialchars($app['remarks']) ?>&rdquo;
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
?>
