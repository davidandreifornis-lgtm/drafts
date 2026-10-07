<div id="modal-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
<?php
  // Shared-backdrop modals (one visible at a time; backdrop managed by core/modal.js)
  require __DIR__ . '/not-found.php';
  require __DIR__ . '/reset.php';
  require __DIR__ . '/receive.php';
  require __DIR__ . '/release.php';
  require __DIR__ . '/defective.php';
  require __DIR__ . '/stock-card.php';
  require __DIR__ . '/locations.php';
  require __DIR__ . '/add-toner.php';
  require __DIR__ . '/remove-toner.php';
  require __DIR__ . '/user.php';
  require __DIR__ . '/logout.php';
?>
</div>
