/**
 * View: receive
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { appConfirm } from "../modals/app-confirm.js";
import { openDuplicateModal } from "../modals/duplicate.js";
import { openNotFoundModal } from "../modals/not-found.js";
import { apiRecordDelivery, loadFromBackend } from "../services/backend.js";
import { getUniqueTonerCodes } from "../services/stock-utils.js";
import { TicketService } from "../services/ticket-service.js";
import { renderCharts, renderDashboard } from "./dashboard.js";
import { renderInventory } from "./inventory.js";
import { renderTransactions } from "./transactions.js";

// ==========================================
// 12. RELEASE INK UI SEARCH & DISPLAY
// ==========================================
export function searchAndDisplayDelivery(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a delivery reference number.', 'warning');
    return;
  }

  // Check if already processed
  if (TicketService.isAlreadyProcessed(normalized)) {
    openDuplicateModal(normalized, 'DELIVERY');
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'DELIVERY') {
    openNotFoundModal(normalized);
    return;
  }

  AppState.activeDeliveryTicket = ticket;

  // Render preview
  const previewBox = document.getElementById('del-ticket-preview');
  document.getElementById('del-preview-ref').textContent = ticket.referenceNumber;
  document.getElementById('del-preview-date').textContent = formatDate(ticket.date);
  document.getElementById('del-preview-supplier').textContent = ticket.supplier || 'N/A';
  document.getElementById('del-preview-status').textContent = ticket.status || 'APPROVED';

  const tbody = document.getElementById('del-preview-tbody');
  let totalQty = 0;

  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const currentQty = matched ? (Number(matched.quantity) || 0) : 0;
    const incomingQty = Number(item.quantity) || 0;
    const newQty = currentQty + incomingQty;
    totalQty += incomingQty;

    return `
      <tr class="hover:bg-slate-50">
        <td class="px-4 py-3 font-bold font-mono text-slate-900">${escapeHTML(item.inkCode)}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.brand || (matched ? matched.brand : 'HP'))}</td>
        <td class="px-4 py-3 text-slate-500 text-xs">${escapeHTML(item.printerModel || (matched ? matched.printerModel : 'Standard'))}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.color || (matched ? matched.color : 'Black'))}</td>
        <td class="px-4 py-3 font-mono text-xs text-slate-600">${escapeHTML(item.serialNumber || 'SN-AUTO')}</td>
        <td class="px-4 py-3 text-right font-mono text-slate-500">${currentQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-blue-600 text-base">+${incomingQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-700">${newQty}</td>
      </tr>
    `;
  }).join('');

  document.getElementById('del-preview-total-items').textContent = ticket.items.length;
  document.getElementById('del-preview-total-qty').textContent = totalQty;

  previewBox.classList.remove('hidden');
  previewBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

export function populateTonerSelect(selectId, includeStock) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  const current = sel.value;
  const list = getUniqueTonerCodes();
  // Label = description (easier to distinguish); value stays item_code for posting
  sel.innerHTML = '<option value="">— Select item —</option>' +
    list.map(t => {
      const label = t.description
        ? `${t.description}`
        : t.code;
      const stock = includeStock ? ` (${t.qty} on hand)` : '';
      const title = t.description ? `${t.description} · ${t.code}` : t.code;
      return `<option value="${escapeHTML(t.code)}" data-qty="${t.qty}" title="${escapeHTML(title)}">${escapeHTML(label)}${escapeHTML(stock)}</option>`;
    }).join('');
  if (current) sel.value = current;
}

export function recordManualDelivery() {
  const inputRef = normalizeRefNumber(document.getElementById('modal-del-ref')?.value);
  const supplier = (document.getElementById('modal-del-supplier')?.value || '').trim();
  const mrrData = AppState.activeMrrLookup || null;

  // Prefer MRR from successful lookup, then input field
  const ref = normalizeRefNumber(
    (mrrData && (mrrData.mrr || mrrData.referenceNumber)) || inputRef || ''
  );

  if (!ref) {
    showToast('Enter an MRR number and click Search MRR first.', 'warning');
    document.getElementById('modal-del-ref')?.focus();
    return;
  }
  if (!mrrData || !Array.isArray(mrrData.lines) || !mrrData.lines.length) {
    showToast('Search the MRR first so ERP lines can load, then confirm.', 'warning');
    return;
  }
  if (mrrData.alreadyRecorded) {
    showToast('This MRR was already recorded.', 'error');
    return;
  }

  const lines = mrrData.lines.map(L => ({
    itemCode: L.itemCode || L.inkCode,
    inkCode: L.itemCode || L.inkCode,
    quantity: L.quantity,
    date: L.date || mrrData.mrrDate || new Date().toISOString().slice(0, 10),
    description: L.description || ''
  }));

  (async () => {
    try {
      const btn = document.getElementById('btn-modal-process-del');
      if (btn) { btn.disabled = true; btn.textContent = 'Posting…'; }

      // Send mrr + referenceNumber + lines so API never says "reference required"
      const okDel = await appConfirm({ title: 'Confirm delivery', message: 'Post this delivery and add stock to inventory?', confirmText: 'Post to stock' });
        if (!okDel) return;
        await apiRecordDelivery({
        mrr: ref,
        mrrNo: ref,
        referenceNumber: ref,
        supplier: supplier,
        lines: lines
      });

      await loadFromBackend();
      // Show in Transaction History (Incoming) without date filter hiding MRR date
      AppState.filters.transactionType = 'RECEIVED';
      AppState.filters.transactionDate = 'ALL';
      AppState.filters.transactionDateFrom = '';
      AppState.filters.transactionDateTo = '';
      const dateSel = document.getElementById('filter-txn-date');
      if (dateSel) dateSel.value = 'ALL';
      renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
      const successRef = document.getElementById('m-del-success-ref');
      if (successRef) successRef.textContent = ref;
      document.getElementById('receive-step-form')?.classList.add('hidden');
      document.getElementById('receive-step-success')?.classList.remove('hidden');
      showToast(`MRR ${ref} posted — check Transaction History → Incoming.`, 'success');
      pushNotification('success', 'Delivery Received', `MRR ${ref} processed from ERP.`, { source: 'action', referenceNumber: ref, action: { type: 'delivery' } });
      AppState.activeMrrLookup = null;
    } catch (e) {
      if (e.data && e.data.duplicate) openDuplicateModal(ref, 'DELIVERY');
      else showToast(e.message || 'Failed to record delivery.', 'error');
    } finally {
      const btn = document.getElementById('btn-modal-process-del');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Confirm & Post Stock';
      }
    }
  })();
}

export function initReceiveViewListeners() {
  // Delivery Search
  const delSearchInput = document.getElementById('input-del-ref');

  const btnDelSearch = document.getElementById('btn-search-delivery');

  if (btnDelSearch && delSearchInput) {
    btnDelSearch.addEventListener('click', () => {
      searchAndDisplayDelivery(delSearchInput.value);
    });
    delSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayDelivery(delSearchInput.value);
      }
    });
  }

  ['btn-demo-del-1', 'btn-demo-del-2', 'btn-demo-del-3'].forEach(btnId => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', () => {
        const ref = btn.getAttribute('data-ref');
        delSearchInput.value = ref;
        searchAndDisplayDelivery(ref);
      });
    }
  });

  const btnProcessDel = document.getElementById('btn-process-delivery');

  if (btnProcessDel) {
    btnProcessDel.addEventListener('click', () => {
      if (!AppState.activeDeliveryTicket) return;
      const success = TicketService.processDeliveryTicket(AppState.activeDeliveryTicket);
      if (success) {
        document.getElementById('del-ticket-preview').classList.add('hidden');
        delSearchInput.value = '';
        AppState.activeDeliveryTicket = null;
      }
    });
  }

  const btnDelCancel = document.getElementById('btn-cancel-del');

  if (btnDelCancel) {
    btnDelCancel.addEventListener('click', () => {
      document.getElementById('del-ticket-preview').classList.add('hidden');
      delSearchInput.value = '';
      AppState.activeDeliveryTicket = null;
    });
  }
}

Object.assign(window, {
  searchAndDisplayDelivery,
  populateTonerSelect,
  recordManualDelivery,
  initReceiveViewListeners
});
