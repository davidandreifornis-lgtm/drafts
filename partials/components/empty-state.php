<?php
/** $emptyMessage, $emptyId */
$emptyMessage = $emptyMessage ?? 'No items found';
$emptyId = $emptyId ?? '';
$idAttr = $emptyId !== '' ? ' id="' . htmlspecialchars($emptyId) . '"' : '';
?>
<div class="empty-state"<?= $idAttr ?>><?= htmlspecialchars($emptyMessage) ?></div>
