<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h2 fw-bold gradient-text">Welcome back, <?= htmlspecialchars($rankName . ' ' . $fullName) ?></h1>
        <p class="text-secondary">Smart Provost Duty Roster dashboard & base overview.</p>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6 col-12">
        <div class="glass-card stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Total Base Personnel</h6>
                <i class="fas fa-users text-info stat-icon"></i>
            </div>
            <h3 class="fw-bold mb-1"><?= $totalPersonnel ?></h3>
            <span class="small text-muted">Active Provost Guard</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="glass-card stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Duty Rosters</h6>
                <i class="fas fa-calendar-days text-success stat-icon"></i>
            </div>
            <h3 class="fw-bold mb-1"><?= $totalRosters ?></h3>
            <span class="small text-muted">All active periods</span>
        </div>
    </div>
    
    <?php if ($roleName === 'OCPROVST' || $roleName === 'Administrator'): ?>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="glass-card stat-card p-4" style="border-bottom-color: var(--accent-indigo);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Pending Approvals</h6>
                <i class="fas fa-clock-rotate-left text-warning stat-icon"></i>
            </div>
            <h3 class="fw-bold mb-1"><?= $pendingApprovals ?></h3>
            <span class="small text-muted">Awaiting review</span>
        </div>
    </div>
    <?php else: ?>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="glass-card stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Active Shifts</h6>
                <i class="fas fa-clock text-primary stat-icon"></i>
            </div>
            <h3 class="fw-bold mb-1"><?= $totalShifts ?></h3>
            <span class="small text-muted">Duty rotations</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-3 col-md-6 col-12">
        <div class="glass-card stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Security State</h6>
                <i class="fas fa-shield-halved text-teal stat-icon"></i>
            </div>
            <h3 class="fw-bold mb-1 text-success">SECURE</h3>
            <span class="small text-muted">Base monitoring normal</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Actions and lists -->
    <div class="col-lg-8">
        <?php if ($roleName === 'Airman' || !empty($upcomingDuties)): ?>
            <!-- My Upcoming Duties -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-shield text-info me-2"></i> My Upcoming Duty Watch</h5>
                <?php if (empty($upcomingDuties)): ?>
                    <p class="text-secondary my-3">No upcoming duties assigned in published rosters.</p>
                <?php else: ?>
                    <div class="table-custom-container">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Duty Type</th>
                                    <th>Shift / Timings</th>
                                    <th>Camp</th>
                                    <th>Roster</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingDuties as $ud): ?>
                                    <tr>
                                        <td class="fw-bold"><?= date('D, M d, Y', strtotime($ud['duty_date'])) ?></td>
                                        <td><span class="badge bg-primary bg-opacity-25 text-info px-3 py-2 border border-info border-opacity-25"><?= htmlspecialchars($ud['duty_type_name']) ?></span></td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($ud['shift_name']) ?></div>
                                            <span class="small text-muted"><?= htmlspecialchars(date('H:i', strtotime($ud['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($ud['end_time']))) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($ud['camp_name']) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($ud['roster_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Recent Rosters -->
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar-week text-success me-2"></i> Recent Duty Rosters</h5>
                <a href="<?= BASE_URL ?>/rosters" class="btn btn-sm btn-custom btn-custom-secondary">View All Rosters</a>
            </div>
            
            <?php if (empty($recentRosters)): ?>
                <p class="text-secondary my-3">No rosters created yet.</p>
            <?php else: ?>
                <div class="table-custom-container">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Roster Name</th>
                                <th>Camp/Base</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRosters as $r): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($r['roster_name']) ?></td>
                                    <td><?= htmlspecialchars($r['camp_name']) ?></td>
                                    <td class="small">
                                        <?= date('M d', strtotime($r['start_date'])) ?> - <?= date('M d, Y', strtotime($r['end_date'])) ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = strtolower($r['status']);
                                        ?>
                                        <span class="badge-custom badge-<?= $statusClass ?>">
                                            <i class="fas fa-circle small"></i> <?= $r['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/rosters/view?id=<?= $r['roster_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Column: Shortcuts and Information -->
    <div class="col-lg-4">
        <!-- Quick Actions Card -->
        <div class="glass-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-compass text-primary me-2"></i> Quick Actions</h5>
            <div class="d-grid gap-3">
                <?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
                    <a href="<?= BASE_URL ?>/rosters/create" class="btn btn-custom btn-custom-primary justify-content-center py-3">
                        <i class="fas fa-calendar-plus"></i> Create New Roster Draft
                    </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-calendar-check"></i> Monthly Calendar View
                </a>
                
                <a href="<?= BASE_URL ?>/rosters/timeline" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-timeline"></i> Timeline Rotation View
                </a>

                <?php if ($roleName === 'Administrator'): ?>
                    <hr class="my-2 border-secondary">
                    <a href="<?= BASE_URL ?>/users" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                        <i class="fas fa-users-gear"></i> Manage User Accounts
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Message -->
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-circle-info text-warning me-2"></i> Station Standing Orders</h5>
            <ul class="list-unstyled mb-0 text-secondary small">
                <li class="mb-2"><i class="fas fa-circle-dot text-info me-2 small"></i> Double bookings are strictly prevented.</li>
                <li class="mb-2"><i class="fas fa-circle-dot text-info me-2 small"></i> A 24-hour rest period is mandatory following a 24 Hour Duty.</li>
                <li class="mb-2"><i class="fas fa-circle-dot text-info me-2 small"></i> Personnel cannot work a Morning Shift immediately following a Night Shift.</li>
                <li class="mb-0"><i class="fas fa-circle-dot text-info me-2 small"></i> Location isolation rules prevent scheduling personnel mapped to other camps.</li>
            </ul>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
?>
