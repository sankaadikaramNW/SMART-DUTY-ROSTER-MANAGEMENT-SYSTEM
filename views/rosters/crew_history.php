<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-8 col-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-history text-purple"></i> Approval Action History</h2>
        <p class="text-secondary mb-0">Audited operational history of watch roster approvals, returns, and rejections.</p>
    </div>
</div>

<div class="glass-card mb-4 animate-fade-in">
    <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-info me-2"></i> Action Log</h5>
        <div class="d-flex gap-2">
            <button id="btnResetHistoryFilters" class="btn btn-sm btn-custom btn-custom-secondary py-1.5"><i class="fas fa-arrows-rotate"></i> Reset Filters</button>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Search and Filter Bar -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-secondary small fw-bold">Search Remarks / User</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary border-opacity-15 text-secondary"><i class="fas fa-search"></i></span>
                    <input type="text" id="historySearch" class="form-control form-control-custom" placeholder="Search Remarks, Name, IP...">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label text-secondary small fw-bold">Filter Action</label>
                <select id="historyActionFilter" class="form-select form-control-custom">
                    <option value="">All Actions</option>
                    <option value="Approve">Approve</option>
                    <option value="Reject">Reject</option>
                    <option value="Return">Return</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label text-secondary small fw-bold">Filter Duty Type</label>
                <select id="historyDutyFilter" class="form-select form-control-custom">
                    <option value="">All Duty Types</option>
                    <?php 
                    $uniqueDuties = [];
                    foreach ($history as $log) {
                        $uniqueDuties[$log['duty_type_name']] = true;
                    }
                    foreach (array_keys($uniqueDuties) as $dtName):
                    ?>
                        <option value="<?= htmlspecialchars($dtName) ?>"><?= htmlspecialchars($dtName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- History Table -->
        <div class="table-custom-container">
            <table class="table-custom" id="historyTable">
                <thead>
                    <tr>
                        <th style="width: 15%;">Action Timestamp</th>
                        <th style="width: 15%;">Action By</th>
                        <th style="width: 10%;">Action</th>
                        <th style="width: 25%;">Roster Details</th>
                        <th style="width: 20%;">Directives / Remarks</th>
                        <th style="width: 15%;">Network & Client Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                <i class="fas fa-receipt fs-2 opacity-25 d-block mb-2"></i>
                                No approval history logs recorded.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $log): ?>
                            <tr class="history-row">
                                <td class="small fw-semibold text-dark">
                                    <i class="far fa-clock text-secondary me-1"></i>
                                    <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                        <?= htmlspecialchars(($log['rank_short_name'] ?? '') . ' ' . $log['full_name']) ?>
                                    </div>
                                    <span class="small text-secondary font-monospace">(<?= htmlspecialchars($log['username']) ?>)</span>
                                </td>
                                <td class="cell-action">
                                    <?php 
                                    $action = htmlspecialchars($log['action']);
                                    $badgeClass = 'bg-secondary';
                                    if ($action === 'Approve') $badgeClass = 'bg-success';
                                    elseif ($action === 'Reject') $badgeClass = 'bg-danger';
                                    elseif ($action === 'Return') $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> px-2.5 py-1.5 rounded small" style="font-size:0.75rem;">
                                        <?= $action ?>d
                                    </span>
                                </td>
                                <td class="cell-crew">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($log['roster_name']) ?></div>
                                    <div class="d-flex align-items-center gap-1.5 mt-1">
                                        <span class="badge px-2 py-0.5 rounded d-inline-flex align-items-center gap-1 small text-dark" style="background: <?= htmlspecialchars($log['color_code']) ?>1c; border: 1px solid <?= htmlspecialchars($log['color_code']) ?>44; color: <?= htmlspecialchars($log['color_code']) ?> !important; font-size: 0.7rem;">
                                            <i class="<?= htmlspecialchars($log['icon_class']) ?>"></i> <?= htmlspecialchars($log['duty_type_name']) ?>
                                        </span>
                                         <span class="text-secondary small font-monospace" style="font-size: 0.72rem;">
                                             <?php if (!empty($log['duty_start_datetime']) && !empty($log['duty_end_datetime'])): ?>
                                                 <?php if (date('Y-m-d', strtotime($log['duty_start_datetime'])) !== date('Y-m-d', strtotime($log['duty_end_datetime']))): ?>
                                                     <?= date('d-M H:i', strtotime($log['duty_start_datetime'])) ?> &rarr; 
                                                     <?= date('d-M H:i', strtotime($log['duty_end_datetime'])) ?>
                                                 <?php else: ?>
                                                     <?= date('d M Y', strtotime($log['duty_date'])) ?> &bull; <?= htmlspecialchars($log['shift_name']) ?> (<?= date('H:i', strtotime($log['duty_start_datetime'])) ?> - <?= date('H:i', strtotime($log['duty_end_datetime'])) ?>)
                                                 <?php endif; ?>
                                             <?php else: ?>
                                                 <?= date('d M Y', strtotime($log['duty_date'])) ?> &bull; <?= htmlspecialchars($log['shift_name']) ?>
                                             <?php endif; ?>
                                         </span>
                                    </div>
                                </td>
                                <td class="small text-secondary">
                                    <?php if ($log['remarks']): ?>
                                        <div class="p-2 bg-light rounded text-dark italic border-start border-3 border-secondary border-opacity-20" style="font-size:0.78rem;">
                                            <em>"<?= htmlspecialchars($log['remarks']) ?>"</em>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted italic">No directives specified.</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted font-monospace" style="font-size:0.7rem;">
                                    <div><i class="fas fa-desktop me-1"></i> IP: <?= htmlspecialchars($log['ip_address'] ?: 'Unknown') ?></div>
                                    <div class="text-truncate mt-0.5" style="max-width: 180px;" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                        <i class="fas fa-globe me-1"></i> <?= htmlspecialchars($log['user_agent'] ?: 'Unknown') ?>
                                    </div>
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
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('historySearch');
    const actionFilter = document.getElementById('historyActionFilter');
    const dutyFilter = document.getElementById('historyDutyFilter');
    const resetBtn = document.getElementById('btnResetHistoryFilters');
    const rows = document.querySelectorAll('.history-row');

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const actionVal = actionFilter.value.toLowerCase().trim();
        const dutyVal = dutyFilter.value.toLowerCase().trim();

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const actionText = row.querySelector('.cell-action').textContent.toLowerCase();
            const crewText = row.querySelector('.cell-crew').textContent.toLowerCase();

            const matchQuery = !query || textContent.includes(query);
            const matchAction = !actionVal || actionText.includes(actionVal);
            const matchDuty = !dutyVal || crewText.includes(dutyVal);

            if (matchQuery && matchAction && matchDuty) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', applyFilters);
    actionFilter.addEventListener('change', applyFilters);
    dutyFilter.addEventListener('change', applyFilters);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        actionFilter.value = '';
        dutyFilter.value = '';
        rows.forEach(row => row.style.display = '');
    });
});
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
