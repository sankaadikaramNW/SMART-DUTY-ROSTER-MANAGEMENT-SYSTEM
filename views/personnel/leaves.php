<?php
include __DIR__ . '/../../views/layout/header.php';
?>
<div class="row mb-4 align-items-center animate-fade-in">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-plane-departure"></i> Leave Management Dashboard</h2>
        <p class="text-secondary">Track, register, and monitor subordinate leaves and check-in schedules.</p>
    </div>
</div>

<div class="row g-4 animate-fade-in">
    <!-- Left Column: Submit Leave Form -->
    <div class="col-lg-4 col-12">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="fas fa-file-signature text-primary me-2"></i>Register Subordinate Leave
            </h5>
            
            <form action="<?= BASE_URL ?>/leaves/save" method="POST">
                <?= Security::csrfField() ?>
                
                <div class="mb-3">
                    <label for="personnel_search" class="form-label text-secondary small fw-bold">Select Subordinate</label>
                    <div class="position-relative personnel-search-wrapper">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control form-control-custom text-dark" id="personnel_search" placeholder="Type Service Number or Name..." autocomplete="off" required style="color: #000000 !important;">
                            <input type="hidden" id="service_number" name="service_number" value="" required>
                            <button type="button" class="btn btn-outline-secondary clear-search-btn" id="clearSearchBtn" style="display: none;"><i class="fas fa-xmark"></i></button>
                        </div>
                        <div class="autocomplete-suggestions dropdown-menu w-100 bg-white text-dark border-light shadow-lg" id="searchSuggestions" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050; position: absolute;"></div>
                        
                        <div class="search-spinner position-absolute end-0 top-0 mt-2 me-5 text-info" id="searchSpinner" style="display: none; z-index: 1060;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="leave_type" class="form-label text-secondary small fw-bold">Leave Type</label>
                    <select class="form-select form-control-custom text-dark" id="leave_type" name="leave_type" required>
                        <option value="" disabled selected>Select Leave Type...</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Duty Leave">Duty Leave</option>
                        <option value="Emergency Leave">Emergency Leave</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="leave_start_date" class="form-label text-secondary small fw-bold">Leave Start Date</label>
                    <input type="date" class="form-control form-control-custom text-dark" id="leave_start_date" name="leave_start_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="leave_end_date" class="form-label text-secondary small fw-bold">Leave End Date</label>
                    <input type="date" class="form-control form-control-custom text-dark" id="leave_end_date" name="leave_end_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-custom btn-custom-primary py-2.5">
                        <i class="fas fa-check-circle me-1"></i> Register Leave Period
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Leaves List -->
    <div class="col-lg-8 col-12">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="fas fa-list-check text-success me-2"></i>Active & Upcoming Leave Records
            </h5>
            
            <div class="table-custom-container">
                <table class="table-custom text-dark" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Service No</th>
                            <th style="width: 20%;">Rank & Name</th>
                            <th style="width: 22%;">Leave Period</th>
                            <th style="width: 20%;">Details / Extensions</th>
                            <th style="width: 14%;">Status</th>
                            <th style="width: 12%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No leave records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $l): ?>
                                <?php
                                    $status = $l['status'];
                                    
                                    // Set badge styles per status
                                    if ($status === 'Expected') {
                                        $statusClass = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                                        $icon = '🔵';
                                    } elseif ($status === 'Completed') {
                                        $statusClass = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                        $icon = '🟢';
                                    } elseif ($status === 'Not Reported') {
                                        $statusClass = 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                                        $icon = '🔴';
                                    } elseif ($status === 'Late Reported') {
                                        $statusClass = 'badge badge-orange bg-opacity-15';
                                        $icon = '🟠';
                                    } else { // Granted
                                        $statusClass = 'badge badge-purple bg-opacity-15';
                                        $icon = '🟣';
                                    }
                                ?>
                                <tr>
                                    <td class="font-monospace text-muted"><?= htmlspecialchars($l['service_number']) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($l['rank'] . ' ' . $l['full_name']) ?></td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($l['leave_start_date']) ?> to <?= htmlspecialchars($l['leave_end_date']) ?></div>
                                        <span class="text-secondary small font-monospace" style="font-size: 0.725rem;">
                                            (<?= ceil((strtotime($l['leave_end_date']) - strtotime($l['leave_start_date'])) / 86400) + 1 ?> days)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark small">
                                            <strong>Type:</strong> <?= htmlspecialchars($l['leave_type']) ?>
                                        </div>
                                        <?php if ($l['actual_reporting_date']): ?>
                                            <div class="text-success small mt-1" style="font-size:0.75rem;">
                                                <i class="fas fa-circle-check"></i> Reported: <?= htmlspecialchars($l['actual_reporting_date']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($l['granted_end_date']): ?>
                                            <div class="text-purple small mt-1 border-top pt-1" style="font-size:0.75rem; border-color: rgba(111,66,193,0.15) !important;">
                                                <div><strong>Extension:</strong> to <?= htmlspecialchars($l['granted_end_date']) ?></div>
                                                <div><strong>Auth:</strong> <?= htmlspecialchars($l['granted_by']) ?></div>
                                                <div class="text-muted text-truncate" style="max-width:180px;"><strong>Reason:</strong> <?= htmlspecialchars($l['granted_reason']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="<?= $statusClass ?>"><?= $icon ?> <?= $status ?></span></td>
                                    <td>
                                        <?php if ($l['actual_reporting_date'] === null): ?>
                                            <div class="d-flex flex-column gap-1.5">
                                                <button type="button" class="btn btn-xs btn-outline-success py-1 px-1.5" onclick="openReturnModal(<?= $l['leave_id'] ?>, '<?= htmlspecialchars($l['service_number']) ?>');" style="font-size:0.75rem;">
                                                    <i class="fas fa-check-circle"></i> Return
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-purple py-1 px-1.5" onclick="openExtensionModal(<?= $l['leave_id'] ?>, '<?= htmlspecialchars($l['service_number']) ?>');" style="font-size:0.75rem;">
                                                    <i class="fas fa-plane-departure"></i> Extend
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-secondary small">Processed</span>
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
</div>

<!-- Grant Extension Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1" aria-labelledby="extensionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card">
            <div class="modal-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="extensionModalLabel">
                    <i class="fas fa-plane-departure text-purple me-2"></i>Grant Additional Leave Extension
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/leaves/grant-extension" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" id="ext_leave_id" name="leave_id" value="">
                
                <div class="modal-body text-dark p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Subordinate Service No</label>
                        <input type="text" class="form-control form-control-custom text-dark bg-light" id="ext_service_number" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="granted_end_date" class="form-label text-secondary small fw-bold">New Extended Return Date</label>
                        <input type="date" class="form-control form-control-custom text-dark" id="granted_end_date" name="granted_end_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="granted_reason" class="form-label text-secondary small fw-bold">Reason for Extension Grant</label>
                        <textarea class="form-control form-control-custom text-dark" id="granted_reason" name="granted_reason" rows="3" placeholder="Explain why the extension was authorized..." required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top border-secondary border-opacity-10">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom btn-custom-primary py-2 px-3">Grant Extension</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card">
            <div class="modal-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="returnModalLabel">
                    <i class="fas fa-check-circle text-success me-2"></i>Report Subordinate Return
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/leaves/report-return" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" id="ret_leave_id" name="leave_id" value="">
                
                <div class="modal-body text-dark p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Subordinate Service No</label>
                        <input type="text" class="form-control form-control-custom text-dark bg-light" id="ret_service_number" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actual_reporting_date" class="form-label text-secondary small fw-bold">Actual Reporting Date</label>
                        <input type="date" class="form-control form-control-custom text-dark" id="actual_reporting_date" name="actual_reporting_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                
                <div class="modal-footer border-top border-secondary border-opacity-10">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom btn-custom-success py-2 px-3">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Adjust form inputs and selectors colors for high accessibility and readable black text */
.form-control-custom {
    color: #000000 !important;
}
.form-select.form-control-custom option {
    color: #000000 !important;
    background-color: #ffffff !important;
}
.table-custom tbody tr td {
    color: #000000 !important;
}
.badge-purple {
    color: #6f42c1 !important;
    border: 1px solid rgba(111, 66, 193, 0.3) !important;
    background-color: rgba(111, 66, 193, 0.1) !important;
}
.badge-orange {
    color: #fd7e14 !important;
    border: 1px solid rgba(253, 126, 20, 0.3) !important;
    background-color: rgba(253, 126, 20, 0.15) !important;
}
.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
    background: transparent;
}
.btn-outline-purple:hover {
    color: #ffffff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
.btn-xs {
    padding: 0.15rem 0.4rem;
    font-size: 0.75rem;
    border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('personnel_search');
    const hidden = document.getElementById('service_number');
    const suggestions = document.getElementById('searchSuggestions');
    const spinner = document.getElementById('searchSpinner');
    const clearBtn = document.getElementById('clearSearchBtn');

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
                            
                            const rankShort = item.rank_short_name || '';
                            const initials = item.initials || '';
                            
                            const nameParts = item.full_name.trim().split(' ');
                            const lastName = nameParts[nameParts.length - 1];
                            const formattedName = `${rankShort} ${initials} ${lastName}`;
                            const highlightedName = formattedName.replace(regex, '<mark class="p-0 bg-warning text-dark">$1</mark>');

                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action bg-white text-dark border-light-subtle small py-2 text-start';
                            btn.innerHTML = `
                                <strong>${highlightedSN}</strong> ${highlightedName}<br>
                                <span class="text-secondary small">Camp: ${item.camp_name}</span>
                            `;

                            btn.addEventListener('click', () => {
                                input.value = `${item.service_number} - ${rankShort} ${initials} ${lastName}`;
                                hidden.value = item.service_number;
                                clearBtn.style.display = 'block';

                                suggestions.innerHTML = '';
                                suggestions.style.display = 'none';
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

    clearBtn.addEventListener('click', () => {
        input.value = '';
        hidden.value = '';
        clearBtn.style.display = 'none';
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
    });

    document.addEventListener('click', (e) => {
        if (e.target !== input && e.target !== suggestions) {
            suggestions.style.display = 'none';
        }
    });

    function escapeRegex(string) {
        return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    // Modal triggering functions
    window.openExtensionModal = function(leaveId, serviceNum) {
        document.getElementById('ext_leave_id').value = leaveId;
        document.getElementById('ext_service_number').value = serviceNum;
        
        const modalEl = document.getElementById('extensionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    window.openReturnModal = function(leaveId, serviceNum) {
        document.getElementById('ret_leave_id').value = leaveId;
        document.getElementById('ret_service_number').value = serviceNum;
        
        const modalEl = document.getElementById('returnModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };
});
</script>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
