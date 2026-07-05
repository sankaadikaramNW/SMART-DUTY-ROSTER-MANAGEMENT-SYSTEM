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

<!-- ===== TODAY'S DUTY CREW — HERO BANNER (above tiles) ===== -->
<?php if ($roleName !== 'Airman' && !empty($todayCrew)): ?>

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

<div class="duty-hero-banner mb-4 animate-fade-in">
    <!-- Banner header -->
    <div class="duty-hero-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="duty-live-dot"></div>
            <div>
                <div class="duty-hero-title">
                    <i class="fas fa-shield-halved me-2"></i>Today's Duty Crew
                </div>
                <div class="duty-hero-subtitle">
                    <?= $today ?> &bull; <?= count($todayCrew) ?> personnel on active duty
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <span class="duty-crew-count-badge">
                <i class="fas fa-users me-1"></i><?= count($todayCrew) ?> Assigned
            </span>
            <a href="<?= BASE_URL ?>/rosters/calendar" class="duty-calendar-btn">
                <i class="fas fa-calendar-days me-1"></i>Full Calendar
            </a>
        </div>
    </div>

    <!-- Shift sections with full-width horizontal personnel cards -->
    <?php foreach ($shiftGroups as $sg): ?>
    <div class="duty-shift-section">
        <!-- Shift strip header -->
        <div class="duty-shift-strip">
            <span class="duty-shift-strip-name">
                <i class="fas fa-clock me-2"></i><?= htmlspecialchars($sg['shift_name']) ?>
            </span>
            <span class="duty-shift-strip-time">
                <?= date('H:i', strtotime($sg['start_time'])) ?> &ndash; <?= date('H:i', strtotime($sg['end_time'])) ?>
                <span class="duty-shift-strip-count"><?= count($sg['personnel']) ?> personnel</span>
            </span>
        </div>
        <!-- Full-width personnel cards -->
        <div class="duty-personnel-cards">
            <?php foreach ($sg['personnel'] as $i => $tc): ?>
            <div class="duty-pcard <?= ($tc['service_number'] === $serviceNum) ? 'duty-pcard-you' : '' ?>">
                <!-- Index / Star avatar -->
                <div class="duty-pcard-num">
                    <?php if ($tc['service_number'] === $serviceNum): ?>
                        <i class="fas fa-star"></i>
                    <?php else: ?>
                        <?= $i + 1 ?>
                    <?php endif; ?>
                </div>
                <!-- Service No -->
                <div class="duty-pcard-cell duty-pcard-svcno">
                    <div class="duty-pcard-label">Svc No</div>
                    <div class="duty-pcard-value mono"><?= htmlspecialchars($tc['service_number']) ?></div>
                </div>
                <!-- Rank (short) -->
                <div class="duty-pcard-cell duty-pcard-rank">
                    <div class="duty-pcard-label">Rank</div>
                    <div class="duty-pcard-value"><?= htmlspecialchars($tc['rank']) ?></div>
                </div>
                <!-- Name -->
                <div class="duty-pcard-cell duty-pcard-name">
                    <div class="duty-pcard-label">Name</div>
                    <div class="duty-pcard-value fw"><?= htmlspecialchars($tc['full_name']) ?></div>
                </div>
                <!-- Camp -->
                <div class="duty-pcard-cell duty-pcard-camp">
                    <div class="duty-pcard-label">Camp / Base</div>
                    <div class="duty-pcard-value"><?= htmlspecialchars($tc['camp_name'] ?? '—') ?></div>
                </div>
                <!-- Duty Type badge -->
                <div class="duty-pcard-cell ms-auto">
                    <span class="duty-type-badge"><?= htmlspecialchars($tc['duty_type_name']) ?></span>
                </div>
                <?php if ($tc['service_number'] === $serviceNum): ?>
                <span class="duty-pcard-you-badge">YOU</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif ($roleName !== 'Airman'): ?>
<div class="duty-hero-banner duty-hero-empty mb-4">
    <div class="text-center py-2">
        <i class="fas fa-calendar-xmark mb-3" style="font-size:2.5rem;color:rgba(255,255,255,0.35);"></i>
        <div style="font-size:1.1rem;font-weight:600;color:rgba(255,255,255,0.8);">No Duty Crew Assigned Today</div>
        <div style="font-size:0.85rem;color:rgba(255,255,255,0.45);margin-top:4px;">No published roster covers <?= $today ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ===== QUICK ACCESS STAT TILES ===== -->

