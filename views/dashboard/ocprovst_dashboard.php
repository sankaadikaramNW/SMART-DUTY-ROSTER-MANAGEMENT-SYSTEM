<?php
include __DIR__ . '/../../views/layout/header.php';
?>

<!-- ===== WELCOME HEADER ===== -->
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-8 col-12">
        <h1 class="h2 fw-bold gradient-text">Welcome, OCPROVST</h1>
        <p class="text-secondary mb-0">Smart Provost Approving Authority &mdash; <?= $today ?></p>
    </div>
    <div class="col-md-4 col-12 text-md-end d-none d-md-block">
        <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="SLAF Crest"
             style="height: 60px; width: auto; opacity: 0.85; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
    </div>
</div>

<!-- ===== KPI METRIC CARDS ===== -->
<div class="row g-3 mb-4 animate-fade-in">
    <!-- Pending Duty Crews -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-warning border-4 hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold d-block text-uppercase">Pending Crews</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $ocStats['pending_crews'] ?></h3>
                </div>
                <div class="fs-3 text-warning opacity-75"><i class="fas fa-clock-rotate-left"></i></div>
            </div>
            <a href="<?= BASE_URL ?>/rosters/approve" class="small text-warning text-decoration-none mt-2 d-block">
                View Pending <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- Approved Duty Crews -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-success border-4 hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold d-block text-uppercase">Approved Crews</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $ocStats['approved_crews'] ?></h3>
                </div>
                <div class="fs-3 text-success opacity-75"><i class="fas fa-circle-check"></i></div>
            </div>
            <span class="small text-secondary mt-2 d-block">Published watch grids</span>
        </div>
    </div>

    <!-- Rejected Duty Crews -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-danger border-4 hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold d-block text-uppercase">Rejected Crews</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $ocStats['rejected_crews'] ?></h3>
                </div>
                <div class="fs-3 text-danger opacity-75"><i class="fas fa-circle-xmark"></i></div>
            </div>
            <span class="small text-secondary mt-2 d-block">Returned to SNCO</span>
        </div>
    </div>

    <!-- Today's Approved Duties -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-info border-4 hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold d-block text-uppercase">Active Today</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $ocStats['today_duties'] ?></h3>
                </div>
                <div class="fs-3 text-info opacity-75"><i class="fas fa-user-shield"></i></div>
            </div>
            <span class="small text-secondary mt-2 d-block">On-duty strength</span>
        </div>
    </div>

    <!-- Upcoming Duties -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-primary border-4 hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-bold d-block text-uppercase">Upcoming Duties</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $ocStats['upcoming_duties'] ?></h3>
                </div>
                <div class="fs-3 text-primary opacity-75"><i class="fas fa-calendar-alt"></i></div>
            </div>
            <span class="small text-secondary mt-2 d-block">Scheduled roster items</span>
        </div>
    </div>

    <!-- Roster Approvals Page Link -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="glass-card p-3 border-start border-primary border-4 hover-lift d-flex flex-column justify-content-center" style="height:100%;">
            <a href="<?= BASE_URL ?>/rosters/approve" class="btn btn-custom btn-custom-primary btn-sm py-2.5 text-white text-center w-100">
                <i class="fas fa-stamp me-1"></i> Approve Roster
            </a>
        </div>
    </div>
</div>

