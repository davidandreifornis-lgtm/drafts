<?php
/**
 * $btnLabel, $btnVariant (primary|secondary|danger|ghost|icon), $btnId, $btnType, $btnClass, $btnAttrs
 */
$btnLabel = $btnLabel ?? 'Button';
$btnVariant = $btnVariant ?? 'primary';
$btnId = $btnId ?? '';
$btnType = $btnType ?? 'button';
$btnClass = $btnClass ?? '';
$btnAttrs = $btnAttrs ?? '';
$map = [
  'primary' => 'btn btn-primary',
  'secondary' => 'btn btn-secondary',
  'danger' => 'btn btn-danger',
  'ghost' => 'btn btn-ghost',
  'icon' => 'btn btn-icon',
];
$classes = trim(($map[$btnVariant] ?? $map['primary']) . ' ' . $btnClass);
$idAttr = $btnId !== '' ? ' id="' . htmlspecialchars($btnId) . '"' : '';
?>
<button type="<?= htmlspecialchars($btnType) ?>"<?= $idAttr ?> class="<?= htmlspecialchars($classes) ?>" <?= $btnAttrs ?>><?= $btnLabel ?></button>
