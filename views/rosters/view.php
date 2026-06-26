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
        <button type="button" class="btn btn-custom btn-custom-secondary me-2" onclick="window.print();">
            <i class="fas fa-print"></i> Print Roster
        </button>
        <?php if (($roleName === 'SNCO' || $roleName === 'Administrator') && ($roster['status'] === 'Draft' || $roster['status'] === 'Rejected')): ?>
            <a href="<?= BASE_URL ?>/rosters/create?id=<?= $roster['roster_id'] ?>" class="btn btn-custom btn-custom-secondary me-2 text-warning border-warning border-opacity-25">
                <i class="fas fa-pen-to-square"></i> Edit Draft
            </a>
            
            <form action="<?= BASE_URL ?>/rosters/action" method="POST" class="d-inline">
                <?= Security::csrfField() ?>
                <input type="hidden" name="roster_id" value="<?= $roster['roster_id'] ?>">
                <input type="hidden" name="action" value="Submit">
                <button type="submit" class="btn btn-custom btn-custom-primary">
                    <i class="fas fa-paper-plane"></i> Submit to OCPROVST
                </button>
            </form>
        <?php endif; ?>
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
        </div>

        <!-- OCPROVST Approval workflow action panel -->
        <?php if ($roleName === 'OCPROVST' && $roster['status'] === 'Submitted'): ?>
            <div class="glass-card p-4 mb-4" style="border-left: 3px solid var(--accent-indigo);">
                <h5 class="fw-bold mb-3 text-info"><i class="fas fa-stamp me-2"></i> Workflow Review</h5>
                <form action="<?= BASE_URL ?>/rosters/action" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="roster_id" value="<?= $roster['roster_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label text-secondary small">Decision Notes / Remarks</label>
                        <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="3" placeholder="Provide reason if rejecting or returning..."></textarea>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2 mt-3">
                        <button type="submit" name="action" value="Reject" class="btn btn-custom btn-custom-danger">
                            <i class="fas fa-circle-xmark"></i> Reject
                        </button>
                        <button type="submit" name="action" value="Return" class="btn btn-custom btn-custom-secondary text-warning border-warning border-opacity-25">
                            <i class="fas fa-arrow-rotate-left"></i> Return Draft
                        </button>
                        <button type="submit" name="action" value="Approve" class="btn btn-custom btn-custom-success">
                            <i class="fas fa-circle-check"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
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
