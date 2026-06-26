<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-calendar-plus"></i> <?= $roster ? 'Edit Roster Draft' : 'Create Smart Roster' ?></h2>
        <p class="text-secondary">Camp: <strong class="text-dark"><?= htmlspecialchars($camp['camp_name']) ?></strong> &bull; Schedule Sentries & Guards</p>
    </div>
</div>

<div class="row g-4">
    <!-- Roster Metadata Card -->
    <div class="col-12">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i> Roster Metadata</h5>
            <form id="rosterForm">
                <input type="hidden" id="roster_id" value="<?= $roster ? $roster['roster_id'] : '' ?>">
                <input type="hidden" id="camp_id" value="<?= $camp['camp_id'] ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="roster_name" class="form-label text-secondary small">Roster Name / Identifier</label>
                        <input type="text" class="form-control form-control-custom" id="roster_name" value="<?= $roster ? htmlspecialchars($roster['roster_name']) : 'Guard Duty Schedule ' . date('M Y') ?>" required placeholder="e.g. Guard Duty June 2026">
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label text-secondary small">Start Date</label>
                        <input type="date" class="form-control form-control-custom" id="start_date" value="<?= $roster ? htmlspecialchars($roster['start_date']) : date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label text-secondary small">End Date</label>
                        <input type="date" class="form-control form-control-custom" id="end_date" value="<?= $roster ? htmlspecialchars($roster['end_date']) : date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Conflicts summary panel -->
    <div class="col-12" id="conflictsSummaryPanel" style="display: none;">
        <div class="alert alert-danger glass-card border-danger text-danger p-4 mb-0" role="alert">
            <h5 class="fw-bold mb-2 text-danger"><i class="fas fa-triangle-exclamation"></i> Schedule Conflicts Detected</h5>
            <p class="small text-secondary mb-3">Please resolve the conflicts marked below before submitting for approval.</p>
            <ul id="conflictsSummaryList" class="mb-0 small text-secondary"></ul>
        </div>
    </div>

    <!-- Scheduling grid -->
    <div class="col-12">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-list text-success me-2"></i> Watch Assignments Grid</h5>
                <button type="button" id="addRowBtn" class="btn btn-sm btn-custom btn-custom-secondary"><i class="fas fa-plus"></i> Add Entry Row</button>
            </div>

            <div class="table-custom-container">
                <table class="table-custom text-center align-middle" id="assignmentsTable">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 15%;">Shift Rotation</th>
                            <th style="width: 20%;">Duty Type</th>
                            <th style="width: 25%;">Assigned Personnel</th>
                            <th style="width: 10%;">Priority</th>
                            <th style="width: 10%;">Remarks</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsTbody">
                        <?php if (empty($assignments)): ?>
                            <!-- Will insert empty row dynamically if new -->
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
                                        <select class="form-select form-control-custom row-personnel" required>
                                            <option value="">-- Select Personnel --</option>
                                            <?php foreach ($personnel as $p): ?>
                                                <option value="<?= $p['service_number'] ?>" <?= $as['service_number'] === $p['service_number'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['rank'] . ' ' . $p['full_name']) ?> (<?= htmlspecialchars($p['service_number']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="row-conflict-info mt-1 small" style="display:none;"></div>
                                    </td>
                                    <td>
                                        <select class="form-select form-control-custom row-priority">
                                            <option value="Low" <?= $as['priority_level'] === 'Low' ? 'selected' : '' ?>>Low</option>
                                            <option value="Medium" <?= $as['priority_level'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                            <option value="High" <?= $as['priority_level'] === 'High' ? 'selected' : '' ?>>High</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-custom row-remarks" value="<?= htmlspecialchars($as['remarks'] ?? '') ?>" placeholder="Notes...">
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

            <div class="mt-4 d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2 border-top pt-3 border-secondary border-opacity-20">
                <a href="<?= BASE_URL ?>/rosters" class="btn btn-custom btn-custom-secondary">
                    <i class="fas fa-xmark"></i> Cancel
                </a>
                <button type="button" id="checkConflictsBtn" class="btn btn-custom btn-custom-secondary text-warning border-warning border-opacity-25">
                    <i class="fas fa-circle-exclamation"></i> Verify Schedule Conflicts
                </button>
                <button type="button" id="saveRosterBtn" class="btn btn-custom btn-custom-success">
                    <i class="fas fa-floppy-disk"></i> Save Roster Draft
                </button>
            </div>
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

<div id="personnelTemplate" style="display:none;">
    <option value="">-- Select Personnel --</option>
    <?php foreach ($personnel as $p): ?>
        <option value="<?= $p['service_number'] ?>">
            <?= htmlspecialchars($p['rank'] . ' ' . $p['full_name']) ?> (<?= htmlspecialchars($p['service_number']) ?>)
        </option>
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
        const personnelHtml = document.getElementById('personnelTemplate').innerHTML;

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
                    <select class="form-select form-control-custom row-personnel" required>
                        ${personnelHtml}
                    </select>
                    <div class="row-conflict-info mt-1 small" style="display:none;"></div>
                </td>
                <td>
                    <select class="form-select form-control-custom row-priority">
                        <option value="Low" selected>Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-custom row-remarks" placeholder="Notes...">
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
            rowIndex++;
        }

        // Add initial row if empty
        if (rowIndex === 0) {
            addRow();
            addRow();
            addRow();
        } else {
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
                const priorityLevel = row.querySelector('.row-priority').value;
                const remarks = row.querySelector('.row-remarks').value;

                if (date && serviceNumber) {
                    data.push({
                        duty_date: date,
                        shift_id: parseInt(shiftId),
                        duty_type_id: parseInt(dutyTypeId),
                        service_number: serviceNumber,
                        priority_level: priorityLevel,
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
                checkConflictsBtn.innerHTML = '<i class="fas fa-circle-exclamation"></i> Verify Schedule Conflicts';

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
                                li.className = 'mb-1 text-secondary';
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
                checkConflictsBtn.innerHTML = '<i class="fas fa-circle-exclamation"></i> Verify Schedule Conflicts';
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
                saveRosterBtn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Roster Draft';

                if (data.success) {
                    alert(data.message);
                    window.location.href = `${BASE_URL}/rosters/view?id=${data.roster_id}`;
                } else {
                    alert("Failed to save draft: " + data.message);
                }
            })
            .catch(err => {
                saveRosterBtn.disabled = false;
                saveRosterBtn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Roster Draft';
                console.error("Save roster request error:", err);
            });
        });
    });
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
