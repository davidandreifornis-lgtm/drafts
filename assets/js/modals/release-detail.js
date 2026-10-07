/**
 * Modal: release-detail
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { resolveTonerDescription } from "../services/stock-utils.js";

export function openReleaseDetailModal(ref, id) {
  const list = AppState.transactions || [];
  let t = null;
  if (id) t = list.find(x => x.type === 'RELEASED' && String(x.id) === String(id));
  if (!t) t = list.find(x => x.type === 'RELEASED' && normalizeRefNumber(x.referenceNumber) === normalizeRefNumber(ref));
  if (!t) { showToast('Issuance record not found.', 'warning'); return; }
  const desc = resolveTonerDescription(t.inkCode) || t.description || '';
  const refEl = document.getElementById('rel-detail-ref');
  if (refEl) refEl.textContent = t.referenceNumber || ref || '—';
  const body = document.getElementById('rel-detail-body');
  const row = (label, val) => `<div class="flex justify-between gap-4 border-b border-slate-100 py-2.5"><dt class="text-slate-500 shrink-0">${label}</dt><dd class="text-slate-900 font-medium text-right break-words">${val}</dd></div>`;
  const yieldVal = (t.actualYield != null && t.actualYield !== '' && Number(t.actualYield) > 0)
    ? Number(t.actualYield).toLocaleString() + ' pages'
    : '—';
  const qtyNum = (t.quantity != null && t.quantity !== '') ? Number(t.quantity) : 1;
  const qtyVal = (Number.isFinite(qtyNum) && qtyNum > 0 ? qtyNum : 1).toLocaleString() + (qtyNum === 1 ? ' unit' : ' units');
  if (body) {
    body.innerHTML = `<dl>
      ${row('Date', escapeHTML(formatDate(t.date || t.createdAt) || '—'))}
      ${row('Item', escapeHTML((t.inkCode || '') + (desc ? ' — ' + desc : '')))}
      ${row('Quantity issued', escapeHTML(qtyVal))}
      ${row('Department', escapeHTML(t.department || '—'))}
      ${row('Location', escapeHTML(t.location || '—'))}
      ${row('Printer assigned', escapeHTML(t.locationPrinter || '—'))}
      ${row('Actual yield', escapeHTML(yieldVal))}
      ${row('Issued by', escapeHTML(t.issuedBy || '—'))}
      ${row('Recorded by', escapeHTML(t.recordedBy || '—'))}
      ${row('Purpose', escapeHTML(t.purpose || 'Stock issuance'))}
      ${(t.notes && String(t.notes).trim()) ? row('Notes', escapeHTML(String(t.notes).trim())) : ''}
      ${t.defective ? row('Flag', '<span class="text-rose-700 font-semibold">Later marked defective</span>') : ''}
    </dl>`;
  }
  const modal = document.getElementById('modal-release-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

export function closeReleaseDetailModal() {
  const modal = document.getElementById('modal-release-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

export function initReleaseDetailModalListeners() {
  const btnCloseRelDetail = document.getElementById('btn-close-release-detail');

  if (btnCloseRelDetail) btnCloseRelDetail.addEventListener('click', closeReleaseDetailModal);

  const btnRelDetailDone = document.getElementById('btn-release-detail-done');

  if (btnRelDetailDone) btnRelDetailDone.addEventListener('click', closeReleaseDetailModal);
}

Object.assign(window, {
  openReleaseDetailModal,
  closeReleaseDetailModal,
  initReleaseDetailModalListeners
});
