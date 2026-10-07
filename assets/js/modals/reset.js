/**
 * Modal: reset
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { navigateTo } from "../core/router.js";
import { showToast } from "../core/toast.js";
import { renderStockCardMovements, syncStockCardCustomRangeUI } from "./stock-card.js";
import { StorageService, initializeDataSafely } from "../services/storage-service.js";

export function openResetModal() {
  const resetModal = document.getElementById('modal-reset-confirm') || document.getElementById('modal-reset');
  if (!resetModal) return;
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  resetModal.classList.remove('hidden');
}

export function closeResetModal() {
  const resetModal = document.getElementById('modal-reset-confirm') || document.getElementById('modal-reset');
  if (resetModal) resetModal.classList.add('hidden');
  const backdrop = document.getElementById('modal-backdrop');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

export function initResetModalListeners() {
  const btnStockCardResetDates = document.getElementById('btn-stock-card-reset-dates');

  if (btnStockCardResetDates) btnStockCardResetDates.addEventListener('click', () => {
    const p = document.getElementById('stock-card-period');
    const f = document.getElementById('stock-card-from');
    const toEl = document.getElementById('stock-card-to');
    if (p) p.value = 'ALL';
    if (f) f.value = '';
    if (toEl) toEl.value = '';
    syncStockCardCustomRangeUI();
    renderStockCardMovements();
  });

  // Reset Demo Modal
  const btnResetDemo = document.getElementById('btn-reset-demo');

  if (btnResetDemo) btnResetDemo.addEventListener('click', openResetModal);

  const btnCancelReset = document.getElementById('btn-cancel-reset');

  if (btnCancelReset) btnCancelReset.addEventListener('click', closeResetModal);

  const btnConfirmReset = document.getElementById('btn-confirm-reset');

  if (btnConfirmReset) {
    btnConfirmReset.addEventListener('click', () => {
      StorageService.resetAllDemoData();
      initializeDataSafely();
      closeResetModal();
      navigateTo('dashboard');
      showToast('System inventory, tickets, and transactions reset to original demo state!', 'success');
    });
  }
}

Object.assign(window, {
  openResetModal,
  closeResetModal,
  initResetModalListeners
});
