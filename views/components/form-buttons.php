<?php
/**
 * Reusable Form Footer Buttons Component
 * 
 * Required Variables:
 * - $submitLabel (string): The label for the primary action button (e.g., "Save Camp")
 * 
 * Optional Variables:
 * - $submitClass (string): Bootstrap / Custom color class for primary action (default: 'btn-custom-primary')
 * - $submitId (string): Optional ID for the submit button (default: null)
 * - $submitIcon (string): FontAwesome icon class for primary action (e.g., 'fas fa-save')
 * - $cancelLabel (string): Label for cancel button (default: 'Cancel')
 * - $cancelClass (string): Class for cancel button (default: 'btn-custom-secondary')
 * - $cancelUrl (string): If provided, cancel will be an <a> tag linking to this URL. Otherwise, it will be a button dismissing a modal.
 * - $cancelIcon (string): FontAwesome icon class for cancel action (default: null)
 * - $resetLabel (string): If provided, displays a reset button with this label.
 * - $resetClass (string): Class for reset button (default: 'btn-custom-orange')
 * - $resetId (string): Optional ID for reset button (default: null)
 * - $resetIcon (string): FontAwesome icon class for reset action (default: null)
 * - $extraButtons (array): Array of additional button HTML strings to inject.
 */
$submitClass = $submitClass ?? 'btn-custom-primary';
$submitId = $submitId ?? null;
$submitIcon = $submitIcon ?? null;
$cancelLabel = $cancelLabel ?? 'Cancel';
$cancelClass = $cancelClass ?? 'btn-custom-secondary';
$cancelUrl = $cancelUrl ?? null;
$cancelIcon = $cancelIcon ?? null;
$resetLabel = $resetLabel ?? null;
$resetClass = $resetClass ?? 'btn-custom-orange';
$resetId = $resetId ?? null;
$resetIcon = $resetIcon ?? null;
$extraButtons = $extraButtons ?? [];
?>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2 w-100">
    <?php if ($cancelUrl): ?>
        <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-custom <?= htmlspecialchars($cancelClass) ?>">
            <?php if ($cancelIcon): ?><i class="<?= htmlspecialchars($cancelIcon) ?> me-1"></i><?php endif; ?>
            <?= htmlspecialchars($cancelLabel) ?>
        </a>
    <?php else: ?>
        <button type="button" class="btn btn-custom <?= htmlspecialchars($cancelClass) ?>" data-bs-dismiss="modal">
            <?php if ($cancelIcon): ?><i class="<?= htmlspecialchars($cancelIcon) ?> me-1"></i><?php endif; ?>
            <?= htmlspecialchars($cancelLabel) ?>
        </button>
    <?php endif; ?>

    <?php if ($resetLabel): ?>
        <button type="reset" <?= $resetId ? 'id="' . htmlspecialchars($resetId) . '"' : '' ?> class="btn btn-custom <?= htmlspecialchars($resetClass) ?>">
            <?php if ($resetIcon): ?><i class="<?= htmlspecialchars($resetIcon) ?> me-1"></i><?php endif; ?>
            <?= htmlspecialchars($resetLabel) ?>
        </button>
    <?php endif; ?>

    <?php foreach ($extraButtons as $btn): ?>
        <?= $btn ?>
    <?php endforeach; ?>

    <button type="submit" <?= $submitId ? 'id="' . htmlspecialchars($submitId) . '"' : '' ?> class="btn btn-custom <?= htmlspecialchars($submitClass) ?>">
        <?php if ($submitIcon): ?><i class="<?= htmlspecialchars($submitIcon) ?> me-1"></i><?php endif; ?>
        <?= htmlspecialchars($submitLabel) ?>
    </button>
</div>
