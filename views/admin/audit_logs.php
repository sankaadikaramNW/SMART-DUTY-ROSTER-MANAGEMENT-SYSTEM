<?php
include __DIR__ . '/../layout/header.php';
?>
<style>
/* Force audit trail table details to render in black/dark color for accessibility and readability */
.table-custom tbody tr td {
    color: #000000 !important;
}
.table-custom tbody tr td div.fw-bold {
    color: #000000 !important;
}
.table-custom tbody tr td span.text-secondary {
    color: #1e293b !important; /* Rich charcoal black */
}
.table-custom tbody tr td span.text-muted {
    color: #334155 !important; /* Dark slate for muted info */
}
.table-custom tbody tr td.fw-medium {
    color: #000000 !important;
}
.table-custom tbody tr td.font-monospace {
    color: #000000 !important;
}
</style>
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
                    <th style="width: 10%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No audit logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $i => $log): ?>
                        <?php
                            $details = AuditLog::parseEntityDetails($log);
                            $diff = ($details['action_type'] === 'UPDATE') ? AuditLog::getDiff($log['previous_data'], $log['new_data']) : [];
                            $modalData = [
                                'module' => $log['module'],
                                'action' => $log['action'],
                                'datetime' => date('Y-m-d H:i:s', strtotime($log['created_at'])),
                                'ip_address' => $log['ip_address'],
                                'responsible_name' => $log['service_number'] ? ($log['rank'] . ' ' . $log['full_name']) : 'System (Cron/Automatic)',
                                'responsible_service' => $log['service_number'] ?: 'System',
                                'entity_type' => $details['entity_type'],
                                'entity_id' => $details['entity_id'],
                                'action_type' => $details['action_type'],
                                'data' => $details['data'],
                                'diff' => $diff
                            ];
                        ?>
                        <tr>
                            <td class="font-monospace text-muted"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            <td>
                                <?php if ($log['service_number']): ?>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($log['rank'] . ' ' . $log['full_name']) ?></div>
                                    <span class="text-secondary font-monospace" style="font-size: 0.75rem;"><?= htmlspecialchars($log['service_number']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">System (Cron/Automatic)</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-10 px-2 py-1"><?= htmlspecialchars($log['module']) ?></span></td>
                            <td class="fw-medium text-dark"><?= htmlspecialchars($log['action']) ?></td>
                            <td class="font-monospace text-secondary"><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td>
                                <button class="btn btn-xs btn-outline-info py-1 px-2.5 d-inline-flex align-items-center gap-1 small" 
                                        type="button" 
                                        data-audit="<?= htmlspecialchars(json_encode($modalData), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="showAuditDetails(this);"
                                        style="font-size: 0.75rem; font-weight: 500;">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </td>
                        </tr>
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

<!-- Details Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="auditDetailModalLabel">
                    <i class="fas fa-circle-info text-info me-2"></i>Audit Log Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <!-- Info Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 p-3 h-100">
                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2"><i class="fas fa-receipt me-2"></i>Audit Information</h6>
                            <p class="mb-2"><strong>Module:</strong> <span id="modalModule" class="badge bg-secondary bg-opacity-25 text-info px-2 py-1 ms-1"></span></p>
                            <p class="mb-2"><strong>Action:</strong> <span id="modalAction" class="fw-bold ms-1 text-dark"></span></p>
                            <p class="mb-2"><strong>Date & Time:</strong> <span id="modalDateTime" class="font-monospace ms-1 text-muted"></span></p>
                            <p class="mb-0"><strong>IP Address:</strong> <span id="modalIpAddress" class="font-monospace text-secondary ms-1"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0 p-3 h-100">
                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2"><i class="fas fa-user me-2"></i>Responsible User</h6>
                            <p class="mb-2"><strong>Name:</strong> <span id="modalResponsibleName" class="text-dark"></span></p>
                            <p class="mb-0"><strong>Service Number:</strong> <span id="modalResponsibleService" class="font-monospace badge bg-secondary bg-opacity-10 text-dark"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Affected Record & Changes -->
                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2"><i class="fas fa-circle-dot me-2"></i>Affected Record Details</h6>
                    <p class="mb-2"><strong>Entity Type:</strong> <span id="modalEntityType" class="fw-bold text-info ms-1"></span></p>
                    <p class="mb-0"><strong>Record ID / Key:</strong> <span id="modalEntityId" class="fw-bold font-monospace ms-1 text-dark"></span></p>
                </div>

                <div class="card bg-light border-0 p-3">
                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2" id="modalChangeTitle"><i class="fas fa-list-check me-2"></i>Changed Information</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0 text-dark" id="modalChangeTable" style="display: none;">
                            <thead class="table-light">
                                <tr>
                                    <th>Field</th>
                                    <th>Previous Value</th>
                                    <th>Updated Value</th>
                                </tr>
                            </thead>
                            <tbody id="modalChangeBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                        <div id="modalNoChanges" class="text-center text-muted py-3" style="display: none;">
                            No changes recorded.
                        </div>
                        <div id="modalSimpleDetails" class="text-secondary" style="display: none;">
                            <!-- Populated dynamically with key-value fields for CREATE/DELETE/AUTH -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-custom btn-custom-secondary py-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showAuditDetails(button) {
    const dataStr = button.getAttribute('data-audit');
    if (!dataStr) return;

    try {
        const data = JSON.parse(dataStr);
        
        document.getElementById('modalModule').textContent = data.module;
        document.getElementById('modalAction').textContent = data.action;
        document.getElementById('modalDateTime').textContent = data.datetime;
        document.getElementById('modalIpAddress').textContent = data.ip_address;
        document.getElementById('modalResponsibleName').textContent = data.responsible_name;
        document.getElementById('modalResponsibleService').textContent = data.responsible_service;
        document.getElementById('modalEntityType').textContent = data.entity_type;
        document.getElementById('modalEntityId').textContent = data.entity_id;

        const table = document.getElementById('modalChangeTable');
        const changeBody = document.getElementById('modalChangeBody');
        const simpleDetails = document.getElementById('modalSimpleDetails');
        const noChanges = document.getElementById('modalNoChanges');
        const changeTitle = document.getElementById('modalChangeTitle');

        // Reset elements
        changeBody.innerHTML = '';
        simpleDetails.innerHTML = '';
        simpleDetails.style.display = 'none';
        table.style.display = 'none';
        noChanges.style.display = 'none';

        if (data.action_type === 'UPDATE') {
            changeTitle.innerHTML = '<i class="fas fa-list-check me-2"></i>Changed Information (Update)';
            const diffKeys = Object.keys(data.diff);
            if (diffKeys.length > 0) {
                table.style.display = 'table';
                diffKeys.forEach(key => {
                    const row = document.createElement('tr');
                    
                    const fieldTd = document.createElement('td');
                    fieldTd.className = 'fw-bold text-dark';
                    fieldTd.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                    
                    const prevTd = document.createElement('td');
                    prevTd.className = 'text-danger font-monospace';
                    prevTd.textContent = data.diff[key].prev;

                    const newTd = document.createElement('td');
                    newTd.className = 'text-success font-monospace';
                    newTd.textContent = data.diff[key].new;

                    row.appendChild(fieldTd);
                    row.appendChild(prevTd);
                    row.appendChild(newTd);
                    changeBody.appendChild(row);
                });
            } else {
                noChanges.style.display = 'block';
            }
        } else {
            // For CREATE, DELETE, AUTH
            changeTitle.innerHTML = `<i class="fas fa-circle-info me-2"></i>Record Attributes (${data.action_type})`;
            const dataKeys = Object.keys(data.data);
            if (dataKeys.length > 0) {
                simpleDetails.style.display = 'block';
                const ul = document.createElement('ul');
                ul.className = 'list-group list-group-flush';
                dataKeys.forEach(key => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center bg-transparent border-light-subtle px-0 py-2';
                    
                    const keySpan = document.createElement('strong');
                    keySpan.className = 'text-dark';
                    keySpan.textContent = key + ':';
                    
                    const valSpan = document.createElement('span');
                    valSpan.className = 'font-monospace text-secondary';
                    valSpan.textContent = data.data[key];

                    li.appendChild(keySpan);
                    li.appendChild(valSpan);
                    ul.appendChild(li);
                });
                simpleDetails.appendChild(ul);
            } else {
                noChanges.style.display = 'block';
            }
        }

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
        modal.show();

    } catch (e) {
        console.error("Failed to parse audit details: ", e);
    }
}
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