<div class="row g-4 animate-fade-in">
    <!-- Left Column: Interactive Watch Calendar -->
    <div class="col-lg-8 col-12">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="fas fa-calendar-week text-primary me-2"></i>Camp Duty Watch Calendar
            </h5>
            <div id="ocProvstWatchCalendar" class="text-dark p-2" style="background:#ffffff; border-radius: 12px; min-height: 550px; border: 1px solid rgba(0,0,0,0.1);"></div>
        </div>
    </div>

    <!-- Right Column: Recent Approval Logs -->
    <div class="col-lg-4 col-12">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="fas fa-history text-purple me-2"></i>My Recent Actions
            </h5>
            
            <?php if (empty($ocStats['recent_approvals'])): ?>
                <div class="text-center py-5 text-secondary">
                    <i class="fas fa-receipt fs-1 opacity-25 d-block mb-2"></i>
                    No approvals log registered.
                </div>
            <?php else: ?>
                <div class="recent-activity-timeline" style="max-height: 580px; overflow-y: auto;">
                    <?php foreach ($ocStats['recent_approvals'] as $log): ?>
                        <div class="position-relative ps-4 pb-3 border-start border-secondary border-opacity-10">
                            <div class="position-absolute start-0 translate-middle-x rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; top: 2px;">
                                <?php if ($log['action'] === 'Approve'): ?>
                                    <span class="text-success"><i class="fas fa-circle-check"></i></span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-circle-xmark"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="ms-1">
                                <span class="badge <?= $log['action'] === 'Approve' ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 text-dark-50 rounded px-2 py-0.5 small" style="font-size:0.7rem;">
                                    <?= htmlspecialchars($log['action']) ?>d
                                </span>
                                <span class="text-secondary small float-end font-monospace" style="font-size:0.7rem;"><?= date('d M, H:i', strtotime($log['created_at'])) ?></span>
                                <div class="mt-1 small">
                                    <strong class="text-dark"><?= htmlspecialchars($log['duty_type_name']) ?></strong> (<?= htmlspecialchars($log['shift_name']) ?>)
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        Date: <?= htmlspecialchars($log['duty_date']) ?> &bull; Roster: <?= htmlspecialchars($log['roster_name']) ?>
                                    </div>
                                    <?php if ($log['remarks']): ?>
                                        <div class="p-1.5 bg-light rounded text-secondary italic mt-1" style="font-size:0.7rem;">
                                            <em>"<?= htmlspecialchars($log['remarks']) ?>"</em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== DETAILED BOOTSTRAP MODAL FOR CREW CLICK ===== -->
