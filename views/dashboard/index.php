<?php
include __DIR__ . '/../layout/header.php';
$today = date('l, d F Y');
?>

<!-- ===== WELCOME HEADER ===== -->
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-8 col-12">
        <h1 class="h2 fw-bold gradient-text">Welcome, <?= htmlspecialchars($rankName . ' ' . $fullName) ?></h1>
        <p class="text-secondary mb-0">Smart Provost Roster &mdash; <?= $today ?></p>
    </div>
    <div class="col-md-4 col-12 text-md-end d-none d-md-block">
        <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="SLAF Crest"
             style="height: 60px; width: auto; opacity: 0.85; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
    </div>
</div>

<!-- ===== DYNAMIC WATCH CALENDAR CARD ===== -->
<div class="glass-card p-4 animate-fade-in">
    <!-- Clickable Dashboard Summary Cards -->
    <div class="row g-2 mb-4 text-center" id="calendarSummaryCards">
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-primary border-opacity-35 cursor-pointer summary-card" data-filter="today" style="background: rgba(13,110,253,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Today's Duties</h6>
                <h4 class="fw-bold mb-0 text-primary" id="sumTodayCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-info border-opacity-35 cursor-pointer summary-card" data-filter="upcoming" style="background: rgba(13,202,240,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Upcoming</h6>
                <h4 class="fw-bold mb-0 text-info" id="sumUpcomingCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-warning border-opacity-35 cursor-pointer summary-card" data-filter="pending" style="background: rgba(245,158,11,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Pending Approval</h6>
                <h4 class="fw-bold mb-0 text-warning" id="sumPendingCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-success border-opacity-35 cursor-pointer summary-card" data-filter="completed" style="background: rgba(25,135,84,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Completed</h6>
                <h4 class="fw-bold mb-0 text-success" id="sumCompletedCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-danger border-opacity-35 cursor-pointer summary-card" data-filter="rejected" style="background: rgba(220,53,69,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Rejected</h6>
                <h4 class="fw-bold mb-0 text-danger" id="sumRejectedCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-secondary border-opacity-35 cursor-pointer summary-card" data-filter="cancelled" style="background: rgba(108,117,125,0.04); transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Cancelled</h6>
                <h4 class="fw-bold mb-0 text-secondary" id="sumCancelledCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-danger border-opacity-75 cursor-pointer summary-card bg-danger bg-opacity-10" data-filter="conflict" style="border: 1px solid #ef4444 !important; transition: transform 0.2s, box-shadow 0.2s; border-radius: 8px;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;"><i class="fas fa-triangle-exclamation text-warning me-1"></i>Conflicts</h6>
                <h4 class="fw-bold mb-0 text-danger" id="sumConflictCount">0</h4>
            </div>
        </div>
    </div>

    <!-- Filters Header -->
    <div class="row g-3 mb-4 p-3 rounded" style="background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.06);">
        <!-- Camp Selector (Visible for high privileges, hidden for camp-constrained roles) -->
        <div class="col-md-3 col-sm-6 <?= ($roleName === 'Administrator' || $roleName === 'Super Admin') ? '' : 'd-none' ?>">
            <label class="form-label text-secondary small fw-bold mb-1">Camp / Base</label>
            <select id="calFilterCamp" class="form-select form-control-custom">
                <option value="All">All Camps</option>
                <?php foreach ($camps as $c): ?>
                    <option value="<?= $c['camp_id'] ?>" <?= (int)$c['camp_id'] === (int)$activeCampId ? 'selected' : '' ?>><?= htmlspecialchars($c['camp_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($roleName !== 'Administrator' && $roleName !== 'Super Admin'): ?>
            <input type="hidden" id="calFilterCamp" value="<?= htmlspecialchars($activeCampId) ?>">
        <?php endif; ?>

        <div class="col-md-3 col-sm-6">
            <label class="form-label text-secondary small fw-bold mb-1">Duty Type</label>
            <select id="calFilterDutyType" class="form-select form-control-custom">
                <option value="">All Duty Types</option>
                <?php foreach ($activeDutyTypes as $dt): ?>
                    <option value="<?= $dt['duty_type_id'] ?>"><?= htmlspecialchars($dt['duty_type_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label text-secondary small fw-bold mb-1">Shift</label>
            <select id="calFilterShift" class="form-select form-control-custom">
                <option value="">All Shifts</option>
                <?php foreach ($shifts as $s): ?>
                    <option value="<?= $s['shift_id'] ?>"><?= htmlspecialchars($s['shift_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label text-secondary small fw-bold mb-1">Priority</label>
            <select id="calFilterPriority" class="form-select form-control-custom">
                <option value="">All Priorities</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </div>

        <div class="col-md-2 col-sm-6 d-flex align-items-end">
            <button type="button" id="btnResetCalFilters" class="btn btn-custom btn-custom-secondary w-100 py-2">
                <i class="fas fa-arrows-rotate me-1"></i> Reset
            </button>
        </div>

        <div class="col-12 mt-2">
            <label class="form-label text-secondary small fw-bold mb-1">Search Personnel or Roster</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary border-opacity-10 text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" id="calFilterSearch" class="form-control form-control-custom" placeholder="Search service number, name, or remarks...">
            </div>
        </div>
    </div>

    <!-- Watch Calendar -->
    <div id="dashboardWatchCalendar" class="text-dark p-2" style="background:#ffffff; border-radius: 12px; min-height: 600px; border: 1px solid rgba(0,0,0,0.1);"></div>

    <!-- Legend -->
    <div class="d-flex flex-wrap gap-2 mt-4 align-items-center justify-content-center p-3 rounded" style="background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05);">
        <strong class="text-secondary small me-2"><i class="fas fa-circle-info text-info me-1"></i> Duty Color Legend:</strong>
        <?php foreach ($activeDutyTypes as $dt): ?>
            <span class="badge d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded text-dark" style="background: <?= htmlspecialchars($dt['color_code']) ?>1c; border: 1px solid <?= htmlspecialchars($dt['color_code']) ?>44; color: <?= htmlspecialchars($dt['color_code']) ?> !important;">
                <i class="<?= htmlspecialchars($dt['icon_class']) ?>"></i> <?= htmlspecialchars($dt['duty_type_name']) ?>
            </span>
        <?php endforeach; ?>
        <span class="badge d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded text-dark" style="background: #fef08a; border: 1px solid #ef4444; color: #854d0e !important;">
            <i class="fas fa-triangle-exclamation text-danger"></i> Conflict Warning
        </span>
    </div>
</div>

<script>
const USER_ROLE = <?= json_encode($roleName) ?>;

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('dashboardWatchCalendar');
    let allLoadedEvents = [];
    let activeSummaryFilter = ''; // Holds active clickable card filter type

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'bootstrap5',
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            const campId = document.getElementById('calFilterCamp').value;
            const dutyTypeId = document.getElementById('calFilterDutyType').value;
            const shiftId = document.getElementById('calFilterShift').value;
            const priority = document.getElementById('calFilterPriority').value;
            const search = document.getElementById('calFilterSearch').value;

            const start = info.startStr.substring(0, 10);
            const end = info.endStr.substring(0, 10);

            let url = `${BASE_URL}/rosters/calendar-data?camp_id=${campId}&start=${start}&end=${end}`;
            if (dutyTypeId) url += `&duty_type_id=${dutyTypeId}`;
            if (shiftId) url += `&shift_id=${shiftId}`;
            if (priority) url += `&priority_level=${priority}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        failureCallback(data.error);
                        return;
                    }

                    allLoadedEvents = data;
                    
                    // Group assignments by date & service_number to check duplicate booking conflicts
                    const assignmentsByDateAndSvc = {};
                    data.forEach(ev => {
                        const key = ev.duty_date + '|' + ev.service_number;
                        if (!assignmentsByDateAndSvc[key]) {
                            assignmentsByDateAndSvc[key] = [];
                        }
                        assignmentsByDateAndSvc[key].push(ev);
                    });

                    // Build full calendar events mapping
                    const mappedEvents = data.map(ev => {
                        const dateSvcKey = ev.duty_date + '|' + ev.service_number;
                        const isDoubleBooked = assignmentsByDateAndSvc[dateSvcKey].length > 1;
                        const isConflict = isDoubleBooked || ev.personnel_status === 'Leave' || ev.personnel_status === 'Inactive';
                        
                        let bgColor = ev.color_code;
                        let borderColor = ev.color_code;
                        let textColor = '#ffffff';

                        if (isConflict) {
                            bgColor = '#fef08a'; // yellow background
                            borderColor = '#ef4444'; // red border
                            textColor = '#854d0e'; // dark text
                        }

                        // Calculate start and end datetime
                        const startDatetime = ev.duty_start_datetime.replace(' ', 'T');
                        const endDatetime = ev.duty_end_datetime.replace(' ', 'T');

                        return {
                            id: ev.assignment_id,
                            title: ev.duty_type_name,
                            start: startDatetime,
                            end: endDatetime,
                            backgroundColor: bgColor,
                            borderColor: borderColor,
                            textColor: textColor,
                            extendedProps: {
                                ...ev,
                                hasConflict: isConflict,
                                isDoubleBooked: isDoubleBooked
                            }
                        };
                    });

                    // Compute summary metrics
                    updateSummaryCards(mappedEvents);

                    // Apply card filters
                    let filteredEvents = mappedEvents;
                    if (activeSummaryFilter) {
                        filteredEvents = mappedEvents.filter(ev => {
                            const props = ev.extendedProps;
                            const todayYmd = new Date().toISOString().substring(0, 10);
                            
                            switch (activeSummaryFilter) {
                                case 'today':
                                    return props.duty_date === todayYmd;
                                case 'upcoming':
                                    return props.duty_date > todayYmd;
                                case 'pending':
                                    return props.status === 'Pending' || props.roster_status === 'Submitted';
                                case 'completed':
                                    return props.status === 'Approved';
                                case 'rejected':
                                    return props.status === 'Rejected';
                                case 'cancelled':
                                    return props.roster_status === 'Rejected' || props.status === 'Rejected';
                                case 'conflict':
                                    return props.hasConflict === true;
                                default:
                                    return true;
                            }
                        });
                    }

                    successCallback(filteredEvents);
                })
                .catch(err => {
                    console.error("AJAX Error calendar load:", err);
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
            label += `<strong class="me-1 text-truncate">${title}</strong>: ${ev.service_number}`;
            outer.innerHTML = label;

            return { domNodes: [outer] };
        },
        eventDidMount: function(info) {
            const ev = info.event.extendedProps;
            const statusLabel = ev.hasConflict ? '⚠️ Duplicate Assignment / Conflict' : ev.personnel_status;
            
            const startHour = ev.duty_start_datetime.substring(11, 16);
            const endHour = ev.duty_end_datetime.substring(11, 16);
            let timeRangeText = '';
            if (ev.duty_start_datetime.substring(0, 10) !== ev.duty_end_datetime.substring(0, 10)) {
                timeRangeText = `${ev.duty_start_datetime.substring(0, 10)} ${startHour} → ${ev.duty_end_datetime.substring(0, 10)} ${endHour}`;
            } else {
                timeRangeText = `${ev.duty_start_datetime.substring(0, 10)} ${startHour} → ${endHour}`;
            }

            const tooltipTitle = `${ev.duty_type_name} (${ev.shift_name})\n` +
                                 `Time: ${timeRangeText}\n` +
                                 `Personnel: ${ev.rank} ${ev.full_name}\n` +
                                 `Status: ${statusLabel}`;

            info.el.setAttribute('title', tooltipTitle);
            info.el.setAttribute('data-bs-toggle', 'tooltip');
            info.el.setAttribute('data-bs-placement', 'top');
            new bootstrap.Tooltip(info.el);
        },
        dateClick: function(info) {
            showDayCrewTimeline(info.dateStr.substring(0, 10));
        },
        eventClick: function(info) {
            showDayCrewTimeline(info.event.startStr.substring(0, 10));
        }
    });

    calendar.render();

    // Trigger filters on selection changes
    document.getElementById('calFilterCamp').addEventListener('change', () => calendar.refetchEvents());
    document.getElementById('calFilterDutyType').addEventListener('change', () => calendar.refetchEvents());
    document.getElementById('calFilterShift').addEventListener('change', () => calendar.refetchEvents());
    document.getElementById('calFilterPriority').addEventListener('change', () => calendar.refetchEvents());

    // Debounce search
    let searchDebounce;
    document.getElementById('calFilterSearch').addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            calendar.refetchEvents();
        }, 300);
    });

    // Reset Filters button
    document.getElementById('btnResetCalFilters').addEventListener('click', () => {
        document.getElementById('calFilterDutyType').value = '';
        document.getElementById('calFilterShift').value = '';
        document.getElementById('calFilterPriority').value = '';
        document.getElementById('calFilterSearch').value = '';
        
        // Reset summary card highlights
        activeSummaryFilter = '';
        document.querySelectorAll('.summary-card').forEach(c => {
            c.style.boxShadow = '';
            c.style.transform = '';
        });

        const campSel = document.getElementById('calFilterCamp');
        if (campSel && !campSel.disabled) {
            campSel.value = 'All';
        }
        calendar.refetchEvents();
    });

    // Handle Clickable Summary Cards filtering
    document.querySelectorAll('.summary-card').forEach(card => {
        card.addEventListener('click', () => {
            const filterType = card.getAttribute('data-filter');
            
            if (activeSummaryFilter === filterType) {
                activeSummaryFilter = '';
                card.style.boxShadow = '';
                card.style.transform = '';
            } else {
                document.querySelectorAll('.summary-card').forEach(c => {
                    c.style.boxShadow = '';
                    c.style.transform = '';
                });
                
                activeSummaryFilter = filterType;
                card.style.boxShadow = '0 0 0 3px #0d6efd';
                card.style.transform = 'translateY(-2px)';
            }
            
            calendar.refetchEvents();
        });
    });

    function updateSummaryCards(events) {
        const todayYmd = new Date().toISOString().substring(0, 10);
        let today = 0, upcoming = 0, pending = 0, completed = 0, rejected = 0, cancelled = 0, conflict = 0;

        events.forEach(ev => {
            const props = ev.extendedProps;
            const start = props.duty_date;
            
            if (start === todayYmd) today++;
            if (start > todayYmd) upcoming++;
            if (props.status === 'Pending' || props.roster_status === 'Submitted') pending++;
            if (props.status === 'Approved') completed++;
            if (props.status === 'Rejected') rejected++;
            if (props.roster_status === 'Rejected' || props.status === 'Rejected') cancelled++;
            if (props.hasConflict) conflict++;
        });

        document.getElementById('sumTodayCount').textContent = today;
        document.getElementById('sumUpcomingCount').textContent = upcoming;
        document.getElementById('sumPendingCount').textContent = pending;
        document.getElementById('sumCompletedCount').textContent = completed;
        document.getElementById('sumRejectedCount').textContent = rejected;
        document.getElementById('sumCancelledCount').textContent = cancelled;
        document.getElementById('sumConflictCount').textContent = conflict;
    }

    // Display Timeline details in SweetAlert2 popup
    function showDayCrewTimeline(dateStr) {
        const dayEvents = allLoadedEvents.filter(ev => ev.duty_date === dateStr);
        
        if (dayEvents.length === 0) {
            Swal.fire({
                title: `Duty Roster &mdash; ${formatDateStr(dateStr)}`,
                text: 'No duties scheduled for this date.',
                icon: 'info',
                background: '#ffffff',
                confirmButtonText: 'Close',
                customClass: {
                    confirmButton: 'btn btn-secondary px-4 py-2'
                }
            });
            return;
        }

        // Group by shift start datetime
        dayEvents.sort((a, b) => a.duty_start_datetime.localeCompare(b.duty_start_datetime));

        let htmlContent = `
            <div class="text-start mt-3">
                <p class="text-secondary mb-4 small"><i class="fas fa-info-circle text-info me-1"></i> Duty deployment and shift timeline of guard crew for the selected date.</p>
                <div class="timeline-container ps-3 border-start border-primary border-opacity-25" style="max-height: 400px; overflow-y: auto;">
        `;

        dayEvents.forEach(ev => {
            const isConflict = (ev.personnel_status === 'Leave' || ev.personnel_status === 'Inactive');
            const conflictWarning = isConflict ? 
                `<span class="badge bg-danger bg-opacity-25 border border-danger border-opacity-25 text-danger px-2.5 py-0.5 rounded small ms-2"><i class="fas fa-triangle-exclamation"></i> Conflict: ${ev.personnel_status}</span>` : '';
            
            let actionHtml = '';
            if (USER_ROLE !== 'Airman') {
                actionHtml += `
                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border small py-1" style="font-size: 0.68rem; font-weight: 500; border-radius: 4px;">
                            Roster: ${ev.roster_name} (${ev.roster_status})
                        </span>
                `;
                
                if ((USER_ROLE === 'Warrant Officer IC' || USER_ROLE === 'Administrator' || USER_ROLE === 'Super Admin') && 
                    (ev.roster_status === 'Draft' || ev.roster_status === 'Rejected')) {
                    actionHtml += `
                        <a href="${BASE_URL}/rosters/create?id=${ev.roster_id}" class="btn btn-warning btn-sm py-0.5 px-2 text-dark d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 600; border-radius: 4px;">
                            <i class="fas fa-pen-to-square"></i> Edit
                        </a>
                    `;
                } else if (ev.roster_status === 'Submitted' && 
                    (USER_ROLE === 'OCPROVST' || USER_ROLE === 'Warrant Officer IC' || USER_ROLE === 'Administrator' || USER_ROLE === 'Super Admin')) {
                    actionHtml += `
                        <a href="${BASE_URL}/rosters/view?id=${ev.roster_id}" class="btn btn-success btn-sm py-0.5 px-2 text-white d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 600; border-radius: 4px;">
                            <i class="fas fa-stamp"></i> Action
                        </a>
                    `;
                } else {
                    actionHtml += `
                        <a href="${BASE_URL}/rosters/view?id=${ev.roster_id}" class="btn btn-outline-primary btn-sm py-0.5 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 600; border-radius: 4px;">
                            <i class="fas fa-eye"></i> View Roster
                        </a>
                    `;
                }
                actionHtml += `</div>`;
            }

            htmlContent += `
                <div class="timeline-item position-relative mb-4">
                    <div class="timeline-badge" style="background-color: ${ev.color_code}; width: 12px; height: 12px; border-radius: 50%; position: absolute; left: -21px; top: 6px; border: 2px solid #ffffff; box-shadow: 0 0 4px ${ev.color_code}88;"></div>
                    <div class="ms-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-opacity-25 border px-2.5 py-0.5 rounded small" style="background-color: ${ev.color_code}22; color: ${ev.color_code}; border-color: ${ev.color_code}44;">
                                <i class="${ev.icon_class} me-1"></i> ${ev.duty_type_name}
                            </span>
                            <span class="text-secondary small font-monospace"><i class="far fa-clock"></i> ${
                                ev.duty_start_datetime.substring(0, 10) !== ev.duty_end_datetime.substring(0, 10) ?
                                `${ev.duty_start_datetime.substring(5, 10)} ${ev.duty_start_datetime.substring(11, 16)} → ${ev.duty_end_datetime.substring(5, 10)} ${ev.duty_end_datetime.substring(11, 16)}` :
                                `${ev.duty_start_datetime.substring(11, 16)} - ${ev.duty_end_datetime.substring(11, 16)}`
                            } (${ev.shift_name})</span>
                            ${conflictWarning}
                        </div>
                        <div class="mt-2 text-dark">
                            <span class="fw-bold text-info">${ev.rank} ${ev.initials} ${ev.full_name}</span>
                            <span class="text-secondary small">(${ev.service_number} &bull; ${ev.trade})</span>
                        </div>
                        ${actionHtml}
                        ${ev.remarks ? `<div class="text-muted small mt-1 italic" style="font-size:0.75rem;"><em>Remarks: ${ev.remarks}</em></div>` : ''}
                    </div>
                </div>
            `;
        });

        htmlContent += `
                </div>
            </div>
        `;

        Swal.fire({
            title: `Duty Crew &mdash; ${formatDateStr(dateStr)}`,
            html: htmlContent,
            icon: 'success',
            background: '#ffffff',
            confirmButtonText: '<i class="fas fa-check"></i> Acknowledge',
            confirmButtonColor: '#0ea5e9',
            customClass: {
                popup: 'glass-card text-dark border-light-subtle',
                confirmButton: 'btn btn-primary px-4 py-2 small'
            },
            buttonsStyling: false
        });
    }

    function formatDateStr(dateStr) {
        if (!dateStr) return '—';
        const parts = dateStr.split('-');
        const d = new Date(parts[0], parts[1] - 1, parts[2]);
        return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    }
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
