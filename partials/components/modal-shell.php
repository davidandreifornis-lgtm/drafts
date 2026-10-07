<?php
/**
 * Shared modal shell.
 * Params (set before include):
 *   $modalId (string, required) — element id e.g. modal-receive
 *   $modalTitle (string)
 *   $modalSize (string) — max-w-sm|max-w-md|max-w-lg|max-w-2xl|max-w-4xl (default max-w-lg)
 *   $modalBody (string) — HTML body
 *   $modalFooter (string) — HTML footer (Cancel left, primary right)
 *   $modalCloseBtnId (string|null) — id for X button
 */
$modalId = $modalId ?? 'modal';
$modalTitle = $modalTitle ?? '';
$modalSize = $modalSize ?? 'max-w-lg';
$modalBody = $modalBody ?? '';
$modalFooter = $modalFooter ?? '';
$modalCloseBtnId = $modalCloseBtnId ?? ('btn-close-' . preg_replace('/^modal-/', '', $modalId));
?>
<div id="<?= htmlspecialchars($modalId) ?>" class="modal-card hidden bg-white rounded-2xl <?= htmlspecialchars($modalSize) ?> w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($modalId) ?>-title">
  <div class="modal-shell-header px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
    <h3 id="<?= htmlspecialchars($modalId) ?>-title" class="text-lg font-bold text-slate-900"><?= htmlspecialchars($modalTitle) ?></h3>
    <button type="button" id="<?= htmlspecialchars($modalCloseBtnId) ?>" class="btn-icon p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-modal-close="<?= htmlspecialchars($modalId) ?>" aria-label="Close">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <div class="modal-shell-body px-5 py-4 overflow-y-auto flex-1">
    <?= $modalBody ?>
  </div>
  <?php if ($modalFooter !== ''): ?>
  <div class="modal-shell-footer px-5 py-4 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
    <?= $modalFooter ?>
  </div>
  <?php endif; ?>
</div>
