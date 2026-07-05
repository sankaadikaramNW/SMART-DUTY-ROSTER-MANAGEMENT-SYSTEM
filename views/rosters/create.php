<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="container-custom">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-calendar-plus"></i> <?= $roster ? 'Edit Roster Draft' : 'Create Smart Roster' ?></h2>
            <p class="text-secondary mb-0">Camp: <strong class="text-dark"><?= htmlspecialchars($camp['camp_name']) ?></strong> &bull; Schedule Sentries & Guards</p>
        </div>
    </div>

    <!-- Single Bootstrap Card layout -->
    <div class="glass-card mb-4 animate-fade-in">
        <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-primary me-2"></i> Roster Details & Watch Assignments</h5>
        </div>
        
        <div class="card-body p-4">
            <form id="rosterForm">
                <input type="hidden" id="roster_id" value="<?= $roster ? $roster['roster_id'] : '' ?>">
                <input type="hidden" id="camp_id" value="<?= $camp['camp_id'] ?>">
                
                <!-- Metadata Fields in a single horizontal row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-lg-4">
                        <label for="roster_name" class="form-label text-secondary small fw-bold">Roster Name / Identifier</label>
                        <input type="text" class="form-control form-control-custom" id="roster_name" value="<?= $roster ? htmlspecialchars($roster['roster_name']) : 'Guard Duty Schedule ' . date('M Y') ?>" required placeholder="e.g. Guard Duty June 2026">
                    </div>
                    <div class="col-md-3 col-lg-4">
                        <label for="start_date" class="form-label text-secondary small fw-bold">Start Date</label>
                        <input type="date" class="form-control form-control-custom" id="start_date" value="<?= $roster ? htmlspecialchars($roster['start_date']) : date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3 col-lg-4">
                        <label for="end_date" class="form-label text-secondary small fw-bold">End Date</label>
                        <input type="date" class="form-control form-control-custom" id="end_date" value="<?= $roster ? htmlspecialchars($roster['end_date']) : date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>

                <hr class="border-secondary border-opacity-10 my-4">

                <!-- Conflicts Alert Panel -->
                <div id="conflictsSummaryPanel" style="display: none;" class="mb-4">
                    <div class="alert alert-danger border-danger text-danger p-3 mb-0 rounded" role="alert" style="background: rgba(239, 68, 68, 0.05);">
                        <h6 class="fw-bold mb-2 text-danger"><i class="fas fa-triangle-exclamation"></i> Schedule Conflicts Found!</h6>
                        <ul id="conflictsSummaryList" class="mb-0 ps-3 small text-danger" style="max-height: 150px; overflow-y: auto;"></ul>
                    </div>
                </div>

                <!-- Table Grid Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-days text-success me-2"></i> Watch Assignments</h6>
                    <button type="button" id="addRowBtn" class="btn btn-sm btn-custom btn-custom-secondary"><i class="fas fa-plus"></i> Add Entry Row</button>
                </div>

                <!-- Assignments Table -->
                <div class="table-custom-container mb-4">
                    <table class="table-custom align-middle" id="assignmentsTable">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 12%;">Shift Rotation</th>
                                <th style="width: 16%;">Duty Type</th>
                                <th style="width: 44%;">Assigned Personnel</th>
                                <th style="width: 14%;">Remarks</th>
                                <th style="width: 4%;"></th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsTbody">
                            <?php if (empty($assignments)): ?>
                                <!-- Empty rows will be added dynamically -->
                            <?php else: ?>
                                <?php foreach ($assignments as $index => $as): ?>
                                    <tr class="assignment-row" data-index="<?= $index ?>">
                                        <td>
                                            <input type="date" class="form-control form-control-custom row-date" value="<?= htmlspecialchars($as['duty_date']) ?>" required>
                                        </td>
                                        <td>
                                            <select class="form-select form-control-custom row-shift" required>
                                                <?php foreach ($shifts as $s): ?>
                                                    <option value="<?= $s['shift_id'] ?>" <?= (int)$as['shift_id'] === (int)$s['shift_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['shift_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-control-custom row-duty-type" required>
                                                <?php foreach ($dutyTypes as $dt): ?>
                                                    <option value="<?= $dt['duty_type_id'] ?>" <?= (int)$as['duty_type_id'] === (int)$dt['duty_type_id'] ? 'selected' : '' ?>><?= htmlspecialchars($dt['duty_type_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="position-relative personnel-search-wrapper">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control form-control-custom row-personnel-search-input" placeholder="Type Service Number or Name..." autocomplete="off" value="<?= $as ? htmlspecialchars($as['service_number'] . ' - ' . ($as['rank_short_name'] ?? $as['rank']) . ' ' . $as['initials'] . ' ' . (array_reverse(explode(' ', trim($as['full_name'])))[0]) ) : '' ?>" required>
                                                    <input type="hidden" class="row-personnel" value="<?= htmlspecialchars($as['service_number']) ?>">
                                                    <button type="button" class="btn btn-outline-secondary clear-search-btn" style="display: <?= $as ? 'block' : 'none' ?>;"><i class="fas fa-xmark"></i></button>
                                                </div>
                                                
                                                <!-- Autocomplete suggestions dropdown -->
                                                <div class="autocomplete-suggestions dropdown-menu w-100 bg-white text-dark border-light shadow-lg" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050; position: absolute;"></div>
                                                
                                                <!-- Loading spinner -->
                                                <div class="search-spinner position-absolute end-0 top-0 mt-2 me-5 text-info" style="display: none; z-index: 1060;">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </div>
                                            </div>
                                            <div class="row-conflict-info mt-1 small" style="display:none;"></div>
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-custom row-remarks" rows="1" placeholder="Notes..."><?= htmlspecialchars($as['remarks'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-custom btn-custom-danger remove-row-btn"><i class="fas fa-trash-can"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <hr class="border-secondary border-opacity-10 my-4">

                <!-- Form Bottom Actions Row -->
                <div class="row align-items-center">
                    <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?= BASE_URL ?>/rosters" class="btn btn-custom btn-custom-secondary px-4 py-2.5">
                            <i class="fas fa-xmark me-2"></i> Cancel
                        </a>
                        <button type="button" id="checkConflictsBtn" class="btn btn-custom btn-custom-warning px-4 py-2.5">
                            <i class="fas fa-circle-exclamation me-2"></i> Verify Schedule Conflicts
                        </button>
                        <button type="button" id="saveRosterBtn" class="btn btn-custom btn-custom-success px-4 py-2.5">
                            <i class="fas fa-floppy-disk me-2"></i> Save Roster Draft
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Option Templates for JS Injection -->
<div id="shiftsTemplate" style="display:none;">
    <?php foreach ($shifts as $s): ?>
        <option value="<?= $s['shift_id'] ?>"><?= htmlspecialchars($s['shift_name']) ?></option>
    <?php endforeach; ?>
</div>

<div id="dutyTypesTemplate" style="display:none;">
    <?php foreach ($dutyTypes as $dt): ?>
        <option value="<?= $dt['duty_type_id'] ?>"><?= htmlspecialchars($dt['duty_type_name']) ?></option>
    <?php endforeach; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('assignmentsTbody');
        const addRowBtn = document.getElementById('addRowBtn');
        const checkConflictsBtn = document.getElementById('checkConflictsBtn');
        const saveRosterBtn = document.getElementById('saveRosterBtn');
        const conflictsSummaryPanel = document.getElementById('conflictsSummaryPanel');
        const conflictsSummaryList = document.getElementById('conflictsSummaryList');

        const shiftsHtml = document.getElementById('shiftsTemplate').innerHTML;
        const dutyTypesHtml = document.getElementById('dutyTypesTemplate').innerHTML;

        let rowIndex = tbody.querySelectorAll('.assignment-row').length;

        // Function to add a row
        function addRow() {
            const tr = document.createElement('tr');
            tr.className = 'assignment-row';
            tr.dataset.index = rowIndex;

            const defaultDate = document.getElementById('start_date').value || '';

            tr.innerHTML = `
                <td>
                    <input type="date" class="form-control form-control-custom row-date" value="${defaultDate}" required>
                </td>
                <td>
                    <select class="form-select form-control-custom row-shift" required>
                        ${shiftsHtml}
                    </select>
                </td>
                <td>
                    <select class="form-select form-control-custom row-duty-type" required>
                        ${dutyTypesHtml}
                    </select>
                </td>
                <td>
                    <div class="position-relative personnel-search-wrapper">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control form-control-custom row-personnel-search-input" placeholder="Type Service Number or Name..." autocomplete="off" required>
                            <input type="hidden" class="row-personnel" value="">
                            <button type="button" class="btn btn-outline-secondary clear-search-btn" style="display: none;"><i class="fas fa-xmark"></i></button>
                        </div>
                        <div class="autocomplete-suggestions dropdown-menu w-100 bg-white text-dark border-light shadow-lg" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050; position: absolute;"></div>
                        
                        <div class="search-spinner position-absolute end-0 top-0 mt-2 me-5 text-info" style="display: none; z-index: 1060;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    <div class="row-conflict-info mt-1 small" style="display:none;"></div>
                <td>
                    <textarea class="form-control form-control-custom row-remarks" rows="1" placeholder="Notes..."></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-custom btn-custom-danger remove-row-btn"><i class="fas fa-trash-can"></i></button>
                </td>
            `;

            // Row remove listener
            tr.querySelector('.remove-row-btn').addEventListener('click', () => {
                tr.remove();
                clearConflictMarkings();
            });

            tbody.appendChild(tr);
            initAutocomplete(tr);
            rowIndex++;
        }

        // Add initial row if empty
        if (rowIndex === 0) {
            addRow();
            addRow();
            addRow();
        } else {
            // Initialize autocomplete on existing rows
            tbody.querySelectorAll('.assignment-row').forEach(row => {
                initAutocomplete(row);
            });

            // Re-bind remove buttons on existing rows
            tbody.querySelectorAll('.remove-row-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.currentTarget.closest('tr').remove();
                    clearConflictMarkings();
                });
            });
        }

        addRowBtn.addEventListener('click', addRow);

        // Gather all assignments array from rows
        function gatherAssignments() {
            const rows = tbody.querySelectorAll('.assignment-row');
            const data = [];
            rows.forEach(row => {
                const date = row.querySelector('.row-date').value;
                const shiftId = row.querySelector('.row-shift').value;
                const dutyTypeId = row.querySelector('.row-duty-type').value;
                const serviceNumber = row.querySelector('.row-personnel').value;
                const remarks = row.querySelector('.row-remarks').value;

                if (date && serviceNumber) {
                    data.push({
                        duty_date: date,
                        shift_id: parseInt(shiftId),
                        duty_type_id: parseInt(dutyTypeId),
                        service_number: serviceNumber,
                        priority_level: 'Low',
                        remarks: remarks
                    });
                }
            });
            return data;
        }

        function clearConflictMarkings() {
            conflictsSummaryPanel.style.display = 'none';
            conflictsSummaryList.innerHTML = '';
            tbody.querySelectorAll('.row-conflict-info').forEach(div => {
                div.style.display = 'none';
                div.innerHTML = '';
            });
            tbody.querySelectorAll('.assignment-row').forEach(row => {
                row.style.borderLeft = 'none';
            });
        }

        // Conflict check click
        checkConflictsBtn.addEventListener('click', () => {
            clearConflictMarkings();
            const assignments = gatherAssignments();
            if (assignments.length === 0) {
                alert("Please add at least one complete assignment (with date and personnel selected) to verify.");
                return;
            }

            const payload = {
                camp_id: parseInt(document.getElementById('camp_id').value),
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                assignments: assignments,
                exclude_roster_id: document.getElementById('roster_id').value ? parseInt(document.getElementById('roster_id').value) : null
            };

            checkConflictsBtn.disabled = true;
            checkConflictsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

            fetch(`${BASE_URL}/rosters/conflict-check`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                checkConflictsBtn.disabled = false;
                checkConflictsBtn.innerHTML = '<i class="fas fa-circle-exclamation me-2"></i> Verify Schedule Conflicts';

                if (data.success) {
                    const conflicts = data.conflicts;
                    const conflictKeys = Object.keys(conflicts);

                    if (conflictKeys.length === 0) {
                        alert("Verification complete! No scheduling conflicts detected.");
                        return;
                    }

                    conflictsSummaryPanel.style.display = 'block';
                    const rows = tbody.querySelectorAll('.assignment-row');

                    conflictKeys.forEach(idx => {
                        const row = rows[idx];
                        const list = conflicts[idx];
                        
                        if (row) {
                            // Highlight the row and insert notes
                            const infoDiv = row.querySelector('.row-conflict-info');
                            let messages = '';
                            let highestLevel = 'Warning';

                            list.forEach(conf => {
                                if (conf.level === 'Critical') highestLevel = 'Critical';
                                messages += `<div class="text-${conf.level === 'Critical' ? 'danger' : 'warning'}"><i class="fas fa-triangle-exclamation"></i> [${conf.type}] ${conf.message}</div>`;
                                
                                // Add to summary
                                const li = document.createElement('li');
                                li.className = 'mb-1 text-danger';
                                li.innerHTML = `<span class="badge bg-${conf.level === 'Critical' ? 'danger' : 'warning'} me-2">${conf.level}</span> On ${row.querySelector('.row-date').value}: ${conf.message}`;
                                conflictsSummaryList.appendChild(li);
                            });

                            row.style.borderLeft = `4px solid ${highestLevel === 'Critical' ? '#ef4444' : '#f59e0b'}`;
                            infoDiv.innerHTML = messages;
                            infoDiv.style.display = 'block';
                        }
                    });
                } else {
                    alert("Verification error: " + data.message);
                }
            })
            .catch(err => {
                checkConflictsBtn.disabled = false;
                checkConflictsBtn.innerHTML = '<i class="fas fa-circle-exclamation me-2"></i> Verify Schedule Conflicts';
                console.error("Conflict checking request error:", err);
            });
        });

        // Save Roster Draft click
        saveRosterBtn.addEventListener('click', () => {
            const rosterName = document.getElementById('roster_name').value.trim();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const assignments = gatherAssignments();

            if (!rosterName || !startDate || !endDate) {
                alert("Please fill in the Roster name, start date, and end date.");
                return;
            }

            const payload = {
                roster_id: document.getElementById('roster_id').value || null,
                roster_name: rosterName,
                camp_id: parseInt(document.getElementById('camp_id').value),
                start_date: startDate,
                end_date: endDate,
                assignments: assignments
            };

            saveRosterBtn.disabled = true;
            saveRosterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            fetch(`${BASE_URL}/rosters/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                saveRosterBtn.disabled = false;
                saveRosterBtn.innerHTML = '<i class="fas fa-floppy-disk me-2"></i> Save Roster Draft';

                if (data.success) {
                    alert(data.message);
                    window.location.href = `${BASE_URL}/rosters/view?id=${data.roster_id}`;
                } else {
                    alert("Failed to save draft: " + data.message);
                }
            })
            .catch(err => {
                saveRosterBtn.disabled = false;
                saveRosterBtn.innerHTML = '<i class="fas fa-floppy-disk me-2"></i> Save Roster Draft';
                console.error("Save roster request error:", err);
            });
        });

        // Initialize AJAX Auto-complete search on watch assignment row
        function initAutocomplete(row) {
            const wrapper = row.querySelector('.personnel-search-wrapper');
            if (!wrapper) return;

            const input = wrapper.querySelector('.row-personnel-search-input');
            const hidden = wrapper.querySelector('.row-personnel');
            const suggestions = wrapper.querySelector('.autocomplete-suggestions');
            const spinner = wrapper.querySelector('.search-spinner');
            const clearBtn = wrapper.querySelector('.clear-search-btn');

            let debounceTimer;

            input.addEventListener('input', () => {
                const query = input.value.trim();
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'none';
                    return;
                }

                spinner.style.display = 'block';

                debounceTimer = setTimeout(() => {
                    fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            spinner.style.display = 'none';
                            suggestions.innerHTML = '';
                            
                            if (data && data.length > 0) {
                                data.forEach(item => {
                                    const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
                                    const highlightedSN = item.service_number.replace(regex, '<mark class="p-0 bg-warning text-dark">$1</mark>');
                                    
                                    const rankName = item.rank || '';
                                    const rankShort = item.rank_short_name || '';
                                    const initials = item.initials || '';
                                    
                                    const nameParts = item.full_name.trim().split(' ');
                                    const lastName = nameParts[nameParts.length - 1];
                                    const formattedName = `${rankShort} ${initials} ${lastName}`;
                                    const highlightedName = formattedName.replace(regex, '<mark class="p-0 bg-warning text-dark">$1</mark>');
                                    const highlightedTrade = item.trade.replace(regex, '<mark class="p-0 bg-warning text-dark">$1</mark>');

                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'list-group-item list-group-item-action bg-white text-dark border-light-subtle small py-2 text-start';
                                    btn.innerHTML = `
                                        <strong>${highlightedSN}</strong> ${highlightedName}<br>
                                        <span class="text-secondary small">Trade: ${highlightedTrade}</span><br>
                                        <span class="text-secondary small">Camp: ${item.camp_name}</span>
                                    `;

                                    btn.addEventListener('click', () => {
                                        // Set input value to rank, name, and service number
                                        input.value = `${item.service_number} - ${rankShort} ${initials} ${lastName}`;
                                        hidden.value = item.service_number;
                                        clearBtn.style.display = 'block';

                                        suggestions.innerHTML = '';
                                        suggestions.style.display = 'none';
                                        
                                        // Clear conflict marking since personnel changed
                                        row.style.borderLeft = 'none';
                                        const infoDiv = row.querySelector('.row-conflict-info');
                                        if (infoDiv) {
                                            const days = parseInt(item.days_in_camp || 0);
                                            if (days > 30) {
                                                row.style.borderLeft = '4px solid #ef4444';
                                                infoDiv.innerHTML = `<div class="text-danger"><i class="fas fa-triangle-exclamation"></i> Warning: This person has been staying ${days} days in the camp.</div>`;
                                                infoDiv.style.display = 'block';
                                            } else {
                                                infoDiv.style.display = 'none';
                                                infoDiv.innerHTML = '';
                                            }
                                        }
                                    });

                                    suggestions.appendChild(btn);
                                });
                                suggestions.style.display = 'block';
                            } else {
                                suggestions.innerHTML = '<div class="list-group-item list-group-item-dark text-muted border-secondary small py-2 text-center">No matches found</div>';
                                suggestions.style.display = 'block';
                            }
                        })
                        .catch(err => {
                            spinner.style.display = 'none';
                            console.error('Error searching:', err);
                        });
                }, 300);
            });

            // Bind click to clear search
            clearBtn.addEventListener('click', () => {
                input.value = '';
                hidden.value = '';
                clearBtn.style.display = 'none';
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
                
                row.style.borderLeft = 'none';
                const infoDiv = row.querySelector('.row-conflict-info');
                if (infoDiv) {
                    infoDiv.style.display = 'none';
                    infoDiv.innerHTML = '';
                }
            });

            // Close suggestions on clicking elsewhere
            document.addEventListener('click', (e) => {
                if (e.target !== input && e.target !== suggestions) {
                    suggestions.style.display = 'none';
                }
            });
        }

        function escapeRegex(string) {
            return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        }
    });
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
