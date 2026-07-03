<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-id-card"></i> Personnel Profile Details</h2>
        <p class="text-secondary">View detailed airman record and posting histories.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= BASE_URL ?>/personnel" class="btn btn-custom btn-custom-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Profile Card -->
    <div class="col-lg-5">
        <div class="glass-card p-4">
            <div class="text-center mb-4 pb-3 border-bottom border-secondary">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle p-3 mb-3 text-info" style="width: 100px; height: 100px;">
                    <i class="fas fa-user-shield fs-1"></i>
                </div>
                <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($person['initials'] . ' ' . $person['full_name']) ?></h4>
                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-1 rounded-pill mb-2"><?= htmlspecialchars($person['rank'] ?? 'No Rank') ?></span>
                <div class="text-secondary small fw-bold"><i class="fas fa-hashtag"></i> SERVICE NO: <?= htmlspecialchars($person['service_number']) ?></div>
            </div>
            
            <div class="profile-details-list">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">Trade / Specialty:</span>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($person['trade']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">Squadron:</span>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($person['squadron']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">Active Camp / Base:</span>
                    <span class="fw-medium text-info"><i class="fas fa-campground"></i> <?= htmlspecialchars($person['camp_name'] ?? 'No Location') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">Email:</span>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($person['email']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">Contact Number:</span>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($person['contact_number'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary small">Status:</span>
                    <?php
                    $status = $person['status'];
                    $badgeClass = 'bg-secondary';
                    if ($status === 'Active') $badgeClass = 'bg-success';
                    elseif ($status === 'Leave') $badgeClass = 'bg-warning';
                    elseif ($status === 'Temporary Duty') $badgeClass = 'bg-info';
                    ?>
                    <span class="badge <?= $badgeClass ?> bg-opacity-25 border border-<?= substr($badgeClass, 3) ?> border-opacity-25 text-<?= substr($badgeClass, 3) === 'success' ? 'success' : (substr($badgeClass, 3) === 'warning' ? 'warning' : 'info') ?> px-2.5 py-1.5 rounded-pill fw-bold">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </div>
            </div>

            <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                <div class="border-top pt-4 mt-4 d-grid gap-2">
                    <button type="button" class="btn btn-custom btn-custom-secondary w-100" data-bs-toggle="modal" data-bs-target="#editPersonnelModal">
                        <i class="fas fa-user-pen"></i> Edit Profile
                    </button>
                    <button type="button" class="btn btn-custom btn-custom-primary w-100" data-bs-toggle="modal" data-bs-target="#addPostingModal">
                        <i class="fas fa-right-left"></i> Assign Transfer Posting
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Posting transfers history -->
    <div class="col-lg-7">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-history text-info me-2"></i> Posting & Camp Transfer History</h5>
            
            <?php if (empty($postings)): ?>
                <p class="text-secondary my-3">No posting movements registered for this profile.</p>
            <?php else: ?>
                <div class="timeline-container">
                    <?php foreach ($postings as $pos): ?>
                        <div class="timeline-card p-3 mb-3 glass-card bg-opacity-25" style="border-left-color: <?= $pos['status'] === 'Active' ? 'var(--accent-teal)' : 'var(--glass-border)' ?>;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0">
                                    <i class="fas fa-right-left text-secondary small me-1"></i> 
                                    <?= htmlspecialchars($pos['from_camp']) ?> &rarr; <?= htmlspecialchars($pos['to_camp']) ?>
                                </h6>
                                <span class="badge rounded-pill bg-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> font-monospace small px-2">
                                    <?= $pos['status'] ?>
                                </span>
                            </div>
                            <div class="small text-secondary">
                                <i class="fas fa-calendar-alt me-1"></i> Effective Date: <?= date('D, M d, Y', strtotime($pos['effective_date'])) ?>
                                <?php if ($pos['end_date']): ?>
                                    &bull; End Date: <?= date('M d, Y', strtotime($pos['end_date'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Personnel Modal -->
<?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
<div class="modal fade" id="editPersonnelModal" tabindex="-1" aria-labelledby="editPersonnelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/personnel/edit" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <!-- Service number is read-only but submitted in post -->
            <input type="hidden" name="service_number" value="<?= htmlspecialchars($person['service_number']) ?>">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="editPersonnelModalLabel"><i class="fas fa-user-pen me-2"></i> Edit Personnel Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Service Number</label>
                        <input type="text" class="form-control form-control-custom bg-opacity-10 text-muted" value="<?= htmlspecialchars($person['service_number']) ?>" readonly disabled>
                    </div>
                    <div class="col-md-6">
                        <label for="rank_id" class="form-label text-secondary small">Rank</label>
                        <select class="form-select form-control-custom" id="rank_id" name="rank_id" <?= strtolower($person['service_number']) === 'admin' ? '' : 'required' ?>>
                            <?php if (strtolower($person['service_number']) === 'admin'): ?>
                                <option value="" <?= empty($person['rank_id']) ? 'selected' : '' ?>>No Rank</option>
                            <?php endif; ?>
                            <?php foreach ($ranks as $rk): ?>
                                <option value="<?= $rk['rank_id'] ?>" <?= (int)$person['rank_id'] === (int)$rk['rank_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rk['rank_name']) ?> (<?= htmlspecialchars($rk['rank_short_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="initials" class="form-label text-secondary small">Initials</label>
                        <input type="text" class="form-control form-control-custom" id="initials" name="initials" value="<?= htmlspecialchars($person['initials']) ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label for="full_name" class="form-label text-secondary small">Full Name</label>
                        <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" value="<?= htmlspecialchars($person['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="trade" class="form-label text-secondary small">Trade / Specialty</label>
                        <input type="text" class="form-control form-control-custom" id="trade" name="trade" value="<?= htmlspecialchars($person['trade']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="squadron" class="form-label text-secondary small">Squadron</label>
                        <input type="text" class="form-control form-control-custom" id="squadron" name="squadron" value="<?= htmlspecialchars($person['squadron']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="camp_id" class="form-label text-secondary small">Assigned Camp / Base</label>
                        <select class="form-select form-control-custom" id="camp_id" name="camp_id" <?= strtolower($person['service_number']) === 'admin' ? '' : 'required' ?>>
                            <?php if (strtolower($person['service_number']) === 'admin'): ?>
                                <option value="" <?= empty($person['camp_id']) ? 'selected' : '' ?>>No Location</option>
                            <?php endif; ?>
                            <?php foreach ($camps as $c): ?>
                                <?php 
                                // SNCO constraint
                                $restrictedCampId = LocationMiddleware::getCampConstraint();
                                if ($restrictedCampId !== null && (int)$c['camp_id'] !== $restrictedCampId) {
                                    continue;
                                }
                                ?>
                                <option value="<?= $c['camp_id'] ?>" <?= (int)$person['camp_id'] === (int)$c['camp_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['camp_name']) ?> (<?= htmlspecialchars($c['camp_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label text-secondary small">Personnel Status</label>
                        <select class="form-select form-control-custom" id="status" name="status" required>
                            <option value="Active" <?= $person['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Leave" <?= $person['status'] === 'Leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="Temporary Duty" <?= $person['status'] === 'Temporary Duty' ? 'selected' : '' ?>>Temporary Duty (TDY)</option>
                            <option value="Inactive" <?= $person['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label text-secondary small">Email Address</label>
                        <input type="email" class="form-control form-control-custom" id="email" name="email" value="<?= htmlspecialchars($person['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="contact_number" class="form-label text-secondary small">Contact Number</label>
                        <input type="text" class="form-control form-control-custom" id="contact_number" name="contact_number" value="<?= htmlspecialchars($person['contact_number'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                $submitLabel = "Save Changes";
                $submitIcon = "fas fa-floppy-disk";
                $cancelIcon = "fas fa-xmark";
                include __DIR__ . '/../components/form-buttons.php';
                ?>
            </div>
        </form>
    </div>
</div>

<!-- Assign Transfer Posting Modal -->
<div class="modal fade" id="addPostingModal" tabindex="-1" aria-labelledby="addPostingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/postings/add" method="POST" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" name="service_number" value="<?= htmlspecialchars($person['service_number']) ?>">
            <input type="hidden" name="from_camp_id" value="<?= htmlspecialchars($person['camp_id']) ?>">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="addPostingModalLabel"><i class="fas fa-right-left me-2"></i> Register Camp Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary small">Move personnel active station from <strong><?= htmlspecialchars($person['camp_name']) ?></strong> to another base.</p>
                <div class="mb-3">
                    <label class="form-label text-secondary small">Origin Camp</label>
                    <input type="text" class="form-control form-control-custom bg-opacity-10 text-muted" value="<?= htmlspecialchars($person['camp_name']) ?>" disabled readonly>
                </div>
                <div class="mb-3">
                    <label for="to_camp_id" class="form-label text-secondary small">Destination Camp</label>
                    <select class="form-select form-control-custom" id="to_camp_id" name="to_camp_id" required>
                        <option value="" disabled selected>Select Destination</option>
                        <?php foreach ($camps as $c): ?>
                            <?php if ((int)$c['camp_id'] === (int)$person['camp_id']) continue; ?>
                            <option value="<?= $c['camp_id'] ?>"><?= htmlspecialchars($c['camp_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="effective_date" class="form-label text-secondary small">Transfer Effective Date</label>
                    <input type="date" class="form-control form-control-custom" id="effective_date" name="effective_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                $submitLabel = "Complete Posting Transfer";
                $submitIcon = "fas fa-right-left";
                $cancelIcon = "fas fa-xmark";
                include __DIR__ . '/../components/form-buttons.php';
                ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
include __DIR__ . '/../layout/footer.php';
?>
