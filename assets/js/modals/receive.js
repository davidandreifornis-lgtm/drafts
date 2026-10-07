/**
 * Modal: receive
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { navigateTo } from "../core/router.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { TicketService } from "../services/ticket-service.js";
import { recordManualDelivery } from "../views/receive.js";

export async function searchMrrLookup() {
  const ref = normalizeRefNumber(document.getElementById('modal-del-ref')?.value);
  const status = document.getElementById('mrr-lookup-status');
  const preview = document.getElementById('mrr-preview');
  const tbody = document.getElementById('mrr-preview-tbody');
  const postBtn = document.getElementById('btn-modal-process-del');
  const already = document.getElementById('mrr-already-warn');

  if (!ref) {
    showToast('Enter an MRR number (e.g. MG009105).', 'warning');
    return;
  }
  AppState.activeMrrLookup = null;
  if (postBtn) postBtn.disabled = true;
  if (preview) preview.classList.add('hidden');
  if (already) already.classList.add('hidden');
  if (status) {
    status.classList.remove('hidden');
    status.className = 'text-sm px-3 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-800';
    status.textContent = 'Looking up MRR in ERP…';
  }

  try {
    const data = await apiRequest('mrr_lookup.php?mrr=' + encodeURIComponent(ref));
    AppState.activeMrrLookup = data;

    if (status) {
      status.className = 'text-sm px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800';
      status.textContent = `Found ${data.lineCount || (data.lines || []).length} line(s) for MRR ${data.mrr}.`;
    }

    document.getElementById('mrr-preview-no').textContent = data.mrr || ref;
    document.getElementById('mrr-preview-date').textContent = data.mrrDate ? formatDate(data.mrrDate) : '—';
    document.getElementById('mrr-preview-lines').textContent = String(data.lineCount || (data.lines || []).length);
    document.getElementById('mrr-preview-qty').textContent = String(data.totalQty || 0);

    if (tbody) {
      tbody.innerHTML = (data.lines || []).map(L => `
        <tr class="hover:bg-slate-50">
          <td class="px-3 py-2 font-mono text-xs font-semibold text-slate-900">${escapeHTML(L.itemCode)}</td>
          <td class="px-3 py-2 text-slate-700 text-xs">${escapeHTML(L.description || '—')}</td>
          <td class="px-3 py-2 text-right font-mono font-bold">${L.quantity}</td>
          <td class="px-3 py-2 text-right font-mono text-slate-600">${L.currentStock}</td>
          <td class="px-3 py-2 text-right font-mono text-emerald-700 font-semibold">${L.projectedStock}</td>
        </tr>
      `).join('');
    }

    if (preview) preview.classList.remove('hidden');
    if (data.alreadyRecorded) {
      if (already) already.classList.remove('hidden');
      if (postBtn) postBtn.disabled = true;
    } else if (postBtn) {
      postBtn.disabled = false;
    }
  } catch (e) {
    if (status) {
      status.className = 'text-sm px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-800';
      status.textContent = (e.message || 'MRR lookup failed.') + (e.status ? ' (HTTP ' + e.status + ')' : '');
    }
    if (preview) preview.classList.add('hidden');
    if (postBtn) postBtn.disabled = true;
  }
}

export function openReceiveModal() {
  document.getElementById('receive-step-form')?.classList.remove('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');
  AppState.activeMrrLookup = null;
  const ref = document.getElementById('modal-del-ref');
  const sup = document.getElementById('modal-del-supplier');
  const status = document.getElementById('mrr-lookup-status');
  const preview = document.getElementById('mrr-preview');
  const already = document.getElementById('mrr-already-warn');
  const postBtn = document.getElementById('btn-modal-process-del');
  if (ref) ref.value = '';
  if (sup) sup.value = '';
  if (status) { status.classList.add('hidden'); status.textContent = ''; }
  if (preview) preview.classList.add('hidden');
  if (already) already.classList.add('hidden');
  if (postBtn) { postBtn.disabled = true; postBtn.textContent = 'Confirm & Post Stock'; }
  const modal = document.getElementById('modal-receive');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => ref?.focus(), 80);
}

export function closeReceiveModal() {
  const modal = document.getElementById('modal-receive');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeDeliveryTicket = null;
}

export function resetReceiveModal() {
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.add('hidden');
  document.getElementById('receive-step-preview')?.classList.add('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');
  AppState.activeDeliveryTicket = null;
}

export function showReceiveError(ref, title, desc) {
  const errRef = document.getElementById('receive-error-ref');
  const errTitle = document.getElementById('receive-error-title');
  const errDesc = document.getElementById('receive-error-desc');
  if (errRef) errRef.textContent = ref || '—';
  if (errTitle) errTitle.textContent = title || 'Ticket Not Found';
  if (errDesc) errDesc.textContent = desc || 'No approved ticket matches this reference in the registry.';

  // Keep search visible; show error in front below it
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.remove('hidden');
  document.getElementById('receive-step-preview')?.classList.add('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');

  // Scroll error into view inside modal
  document.getElementById('receive-step-error')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

export function searchAndDisplayDeliveryModal(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a delivery reference number.', 'warning');
    return;
  }

  // Keep search visible area but switch steps based on result
  if (TicketService.isAlreadyProcessed(normalized)) {
    showReceiveError(
      normalized,
      'Ticket Already Processed',
      'This delivery ticket was already executed. Duplicate stock movements are blocked.'
    );
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'DELIVERY') {
    showReceiveError(
      normalized,
      'Ticket Not Found',
      'No approved delivery ticket matches this reference in the registry.'
    );
    return;
  }

  AppState.activeDeliveryTicket = ticket;

  // Fill validated preview
  document.getElementById('m-del-ref').textContent = ticket.referenceNumber;
  document.getElementById('m-del-date').textContent = formatDate(ticket.date);
  document.getElementById('m-del-supplier').textContent = ticket.supplier || '—';

  let totalQty = 0;
  const tbody = document.getElementById('m-del-tbody');
  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const currentQty = matched ? (Number(matched.quantity) || 0) : 0;
    const incomingQty = Number(item.quantity) || 0;
    const newQty = currentQty + incomingQty;
    totalQty += incomingQty;
    return `<tr>
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(item.inkCode)}</td>
      <td class="px-3 py-2">${escapeHTML(item.brand || matched?.brand || '—')}</td>
      <td class="px-3 py-2">${escapeHTML(item.color || matched?.color || '—')}</td>
      <td class="px-3 py-2 text-right font-mono text-slate-500">${currentQty}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-blue-600">+${incomingQty}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-emerald-700">${newQty}</td>
    </tr>`;
  }).join('');

  document.getElementById('m-del-total-items').textContent = ticket.items.length;
  document.getElementById('m-del-total-qty').textContent = totalQty;

  // Keep search visible; show validated preview in front below it
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.add('hidden');
  document.getElementById('receive-step-preview')?.classList.remove('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');

  document.getElementById('receive-step-preview')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

export function initReceiveModalListeners() {
  // ---------- Dashboard Quick Action Buttons → open modals ----------
  const quickReceive = document.getElementById('quick-btn-receive');

  if (quickReceive) quickReceive.addEventListener('click', openReceiveModal);

  // ---------- Receive Modal ----------
  const btnCloseReceive = document.getElementById('btn-close-receive-modal');

  if (btnCloseReceive) btnCloseReceive.addEventListener('click', closeReceiveModal);

  const btnModalSearchDel = document.getElementById('btn-modal-search-del');

  if (btnModalSearchDel) {
    btnModalSearchDel.addEventListener('click', () => searchMrrLookup());
  }

  const modalDelRef = document.getElementById('modal-del-ref');

  if (modalDelRef) {
    modalDelRef.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchMrrLookup();
      }
    });
  }

  const btnModalProcessDel = document.getElementById('btn-modal-process-del');

  if (btnModalProcessDel) {
    btnModalProcessDel.addEventListener('click', recordManualDelivery);
  }

  const btnModalCancelDel = document.getElementById('btn-modal-cancel-del');

  if (btnModalCancelDel) btnModalCancelDel.addEventListener('click', closeReceiveModal);

  const btnReceiveDone = document.getElementById('btn-receive-done');

  if (btnReceiveDone) btnReceiveDone.addEventListener('click', () => {
    closeReceiveModal();
    navigateTo('dashboard');
  });

  const btnReceiveErrorBack = document.getElementById('btn-receive-error-back');

  if (btnReceiveErrorBack) {
    btnReceiveErrorBack.addEventListener('click', () => {
      resetReceiveModal();
      const input = document.getElementById('modal-del-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }

  // MRR search (Receive Delivery)
  const btnSearchMrr = document.getElementById('btn-modal-search-mrr');

  if (btnSearchMrr) {
    btnSearchMrr.addEventListener('click', (e) => {
      e.preventDefault();
      searchMrrLookup();
    });
  }
}

Object.assign(window, {
  searchMrrLookup,
  openReceiveModal,
  closeReceiveModal,
  resetReceiveModal,
  showReceiveError,
  searchAndDisplayDeliveryModal,
  initReceiveModalListeners
});