<div class="modal fade" id="ocCrewDetailsModal" tabindex="-1" aria-labelledby="ocCrewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card text-dark">
            <div class="modal-header bg-primary bg-opacity-10 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="ocCrewDetailsModalLabel">
                    <i class="fas fa-shield-halved text-primary me-2"></i> Duty Crew Roster Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Column 1: Crew Details -->
                    <div class="col-md-6 border-end border-secondary border-opacity-10 pr-md-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-circle-info"></i> Crew Watch Parameters</h6>
                        <table class="table table-sm table-borderless small mb-0 align-middle text-dark">
                            <tr>
                                <td class="text-secondary py-1" style="width: 38%;">Crew ID:</td>
                                <td class="fw-bold py-1 text-dark" id="mCrewId">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Date:</td>
                                <td class="fw-bold py-1 text-info" id="mCrewDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Type:</td>
                                <td class="fw-bold py-1" id="mCrewType">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Shift Name:</td>
                                <td class="fw-bold py-1 text-dark" id="mCrewShift">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Location:</td>
                                <td class="fw-bold py-1 text-dark" id="mCrewLocation">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Roster Name:</td>
                                <td class="py-1 text-secondary" id="mRosterName">-</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Column 2: Roster Submission Audit -->
                    <div class="col-md-6 ps-md-4">
                        <h6 class="fw-bold text-success mb-3"><i class="fas fa-clipboard-check"></i> Roster Metadata</h6>
                        <table class="table table-sm table-borderless small mb-0 align-middle text-dark">
                            <tr>
                                <td class="text-secondary py-1" style="width: 40%;">Submitted By:</td>
                                <td class="fw-bold py-1 text-dark" id="mRosterCreator">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Status:</td>
                                <td class="py-1"><span class="badge bg-light text-dark border" id="mRosterStatus">-</span></td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Directives:</td>
                                <td class="py-1 text-muted italic" id="mCrewRemarks">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-3">

                <!-- Assigned Personnel Accordion-like sub-table -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-users"></i> Assigned Guard Personnel</h6>
                        <div class="table-custom-container" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover table-sm small align-middle text-dark">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service No</th>
                                        <th>Rank & Name</th>
                                        <th>Trade</th>
                                        <th>Status Warning</th>
                                    </tr>
                                </thead>
                                <tbody id="mPersonnelTableBody">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Approval Trail Log section -->
                <div class="row mt-3" id="mHistorySection" style="display:none;">
                    <div class="col-12">
                        <h6 class="fw-bold text-purple mb-2"><i class="fas fa-receipt"></i> Approval Action History</h6>
                        <div class="bg-light p-2.5 rounded border small font-monospace text-dark" style="max-height: 120px; overflow-y: auto;" id="mHistoryLog">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary border-opacity-10 justify-content-between py-3">
                <span class="text-danger small" id="mConflictAlert" style="display:none;"><i class="fas fa-triangle-exclamation"></i> Warning: Duplicate booking detected!</span>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                    <a href="<?= BASE_URL ?>/rosters/approve" class="btn btn-primary btn-sm px-3" id="mApproveLink"><i class="fas fa-stamp"></i> Go to Approvals</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Watch Calendar Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('ocProvstWatchCalendar');
    let allRawAssignments = [];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        themeSystem: 'bootstrap5',
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            const start = info.startStr.substring(0, 10);
            const end = info.endStr.substring(0, 10);

            // Fetch roster assignments for OCPROVST active camp
            const url = `${BASE_URL}/rosters/calendar-data?camp_id=<?= $activeCampId ?>&start=${start}&end=${end}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        failureCallback(data.error);
                        return;
                    }

                    allRawAssignments = data;

                    // Group by roster_id | duty_date | shift_id | duty_type_id
                    const crews = {};
                    data.forEach(item => {
                        const key = `${item.roster_id}-${item.duty_date}-${item.shift_id}-${item.duty_type_id}`;
                        if (!crews[key]) {
                            crews[key] = {
                                crew_id: key,
                                roster_id: item.roster_id,
                                roster_name: item.roster_name,
                                duty_date: item.duty_date,
                                shift_id: item.shift_id,
                                shift_name: item.shift_name,
                                start_time: item.start_time,
                                end_time: item.end_time,
                                duty_type_id: item.duty_type_id,
                                duty_type_name: item.duty_type_name,
                                color_code: item.color_code,
                                icon_class: item.icon_class,
                                camp_name: item.camp_name || item.roster_camp_name,
                                roster_status: item.roster_status || item.status,
                                remarks: item.remarks || item.supervisor_remarks,
                                created_by_name: item.full_name || 'SNCO',
                                created_at: item.created_at,
                                personnel: []
                            };
                        }
                        crews[key].personnel.push({
                            service_number: item.service_number,
                            rank: item.rank,
                            full_name: item.full_name,
                            trade: item.trade,
                            personnel_status: item.personnel_status
                        });
                    });

                    // Build calendar events mapping
                    const mappedEvents = Object.values(crews).map(crew => {
                        // Check if any personnel has active leaves or inactive status
                        const hasConflict = crew.personnel.some(p => p.personnel_status === 'Leave' || p.personnel_status === 'Inactive');
                        
                        let bgColor = crew.color_code;
                        let borderColor = crew.color_code;
                        let textColor = '#ffffff';

                        if (hasConflict) {
                            bgColor = '#fef08a';
                            borderColor = '#ef4444';
                            textColor = '#854d0e';
                        }

                        return {
                            id: crew.crew_id,
                            title: `${crew.duty_type_name} (${crew.shift_name}) [${crew.personnel.length}]`,
                            start: crew.duty_date,
                            backgroundColor: bgColor,
                            borderColor: borderColor,
                            textColor: textColor,
                            extendedProps: {
                                ...crew,
                                hasConflict: hasConflict
                            }
                        };
                    });

                    successCallback(mappedEvents);
                })
                .catch(err => {
                    console.error("AJAX Error loading OCPROVST calendar:", err);
                    failureCallback(err);
                });
        },
        eventContent: function(arg) {
            const ev = arg.event.extendedProps;
            const title = arg.event.title;
            
            const outer = document.createElement('div');
            outer.className = 'fc-event-main-custom d-flex align-items-center justify-content-between px-1 py-0.5 text-truncate w-100';
            outer.style.fontSize = '0.72rem';
            
            let label = '';
            if (ev.hasConflict) {
                label += `<span class="text-danger me-1" title="Duplicate Assignment Conflict"><i class="fas fa-triangle-exclamation"></i></span>`;
            } else {
                label += `<span class="me-1"><i class="${ev.icon_class}"></i></span>`;
            }
            label += `<strong class="text-truncate">${title}</strong>`;
            outer.innerHTML = label;

            return { domNodes: [outer] };
        },
        eventClick: function(info) {
            const ev = info.event.extendedProps;

            // Populate text fields
            document.getElementById('mCrewId').textContent = 'CREW-' + ev.crew_id;
            document.getElementById('mCrewDate').textContent = formatDateStr(ev.duty_date);
            document.getElementById('mCrewType').textContent = ev.duty_type_name;
            document.getElementById('mCrewShift').textContent = `${ev.shift_name} (${ev.start_time.substring(0,5)} - ${ev.end_time.substring(0,5)})`;
            document.getElementById('mCrewLocation').textContent = ev.camp_name;
            document.getElementById('mRosterName').textContent = ev.roster_name + ' (ID: #' + ev.roster_id + ')';
            
            document.getElementById('mRosterCreator').textContent = ev.created_by_name;
            document.getElementById('mRosterStatus').textContent = ev.roster_status;
            document.getElementById('mCrewRemarks').textContent = ev.remarks || 'No remarks / directives specified.';

            // Conflict Warning logic
            const conflictAlert = document.getElementById('mConflictAlert');
            if (ev.hasConflict) {
                conflictAlert.style.display = 'block';
            } else {
                conflictAlert.style.display = 'none';
            }

            // Populate Personnel table
            const tbody = document.getElementById('mPersonnelTableBody');
            tbody.innerHTML = '';
            ev.personnel.forEach(p => {
                const warnBadge = (p.personnel_status === 'Leave' || p.personnel_status === 'Inactive') ?
                    `<span class="badge bg-danger bg-opacity-10 text-danger"><i class="fas fa-triangle-exclamation"></i> Status: ${p.personnel_status}</span>` : '🟢 Normal';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-monospace text-muted">${p.service_number}</td>
                    <td class="fw-bold">${p.rank} ${p.full_name}</td>
                    <td>${p.trade || '—'}</td>
                    <td>${warnBadge}</td>
                `;
                tbody.appendChild(tr);
            });

            // Fetch approval logs for this crew
            const histLog = document.getElementById('mHistoryLog');
            const histSection = document.getElementById('mHistorySection');
            histLog.innerHTML = 'Loading history...';
            histSection.style.display = 'block';

            fetch(`${BASE_URL}/rosters/crew-history?roster_id=${ev.roster_id}&duty_date=${ev.duty_date}&shift_id=${ev.shift_id}&duty_type_id=${ev.duty_type_id}`)
                .then(res => res.json())
                .then(history => {
                    if (history && history.length > 0) {
                        histLog.innerHTML = '';
                        history.forEach(h => {
                            const dateStr = new Date(h.created_at).toLocaleString();
                            histLog.innerHTML += `
                                <div class="border-bottom border-secondary border-opacity-10 pb-1.5 mb-1.5">
                                    [${dateStr}] <strong>${h.rank_short_name} ${h.full_name} (${h.username})</strong>: 
                                    <span class="badge ${h.action === 'Approve' ? 'bg-success' : 'bg-danger'} py-0.5 px-1.5 small text-white">${h.action}d</span>
                                    <div class="text-secondary small mt-0.5">Remarks: "${h.remarks || 'None'}"</div>
                                    <div class="text-muted" style="font-size:0.65rem;">IP: ${h.ip_address} &bull; Browser: ${h.user_agent}</div>
                                </div>
                            `;
                        });
                    } else {
                        histLog.innerHTML = '<div class="text-secondary italic">No history log recorded for this crew.</div>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    histLog.innerHTML = 'Error loading history log.';
                });

            // Update Approve link to point directly to the roster view
            const approveLink = document.getElementById('mApproveLink');
            if (approveLink) {
                approveLink.href = `${BASE_URL}/rosters/view?id=${ev.roster_id}`;
                approveLink.innerHTML = `<i class="fas fa-stamp"></i> Action Roster`;
            }

            // Show Bootstrap modal
            const modal = new bootstrap.Modal(document.getElementById('ocCrewDetailsModal'));
            modal.show();
        }
    });

    calendar.render();

    function formatDateStr(dateStr) {
        if (!dateStr) return '—';
        const parts = dateStr.split('-');
        const d = new Date(parts[0], parts[1] - 1, parts[2]);
        return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    }
});
</script>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
