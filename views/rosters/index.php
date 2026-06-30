<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-calendar-days"></i> Duty Rosters</h2>
        <p class="text-secondary">View and schedule operational watch duty schedules.</p>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-info me-2"></i> Duty Rosters List</h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/rosters/calendar" class="btn btn-sm btn-custom btn-custom-secondary py-2">
                <i class="fas fa-calendar-check"></i> Calendar
            </a>
            <a href="<?= BASE_URL ?>/rosters/timeline" class="btn btn-sm btn-custom btn-custom-secondary py-2">
                <i class="fas fa-timeline"></i> Timeline
            </a>
            <?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
                <a href="<?= BASE_URL ?>/rosters/create" class="btn btn-sm btn-custom btn-custom-primary py-2">
                    <i class="fas fa-calendar-plus"></i> Create Roster
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Filters -->
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <label class="form-label text-secondary small">Filter by Base</label>
                <select id="rosterCampFilter" class="form-select form-control-custom">
                    <option value="">All Bases / Camps</option>
                    <?php foreach ($camps as $c): ?>
                        <?php 
                        // SNCO constraint
                        $restrictedCampId = LocationMiddleware::getCampConstraint();
                        if ($restrictedCampId !== null && (int)$c['camp_id'] !== $restrictedCampId) {
                            continue;
                        }
                        ?>
                        <option value="<?= htmlspecialchars($c['camp_name']) ?>"><?= htmlspecialchars($c['camp_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label text-secondary small">Filter by Status</label>
                <select id="rosterStatusFilter" class="form-select form-control-custom">
                    <option value="">All Statuses</option>
                    <option value="Draft">Draft</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Published">Published</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="resetFilters" class="btn btn-custom btn-custom-secondary w-100 py-2"><i class="fas fa-arrows-rotate"></i> Reset</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-custom-container">
            <table class="table-custom" id="rostersTable">
                <thead>
                    <tr>
                        <th>Roster Name</th>
                        <th>Camp/Base</th>
                        <th>Date Duration</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rosters)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No duty rosters created yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rosters as $r): ?>
                            <tr class="roster-row">
                                <td class="fw-bold"><?= htmlspecialchars($r['roster_name']) ?></td>
                                <td class="cell-camp"><?= htmlspecialchars($r['camp_name']) ?></td>
                                <td>
                                    <i class="fas fa-calendar-alt text-secondary me-1"></i>
                                    <?= date('d M Y', strtotime($r['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($r['end_date'])) ?>
                                </td>
                                <td class="small">
                                    <span class="text-info"><?= htmlspecialchars($r['creator_rank']) ?></span> <?= htmlspecialchars($r['creator_name']) ?>
                                </td>
                                <td class="cell-status">
                                    <?php 
                                    $statusClass = strtolower($r['status']);
                                    ?>
                                    <span class="badge-custom badge-<?= $statusClass ?>">
                                        <i class="fas fa-circle small"></i> <?= $r['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/rosters/view?id=<?= $r['roster_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2 me-1">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if (($roleName === 'SNCO' || $roleName === 'Administrator') && ($r['status'] === 'Draft' || $r['status'] === 'Rejected')): ?>
                                        <a href="<?= BASE_URL ?>/rosters/create?id=<?= $r['roster_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2 me-1 text-warning border-warning border-opacity-25">
                                            <i class="fas fa-pen-to-square"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Front-end Filter Logic
    document.addEventListener('DOMContentLoaded', () => {
        const campFilter = document.getElementById('rosterCampFilter');
        const statusFilter = document.getElementById('rosterStatusFilter');
        const resetBtn = document.getElementById('resetFilters');
        const rows = document.querySelectorAll('.roster-row');

        function applyFilters() {
            const campVal = campFilter.value.toLowerCase().trim();
            const statusVal = statusFilter.value.toLowerCase().trim();

            rows.forEach(row => {
                const camp = row.querySelector('.cell-camp').textContent.toLowerCase();
                const status = row.querySelector('.cell-status').textContent.toLowerCase();

                let matchCamp = !campVal || camp.includes(campVal);
                let matchStatus = !statusVal || status.includes(statusVal);

                if (matchCamp && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        campFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        
        resetBtn.addEventListener('click', () => {
            campFilter.value = '';
            statusFilter.value = '';
            rows.forEach(row => row.style.display = '');
        });
    });
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
