<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-timeline"></i> Operational Duty Timeline</h2>
        <p class="text-secondary">Track consecutive shift sequences and rotation timelines.</p>
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
            <label class="form-label text-secondary small">Base / Camp Location</label>
            <select id="timelineCampSelect" class="form-select form-control-custom">
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
            <label class="form-label text-secondary small">From Date</label>
            <input type="date" id="timelineStartInput" class="form-control form-control-custom" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label text-secondary small">To Date</label>
            <input type="date" id="timelineEndInput" class="form-control form-control-custom" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="loadTimelineBtn" class="btn btn-custom btn-custom-primary w-100 py-2">
                <i class="fas fa-play"></i> Fetch Timeline
            </button>
        </div>
    </div>

    <!-- Timeline Body -->
    <div class="position-relative ps-4" style="border-left: 2px solid var(--glass-border);" id="timelineDisplay">
        <!-- Will be populated dynamically by JavaScript -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const campSelect = document.getElementById('timelineCampSelect');
        const startInput = document.getElementById('timelineStartInput');
        const endInput = document.getElementById('timelineEndInput');
        const loadBtn = document.getElementById('loadTimelineBtn');
        const timeline = document.getElementById('timelineDisplay');

        function loadTimelineData() {
            const campId = campSelect.value;
            const start = startInput.value;
            const end = endInput.value;

            if (!campId || !start || !end) {
                alert("Please select base and date filters.");
                return;
            }

            timeline.innerHTML = '<div class="text-center text-secondary py-5"><i class="fas fa-circle-notch fa-spin fs-2 text-info mb-2"></i><div>Rendering timeline...</div></div>';

            fetch(`${BASE_URL}/rosters/calendar-data?camp_id=${campId}&start=${start}&end=${end}`)
                .then(res => res.json())
                .then(data => {
                    timeline.innerHTML = '';
                    if (data.error) {
                        timeline.innerHTML = `<div class="text-danger py-4 text-center"><i class="fas fa-circle-exclamation fs-3 mb-2"></i><div>Error: ${data.error}</div></div>`;
                        return;
                    }
                    if (data.length === 0) {
                        timeline.innerHTML = '<div class="text-secondary py-5 text-center"><i class="fas fa-calendar-xmark fs-3 mb-2 text-warning"></i><div>No published schedules found for this range.</div></div>';
                        return;
                    }

                    // Group by date
                    const grouped = {};
                    data.forEach(item => {
                        const date = item.duty_date;
                        if (!grouped[date]) {
                            grouped[date] = [];
                        }
                        grouped[date].push(item);
                    });

                    const sortedDates = Object.keys(grouped).sort();
                    sortedDates.forEach(date => {
                        const list = grouped[date];
                        const dateObj = new Date(date);
                        const dateFormatted = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

                        const timelineGroup = document.createElement('div');
                        timelineGroup.className = 'timeline-group mb-5 position-relative';
                        
                        // Add marker
                        const marker = document.createElement('div');
                        marker.className = 'position-absolute rounded-circle bg-info border border-3 border-dark';
                        marker.style.width = '16px';
                        marker.style.height = '16px';
                        marker.style.left = '-33px';
                        marker.style.top = '4px';
                        timelineGroup.appendChild(marker);

                        let groupContent = `
                            <h5 class="fw-bold text-info mb-3">${dateFormatted}</h5>
                            <div class="row row-cols-1 row-cols-md-2 g-3">
                        `;

                        list.forEach(as => {
                             const startHour = as.duty_start_datetime.substring(11, 16);
                             const endHour = as.duty_end_datetime.substring(11, 16);
                             let timingsText = '';
                             if (as.duty_start_datetime.substring(0, 10) !== as.duty_end_datetime.substring(0, 10)) {
                                 const nextDateFormatted = new Date(as.duty_end_datetime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                 const startDateFormatted = new Date(as.duty_start_datetime.replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                 timingsText = `${startDateFormatted} ${startHour} &rarr; ${nextDateFormatted} ${endHour}`;
                             } else {
                                 timingsText = `${startHour} - ${endHour}`;
                             }

                             groupContent += `
                                 <div class="col">
                                     <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                                         <div>
                                             <div class="fw-bold text-dark">${as.rank} ${as.full_name}</div>
                                             <div class="text-secondary font-monospace" style="font-size: 0.8rem;"><i class="fas fa-hashtag"></i> ${as.service_number}</div>
                                             <div class="small text-muted mt-2"><i class="fas fa-clock"></i> ${as.shift_name} (${timingsText})</div>
                                         </div>
                                         <div class="text-end">
                                             <span class="badge bg-primary bg-opacity-25 border border-primary border-opacity-25 text-info px-3 py-1.5 rounded">${as.duty_type_name}</span>
                                         </div>
                                     </div>
                                 </div>
                             `;
                        });

                        groupContent += `
                            </div>
                        `;
                        timelineGroup.innerHTML += groupContent;
                        timeline.appendChild(timelineGroup);
                    });
                })
                .catch(err => {
                    timeline.innerHTML = `<div class="text-danger py-4 text-center"><i class="fas fa-circle-exclamation fs-3 mb-2"></i><div>Network failure loading timeline.</div></div>`;
                    console.error("Timeline loading network error:", err);
                });
        }

        loadBtn.addEventListener('click', loadTimelineData);
        loadTimelineData();
    });
</script>
<?php
include __DIR__ . '/../layout/footer.php';
?>
