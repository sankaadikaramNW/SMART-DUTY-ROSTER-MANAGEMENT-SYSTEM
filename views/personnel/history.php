<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-history"></i> Posting History Log</h2>
        <p class="text-secondary">Historical station transfer logs for: <?= htmlspecialchars($person['rank'] . ' ' . $person['full_name']) ?></p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= BASE_URL ?>/personnel/view?service_number=<?= urlencode($person['service_number']) ?>" class="btn btn-custom btn-custom-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
</div>

<div class="glass-card p-4">
    <div class="table-custom-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>From Camp/Base</th>
                    <th>To Camp/Base</th>
                    <th>Effective Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($postings)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">No posting transfers registered.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($postings as $pos): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($pos['from_camp']) ?></td>
                            <td class="fw-bold text-info"><?= htmlspecialchars($pos['to_camp']) ?></td>
                            <td><?= date('F d, Y', strtotime($pos['effective_date'])) ?></td>
                            <td><?= $pos['end_date'] ? date('F d, Y', strtotime($pos['end_date'])) : '<span class="text-muted">Ongoing</span>' ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-25 border border-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> border-opacity-25 text-<?= $pos['status'] === 'Active' ? 'success' : 'secondary' ?> px-2">
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
<?php
include __DIR__ . '/../layout/footer.php';
?>
