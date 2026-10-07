<?php
/**
 * $fieldId, $fieldLabel, $fieldType (text|number|date|email|password|select|textarea),
 * $fieldRequired, $fieldPlaceholder, $fieldHelper, $fieldValue, $fieldOptions (for select),
 * $fieldClass, $fieldAttrs, $fieldRows
 */
$fieldId = $fieldId ?? '';
$fieldLabel = $fieldLabel ?? '';
$fieldType = $fieldType ?? 'text';
$fieldRequired = !empty($fieldRequired);
$fieldPlaceholder = $fieldPlaceholder ?? '';
$fieldHelper = $fieldHelper ?? '';
$fieldValue = $fieldValue ?? '';
$fieldOptions = $fieldOptions ?? [];
$fieldClass = $fieldClass ?? '';
$fieldAttrs = $fieldAttrs ?? '';
$fieldRows = $fieldRows ?? 3;
$req = $fieldRequired ? ' required' : '';
$reqMark = $fieldRequired ? ' <span class="required">*</span>' : '';
?>
<div class="form-field">
  <?php if ($fieldLabel !== ''): ?>
  <label for="<?= htmlspecialchars($fieldId) ?>" class="form-label"><?= htmlspecialchars($fieldLabel) ?><?= $reqMark ?></label>
  <?php endif; ?>
  <?php if ($fieldType === 'textarea'): ?>
  <textarea id="<?= htmlspecialchars($fieldId) ?>" name="<?= htmlspecialchars($fieldId) ?>" rows="<?= (int)$fieldRows ?>" class="form-textarea <?= htmlspecialchars($fieldClass) ?>" placeholder="<?= htmlspecialchars($fieldPlaceholder) ?>"<?= $req ?> <?= $fieldAttrs ?>><?= htmlspecialchars($fieldValue) ?></textarea>
  <?php elseif ($fieldType === 'select'): ?>
  <select id="<?= htmlspecialchars($fieldId) ?>" name="<?= htmlspecialchars($fieldId) ?>" class="form-select <?= htmlspecialchars($fieldClass) ?>"<?= $req ?> <?= $fieldAttrs ?>>
    <?php foreach ($fieldOptions as $optVal => $optLabel): ?>
    <option value="<?= htmlspecialchars((string)$optVal) ?>"<?= ((string)$optVal === (string)$fieldValue) ? ' selected' : '' ?>><?= htmlspecialchars($optLabel) ?></option>
    <?php endforeach; ?>
  </select>
  <?php else: ?>
  <input type="<?= htmlspecialchars($fieldType) ?>" id="<?= htmlspecialchars($fieldId) ?>" name="<?= htmlspecialchars($fieldId) ?>" value="<?= htmlspecialchars($fieldValue) ?>" class="form-input <?= htmlspecialchars($fieldClass) ?>" placeholder="<?= htmlspecialchars($fieldPlaceholder) ?>"<?= $req ?> <?= $fieldAttrs ?>>
  <?php endif; ?>
  <?php if ($fieldHelper !== ''): ?><p class="form-helper"><?= htmlspecialchars($fieldHelper) ?></p><?php endif; ?>
  <p class="form-error hidden" data-error-for="<?= htmlspecialchars($fieldId) ?>"></p>
</div>
