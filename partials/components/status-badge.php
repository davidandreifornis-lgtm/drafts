<?php
/**
 * $badgeStatus: In|Low|Out|DEFECTIVE|SENT_TO_SUPPLIER|REPLACED|or custom
 * $badgeLabel optional override
 */
$badgeStatus = $badgeStatus ?? 'In';
$badgeLabel = $badgeLabel ?? $badgeStatus;
$map = [
  'In' => 'badge-in',
  'IN' => 'badge-in',
  'Low' => 'badge-low',
  'LOW' => 'badge-low',
  'Out' => 'badge-out',
  'OUT' => 'badge-out',
  'DEFECTIVE' => 'badge-defective',
  'SENT_TO_SUPPLIER' => 'badge-sent',
  'REPLACED' => 'badge-replaced',
];
$cls = $map[$badgeStatus] ?? 'badge-neutral';
?>
<span class="badge <?= $cls ?>"><?= htmlspecialchars($badgeLabel) ?></span>
