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
            <i class="fas fa-calendar-days"></i> Roster List View
        </a>
    </div>
</div>

<div class="glass-card p-4">
    <!-- Controls -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label text-secondary small">Select Camp / Base</label>
            <select id="calendarCampSelect" class="form-select form-control-custom">
                <?php foreach ($camps as $c): ?>
                    <?php 
                    // SNCO restriction
                    $restrictedCampId = LocationMiddleware::getCampConstraint();
                    if ($restrictedCampId !== null && (int)$c['camp_id'] !== $restrictedCampId) {
                        continue;
                    }
                    ?>
                    <option value="<?= $c['camp_id'] ?>" <?= (int)$c['camp_id'] === (int)$activeCampId ? 'selected' : '' ?>><?= htmlspecialchars($c['camp_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-secondary small">Start Date</label>
            <input type="date" id="calendarStartInput" class="form-control form-control-custom" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label text-secondary small">End Date</label>
            <input type="date" id="calendarEndInput" class="form-control form-control-custom" value="<?= date('Y-m-t') ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="loadCalendarBtn" class="btn btn-custom btn-custom-primary w-100 py-2">
                <i class="fas fa-arrows-rotate"></i> Load Data
            </button>
        </div>
    </div>

    <!-- Calendar Layout -->
    <div id="calendarDisplayGrid" class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3">
        <!-- Will be dynamically populated by JS -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const campSelect = document.getElementById('calendarCampSelect');
        const startInput = document.getElementById('calendarStartInput');
        const endInput = document.getElementById('calendarEndInput');
        const loadBtn = document.getElementById('loadCalendarBtn');
        const grid = document.getElementById('calendarDisplayGrid');

        function loadCalendarData() {
            const campId = campSelect.value;
            const start = startInput.value;
            const end = endInput.value;

            if (!campId || !start || !end) {
                alert("Please select base and duration range.");
                return;
            }

            grid.innerHTML = '<div class="col-12 text-center text-secondary py-5"><i class="fas fa-circle-notch fa-spin fs-2 text-info mb-2"></i><div>Fetching assignments data...</div></div>';

            fetch(`${BASE_URL}/rosters/calendar-data?camp_id=${campId}&start=${start}&end=${end}`)
                .then(res => res.json())
                .then(data => {
                    grid.innerHTML = '';
                    if (data.error) {
                        grid.innerHTML = `<div class="col-12 text-center text-danger py-4"><i class="fas fa-circle-exclamation fs-3 mb-2"></i><div>Error: ${data.error}</div></div>`;
                        return;
                    }
                    if (data.length === 0) {
                        grid.innerHTML = '<div class="col-12 text-center text-secondary py-5"><i class="fas fa-circle-info fs-3 mb-2 text-warning"></i><div>No published duty assignments scheduled for this period.</div></div>';
                        return;
                    }

                    // Group assignments by date
                    const grouped = {};
                    data.forEach(item => {
                        const date = item.duty_date;
                        if (!grouped[date]) {
                            grouped[date] = [];
                        }
                        grouped[date].push(item);
                    });

                    // Render date cards sorted
                    const sortedDates = Object.keys(grouped).sort();
                    sortedDates.forEach(date => {
                        const list = grouped[date];
                        const dateObj = new Date(date);
                        const dateFormatted = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

                        const card = document.createElement('div');
                        card.className = 'col';
                        
                        let cardContent = `
                            <div class="glass-card p-3 h-100 border-opacity-30 border-info">
                                <div class="fw-bold mb-2 text-info border-bottom border-secondary border-opacity-20 pb-2">${dateFormatted}</div>
                                <div class="d-flex flex-column gap-2.5">
                        `;

                        list.forEach(as => {
                            cardContent += `
                                <div class="p-2.5 bg-dark bg-opacity-25 rounded border border-secondary border-opacity-10">
                                    <div class="small fw-bold text-dark">${as.rank} ${as.full_name}</div>
                                    <div class="x-small text-secondary font-monospace" style="font-size:0.75rem;">${as.service_number}</div>
                                    <div class="mt-1.5 d-flex justify-content-between align-items-center">
                                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-10" style="font-size:0.7rem;">${as.duty_type_name}</span>
                                        <span class="text-muted small" style="font-size:0.7rem;"><i class="fas fa-clock"></i> ${as.shift_name.split(' ')[0]}</span>
                                    </div>
                                </div>
                            `;
                        });

                        cardContent += `
                                </div>
                            </div>
                        `;
                        card.innerHTML = cardContent;
                        grid.appendChild(card);
                    });
                })
                .catch(err => {
                    grid.innerHTML = `<div class="col-12 text-center text-danger py-4"><i class="fas fa-circle-exclamation fs-3 mb-2"></i><div>Network error loading calendar.</div></div>`;
                    console.error("Calendar load network error:", err);
                });
        }

        loadBtn.addEventListener('click', loadCalendarData);
        
        // Initial load
        loadCalendarData();
    });
</script>
<?php
include __DIR__ . '/../layout/footer.php';
?>