<div class="row g-4 mb-4">

    <!-- Tile 1: Personnel -->
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <?php $canViewPersonnel = in_array($roleName, ['Super Admin', 'Administrator', 'OCPROVST', 'SNCO', 'Warrant Officer IC']); ?>
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
    <div class="col-lg col-md-4 col-sm-6 col-12">
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
    <div class="col-lg col-md-4 col-sm-6 col-12">
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
    <div class="col-lg col-md-4 col-sm-6 col-12">
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
    <div class="col-lg col-md-4 col-sm-6 col-12">
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
    <div class="col-lg col-md-4 col-sm-6 col-12">
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

    <!-- Tile 5: Leave & Duty Monitor (Visible to SNCO, WO, OCPROVST, Admins) -->
    <?php if (in_array($roleName, ['SNCO', 'Warrant Officer IC', 'OCPROVST', 'Administrator', 'Super Admin'])): ?>
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="glass-card stat-card p-4 h-100 stat-tile-link stat-tile-hover" style="border-bottom-color: var(--accent-indigo); cursor: pointer;" onclick="openAttendanceModal();">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary text-uppercase small mb-0">Leave & Duty Monitor</h6>
                <i class="fas fa-campground text-info stat-icon"></i>
            </div>
            <div class="mb-2 text-dark">
                <div class="small mb-1" style="font-size:0.75rem;"><i class="fas fa-clock me-1 text-secondary"></i> Time: <span class="fw-bold text-dark" id="tileSysTime">--:--:--</span></div>
                <div class="small mb-1" style="font-size:0.75rem;"><i class="fas fa-circle-exclamation me-1 text-danger"></i> Overdue: <span class="fw-bold text-danger" id="tileOverdueCount">-</span></div>
                <div class="small" style="font-size:0.75rem;"><i class="fas fa-users-rectangle me-1 text-success"></i> Active Duty: <span class="fw-bold text-success" id="tileActiveCrewCount">-</span></div>
            </div>
            <div class="stat-tile-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
    </div>
    <?php endif; ?>
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



<!-- duty hero CSS already in <style> block below -->

<style>
/* ---- DUTY HERO BANNER ---- */
.duty-hero-banner {
    background: linear-gradient(135deg, #0f2044 0%, #1a3a6b 40%, #0d2d5e 70%, #112040 100%);
    border-radius: 16px;
    border: 1px solid rgba(99,179,237,0.18);
    box-shadow: 0 12px 40px rgba(10,30,70,0.45), inset 0 1px 0 rgba(255,255,255,0.06);
    overflow: hidden;
    position: relative;
}
.duty-hero-banner::before {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(99,179,237,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.duty-hero-banner::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(79,209,197,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.duty-hero-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 28px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    flex-wrap: wrap;
}
.duty-live-dot {
    width: 13px; height: 13px;
    border-radius: 50%;
    background: #4ade80;
    flex-shrink: 0;
    box-shadow: 0 0 0 0 rgba(74,222,128,0.6);
    animation: duty-pulse 2s infinite;
}
@keyframes duty-pulse {
    0%  { box-shadow: 0 0 0 0 rgba(74,222,128,0.6); }
    70% { box-shadow: 0 0 0 10px rgba(74,222,128,0); }
    100%{ box-shadow: 0 0 0 0 rgba(74,222,128,0); }
}
.duty-hero-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.2px;
}
.duty-hero-subtitle {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    margin-top: 2px;
}
.duty-crew-count-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 20px;
    background: rgba(74,222,128,0.15);
    border: 1px solid rgba(74,222,128,0.35);
    color: #4ade80;
    font-size: 0.78rem;
    font-weight: 700;
}
.duty-calendar-btn {
    display: inline-flex;
    align-items: center;
    padding: 7px 16px;
    border-radius: 20px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.85);
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.duty-calendar-btn:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
    transform: translateY(-1px);
}
.duty-shifts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    padding: 20px 28px 24px;
}
.duty-shift-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 12px;
    overflow: hidden;
    backdrop-filter: blur(6px);
}
.duty-shift-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: rgba(255,255,255,0.05);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.duty-shift-name {
    font-size: 0.82rem;
    font-weight: 700;
    color: #93c5fd;
    letter-spacing: 0.2px;
}
.duty-shift-time {
    font-family: monospace;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.45);
    background: rgba(255,255,255,0.05);
    padding: 2px 8px;
    border-radius: 6px;
}
.duty-personnel-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.15s ease;
}
.duty-personnel-row:last-child { border-bottom: none; }
.duty-personnel-row:hover { background: rgba(255,255,255,0.04); }
.duty-personnel-row.duty-you {
    background: rgba(99,179,237,0.08);
    border-left: 3px solid #63b3ed;
}
.duty-personnel-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.6);
    flex-shrink: 0;
}
.duty-you .duty-personnel-avatar {
    background: rgba(245,158,11,0.2);
    border-color: rgba(245,158,11,0.4);
    color: #fbbf24;
}
.duty-personnel-info { flex: 1; min-width: 0; }
.duty-personnel-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(255,255,255,0.92);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.duty-personnel-svc {
    font-family: monospace;
    font-size: 0.72rem;
    color: rgba(255,255,255,0.38);
    margin-top: 1px;
}
.duty-type-badge {
    flex-shrink: 0;
    font-size: 0.66rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: rgba(13,202,240,0.15);
    border: 1px solid rgba(13,202,240,0.3);
    color: #22d3ee;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.duty-hero-empty {
    padding: 36px 28px;
}

