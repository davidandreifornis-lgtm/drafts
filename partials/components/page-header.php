<?php
/** $pageTitle, $pageSubtitle, $pageActions (HTML) */
$pageTitle = $pageTitle ?? '';
$pageSubtitle = $pageSubtitle ?? '';
$pageActions = $pageActions ?? '';
?>
<div class="page-header">
  <div>
    <h2 class="page-header-title"><?= htmlspecialchars($pageTitle) ?></h2>
    <?php if ($pageSubtitle !== ''): ?>
    <p class="page-header-subtitle"><?= htmlspecialchars($pageSubtitle) ?></p>
    <?php endif; ?>
  </div>
  <?php if ($pageActions !== ''): ?>
  <div class="flex items-center gap-2 flex-wrap"><?= $pageActions ?></div>
  <?php endif; ?>
</div>
