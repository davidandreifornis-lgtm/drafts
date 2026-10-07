/**
 * Modal: remove-toner
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { appConfirm } from "./app-confirm.js";
import { apiRemoveToner, loadFromBackend } from "../services/backend.js";
import { StorageService } from "../services/storage-service.js";
import { renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";

export function openRemoveTonerModal(inkCode) {
  AppState.pendingRemoveTonerCode = inkCode;
  const label = document.getElementById('remove-toner-code-label');
  if (label) label.textContent = inkCode;
  const modal = document.getElementById('modal-remove-toner');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

export function closeRemoveTonerModal() {
  const modal = document.getElementById('modal-remove-toner');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.pendingRemoveTonerCode = null;
}

export async function confirmRemoveToner() {
  const ok = await appConfirm({ title: 'Remove toner', message: 'Remove this toner from inventory? This cannot be undone easily.', confirmText: 'Remove', danger: true });
  if (!ok) return;
  const code = AppState.pendingRemoveTonerCode;
  if (!code) return;
  const key = code.toUpperCase();
  const inks = AppState.inks.filter(i => (i.inkCode || '').toUpperCase() !== key);
  if (inks.length === AppState.inks.length) {
    showToast('Toner not found.', 'warning');
    closeRemoveTonerModal();
    return;
  }
  (async () => {
    try {
      if (AppState.useBackend) {
        await apiRemoveToner(code);
        await loadFromBackend();
      } else {
        StorageService.saveInks(inks);
        AppState.inks = inks;
      }
      renderInventory();
      renderDashboard();
      renderAlerts();
      closeRemoveTonerModal();
      showToast(`Toner ${code} removed from inventory.`, 'success');
      pushNotification('warning', 'Toner Removed', `${code} was removed from the master inventory.`, { source: 'action', inkCode: code, action: { type: 'inventory' } });
    } catch (e) {
      showToast(e.message || 'Failed to remove toner.', 'error');
    }
  })();
}

export function initRemoveTonerModalListeners() {
  const btnCancelRemoveToner = document.getElementById('btn-cancel-remove-toner');

  if (btnCancelRemoveToner) btnCancelRemoveToner.addEventListener('click', closeRemoveTonerModal);

  const btnConfirmRemoveToner = document.getElementById('btn-confirm-remove-toner');

  if (btnConfirmRemoveToner) btnConfirmRemoveToner.addEventListener('click', confirmRemoveToner);
}

Object.assign(window, {
  openRemoveTonerModal,
  closeRemoveTonerModal,
  confirmRemoveToner,
  initRemoveTonerModalListeners
});
