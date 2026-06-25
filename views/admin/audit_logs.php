<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-receipt"></i> System Audit Trail</h2>
        <p class="text-secondary">Track administrative and roster scheduling operations in an immutable database log.</p>
    </div>
</div>

<div class="glass-card p-4 mb-4">
    <!-- Filter Form -->
    <form action="<?= BASE_URL ?>/audit-logs" method="GET">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="module" class="form-label text-secondary small">Filter by Module</label>
                <select class="form-select form-control-custom" id="module" name="module">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>" <?= $module === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="user" class="form-label text-secondary small">Search User Name or Service Number</label>
                <input type="text" class="form-control form-control-custom" id="user" name="user" value="<?= htmlspecialchars($user) ?>" placeholder="e.g. SLAF/PROV/100 or Kamal">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-custom btn-custom-primary flex-grow-1 py-2"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="<?= BASE_URL ?>/audit-logs" class="btn btn-custom btn-custom-secondary py-2"><i class="fas fa-arrows-rotate"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="glass-card p-4">
    <div class="table-custom-container">
        <table class="table-custom" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th style="width: 15%;">Timestamp</th>
                    <th style="width: 20%;">Responsible Account</th>
                    <th style="width: 15%;">System Module</th>
                    <th style="width: 25%;">Logged Action</th>
                    <th style="width: 15%;">IP Address</th>
                    <th style="width: 10%;">Data Diff</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No audit logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $i => $log): ?>
                        <tr>
                            <td class="font-monospace text-muted"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            <td>
                                <?php if ($log['service_number']): ?>
                                    <div class="fw-bold text-light"><?= htmlspecialchars($log['rank'] . ' ' . $log['full_name']) ?></div>
                                    <span class="text-secondary font-monospace" style="font-size: 0.75rem;"><?= htmlspecialchars($log['service_number']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">System (Cron/Automatic)</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-10 px-2 py-1"><?= htmlspecialchars($log['module']) ?></span></td>
                            <td class="fw-medium text-light"><?= htmlspecialchars($log['action']) ?></td>
                            <td class="font-monospace text-secondary"><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td>
                                <?php if ($log['previous_data'] || $log['new_data']): ?>
                                    <button class="btn btn-xs btn-custom btn-custom-secondary py-0.5 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#diff-<?= $i ?>" aria-expanded="false">
                                        <i class="fas fa-magnifying-glass"></i> Diff
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">None</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <?php if ($log['previous_data'] || $log['new_data']): ?>
                            <tr class="collapse d-table-row bg-dark bg-opacity-40" id="diff-<?= $i ?>">
                                <td colspan="6" class="p-3 border-bottom border-secondary border-opacity-10">
                                    <div class="row g-2">
                                        <?php if ($log['previous_data']): ?>
                                            <div class="col-md-6">
                                                <div class="text-secondary small fw-bold mb-1">Previous State Data:</div>
                                                <pre class="bg-dark bg-opacity-65 text-warning p-2 rounded small font-monospace overflow-auto mb-0" style="max-height: 200px;"><?= htmlspecialchars(json_encode(json_decode($log['previous_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($log['new_data']): ?>
                                            <div class="col-md-6">
                                                <div class="text-secondary small fw-bold mb-1">Updated State Data:</div>
                                                <pre class="bg-dark bg-opacity-65 text-success p-2 rounded small font-monospace overflow-auto mb-0" style="max-height: 200px;"><?= htmlspecialchars(json_encode(json_decode($log['new_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php 
                $queryParams = $_GET; 
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <?php 
                    $queryParams['page'] = $page - 1; 
                    ?>
                    <a class="page-link bg-dark text-light border-secondary small" href="<?= BASE_URL ?>/audit-logs?<?= http_build_query($queryParams) ?>">&laquo; Previous</a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php 
                    $queryParams['page'] = $p; 
                    ?>
                    <li class="page-item <?= $page == $p ? 'active' : '' ?>">
                        <a class="page-link <?= $page == $p ? 'bg-primary text-light' : 'bg-dark text-light' ?> border-secondary small" href="<?= BASE_URL ?>/audit-logs?<?= http_build_query($queryParams) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <?php 
                    $queryParams['page'] = $page + 1; 
                    ?>
                    <a class="page-link bg-dark text-light border-secondary small" href="<?= BASE_URL ?>/audit-logs?<?= http_build_query($queryParams) ?>">Next &raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
?>