/* ---- NEW: Full-width horizontal personnel cards ---- */
.duty-shift-section {
    border-top: 1px solid rgba(255,255,255,0.06);
}
.duty-shift-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 28px;
    background: rgba(255,255,255,0.04);
    gap: 12px;
}
.duty-shift-strip-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #93c5fd;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.duty-shift-strip-time {
    font-family: monospace;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    gap: 10px;
}
.duty-shift-strip-count {
    background: rgba(255,255,255,0.07);
    border-radius: 10px;
    padding: 1px 8px;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
}
.duty-personnel-cards {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 8px 16px 16px;
}
.duty-pcard {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 6px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    transition: background 0.15s ease;
    position: relative;
    overflow: hidden;
}
.duty-pcard:hover { background: rgba(255,255,255,0.07); }
.duty-pcard-you {
    background: rgba(99,179,237,0.08) !important;
    border-color: rgba(99,179,237,0.25) !important;
    border-left: 3px solid #63b3ed !important;
}
.duty-pcard-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
    flex-shrink: 0;
    margin-right: 16px;
}
.duty-pcard-you .duty-pcard-num,
.duty-pcard-you .duty-pcard-num i {
    background: rgba(245,158,11,0.2);
    border-color: rgba(245,158,11,0.4);
    color: #fbbf24;
}
.duty-pcard-cell {
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding-right: 28px;
}
.duty-pcard-label {
    font-size: 0.6rem;
    font-weight: 600;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 2px;
}
.duty-pcard-value {
    font-size: 0.82rem;
    font-weight: 500;
    color: rgba(255,255,255,0.85);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.duty-pcard-value.mono { font-family: monospace; font-size: 0.8rem; }
.duty-pcard-value.fw { font-weight: 700; color: #fff; }
.duty-pcard-svcno { min-width: 90px; }
.duty-pcard-rank  { min-width: 70px; }
.duty-pcard-name  { min-width: 160px; flex: 1; }
.duty-pcard-camp  { min-width: 130px; }
.duty-pcard-you-badge {
    position: absolute;
    right: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 0.58rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 10px;
    background: rgba(99,179,237,0.2);
    border: 1px solid rgba(99,179,237,0.4);
    color: #63b3ed;
    letter-spacing: 1px;
    margin-left: 8px;
}
</style>



<div class="row g-4">
    <!-- Full Width Content -->
    <div class="col-12">

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
</div>

<!-- ===== FULL-WIDTH INTERACTIVE LEAVE CALENDAR ===== -->
<div class="glass-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-plane-departure text-info me-2"></i> Interactive Leave Calendar
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="prevMonthBtn" class="btn btn-xs btn-outline-secondary py-1 px-2"><i class="fas fa-chevron-left"></i></button>
            <span id="calendarMonthYear" class="fw-bold text-dark font-monospace small px-2" style="min-width: 120px; text-align: center;"></span>
            <button type="button" id="nextMonthBtn" class="btn btn-xs btn-outline-secondary py-1 px-2"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <!-- Status Legend -->
    <div class="d-flex flex-wrap gap-2 mb-3 text-dark" style="font-size: 0.72rem;">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">🔵 Expected</span>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">🟢 Completed</span>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">🔴 Not Reported</span>
        <span class="badge px-2 py-1" style="color:#fd7e14;border:1px solid rgba(253,126,20,0.3);background:rgba(253,126,20,0.1);">🟠 Late Reported</span>
        <span class="badge px-2 py-1" style="color:#6f42c1;border:1px solid rgba(111,66,193,0.3);background:rgba(111,66,193,0.1);">🟣 Granted</span>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0 text-dark w-100" style="table-layout: fixed; font-size: 0.8rem;">
            <thead class="table-light">
                <tr>
                    <th style="width:14.28%;">Sun</th>
                    <th style="width:14.28%;">Mon</th>
                    <th style="width:14.28%;">Tue</th>
                    <th style="width:14.28%;">Wed</th>
                    <th style="width:14.28%;">Thu</th>
                    <th style="width:14.28%;">Fri</th>
                    <th style="width:14.28%;">Sat</th>
                </tr>
            </thead>
            <tbody id="leaveCalendarBody">
                <!-- Populated dynamically by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ===== QUICK ACTIONS + STANDING ORDERS (BELOW CALENDAR) ===== -->
<div class="row g-4 mt-0">

    <!-- Quick Actions -->
    <div class="col-lg-8 col-12">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-compass text-primary me-2"></i> Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/rosters/create" class="btn btn-custom btn-custom-primary" style="min-width:130px;">
                    <i class="fas fa-calendar-plus me-1"></i> New Roster
                </a>
                <?php endif; ?>

                <?php if ($roleName === 'OCPROVST' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/rosters/approve" class="btn position-relative <?= $pendingApprovals > 0 ? 'btn-warning' : 'btn-custom btn-custom-secondary' ?>" style="min-width:130px;<?= $pendingApprovals > 0 ? 'background:rgba(245,158,11,0.15);border-color:rgba(245,158,11,0.4);color:#f59e0b;' : '' ?>">
                    <i class="fas fa-stamp me-1"></i> Duty Approvals
                    <?php if ($pendingApprovals > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?= $pendingApprovals ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/personnel" class="btn btn-custom btn-custom-secondary <?= $roleName === 'Airman' ? 'd-none' : '' ?>" style="min-width:130px;">
                    <i class="fas fa-users me-1"></i> View Personnel
                </a>
                <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-custom btn-custom-secondary" style="min-width:130px;">
                    <i class="fas fa-calendar-check me-1"></i> Monthly Calendar
                </a>
                <a href="<?= BASE_URL ?>/rosters/timeline" class="btn btn-custom btn-custom-secondary" style="min-width:130px;">
                    <i class="fas fa-timeline me-1"></i> Timeline View
                </a>

                <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/reports" class="btn btn-custom btn-custom-secondary" style="min-width:130px;">
                    <i class="fas fa-file-chart-column me-1"></i> Generate Reports
                </a>
                <?php endif; ?>

                <?php if ($roleName === 'Administrator' || $roleName === 'Warrant Officer IC' || $roleName === 'OCPROVST'): ?>
                <a href="<?= BASE_URL ?>/users" class="btn btn-custom btn-custom-secondary" style="min-width:130px;">
                    <i class="fas fa-users-gear me-1"></i> Manage Users
                </a>
                <?php endif; ?>
                <?php if ($roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/camps" class="btn btn-custom btn-custom-secondary" style="min-width:130px;">
                    <i class="fas fa-campground me-1"></i> Manage Camps
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Station Standing Orders -->
    <div class="col-lg-4 col-12">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-circle-info text-warning me-2"></i> Station Standing Orders</h5>
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

</style>

<!-- Attendance stays modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card">
            <div class="modal-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="attendanceModalLabel">
                    <i class="fas fa-campground text-info me-2"></i>Personnel Camp Stay Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark p-4">
                <!-- Loading State -->
                <div id="attendanceLoading" class="text-center py-5">
                    <div class="spinner-border text-info mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-secondary small">Fetching current attendance and stay statistics...</p>
                </div>
                <!-- Content State -->
                <div id="attendanceContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 text-dark small" style="font-size:0.825rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Number</th>
                                    <th>Rank & Name</th>
                                    <th>Check-in Date</th>
                                    <th>Days in Camp</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Attendance & Leave metrics tile auto-refresh on load
    const tileRole = <?= json_encode(in_array($roleName, ['SNCO', 'Warrant Officer IC', 'OCPROVST', 'Administrator', 'Super Admin'])) ?>;
    
    function fetchAttendanceStats(showModal = false) {
        if (!tileRole) return;
        
        fetch(`${BASE_URL}/dashboard/attendance-stats`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    console.error("Dashboard stats error: ", data.error);
                    return;
                }
                
                // Update tile values
                const sysTimeEl = document.getElementById('tileSysTime');
                const overdueEl = document.getElementById('tileOverdueCount');
                const activeEl = document.getElementById('tileActiveCrewCount');
                
                if (sysTimeEl) sysTimeEl.textContent = data.system_time;
                if (overdueEl) overdueEl.textContent = data.overdue_count;
                if (activeEl) activeEl.textContent = data.active_crew_count;
                
                if (showModal) {
                    const tbody = document.getElementById('attendanceTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.personnel_stays && data.personnel_stays.length > 0) {
                        data.personnel_stays.forEach(p => {
                            const days = parseInt(p.days_in_camp || 0);
                            let badge = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">Normal</span>';
                            let rowStyle = '';
                            if (days > 30) {
                                badge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1"><i class="fas fa-triangle-exclamation me-1"></i> Stay > 30 Days</span>';
                                rowStyle = 'style="background-color: rgba(239, 68, 68, 0.05);"';
                            }
                            
                            const tr = document.createElement('tr');
                            if (rowStyle) tr.setAttribute('style', 'background-color: rgba(239, 68, 68, 0.05);');
                            tr.innerHTML = `
                                <td class="font-monospace text-secondary">${escapeHtml(p.service_number)}</td>
                                <td class="fw-bold text-dark">${escapeHtml(p.rank + ' ' + p.full_name)}</td>
                                <td class="text-dark">${escapeHtml(p.check_in_date)}</td>
                                <td class="fw-bold text-dark">${days} Days</td>
                                <td>${badge}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">No checked-in personnel found.</td></tr>';
                    }
                    
                    document.getElementById('attendanceLoading').style.display = 'none';
                    document.getElementById('attendanceContent').style.display = 'block';
                }
            })
            .catch(err => {
                console.error("Dashboard stats fetch failure:", err);
            });
    }

    window.openAttendanceModal = function() {
        document.getElementById('attendanceLoading').style.display = 'block';
        document.getElementById('attendanceContent').style.display = 'none';
        
        const modalEl = document.getElementById('attendanceModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        fetchAttendanceStats(true);
    };

    // Run immediately on load, and tick the system time every 30 seconds
    if (tileRole) {
        fetchAttendanceStats(false);
        setInterval(() => {
            fetchAttendanceStats(false);
        }, 30000);
    }

    // 2. Interactive Leave Calendar
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth(); // 0-indexed

    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    const monthYearSpan = document.getElementById('calendarMonthYear');
    const calendarBody = document.getElementById('leaveCalendarBody');

    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    function renderLeaveCalendar() {
        // Calculate start & end range of month
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        
        // Month formatting label
        monthYearSpan.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
        // Format query params
        const formatYmd = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        const startStr = formatYmd(firstDay);
        const endStr = formatYmd(lastDay);

        calendarBody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4"><i class="fas fa-circle-notch fa-spin text-info me-2"></i> Loading Leaves...</td></tr>';

        // Fetch leaves from endpoint
        fetch(`${BASE_URL}/leaves/calendar-data?start=${startStr}&end=${endStr}`)
            .then(res => res.json())
            .then(leaves => {
                calendarBody.innerHTML = '';
                
                // Group leaves by Date string
                const leavesByDate = {};
                
                // Get month offset day of week
                const startDayOfWeek = firstDay.getDay(); // 0 = Sun, 6 = Sat
                const daysInMonth = lastDay.getDate();
                
                // Gather date range matches
                leaves.forEach(l => {
                    const lStart = new Date(l.leave_start_date);
                    const lEnd = new Date(l.granted_end_date || l.leave_end_date);
                    
                    // Loop dates overlapping the current month
                    const startLoop = new Date(Math.max(lStart, firstDay));
                    const endLoop = new Date(Math.min(lEnd, lastDay));
                    
                    for (let d = new Date(startLoop); d <= endLoop; d.setDate(d.getDate() + 1)) {
                        const dateKey = formatYmd(d);
                        if (!leavesByDate[dateKey]) {
                            leavesByDate[dateKey] = [];
                        }
                        leavesByDate[dateKey].push(l);
                    }
                });

                // Generate grid
                const todayKey = formatYmd(new Date());
                let currentDay = 1;
                let html = '';
                
                // We need maximum 6 weeks (6 rows)
                for (let w = 0; w < 6; w++) {
                    if (currentDay > daysInMonth) break;
                    
                    html += '<tr>';
                    for (let d = 0; d < 7; d++) {
                        if ((w === 0 && d < startDayOfWeek) || currentDay > daysInMonth) {
                            html += '<td class="bg-light bg-opacity-50" style="height: 80px; vertical-align: top;"></td>';
                        } else {
                            const cellDate = new Date(currentYear, currentMonth, currentDay);
                            const dateKey = formatYmd(cellDate);
                            const dayLeaves = leavesByDate[dateKey] || [];
                            
                            let leavesHtml = '';
                            dayLeaves.forEach(l => {
                                let color = '#0d6efd';
                                let bgColor = 'rgba(13, 110, 253, 0.1)';
                                if (l.status === 'Completed') {
                                    color = '#198754';
                                    bgColor = 'rgba(25, 135, 84, 0.1)';
                                } else if (l.status === 'Not Reported') {
                                    color = '#dc3545';
                                    bgColor = 'rgba(220, 53, 69, 0.1)';
                                } else if (l.status === 'Late Reported') {
                                    color = '#fd7e14';
                                    bgColor = 'rgba(253, 126, 20, 0.15)';
                                } else if (l.status === 'Granted') {
                                    color = '#6f42c1';
                                    bgColor = 'rgba(111, 66, 193, 0.1)';
                                }

                                let tooltipTitle = '';
                                if (l.status === 'Granted') {
                                    const daysDiff = Math.ceil((new Date(l.granted_end_date) - new Date(l.leave_end_date)) / 86400000);
                                    tooltipTitle = `Service Number: ${escapeHtml(l.service_number)}\n` +
                                                   `Name: ${escapeHtml(l.rank + ' ' + l.full_name)}\n` +
                                                   `Original Return Date: ${escapeHtml(l.leave_end_date)}\n` +
                                                   `Granted Return Date: ${escapeHtml(l.granted_end_date)}\n` +
                                                   `Granted Days: ${daysDiff} Days\n` +
                                                   `Reason for Grant: ${escapeHtml(l.granted_reason)}\n` +
                                                   `Status: Granted`;
                                } else {
                                    tooltipTitle = `Svc No: ${escapeHtml(l.service_number)} (${escapeHtml(l.rank + ' ' + l.full_name)})\n` +
                                                   `Period: ${escapeHtml(l.leave_start_date)} to ${escapeHtml(l.leave_end_date)}\n` +
                                                   `Type: ${escapeHtml(l.leave_type)}\n` +
                                                   `Status: ${escapeHtml(l.status)}`;
                                }

                                leavesHtml += `
                                    <span class="badge text-truncate d-block mb-1 py-1 px-1.5 cursor-pointer text-start" 
                                          style="font-size: 0.675rem; border-left: 3px solid ${color}; color: ${color}; background-color: ${bgColor};"
                                          data-bs-toggle="tooltip" 
                                          data-bs-placement="top" 
                                          title="${tooltipTitle.replace(/"/g, '&quot;')}">
                                        ${escapeHtml(l.service_number)}
                                    </span>
                                `;
                            });
                            
                            const isToday = dateKey === todayKey;

                            html += `
                                <td style="height: 80px; vertical-align: top; text-align: left; padding: 6px; position: relative; ${isToday ? 'background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(13,110,253,0.04) 100%); box-shadow: inset 0 0 0 2px rgba(13,110,253,0.35);' : ''}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small ${isToday ? 'text-primary' : 'text-secondary'}" style="${isToday ? 'font-size:0.85rem;' : ''}">${currentDay}</span>
                                        ${isToday ? '<span style="font-size:0.6rem;font-weight:700;color:#fff;background:#0d6efd;border-radius:20px;padding:1px 6px;line-height:1.5;">TODAY</span>' : ''}
                                    </div>
                                    <div style="max-height: 50px; overflow-y: auto;">
                                        ${leavesHtml}
                                    </div>
                                </td>
                            `;
                            currentDay++;
                        }
                    }
                    html += '</tr>';
                }
                
                calendarBody.innerHTML = html;

                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            })
            .catch(err => {
                calendarBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error loading leave records.</td></tr>';
                console.error("Calendar data fetch error:", err);
            });
    }

    prevMonthBtn.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderLeaveCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderLeaveCalendar();
    });

    // Render calendar initially
    renderLeaveCalendar();

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
