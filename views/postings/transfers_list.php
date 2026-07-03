<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-right-left"></i> Station Posting Transfers</h2>
        <p class="text-secondary">Track station movements and manage military transfer workflows for personnel.</p>
    </div>
</div>

<!-- Nav Tabs for Posting Log vs Transfer Workflow -->
<div class="mb-4">
    <ul class="nav nav-pills gap-2 p-1 bg-dark bg-opacity-10 rounded-pill d-inline-flex" id="postingsTabs" role="tablist" style="border: 1px solid rgba(255,255,255,0.08);">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold" id="transfers-tab" data-bs-toggle="tab" data-bs-target="#transfers-content" type="button" role="tab" aria-controls="transfers-content" aria-selected="true">
                <i class="fas fa-arrows-spin me-2 text-info"></i> Transfer Requests
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button" role="tab" aria-controls="history-content" aria-selected="false">
                <i class="fas fa-clock-rotate-left me-2 text-success"></i> Active Posting Log
            </button>
        </li>
    </ul>
</div>

<div class="tab-content" id="postingsTabsContent">
    
    <!-- Tab 1: Transfer Workflow Requests -->
    <div class="tab-pane fade show active animate-fade-in" id="transfers-content" role="tabpanel" aria-labelledby="transfers-tab">
        <div class="glass-card mb-4">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-circle-nodes text-info me-2"></i> Workflow Management</h5>
                <?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
                    <button type="button" class="btn btn-sm btn-custom btn-custom-primary py-2" data-bs-toggle="modal" data-bs-target="#addTransferModal">
                        <i class="fas fa-plus"></i> Create Transfer Request
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-4">
                
                <?php if ($roleName === 'Administrator'): ?>
                    <!-- Administrator Global View -->
                    <h6 class="fw-bold mb-3 text-secondary"><i class="fas fa-globe text-primary me-2"></i> All System Transfers</h6>
                    <div class="table-custom-container">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Service Number</th>
                                    <th>Rank & Name</th>
                                    <th>From Camp</th>
                                    <th>To Camp</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allTransfers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">No transfer requests registered.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allTransfers as $t): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($t['service_number']) ?></td>
                                            <td>
                                                <span class="text-info fw-medium"><?= htmlspecialchars($t['rank_short_name']) ?></span> 
                                                <?= htmlspecialchars($t['initials'] . ' ' . $t['full_name']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($t['from_camp_name']) ?></td>
                                            <td class="text-info fw-bold"><?= htmlspecialchars($t['to_camp_name']) ?></td>
                                            <td class="font-monospace small"><?= date('Y-m-d', strtotime($t['effective_date'])) ?></td>
                                            <td>
                                                <?php
                                                $badgeColor = 'secondary';
                                                switch ($t['status']) {
                                                    case 'Draft': $badgeColor = 'secondary'; break;
                                                    case 'Pending Origin Approval': $badgeColor = 'warning'; break;
                                                    case 'Origin Approved': $badgeColor = 'info'; break;
                                                    case 'Pending Destination Review': $badgeColor = 'info'; break;
                                                    case 'Pending Destination Approval': $badgeColor = 'primary'; break;
                                                    case 'Transfer Completed': $badgeColor = 'success'; break;
                                                    case 'Returned for Correction': $badgeColor = 'dark'; break;
                                                    case 'Rejected': $badgeColor = 'danger'; break;
                                                    case 'Cancelled': $badgeColor = 'secondary'; break;
                                                }
                                                ?>
                                                <span class="badge rounded-pill bg-<?= $badgeColor ?> bg-opacity-25 border border-<?= $badgeColor ?> border-opacity-25 text-<?= $badgeColor ?> px-2.5">
                                                    <?= $t['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/transfers/view?id=<?= $t['transfer_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                
                <?php else: ?>
                    <!-- Camp-Restricted SNCO / OCPROVST View -->
                    
                    <!-- Section 1: Outgoing Transfers -->
                    <div class="mb-5">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="fas fa-arrow-right-from-bracket text-warning me-2"></i> Outgoing Transfers (From Our Base)</h6>
                        <div class="table-custom-container">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Service Number</th>
                                        <th>Rank & Name</th>
                                        <th>Destination Camp</th>
                                        <th>Effective Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($outgoingTransfers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-3">No outgoing transfer requests from this camp.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($outgoingTransfers as $t): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($t['service_number']) ?></td>
                                                <td>
                                                    <span class="text-info fw-medium"><?= htmlspecialchars($t['rank_short_name']) ?></span> 
                                                    <?= htmlspecialchars($t['initials'] . ' ' . $t['full_name']) ?>
                                                </td>
                                                <td class="text-info fw-bold"><?= htmlspecialchars($t['to_camp_name']) ?></td>
                                                <td class="font-monospace small"><?= date('Y-m-d', strtotime($t['effective_date'])) ?></td>
                                                <td>
                                                    <?php
                                                    $badgeColor = 'secondary';
                                                    switch ($t['status']) {
                                                        case 'Draft': $badgeColor = 'secondary'; break;
                                                        case 'Pending Origin Approval': $badgeColor = 'warning'; break;
                                                        case 'Origin Approved': $badgeColor = 'info'; break;
                                                        case 'Pending Destination Review': $badgeColor = 'info'; break;
                                                        case 'Pending Destination Approval': $badgeColor = 'primary'; break;
                                                        case 'Transfer Completed': $badgeColor = 'success'; break;
                                                        case 'Returned for Correction': $badgeColor = 'dark'; break;
                                                        case 'Rejected': $badgeColor = 'danger'; break;
                                                        case 'Cancelled': $badgeColor = 'secondary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge rounded-pill bg-<?= $badgeColor ?> bg-opacity-25 border border-<?= $badgeColor ?> border-opacity-25 text-<?= $badgeColor ?> px-2.5">
                                                        <?= $t['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>/transfers/view?id=<?= $t['transfer_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Section 2: Incoming Transfers -->
                    <div>
                        <h6 class="fw-bold mb-3 text-secondary"><i class="fas fa-arrow-right-to-bracket text-success me-2"></i> Incoming Transfers (Pending Incoming / Review)</h6>
                        <div class="table-custom-container">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Service Number</th>
                                        <th>Rank & Name</th>
                                        <th>Origin Camp</th>
                                        <th>Effective Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($incomingTransfers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-3">No incoming transfers received.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($incomingTransfers as $t): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($t['service_number']) ?></td>
                                                <td>
                                                    <span class="text-info fw-medium"><?= htmlspecialchars($t['rank_short_name']) ?></span> 
                                                    <?= htmlspecialchars($t['initials'] . ' ' . $t['full_name']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($t['from_camp_name']) ?></td>
                                                <td class="font-monospace small"><?= date('Y-m-d', strtotime($t['effective_date'])) ?></td>
                                                <td>
                                                    <?php
                                                    $badgeColor = 'secondary';
                                                    switch ($t['status']) {
                                                        case 'Draft': $badgeColor = 'secondary'; break;
                                                        case 'Pending Origin Approval': $badgeColor = 'warning'; break;
                                                        case 'Origin Approved': $badgeColor = 'info'; break;
                                                        case 'Pending Destination Review': $badgeColor = 'info'; break;
                                                        case 'Pending Destination Approval': $badgeColor = 'primary'; break;
                                                        case 'Transfer Completed': $badgeColor = 'success'; break;
                                                        case 'Returned for Correction': $badgeColor = 'dark'; break;
                                                        case 'Rejected': $badgeColor = 'danger'; break;
                                                        case 'Cancelled': $badgeColor = 'secondary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge rounded-pill bg-<?= $badgeColor ?> bg-opacity-25 border border-<?= $badgeColor ?> border-opacity-25 text-<?= $badgeColor ?> px-2.5">
                                                        <?= $t['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>/transfers/view?id=<?= $t['transfer_id'] ?>" class="btn btn-sm btn-custom btn-custom-secondary py-1 px-2">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Historical Active Posting Log -->
    <div class="tab-pane fade animate-fade-in" id="history-content" role="tabpanel" aria-labelledby="history-tab">
        <div class="glass-card mb-4">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-success me-2"></i> Active Station Postings History</h5>
            </div>
            <div class="card-body p-4">
                <!-- Search bar for history -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" id="postingsSearchInput" class="form-control form-control-custom" placeholder="Search postings by rank, name, base or service number...">
                    </div>
                </div>

                <div class="table-custom-container">
                    <table class="table-custom" id="postingsTable">
                        <thead>
                            <tr>
                                <th>Service Number</th>
                                <th>Rank & Name</th>
                                <th>From Base</th>
                                <th>To Base</th>
                                <th>Effective Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($postings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">No historical posting records registered.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($postings as $p): ?>
                                    <tr class="posting-row">
                                        <td class="fw-bold search-cell-service"><?= htmlspecialchars($p['service_number']) ?></td>
                                        <td class="search-cell-name">
                                            <span class="text-info fw-medium"><?= htmlspecialchars($p['rank']) ?></span> 
                                            <?= htmlspecialchars($p['initials'] . ' ' . $p['full_name']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($p['from_camp']) ?></td>
                                        <td class="text-info fw-bold"><?= htmlspecialchars($p['to_camp']) ?></td>
                                        <td class="font-monospace small"><?= date('Y-m-d', strtotime($p['effective_date'])) ?></td>
                                        <td class="font-monospace small"><?= $p['end_date'] ? date('Y-m-d', strtotime($p['end_date'])) : 'Present' ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= $p['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $p['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $p['status'] === 'Active' ? 'success' : 'secondary' ?> px-2.5">
                                                <?= $p['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Add Transfer Request Modal -->
<?php if ($roleName === 'SNCO' || $roleName === 'Warrant Officer IC' || $roleName === 'Administrator'): ?>
<div class="modal fade" id="addTransferModal" tabindex="-1" aria-labelledby="addTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form action="<?= BASE_URL ?>/transfers/create" method="POST" enctype="multipart/form-data" class="modal-content glass-card">
            <?= Security::csrfField() ?>
            <input type="hidden" id="submit_action_val" name="submit_action" value="save_draft">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="addTransferModalLabel"><i class="fas fa-right-left me-2 text-primary"></i> Create Station Transfer Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                
                <div class="row g-3">
                    <!-- Search airmen dynamic input -->
                    <div class="col-md-12">
                        <label for="personnel_search" class="form-label text-secondary small fw-bold">Search Personnel</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-user-tag"></i></span>
                            <input type="text" class="form-control form-control-custom" id="personnel_search" placeholder="Type service number, rank, or name to search..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="list-group mt-2 border border-light-subtle bg-white shadow position-absolute w-75 z-3" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                        <!-- Hidden fields to hold selections -->
                        <input type="hidden" id="service_number_val" name="service_number" required>
                        <input type="hidden" id="from_camp_id_val" name="from_camp_id" required>
                    </div>

                    <!-- Auto-populated fields -->
                    <div class="col-md-4">
                        <label class="form-label text-secondary small">Service Number</label>
                        <input type="text" class="form-control form-control-custom bg-light text-muted" id="service_number_display" disabled placeholder="Auto-populated" readonly>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label text-secondary small">Rank</label>
                        <input type="text" class="form-control form-control-custom bg-light text-muted" id="rank_display" disabled placeholder="Auto-populated" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary small">Full Name</label>
                        <input type="text" class="form-control form-control-custom bg-light text-muted" id="name_display" disabled placeholder="Auto-populated" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small">Current Camp/Base</label>
                        <input type="text" class="form-control form-control-custom bg-light text-muted" id="from_camp_display" disabled placeholder="Auto-populated" readonly>
                    </div>

                    <!-- Input Fields -->
                    <div class="col-md-6">
                        <label for="to_camp_id" class="form-label text-secondary small fw-bold">Destination Camp/Base</label>
                        <select class="form-select form-control-custom" id="to_camp_id" name="to_camp_id" required>
                            <option value="" disabled selected>Select Destination Base</option>
                            <?php foreach ($camps as $c): ?>
                                <option value="<?= $c['camp_id'] ?>"><?= htmlspecialchars($c['camp_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="effective_date" class="form-label text-secondary small fw-bold">Transfer Effective Date</label>
                        <input type="date" class="form-control form-control-custom" id="effective_date" name="effective_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="supporting_document" class="form-label text-secondary small fw-bold">Supporting Documents (Optional)</label>
                        <input type="file" class="form-control form-control-custom" id="supporting_document" name="supporting_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>

                    <div class="col-md-12">
                        <label for="reason" class="form-label text-secondary small fw-bold">Reason for Transfer</label>
                        <textarea class="form-control form-control-custom" id="reason" name="reason" rows="3" placeholder="Provide detailed reasoning for this transfer posting request..." required></textarea>
                    </div>

                    <div class="col-md-12">
                        <label for="remarks" class="form-label text-secondary small fw-bold">Remarks</label>
                        <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="2" placeholder="Any additional administrative remarks..."></textarea>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-xmark"></i> Cancel
                </button>
                <div class="d-flex gap-2">
                    <button type="submit" onclick="document.getElementById('submit_action_val').value='save_draft';" class="btn btn-custom btn-custom-secondary">
                        <i class="fas fa-floppy-disk text-info"></i> Save as Draft
                    </button>
                    <button type="submit" onclick="document.getElementById('submit_action_val').value='submit_request';" class="btn btn-custom btn-custom-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // AJAX Personnel Autocomplete Search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('personnel_search');
        const resultsDiv = document.getElementById('searchResults');
        
        // Form inputs
        const hiddenService = document.getElementById('service_number_val');
        const displayService = document.getElementById('service_number_display');
        const displayRank = document.getElementById('rank_display');
        const displayName = document.getElementById('name_display');
        const hiddenFromCamp = document.getElementById('from_camp_id_val');
        const displayFromCamp = document.getElementById('from_camp_display');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'none';
                return;
            }

            fetch(`${BASE_URL}/personnel/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action list-group-item-dark text-light border-secondary small py-2';
                            btn.innerHTML = `<strong>${item.service_number}</strong> - ${item.rank} ${item.full_name} (${item.camp_name})`;
                            btn.addEventListener('click', () => {
                                searchInput.value = `${item.rank} ${item.full_name} (${item.service_number})`;
                                hiddenService.value = item.service_number;
                                displayService.value = item.service_number;
                                displayRank.value = item.rank;
                                displayName.value = item.full_name;
                                hiddenFromCamp.value = item.camp_id;
                                displayFromCamp.value = item.camp_name;
                                resultsDiv.innerHTML = '';
                                resultsDiv.style.display = 'none';
                            });
                            resultsDiv.appendChild(btn);
                        });
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.innerHTML = '<div class="list-group-item list-group-item-dark text-muted border-secondary small py-2">No matches found</div>';
                        resultsDiv.style.display = 'block';
                    }
                })
                .catch(err => console.error('Error searching personnel:', err));
        });

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target !== searchInput && e.target !== resultsDiv) {
                resultsDiv.style.display = 'none';
            }
        });
    });
</script>
<?php endif; ?>

<script>
    // Live Search Table Filtering for Postings History
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('postingsSearchInput');
        const rows = document.querySelectorAll('.posting-row');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const service = row.querySelector('.search-cell-service').textContent.toLowerCase();
                const name = row.querySelector('.search-cell-name').textContent.toLowerCase();
                const textContent = row.textContent.toLowerCase();

                if (service.includes(query) || name.includes(query) || textContent.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>

<?php
include __DIR__ . '/../layout/footer.php';
?>
