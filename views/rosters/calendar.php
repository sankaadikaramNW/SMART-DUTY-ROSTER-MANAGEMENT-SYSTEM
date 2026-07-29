<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-calendar-check"></i> Monthly Watch Calendar</h2>
        <p class="text-secondary">Comprehensive view of all guard watches scheduled on bases.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= BASE_URL ?>/rosters" class="btn btn-custom btn-custom-secondary">
            <i class="fas fa-list me-1"></i> Roster List View
        </a>
    </div>
</div>

<div class="glass-card p-4">
    <!-- Clickable Dashboard Summary Cards -->
    <div class="row g-2 mb-4 text-center" id="calendarSummaryCards">
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-primary border-opacity-35 cursor-pointer summary-card" data-filter="today" style="background: rgba(13,110,253,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Today's Duties</h6>
                <h4 class="fw-bold mb-0 text-primary" id="sumTodayCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-info border-opacity-35 cursor-pointer summary-card" data-filter="upcoming" style="background: rgba(13,202,240,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Upcoming</h6>
                <h4 class="fw-bold mb-0 text-info" id="sumUpcomingCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-warning border-opacity-35 cursor-pointer summary-card" data-filter="pending" style="background: rgba(245,158,11,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Pending Approval</h6>
                <h4 class="fw-bold mb-0 text-warning" id="sumPendingCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-success border-opacity-35 cursor-pointer summary-card" data-filter="completed" style="background: rgba(25,135,84,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Completed</h6>
                <h4 class="fw-bold mb-0 text-success" id="sumCompletedCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-danger border-opacity-35 cursor-pointer summary-card" data-filter="rejected" style="background: rgba(220,53,69,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Rejected</h6>
                <h4 class="fw-bold mb-0 text-danger" id="sumRejectedCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-secondary border-opacity-35 cursor-pointer summary-card" data-filter="cancelled" style="background: rgba(108,117,125,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;">Cancelled</h6>
                <h4 class="fw-bold mb-0 text-secondary" id="sumCancelledCount">0</h4>
            </div>
        </div>
        <div class="col-md col-sm-4 col-6">
            <div class="glass-card p-2 border-danger border-opacity-75 cursor-pointer summary-card bg-danger bg-opacity-10" data-filter="conflict" style="border: 1px solid #ef4444 !important; transition: transform 0.2s, box-shadow 0.2s;">
                <h6 class="text-secondary small mb-1" style="font-size: 0.72rem;"><i class="fas fa-triangle-exclamation text-warning me-1"></i>Conflicts</h6>
                <h4 class="fw-bold mb-0 text-danger" id="sumConflictCount">0</h4>
            </div>
        </div>
    </div>

    <!-- Dynamic AJAX Filters -->
    <div class="row g-3 mb-4 p-3 rounded" style="background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.06);">
        <!-- Camp Selector -->
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

        <div class="col-md-2 col-sm-6">
            <label class="form-label text-secondary small fw-bold mb-1">Status</label>
            <select id="calFilterStatus" class="form-select form-control-custom">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>

        <!-- Assigned Person autocomplete -->
        <div class="col-md-3 col-sm-6 <?= ($roleName === 'Airman') ? 'd-none' : '' ?>">
            <label class="form-label text-secondary small fw-bold mb-1">Assigned Person</label>
            <div class="position-relative">
                <input type="text" id="calFilterPersonSearch" class="form-control form-control-custom" placeholder="Svc No or Name..." autocomplete="off">
                <input type="hidden" id="calFilterPerson" value="">
                <button type="button" id="calFilterClearPerson" class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" style="display:none; padding:1px 6px; font-size:0.75rem;"><i class="fas fa-xmark"></i></button>
                <div id="calFilterSuggestions" class="dropdown-menu w-100 bg-white border shadow-lg" style="display:none; max-height:200px; overflow-y:auto; z-index:1050; position:absolute;"></div>
            </div>
        </div>
        <?php if ($roleName === 'Airman'): ?>
            <input type="hidden" id="calFilterPerson" value="<?= htmlspecialchars(Session::get('service_number')) ?>">
        <?php endif; ?>

        <div class="col-md-6 col-sm-6">
            <label class="form-label text-secondary small fw-bold mb-1">General Search</label>
            <div class="input-group input-group-custom">
                <span class="input-group-text bg-transparent border-secondary border-opacity-10 text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" id="calFilterSearch" class="form-control form-control-custom" placeholder="Search roster name, remarks, or personnel...">
            </div>
        </div>

        <div class="col-md-3 col-sm-6 d-flex align-items-end">
            <button type="button" id="btnResetAllCalFilters" class="btn btn-custom btn-custom-secondary w-100 py-2">
                <i class="fas fa-arrows-rotate me-1"></i> Reset Filters
            </button>
        </div>
    </div>

    <!-- FullCalendar Container -->
    <div id="dutyWatchCalendar" class="text-dark p-2" style="background:#ffffff; border-radius: 12px; min-height: 550px; border: 1px solid rgba(0,0,0,0.1);"></div>

    <!-- Dynamic Legend -->
    <div class="d-flex flex-wrap gap-2 mt-4 align-items-center justify-content-center p-3 rounded" style="background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05);">
        <strong class="text-secondary small me-2"><i class="fas fa-circle-info text-info me-1"></i> Duty Legend:</strong>
        <?php foreach ($activeDutyTypes as $dt): ?>
            <span class="badge d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded text-dark" style="background: <?= htmlspecialchars($dt['color_code']) ?>1c; border: 1px solid <?= htmlspecialchars($dt['color_code']) ?>44; color: <?= htmlspecialchars($dt['color_code']) ?> !important;">
                <i class="<?= htmlspecialchars($dt['icon_class']) ?>"></i> <?= htmlspecialchars($dt['duty_type_name']) ?>
            </span>
        <?php endforeach; ?>
        <span class="badge d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded text-dark" style="background: #fef08a; border: 1px solid #ef4444; color: #854d0e !important;">
            <i class="fas fa-triangle-exclamation text-danger"></i> ⚠ Duplicate Assignment / Conflict
        </span>
    </div>
</div>

<!-- Modal for Calendar Event click -->
<div class="modal fade" id="calendarEventModal" tabindex="-1" aria-labelledby="calendarEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card text-dark">
            <div class="modal-header bg-primary bg-opacity-10 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="calendarEventModalLabel">
                    <i class="fas fa-shield-halved text-primary me-2"></i> Watch Duty Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-dark">
                <!-- Conflict warning container inside modal -->
                <div id="modalConflictAlert" style="display: none;" class="mb-3">
                    <div class="alert alert-danger border-danger p-3 rounded d-flex align-items-center gap-2 mb-0" style="background: rgba(239, 68, 68, 0.08);">
                        <i class="fas fa-triangle-exclamation fs-5 text-danger"></i>
                        <div>
                            <strong class="text-danger">Schedule Conflict Warning:</strong> 
                            <span class="small text-danger d-block">This personnel has a duplicate booking on this date, or is currently on leave/inactive.</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Column 1: Duty Details -->
                    <div class="col-md-6 border-end border-secondary border-opacity-10 pr-md-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-circle-info"></i> Duty & Shift Parameters</h6>
                        <table class="table table-sm table-borderless small mb-0 align-middle">
                            <tr>
                                <td class="text-secondary py-1" style="width: 35%;">Assignment ID:</td>
                                <td class="fw-bold py-1 text-dark" id="mDutyNum">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Date:</td>
                                <td class="fw-bold py-1 text-info" id="mDutyDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Type:</td>
                                <td class="py-1">
                                    <span class="badge px-2.5 py-1 rounded" id="mDutyTypeBadge" style="background: #000; color: #fff;">
                                        <i id="mDutyIcon"></i> <span id="mDutyName">-</span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Shift Name:</td>
                                <td class="fw-bold py-1 text-dark" id="mShiftName">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duty Time:</td>
                                <td class="font-monospace py-1 text-dark" id="mShiftTimings">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Duration:</td>
                                <td class="fw-bold py-1 text-dark" id="mDuration">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Approval Status:</td>
                                <td class="py-1">
                                    <span class="badge" id="mApprovalStatusBadge">-</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Camp / Base:</td>
                                <td class="py-1 text-dark" id="mCampName">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Priority:</td>
                                <td class="py-1">
                                    <span class="badge" id="mPriorityBadge">-</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Column 2: Personnel Details -->
                    <div class="col-md-6 pl-md-4">
                        <h6 class="fw-bold text-success mb-3"><i class="fas fa-user-shield"></i> Assigned Personnel</h6>
                        <table class="table table-sm table-borderless small mb-0 align-middle">
                            <tr>
                                <td class="text-secondary py-1" style="width: 35%;">Service Number:</td>
                                <td class="font-monospace fw-bold py-1 text-dark" id="mSvcNo">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Rank:</td>
                                <td class="fw-bold py-1 text-info" id="mRank">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Full Name:</td>
                                <td class="fw-bold py-1 text-dark" id="mFullName">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Trade:</td>
                                <td class="py-1 text-dark" id="mTrade">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Personnel Camp:</td>
                                <td class="py-1 text-dark" id="mPersonnelCamp">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary py-1">Current Status:</td>
                                <td class="py-1"><span class="badge bg-light text-dark border animate-pulse" id="mPersonnelStatus">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-3">

                <!-- Row 3: Workflow Audit Logs & Remarks -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-2"><i class="fas fa-clipboard-question"></i> Approval Workflow & Remarks</h6>
                        <div class="p-2.5 bg-light bg-opacity-50 border border-secondary border-opacity-10 rounded mb-3">
                            <div class="row text-center g-2 small">
                                <div class="col-sm-4 border-end border-secondary border-opacity-10">
                                    <div class="text-secondary text-dark-50" style="font-size:0.7rem;">CREATED BY</div>
                                    <div class="fw-bold mt-0.5 text-dark" id="mCreatedBy">-</div>
                                </div>
                                <div class="col-sm-4 border-end border-secondary border-opacity-10">
                                    <div class="text-secondary text-dark-50" style="font-size:0.7rem;">APPROVED BY</div>
                                    <div class="fw-bold mt-0.5 text-dark" id="mApprovedBy">-</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-secondary text-dark-50" style="font-size:0.7rem;">APPROVAL DATE</div>
                                    <div class="fw-bold mt-0.5 text-dark" id="mApprovalDate">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label text-secondary small mb-1 fw-bold">Assignment Remarks / Directives</label>
                            <div class="p-2 bg-light bg-opacity-75 border rounded small text-dark" id="mRemarks" style="min-height: 40px; color:#475569;">-</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top border-secondary border-opacity-10 justify-content-between py-3">
                <!-- Left-side print button -->
                <button type="button" class="btn btn-outline-secondary btn-sm" id="mPrintBtn">
                    <i class="fas fa-print"></i> Print Details
                </button>
                
                <!-- Right-side action buttons -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm px-3" id="mApproveBtn" style="display:none;"><i class="fas fa-check"></i> Approve</button>
                    <button type="button" class="btn btn-danger btn-sm px-3" id="mRejectBtn" style="display:none;"><i class="fas fa-xmark"></i> Reject</button>
                    <a href="#" class="btn btn-warning btn-sm text-dark px-3" id="mEditBtn" style="display:none;"><i class="fas fa-pen-to-square"></i> Edit Roster</a>
                    <a href="#" class="btn btn-primary btn-sm px-3" id="mViewBtn"><i class="fas fa-eye"></i> View Full Roster</a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal action submission form -->
<form id="modalActionForm" action="<?= BASE_URL ?>/rosters/action" method="POST" style="display:none;">
    <?= Security::csrfField() ?>
    <input type="hidden" name="roster_id" id="modalActionRosterId">
    <input type="hidden" name="action" id="modalActionType">
    <input type="hidden" name="remarks" id="modalActionRemarks">
</form>

<!-- Watch Duty Calendar Implementation JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Autocomplete for AJAX filters Assigned Person
    const personInput = document.getElementById('calFilterPersonSearch');
    const personHidden = document.getElementById('calFilterPerson');
    const personSuggestions = document.getElementById('calFilterSuggestions');
    const personClearBtn = document.getElementById('calFilterClearPerson');

    if (personInput) {
        let debounceTimer;
        personInput.addEventListener('input', () => {
            const query = personInput.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                personSuggestions.innerHTML = '';
                personSuggestions.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        personSuggestions.innerHTML = '';
                        if (data && data.length > 0) {
                            data.forEach(item => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'dropdown-item small py-2 text-start text-dark';
                                btn.innerHTML = `<strong>${item.service_number}</strong> - ${item.rank_short_name} ${item.initials} ${item.full_name.split(' ').pop()}`;
                                btn.addEventListener('click', () => {
                                    personInput.value = `${item.service_number} - ${item.rank_short_name} ${item.initials} ${item.full_name.split(' ').pop()}`;
                                    personHidden.value = item.service_number;
                                    personClearBtn.style.display = 'block';
                                    personSuggestions.innerHTML = '';
                                    personSuggestions.style.display = 'none';
                                    calendar.refetchEvents();
                                });
                                personSuggestions.appendChild(btn);
                            });
                            personSuggestions.style.display = 'block';
                        } else {
                            personSuggestions.innerHTML = '<div class="dropdown-item text-muted small py-2 text-center">No matches found</div>';
                            personSuggestions.style.display = 'block';
                        }
                    });
            }, 300);
        });

        personClearBtn.addEventListener('click', () => {
            personInput.value = '';
            personHidden.value = '';
            personClearBtn.style.display = 'none';
            personSuggestions.innerHTML = '';
            personSuggestions.style.display = 'none';
            calendar.refetchEvents();
        });

        document.addEventListener('click', (e) => {
            if (e.target !== personInput && e.target !== personSuggestions) {
                personSuggestions.style.display = 'none';
            }
        });
    }

    // Initialize FullCalendar
    const calendarEl = document.getElementById('dutyWatchCalendar');
    let activeSummaryFilter = ''; // Holds active clickable card filter type
    
    // We will group loaded events to compute conflicts and stats
    let allLoadedEvents = [];

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
            const campId = document.getElementById('calFilterCamp').value;
            const dutyTypeId = document.getElementById('calFilterDutyType').value;
            const shiftId = document.getElementById('calFilterShift').value;
            const priority = document.getElementById('calFilterPriority').value;
            const status = document.getElementById('calFilterStatus').value;
            const person = document.getElementById('calFilterPerson').value;
            const search = document.getElementById('calFilterSearch').value;

            const start = info.startStr.substring(0, 10);
            const end = info.endStr.substring(0, 10);

            let url = `${BASE_URL}/rosters/calendar-data?camp_id=${campId}&start=${start}&end=${end}`;
            if (dutyTypeId) url += `&duty_type_id=${dutyTypeId}`;
            if (shiftId) url += `&shift_id=${shiftId}`;
            if (priority) url += `&priority_level=${priority}`;
            if (status) url += `&status=${status}`;
            if (person) url += `&service_number=${encodeURIComponent(person)}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
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
                            bgColor = '#fef08a'; // yellow-100 soft background
                            borderColor = '#ef4444'; // red border
                            textColor = '#854d0e'; // dark yellow text
                        }

                        const nameParts = ev.full_name.trim().split(' ');
                        const lastName = nameParts[nameParts.length - 1];

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
                                isDoubleBooked: isDoubleBooked,
                                lastName: lastName
                            }
                        };
                    });

                    // Compute summary metrics
                    updateSummaryCards(mappedEvents);

                    // Apply the clickable summary card filter if set
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
            // Add hover tooltip using Bootstrap Tooltip
            const ev = info.event.extendedProps;
            const statusLabel = ev.hasConflict ? '⚠️ Duplicate Assignment / Conflict' : ev.status;
            
            const startHour = ev.duty_start_datetime.substring(11, 16);
            const endHour = ev.duty_end_datetime.substring(11, 16);
            let timeRangeText = '';
            if (ev.duty_start_datetime.substring(0, 10) !== ev.duty_end_datetime.substring(0, 10)) {
                timeRangeText = `${ev.duty_start_datetime.substring(0, 10)} ${startHour} → ${ev.duty_end_datetime.substring(0, 10)} ${endHour}`;
            } else {
                timeRangeText = `${startHour} - ${endHour}`;
            }

            const tooltipTitle = `${ev.duty_type_name} (${ev.shift_name})\n` +
                                 `Time: ${timeRangeText}\n` +
                                 `Personnel: ${ev.rank} ${ev.full_name}\n` +
                                 `Status: ${statusLabel}\n` +
                                 `Priority: ${ev.priority_level}`;

            info.el.setAttribute('title', tooltipTitle);
            info.el.setAttribute('data-bs-toggle', 'tooltip');
            info.el.setAttribute('data-bs-placement', 'top');
            
            new bootstrap.Tooltip(info.el);
        },
        eventClick: function(info) {
            const ev = info.event.extendedProps;
            
            // Populate Details Modal fields
            document.getElementById('mDutyNum').textContent = '#' + ev.assignment_id;
            document.getElementById('mDutyDate').textContent = formatDateStr(ev.duty_date);
            document.getElementById('mDutyName').textContent = ev.duty_type_name;
            document.getElementById('mDutyIcon').className = ev.icon_class + ' me-1';
            
            // Roster badges
            const badge = document.getElementById('mDutyTypeBadge');
            badge.style.backgroundColor = ev.color_code;
            badge.style.color = '#ffffff';

            document.getElementById('mShiftName').textContent = ev.shift_name;
            
            // Duty Time (Shift Timings)
            const startD = new Date(ev.duty_start_datetime.replace(' ', 'T'));
            const endD = new Date(ev.duty_end_datetime.replace(' ', 'T'));
            
            const startStrFormatted = formatDateStr(ev.duty_start_datetime.substring(0, 10));
            const endStrFormatted = formatDateStr(ev.duty_end_datetime.substring(0, 10));
            const startHour = ev.duty_start_datetime.substring(11, 16);
            const endHour = ev.duty_end_datetime.substring(11, 16);
            
            let dutyTimeText = '';
            if (ev.duty_start_datetime.substring(0, 10) !== ev.duty_end_datetime.substring(0, 10)) {
                dutyTimeText = `${startStrFormatted} ${startHour} hrs → ${endStrFormatted} ${endHour} hrs`;
            } else {
                dutyTimeText = `${startStrFormatted} ${startHour} hrs → ${endHour} hrs`;
            }
            document.getElementById('mShiftTimings').textContent = dutyTimeText;

            // Duration calculation
            document.getElementById('mDuration').textContent = `${parseFloat(ev.duty_duration_hours)} Hours`;

            // Approval Status Badge
            const appStatusBadge = document.getElementById('mApprovalStatusBadge');
            const rosterStatus = ev.roster_status || ev.status;
            appStatusBadge.textContent = rosterStatus;
            appStatusBadge.className = 'badge';
            if (rosterStatus === 'Approved') {
                appStatusBadge.classList.add('bg-success');
            } else if (rosterStatus === 'Pending' || rosterStatus === 'Submitted') {
                appStatusBadge.classList.add('bg-warning', 'text-dark');
            } else if (rosterStatus === 'Rejected') {
                appStatusBadge.classList.add('bg-danger');
            } else {
                appStatusBadge.classList.add('bg-secondary');
            }

            document.getElementById('mCampName').textContent = ev.roster_camp_name || ev.camp_name;
            
            // Priority styling
            const prioBadge = document.getElementById('mPriorityBadge');
            prioBadge.textContent = ev.priority_level;
            prioBadge.className = 'badge';
            if (ev.priority_level === 'High') {
                prioBadge.classList.add('bg-danger');
            } else if (ev.priority_level === 'Medium') {
                prioBadge.classList.add('bg-warning', 'text-dark');
            } else {
                prioBadge.classList.add('bg-secondary');
            }

            document.getElementById('mSvcNo').textContent = ev.service_number;
            document.getElementById('mRank').textContent = ev.rank;
            document.getElementById('mFullName').textContent = ev.full_name;
            document.getElementById('mTrade').textContent = ev.trade || 'Provost';
            document.getElementById('mPersonnelCamp').textContent = ev.personnel_camp_name || 'Own Base';
            
            // Personnel Status
            const pStatus = document.getElementById('mPersonnelStatus');
            pStatus.textContent = ev.personnel_status || 'Active';
            pStatus.className = 'badge';
            if (ev.personnel_status === 'Leave') {
                pStatus.classList.add('bg-danger');
            } else if (ev.personnel_status === 'Temporary Duty') {
                pStatus.classList.add('bg-warning', 'text-dark');
            } else {
                pStatus.classList.add('bg-success');
            }

            document.getElementById('mCreatedBy').textContent = ev.creator_rank ? `${ev.creator_rank} ${ev.creator_name}` : (ev.creator_name || 'Admin');
            document.getElementById('mApprovedBy').textContent = ev.approver_name ? `${ev.approver_rank} ${ev.approver_name}` : '—';
            document.getElementById('mApprovalDate').textContent = ev.roster_approved_at ? formatDateStr(ev.roster_approved_at) : '—';
            document.getElementById('mRemarks').textContent = ev.remarks || 'No remarks provided.';

            // Conflict Warning Alert in modal
            const conflictAlert = document.getElementById('modalConflictAlert');
            if (ev.hasConflict) {
                conflictAlert.style.display = 'block';
            } else {
                conflictAlert.style.display = 'none';
            }

            // Button actions setup
            const role = <?= json_encode($roleName) ?>;
            
            // 1. Full Roster link
            const viewBtn = document.getElementById('mViewBtn');
            viewBtn.setAttribute('href', `${BASE_URL}/rosters/view?id=${ev.roster_id}`);

            // 2. Edit Button
            const editBtn = document.getElementById('mEditBtn');
            if ((role === 'SNCO' || role === 'Administrator' || role === 'Super Admin') && (ev.roster_status === 'Draft' || ev.roster_status === 'Rejected')) {
                editBtn.style.display = 'inline-block';
                editBtn.setAttribute('href', `${BASE_URL}/rosters/create?id=${ev.roster_id}`);
            } else {
                editBtn.style.display = 'none';
            }

            // 3. Approve / Reject Buttons (for OCPROVST & Admins)
            const approveBtn = document.getElementById('mApproveBtn');
            const rejectBtn = document.getElementById('mRejectBtn');
            if ((role === 'OCPROVST' || role === 'Administrator' || role === 'Super Admin') && ev.roster_status === 'Submitted') {
                approveBtn.style.display = 'inline-block';
                rejectBtn.style.display = 'inline-block';

                approveBtn.onclick = function() {
                    if (confirm("Are you sure you want to approve and publish this roster?")) {
                        submitRosterAction(ev.roster_id, 'Approve');
                    }
                };

                rejectBtn.onclick = function() {
                    const rem = prompt("Please enter the reason for rejection:");
                    if (rem !== null) {
                        submitRosterAction(ev.roster_id, 'Reject', rem);
                    }
                };
            } else {
                approveBtn.style.display = 'none';
                rejectBtn.style.display = 'none';
            }

            // 4. Print Details Button
            const printBtn = document.getElementById('mPrintBtn');
            printBtn.onclick = function() {
                const printUrl = `${BASE_URL}/reports/generate?camp_id=${ev.camp_id}&start_date=${ev.duty_date}&end_date=${ev.duty_date}&export_type=print`;
                window.open(printUrl, '_blank');
            };

            const detailsModal = new bootstrap.Modal(document.getElementById('calendarEventModal'));
            detailsModal.show();
        }
    });

    calendar.render();

    // Trigger filters updates on change
    const filterIds = ['calFilterCamp', 'calFilterDutyType', 'calFilterShift', 'calFilterPriority', 'calFilterStatus', 'calFilterSearch'];
    filterIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                calendar.refetchEvents();
            });
            if (el.tagName === 'INPUT') {
                el.addEventListener('input', () => {
                    calendar.refetchEvents();
                });
            }
        }
    });

    // Reset Filters click
    document.getElementById('btnResetAllCalFilters').addEventListener('click', () => {
        document.getElementById('calFilterDutyType').value = '';
        document.getElementById('calFilterShift').value = '';
        document.getElementById('calFilterPriority').value = '';
        document.getElementById('calFilterStatus').value = '';
        document.getElementById('calFilterSearch').value = '';
        
        const clearPerson = document.getElementById('calFilterClearPerson');
        if (clearPerson) clearPerson.click();
        
        const adminCamp = document.getElementById('calFilterCamp');
        if (adminCamp && !adminCamp.classList.contains('d-none')) {
            adminCamp.value = 'All';
        }

        // Clear active summary card highlights
        activeSummaryFilter = '';
        document.querySelectorAll('.summary-card').forEach(c => {
            c.style.boxShadow = '';
            c.style.transform = '';
        });

        calendar.refetchEvents();
    });

    // Handle Clickable Summary Cards filtering
    document.querySelectorAll('.summary-card').forEach(card => {
        card.addEventListener('click', () => {
            const filterType = card.getAttribute('data-filter');
            
            if (activeSummaryFilter === filterType) {
                // Toggle off
                activeSummaryFilter = '';
                card.style.boxShadow = '';
                card.style.transform = '';
            } else {
                // Reset other highlights
                document.querySelectorAll('.summary-card').forEach(c => {
                    c.style.boxShadow = '';
                    c.style.transform = '';
                });
                
                activeSummaryFilter = filterType;
                card.style.boxShadow = '0 0 0 3px #0d6efd';
                card.style.transform = 'translateY(-2px)';
            }
            
            // Refetch
            calendar.refetchEvents();
        });
    });

    // Helper functions
    function submitRosterAction(rosterId, action, remarks = '') {
        document.getElementById('modalActionRosterId').value = rosterId;
        document.getElementById('modalActionType').value = action;
        document.getElementById('modalActionRemarks').value = remarks;
        document.getElementById('modalActionForm').submit();
    }

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

    function formatDateStr(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    }
});
</script>
<?php
include __DIR__ . '/../layout/footer.php';
?>
