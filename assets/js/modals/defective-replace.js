/**
 * Modal: defective-replace
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { loadAdminUsersForIssuance } from "./release.js";
import { loadFromBackend } from "../services/backend.js";
import { resolveTonerDescription } from "../services/stock-utils.js";
import { renderCharts, renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";
import { renderTransactions } from "../views/transactions.js";

export async function openReceiveReplacementModal(ref, code) {
  await loadAdminUsersForIssuance().catch(() => {});
  document.getElementById('def-replace-ref').value = ref || '';
  document.getElementById('def-replace-code').value = code || '';
  document.getElementById('def-replace-ref-label').textContent = ref || '—';
  document.getElementById('def-replace-code-label').textContent = code || '—';
  document.getElementById('def-replace-desc-label').textContent = resolveTonerDescription(code) || '—';
  const me = AppState.currentUser;
  const rec = document.getElementById('def-replace-recorded-by');
  if (rec) rec.value = me ? (me.fullName ? `${me.fullName} (${me.username})` : me.username) : '';
  const sel = document.getElementById('def-replace-accepted-by');
  if (sel) {
    const users = AppState.adminUsers || [];
    const meUser = me?.username || '';
    sel.innerHTML = '<option value="">— Select admin —</option>' +
      users.map(u => {
        const label = u.fullName ? `${u.fullName} (${u.username})` : u.username;
        return `<option value="${escapeHTML(u.username)}">${escapeHTML(label)}</option>`;
      }).join('');
    if (meUser) sel.value = meUser;
  }
  const modal = document.getElementById('modal-defective-replace');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
}

export function closeReceiveReplacementModal() {
  const modal = document.getElementById('modal-defective-replace');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

export async function confirmReceiveReplacement() {
  const ref = document.getElementById('def-replace-ref')?.value || '';
  const acceptedBy = document.getElementById('def-replace-accepted-by')?.value || '';
  if (!ref) return;
  if (!acceptedBy) {
    showToast('Select the admin who accepted the replacement.', 'warning');
    return;
  }
  try {
    const btn = document.getElementById('btn-confirm-def-replace');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    const res = await apiRequest('defective.php', {
      method: 'POST',
      body: { action: 'receive_replacement', referenceNumber: ref, acceptedBy }
    });
    closeReceiveReplacementModal();
    await loadFromBackend();
    renderTransactions();
    renderInventory();
    renderDashboard();
    renderCharts();
    showToast(res.message || 'Replacement received — stock +1.', 'success');
  } catch (e) {
    showToast(e.message || 'Failed to receive replacement.', 'error');
  } finally {
    const btn = document.getElementById('btn-confirm-def-replace');
    if (btn) { btn.disabled = false; btn.textContent = 'Confirm & add to stock'; }
  }
}

export function initDefectiveReplaceModalListeners() {
  const btnCloseDefRep = document.getElementById('btn-close-def-replace');

  if (btnCloseDefRep) btnCloseDefRep.addEventListener('click', closeReceiveReplacementModal);

  const btnCancelDefRep = document.getElementById('btn-cancel-def-replace');

  if (btnCancelDefRep) btnCancelDefRep.addEventListener('click', closeReceiveReplacementModal);

  const btnConfirmDefRep = document.getElementById('btn-confirm-def-replace');

  if (btnConfirmDefRep) btnConfirmDefRep.addEventListener('click', confirmReceiveReplacement);

  const defRepBackdrop = document.getElementById('modal-defective-replace-backdrop');

  if (defRepBackdrop) defRepBackdrop.addEventListener('click', closeReceiveReplacementModal);
}

Object.assign(window, {
  openReceiveReplacementModal,
  closeReceiveReplacementModal,
  confirmReceiveReplacement,
  initDefectiveReplaceModalListeners
});
