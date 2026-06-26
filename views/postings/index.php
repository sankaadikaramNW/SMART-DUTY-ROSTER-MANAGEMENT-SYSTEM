<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-arrows-spin"></i> Camp Postings Log</h2>
        <p class="text-secondary">Track station movements and transfer histories for all personnel.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
            <button type="button" class="btn btn-custom btn-custom-primary" data-bs-toggle="modal" data-bs-target="#addPostingModal">
                <i class="fas fa-right-left"></i> Create Station Transfer
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="glass-card p-4">
    <!-- Live search input -->
    <div class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
            <input type="text" id="postingsSearchInput" class="form-control form-control-custom" placeholder="Search postings by rank, name, base or service number...">
        </div>
    </div>

    <!-- Table -->
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
                        <td colspan="7" class="text-center text-secondary py-4">No posting transfers registered.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($postings as $pos): ?>
                        <tr class="posting-row">
                            <td class="fw-bold search-cell-service"><?= htmlspecialchars($pos['service_number']) ?></td>
                            <td class="search-cell-name">
                                <span class="text-info fw-medium"><?= htmlspecialchars($pos['rank']) ?></span> 
                                <?= htmlspecialchars($pos['full_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($pos['from_camp']) ?></td>
                            <td class="text-info fw-bold"><?= htmlspecialchars($pos['to_camp']) ?></td>
                            <td><?= date('M d, Y', strtotime($pos['effective_date'])) ?></td>
                            <td><?= $pos['end_date'] ? date('M d, Y', strtotime($pos['end_date'])) : '<span class="text-muted">Ongoing</span>' ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> px-2.5">
                                    <?= $pos['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Transfer Posting Modal -->
<?php if ($roleName === 'SNCO' || $roleName === 'Administrator'): ?>
<div class="modal fade" id="addPostingModal" tabindex="-1" aria-labelledby="addPostingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content glass-card bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="addPostingModalLabel"><i class="fas fa-right-left me-2"></i> Register Camp Transfer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/postings/add" method="POST">
                <?= Security::csrfField() ?>
                <div class="modal-body">
                    <!-- Search airmen dynamic input -->
                    <div class="mb-3">
                        <label for="personnel_search" class="form-label text-secondary small">Search Personnel</label>
                        <input type="text" class="form-control form-control-custom" id="personnel_search" placeholder="Type name or service number..." autocomplete="off">
                        <div id="searchResults" class="list-group mt-2 border border-secondary bg-dark text-light shadow position-absolute w-75 z-3" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                        <!-- Hidden fields to hold selections -->
                        <input type="hidden" id="service_number_val" name="service_number" required>
                        <input type="hidden" id="from_camp_id_val" name="from_camp_id" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Origin Base</label>
                        <input type="text" class="form-control form-control-custom bg-opacity-10 text-muted" id="from_camp_display" disabled placeholder="Will auto-populate..." readonly>
                    </div>

                    <div class="mb-3">
                        <label for="to_camp_id" class="form-label text-secondary small">Destination Base</label>
                        <select class="form-select form-control-custom" id="to_camp_id" name="to_camp_id" required>
                            <option value="" disabled selected>Select Destination</option>
                            <?php foreach ($camps as $c): ?>
                                <option value="<?= $c['camp_id'] ?>"><?= htmlspecialchars($c['camp_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="effective_date" class="form-label text-secondary small">Transfer Effective Date</label>
                        <input type="date" class="form-control form-control-custom" id="effective_date" name="effective_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                    <button type="button" class="btn btn-custom btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom btn-custom-primary">Complete Posting Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // AJAX Personnel Autocomplete Search
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('personnel_search');
        const resultsDiv = document.getElementById('searchResults');
        const hiddenService = document.getElementById('service_number_val');
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
    // Live Search Table Filtering
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
