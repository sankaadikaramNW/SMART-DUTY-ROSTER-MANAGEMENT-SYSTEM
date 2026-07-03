<?php
include __DIR__ . '/../layout/header.php';
$today = date('l, d F Y');
?>

<!-- ===== PERSONAL TODAY'S DUTY ALERT (if user is on duty today) ===== -->
<?php if (!empty($myTodayDuty)): ?>
<div class="alert-today-duty mb-4 animate-fade-in">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="today-duty-pulse-dot"></div>
        <div class="flex-grow-1">
            <div class="fw-bold text-white mb-1" style="font-size:1.05rem;">
                <i class="fas fa-shield-halved me-2"></i>You Are On Duty Today
            </div>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach ($myTodayDuty as $md): ?>
                    <span class="text-white-75 small">
                        <i class="fas fa-circle-dot me-1 text-warning"></i>
                        <strong><?= htmlspecialchars($md['duty_type_name']) ?></strong>
                        &mdash; <?= htmlspecialchars($md['shift_name']) ?>
                        (<?= date('H:i', strtotime($md['start_time'])) ?> – <?= date('H:i', strtotime($md['end_time'])) ?>)
                        &bull; <?= htmlspecialchars($md['camp_name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <span class="badge bg-white text-success fw-bold px-3 py-2 rounded-pill" style="font-size:0.8rem;">
            <i class="fas fa-calendar-check me-1"></i><?= $today ?>
        </span>
    </div>
</div>
<?php endif; ?>

<!-- ===== WELCOME HEADER ===== -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8 col-12">
        <h1 class="h2 fw-bold gradient-text">Welcome back, <?= htmlspecialchars($rankName . ' ' . $fullName) ?></h1>
        <p class="text-secondary mb-0">Smart Provost Duty Roster &mdash; <?= $today ?></p>
    </div>
    <div class="col-md-4 col-12 text-md-end d-none d-md-block">
        <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="SLAF Crest"
             style="height: 65px; width: auto; opacity: 0.85; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
    </div>
</div>

<!-- ===== QUICK ACCESS STAT TILES ===== -->
<div class="row g-4 mb-4">

    <!-- Tile 1: Personnel -->
    <div class="col-lg-3 col-md-6 col-12">
        <?php $canViewPersonnel = in_array($roleName, ['Administrator', 'OCPROVST', 'SNCO', 'Warrant Officer IC']); ?>
        <a href="<?= $canViewPersonnel ? BASE_URL . '/personnel' : '#' ?>" class="text-decoration-none d-block h-100 <?= !$canViewPersonnel ? 'pe-none' : '' ?>">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link <?= $canViewPersonnel ? 'stat-tile-hover' : '' ?>">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Total Active Personnel</h6>
                    <i class="fas fa-users text-info stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $totalPersonnel ?></h3>
                <span class="small text-muted">Active Provost Guard</span>
                <?php if ($canViewPersonnel): ?>
                    <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
                <?php endif; ?>
            </div>
        </a>
    </div>

    <!-- Tile 2: Duty Rosters -->
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= BASE_URL ?>/rosters" class="text-decoration-none d-block h-100">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover" style="border-bottom-color: var(--accent-teal);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Duty Rosters</h6>
                    <i class="fas fa-calendar-days text-success stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $totalRosters ?></h3>
                <span class="small text-muted">All active periods</span>
                <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
        </a>
    </div>

    <!-- Tile 3: Role-specific tile -->
    <?php if ($roleName === 'OCPROVST' || $roleName === 'Administrator'): ?>
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= BASE_URL ?>/rosters/approve" class="text-decoration-none d-block h-100">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover" style="border-bottom-color: var(--accent-indigo);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Pending Approvals</h6>
                    <i class="fas fa-clock-rotate-left text-warning stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1 <?= $pendingApprovals > 0 ? 'text-warning' : 'text-success' ?>">
                    <?= $pendingApprovals ?>
                </h3>
                <span class="small text-muted"><?= $pendingApprovals > 0 ? 'Awaiting your review' : 'All clear' ?></span>
                <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <?php elseif ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC'): ?>
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= BASE_URL ?>/rosters/create" class="text-decoration-none d-block h-100">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover" style="border-bottom-color: var(--accent-teal);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Create Roster</h6>
                    <i class="fas fa-calendar-plus text-primary stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $totalShifts ?></h3>
                <span class="small text-muted">Active shift rotations</span>
                <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <?php else: ?>
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= BASE_URL ?>/rosters/timeline" class="text-decoration-none d-block h-100">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Active Shifts</h6>
                    <i class="fas fa-clock text-primary stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $totalShifts ?></h3>
                <span class="small text-muted">Duty rotations</span>
                <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Tile 4: Calendar / Security State -->
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= BASE_URL ?>/rosters/calendar" class="text-decoration-none d-block h-100">
            <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover" style="border-bottom-color: #10b981;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small mb-0">Calendar View</h6>
                    <i class="fas fa-shield-halved text-success stat-icon"></i>
                </div>
                <h3 class="fw-bold mb-1 text-success">SECURE</h3>
                <span class="small text-muted">View monthly schedule</span>
                <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
        </a>
    </div>
</div>

<!-- ===== PERSONNEL POSTING TRANSFERS OVERVIEW ===== -->
<?php if ($roleName === 'SNCO' || $roleName === 'OCPROVST' || $roleName === 'Warrant Officer IC'): ?>
<div class="row g-4 mb-4 animate-fade-in">
    <div class="col-md-6 col-12">
        <div class="glass-card p-4 h-100" style="border-left: 4px solid var(--accent-indigo);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-arrow-right-from-bracket text-warning me-2"></i> Outgoing Station Transfers
                </h5>
                <a href="<?= BASE_URL ?>/transfers" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2.5">
                    Manage Outgoing
                </a>
            </div>
            <div class="row text-center mt-3">
                <div class="col-4 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-dark mb-0"><?= $transferStats['outgoing']['total'] ?></h3>
                    <span class="text-secondary small">Total Outgoing</span>
                </div>
                <div class="col-4 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-warning mb-0"><?= $transferStats['outgoing']['pending'] ?></h3>
                    <span class="text-secondary small">Pending Origin</span>
                </div>
                <div class="col-4">
                    <h3 class="fw-bold text-success mb-0"><?= $transferStats['outgoing']['completed'] ?></h3>
                    <span class="text-secondary small">Completed</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="glass-card p-4 h-100" style="border-left: 4px solid #10b981;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-arrow-right-to-bracket text-success me-2"></i> Incoming Station Transfers
                </h5>
                <a href="<?= BASE_URL ?>/transfers" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2.5">
                    Review Incoming
                </a>
            </div>
            <div class="row text-center mt-3">
                <div class="col-4 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-dark mb-0"><?= $transferStats['incoming']['total'] ?></h3>
                    <span class="text-secondary small">Total Incoming</span>
                </div>
                <div class="col-4 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-info mb-0"><?= $transferStats['incoming']['pending'] ?></h3>
                    <span class="text-secondary small">Pending Incoming</span>
                </div>
                <div class="col-4">
                    <h3 class="fw-bold text-success mb-0"><?= $transferStats['incoming']['completed'] ?></h3>
                    <span class="text-secondary small">Completed</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($roleName === 'Administrator'): ?>
<div class="row g-4 mb-4 animate-fade-in">
    <div class="col-12">
        <div class="glass-card p-4" style="border-left: 4px solid var(--accent-indigo);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-right-left text-primary me-2"></i> Personnel Station Transfers Overview
                </h5>
                <a href="<?= BASE_URL ?>/transfers" class="btn btn-sm btn-custom btn-custom-secondary">
                    View All Transfers
                </a>
            </div>
            <div class="row text-center mt-3 g-2">
                <div class="col-md-3 col-6 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-dark mb-0"><?= $transferStats['total'] ?></h3>
                    <span class="text-secondary small">Total Transfers</span>
                </div>
                <div class="col-md-3 col-6 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-warning mb-0"><?= $transferStats['pending'] ?></h3>
                    <span class="text-secondary small">Pending Actions</span>
                </div>
                <div class="col-md-3 col-6 border-end border-secondary border-opacity-10">
                    <h3 class="fw-bold text-success mb-0"><?= $transferStats['completed'] ?></h3>
                    <span class="text-secondary small">Completed Transfers</span>
                </div>
                <div class="col-md-3 col-6">
                    <h3 class="fw-bold text-danger mb-0"><?= $transferStats['rejected'] ?></h3>
                    <span class="text-secondary small">Rejected / Cancelled</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== TODAY'S DUTY CREW (full panel) ===== -->
<?php if ($roleName !== 'Airman' && !empty($todayCrew)): ?>
<div class="glass-card p-4 mb-4 animate-fade-in" style="border-left: 4px solid var(--accent-indigo);">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">
            <i class="fas fa-users-rectangle text-indigo me-2" style="color: var(--accent-indigo);"></i>
            Today's Duty Crew
            <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 ms-2 fw-normal" style="font-size:0.75rem;">
                <?= count($todayCrew) ?> assigned
            </span>
        </h5>
        <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
            <i class="fas fa-calendar-day me-1"></i><?= $today ?>
        </span>
    </div>

    <?php
    // Group today's crew by shift
    $shiftGroups = [];
    foreach ($todayCrew as $tc) {
        $shiftKey = $tc['shift_name'] . '|' . $tc['start_time'] . '|' . $tc['end_time'];
        if (!isset($shiftGroups[$shiftKey])) {
            $shiftGroups[$shiftKey] = [
                'shift_name' => $tc['shift_name'],
                'start_time' => $tc['start_time'],
                'end_time'   => $tc['end_time'],
                'personnel'  => []
            ];
        }
        $shiftGroups[$shiftKey]['personnel'][] = $tc;
    }
    ?>

    <div class="row g-3">
        <?php foreach ($shiftGroups as $sg): ?>
        <div class="col-md-6 col-12">
            <div class="p-3 rounded-3 bg-dark bg-opacity-5 border border-secondary border-opacity-10 h-100">
                <!-- Shift header -->
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                    <span class="fw-bold text-dark small">
                        <i class="fas fa-clock text-primary me-1"></i>
                        <?= htmlspecialchars($sg['shift_name']) ?>
                    </span>
                    <span class="font-monospace small text-muted">
                        <?= date('H:i', strtotime($sg['start_time'])) ?> &ndash; <?= date('H:i', strtotime($sg['end_time'])) ?>
                    </span>
                </div>
                <!-- Personnel list -->
                <ul class="list-unstyled mb-0">
                    <?php foreach ($sg['personnel'] as $tc): ?>
                    <li class="d-flex align-items-center justify-content-between gap-2 py-1 <?= ($tc['service_number'] === $serviceNum) ? 'text-primary fw-bold' : '' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($tc['service_number'] === $serviceNum): ?>
                                <i class="fas fa-star text-warning" style="font-size:0.6rem;" title="You"></i>
                            <?php else: ?>
                                <i class="fas fa-circle text-secondary" style="font-size:0.45rem; opacity:0.4;"></i>
                            <?php endif; ?>
                            <span class="font-monospace small text-muted"><?= htmlspecialchars($tc['service_number']) ?></span>
                            <span class="small"><?= htmlspecialchars($tc['rank'] . ' ' . $tc['full_name']) ?></span>
                        </div>
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25" style="font-size:0.65rem;">
                            <?= htmlspecialchars($tc['duty_type_name']) ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-3 text-end">
        <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-sm btn-custom btn-custom-secondary">
            <i class="fas fa-calendar-days me-1"></i> Full Calendar View
        </a>
    </div>
</div>
<?php elseif ($roleName !== 'Airman'): ?>
<div class="glass-card p-4 mb-4 text-center" style="border-left: 4px solid var(--accent-indigo);">
    <i class="fas fa-calendar-xmark text-secondary mb-2" style="font-size:2rem;"></i>
    <p class="text-secondary mb-1 fw-medium">No duty crew assigned today</p>
    <small class="text-muted">No published roster covers <?= $today ?></small>
</div>
<?php endif; ?>


<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">

        <!-- My Upcoming Duties (future) -->
        <?php if (!empty($upcomingDuties) || $roleName === 'Airman'): ?>
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-shield text-info me-2"></i> My Upcoming Duty Watch</h5>
                <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-sm btn-custom btn-custom-secondary">
                    <i class="fas fa-calendar me-1"></i>Calendar
                </a>
            </div>
            <?php if (empty($upcomingDuties)): ?>
                <div class="text-center py-3">
                    <i class="fas fa-check-circle text-success mb-2" style="font-size:1.8rem;"></i>
                    <p class="text-secondary mb-0 small">No upcoming duties scheduled in published rosters.</p>
                </div>
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
                                        <span class="small text-muted"><?= date('H:i', strtotime($ud['start_time'])) ?> - <?= date('H:i', strtotime($ud['end_time'])) ?></span>
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
        <?php if ($roleName !== 'Airman'): ?>
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar-week text-success me-2"></i> Recent Duty Rosters</h5>
                <a href="<?= BASE_URL ?>/rosters" class="btn btn-sm btn-custom btn-custom-secondary">View All</a>
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
                                        <?php $statusClass = strtolower($r['status']); ?>
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
        <?php endif; ?>
    </div>

    <!-- Right Column: Quick Actions -->
    <div class="col-lg-4">
        <div class="glass-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-compass text-primary me-2"></i> Quick Actions</h5>
            <div class="d-grid gap-2">
                <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/rosters/create" class="btn btn-custom btn-custom-primary justify-content-center py-3">
                    <i class="fas fa-calendar-plus"></i> Create New Roster Draft
                </a>
                <?php endif; ?>

                <?php if ($roleName === 'OCPROVST' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/rosters/approve" class="btn btn-custom justify-content-center py-2 position-relative <?= $pendingApprovals > 0 ? 'btn-warning' : 'btn-custom-secondary' ?>" style="<?= $pendingApprovals > 0 ? 'background:rgba(245,158,11,0.15);border-color:rgba(245,158,11,0.4);color:#f59e0b;' : '' ?>">
                    <i class="fas fa-stamp"></i> Duty Approvals
                    <?php if ($pendingApprovals > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?= $pendingApprovals ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/personnel" class="btn btn-custom btn-custom-secondary justify-content-center py-2 <?= $roleName === 'Airman' ? 'd-none' : '' ?>">
                    <i class="fas fa-users"></i> View Personnel
                </a>

                <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-calendar-check"></i> Monthly Calendar
                </a>

                <a href="<?= BASE_URL ?>/rosters/timeline" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-timeline"></i> Timeline View
                </a>

                <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/reports" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-file-chart-column"></i> Generate Reports
                </a>
                <?php endif; ?>

                <?php if ($roleName === 'Administrator' || $roleName === 'Warrant Officer IC' || $roleName === 'OCPROVST'): ?>
                <a href="<?= BASE_URL ?>/users" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-users-gear"></i> Manage Users
                </a>
                <?php endif; ?>
                <?php if ($roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/camps" class="btn btn-custom btn-custom-secondary justify-content-center py-2">
                    <i class="fas fa-campground"></i> Manage Camps
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Station Standing Orders -->
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

<style>
/* Today's duty alert banner */
.alert-today-duty {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 50%, #1a4a7a 100%);
    border: 1px solid rgba(99, 179, 237, 0.3);
    border-left: 5px solid #63b3ed;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(30, 58, 95, 0.35);
}
.alert-today-duty::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(99,179,237,0.15) 0%, transparent 70%);
    pointer-events: none;
}
.text-white-75 { color: rgba(255,255,255,0.85) !important; }
.today-duty-pulse-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #4ade80;
    flex-shrink: 0;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.6);
    animation: duty-pulse 2s infinite;
}
@keyframes duty-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.6); }
    70%  { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
    100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}
/* Quick-access stat tiles */
.stat-tile-link { transition: all 0.22s ease; position: relative; }
.stat-tile-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.18);
    border-color: rgba(99,179,237,0.3);
}
.stat-tile-arrow {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    opacity: 0;
    color: var(--accent-indigo);
    transition: opacity 0.2s ease, transform 0.2s ease;
    transform: translateX(-4px);
    font-size: 0.85rem;
}
.stat-tile-hover:hover .stat-tile-arrow {
    opacity: 1;
    transform: translateX(0);
}
@keyframes animate-fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: animate-fade-in 0.5s ease both; }
</style>

<?php
include __DIR__ . '/../layout/footer.php';
?>
