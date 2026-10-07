/**
 * Modal: defective-detail
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML, formatDateTime, normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { appConfirm } from "./app-confirm.js";
import { openReceiveReplacementModal } from "./defective-replace.js";
import { loadFromBackend } from "../services/backend.js";
import { parseDefectiveMeta, resolveTonerDescription } from "../services/stock-utils.js";
import { renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";
import { renderTransactions } from "../views/transactions.js";

export async function sendDefectiveToSupplier(ref) {
  if (!ref) return;
  const ok = await appConfirm({
    title: 'Send to supplier',
    message: `Mark ${ref} as sent to the supplier for replacement?`,
    confirmText: 'Send to supplier',
    cancelText: 'Cancel'
  });
  if (!ok) return;
  try {
    await apiRequest('defective.php', {
      method: 'POST',
      body: { action: 'send_to_supplier', referenceNumber: ref }
    });
    await loadFromBackend();
    renderTransactions();
    renderInventory();
    renderDashboard();
    showToast(`${ref} marked as sent to supplier.`, 'success');
  } catch (e) {
    showToast(e.message || 'Failed to update.', 'error');
  }
}

export function openDefectiveDetailModal(ref) {
  const t = (AppState.transactions || []).find(x => x.type === 'DEFECTIVE' && normalizeRefNumber(x.referenceNumber) === normalizeRefNumber(ref));
  if (!t) { showToast('Defective record not found.', 'warning'); return; }
  const st = String(t.status || 'DEFECTIVE').toUpperCase();
  const meta = parseDefectiveMeta(t.defectiveNotes || '');
  const desc = resolveTonerDescription(t.inkCode) || t.description || '';
  const statusLabel = st === 'SENT_TO_SUPPLIER' ? 'Sent to supplier' : st === 'REPLACED' ? 'Replaced (back in inventory)' : 'Defective';
  const refEl = document.getElementById('def-detail-ref');
  if (refEl) refEl.textContent = t.referenceNumber || ref;
  const body = document.getElementById('def-detail-body');
  const row = (label, val) => `<div class="flex justify-between gap-4 border-b border-slate-100 py-2.5"><dt class="text-slate-500 shrink-0">${label}</dt><dd class="text-slate-900 font-medium text-right break-words">${val}</dd></div>`;
  if (body) {
    body.innerHTML = `<dl>
      ${row('Status', escapeHTML(statusLabel))}
      ${row('Item', escapeHTML((t.inkCode || '') + (desc ? ' — ' + desc : '')))}
      ${row('Department', escapeHTML(t.department || '—'))}
      ${row('Location', escapeHTML(t.location || '—'))}
      ${row('Flagged on', escapeHTML(formatDateTime(t.defectiveAt || t.createdAt || t.date) || '—'))}
      ${row('Sent to supplier', escapeHTML(meta.sentAt ? formatDateTime(meta.sentAt) : (st === 'SENT_TO_SUPPLIER' || st === 'REPLACED' ? 'Recorded' : 'Not sent yet')))}
      ${row('Received back to inventory', escapeHTML(meta.receivedAt ? formatDateTime(meta.receivedAt) : (st === 'REPLACED' ? 'Recorded' : 'Not received yet')))}
      ${row('Accepted by', escapeHTML(t.issuedBy || meta.acceptedByMeta || '—'))}
      ${row('Transacted / recorded by', escapeHTML(t.recordedBy || meta.recordedByMeta || '—'))}
      ${row('Notes', escapeHTML(meta.humanNotes || t.purpose || '—'))}
    </dl>`;
  }
  const actions = document.getElementById('def-detail-actions');
  if (actions) {
    let html = '';
    if (st === 'DEFECTIVE') html = `<button type="button" id="def-detail-send" class="px-3 py-2 text-xs font-semibold rounded-xl border border-amber-200 text-amber-800 bg-amber-50" data-ref="${escapeHTML(t.referenceNumber)}">Send to supplier</button>`;
    else if (st === 'SENT_TO_SUPPLIER') html = `<button type="button" id="def-detail-recv" class="px-3 py-2 text-xs font-semibold rounded-xl border border-emerald-200 text-emerald-800 bg-emerald-50" data-ref="${escapeHTML(t.referenceNumber)}" data-code="${escapeHTML(t.inkCode)}">Receive replacement</button>`;
    actions.innerHTML = html;
    document.getElementById('def-detail-send')?.addEventListener('click', () => {
      closeDefectiveDetailModal();
      sendDefectiveToSupplier(t.referenceNumber);
    });
    document.getElementById('def-detail-recv')?.addEventListener('click', () => {
      closeDefectiveDetailModal();
      openReceiveReplacementModal(t.referenceNumber, t.inkCode || '');
    });
  }
  const modal = document.getElementById('modal-defective-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

export function closeDefectiveDetailModal() {
  const modal = document.getElementById('modal-defective-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

export function initDefectiveDetailModalListeners() {
  const btnCloseDefDetail = document.getElementById('btn-close-defective-detail');

  if (btnCloseDefDetail) btnCloseDefDetail.addEventListener('click', closeDefectiveDetailModal);

  const btnDefDetailDone = document.getElementById('btn-defective-detail-done');

  if (btnDefDetailDone) btnDefDetailDone.addEventListener('click', closeDefectiveDetailModal);

  const defDetailBackdrop = document.getElementById('modal-defective-detail-backdrop');

  if (defDetailBackdrop) defDetailBackdrop.addEventListener('click', closeDefectiveDetailModal);
}

Object.assign(window, {
  sendDefectiveToSupplier,
  openDefectiveDetailModal,
  closeDefectiveDetailModal,
  initDefectiveDetailModalListeners
});
