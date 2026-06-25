<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-bell"></i> My Notifications</h2>
        <p class="text-secondary">Recent operational alert updates and roster changes.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <?php if (!empty($notifications)): ?>
            <form action="<?= BASE_URL ?>/notifications/read" method="POST" class="d-inline">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-custom btn-custom-secondary">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <?php if (empty($notifications)): ?>
                <div class="text-center text-secondary py-5">
                    <i class="fas fa-bell-slash fs-2 mb-3 text-muted"></i>
                    <div>Your inbox is completely clear!</div>
                </div>
            <?php else: ?>
                <div class="notification-list d-flex flex-column gap-3">
                    <?php foreach ($notifications as $note): ?>
                        <div class="p-3 rounded glass-card bg-opacity-25 d-flex justify-content-between align-items-start position-relative notification-card <?= $note['is_read'] ? 'text-secondary' : 'border-info border-opacity-35 text-light' ?>" style="<?= $note['is_read'] ? '' : 'background: rgba(13, 148, 136, 0.05);' ?>">
                            
                            <div class="pe-4">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <?php if (!$note['is_read']): ?>
                                        <span class="d-inline-block rounded-circle bg-info" style="width: 8px; height: 8px; box-shadow: 0 0 8px #0d9488;"></span>
                                    <?php endif; ?>
                                    <h6 class="fw-bold mb-0 text-light"><?= htmlspecialchars($note['title']) ?></h6>
                                </div>
                                <p class="small mb-1 text-secondary"><?= htmlspecialchars($note['message']) ?></p>
                                <span class="x-small text-muted" style="font-size:0.75rem;"><i class="fas fa-clock me-1"></i> <?= date('M d, Y H:i', strtotime($note['created_at'])) ?></span>
                            </div>
                            
                            <?php if (!$note['is_read']): ?>
                                <button type="button" class="btn btn-xs btn-custom btn-custom-secondary py-1 px-2.5 font-monospace text-info border-info border-opacity-20 mark-read-btn" data-id="<?= $note['notification_id'] ?>">
                                    <i class="fas fa-check"></i> Mark Read
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const markReadBtns = document.querySelectorAll('.mark-read-btn');

        markReadBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const button = e.currentTarget;
                const noteId = button.dataset.id;
                const card = button.closest('.notification-card');

                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(`${BASE_URL}/notifications/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ notification_id: noteId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Success micro-interaction
                        button.remove();
                        card.style.background = '';
                        card.classList.add('text-secondary');
                        const dot = card.querySelector('.bg-info');
                        if (dot) dot.remove();
                    } else {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-check"></i> Mark Read';
                    }
                })
                .catch(err => {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i> Mark Read';
                    console.error('Error marking notification read:', err);
                });
            });
        });
    });
</script>
<?php
include __DIR__ . '/../layout/footer.php';
?>
