<?php
/** $tableContent (full <table>...</table>), $tableWrapId, $tableWrapClass */
$tableContent = $tableContent ?? '';
$tableWrapId = $tableWrapId ?? '';
$tableWrapClass = $tableWrapClass ?? '';
$idAttr = $tableWrapId !== '' ? ' id="' . htmlspecialchars($tableWrapId) . '"' : '';
?>
<div class="table-wrap <?= htmlspecialchars($tableWrapClass) ?>"<?= $idAttr ?>><?= $tableContent ?></div>
