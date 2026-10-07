/**
 * View: release
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { forceHideGlobalLoading, showGlobalLoading } from "../core/api.js";
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { appConfirm } from "../modals/app-confirm.js";
import { openDuplicateModal } from "../modals/duplicate.js";
import { openNotFoundModal } from "../modals/not-found.js";
import { apiRecordRelease, loadFromBackendSilent } from "../services/backend.js";
import { TicketService } from "../services/ticket-service.js";
import { renderCharts, renderDashboard } from "./dashboard.js";
import { renderInventory } from "./inventory.js";
import { renderTransactions } from "./transactions.js";

export function searchAndDisplayRelease(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a release reference number.', 'warning');
    return;
  }

  // Check if already processed
  if (TicketService.isAlreadyProcessed(normalized)) {
    openDuplicateModal(normalized, 'RELEASE');
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'RELEASE') {
    openNotFoundModal(normalized);
    return;
  }

  AppState.activeReleaseTicket = ticket;

  // Render preview details
  const previewBox = document.getElementById('rel-ticket-preview');
  document.getElementById('rel-preview-ref').textContent = ticket.referenceNumber;
  document.getElementById('rel-preview-date').textContent = formatDate(ticket.date);
  document.getElementById('rel-preview-dept').textContent = ticket.department || 'General';
  document.getElementById('rel-preview-givento').textContent = ticket.givenTo || 'Staff';
  document.getElementById('rel-preview-purpose').textContent = ticket.purpose || 'Toner replacement';

  const tbody = document.getElementById('rel-preview-tbody');
  const errorBox = document.getElementById('rel-stock-error-box');
  const errorMsg = document.getElementById('rel-stock-error-msg');
  const processBtn = document.getElementById('btn-process-release');
  const validationBadge = document.getElementById('rel-validation-status-badge');

  let totalQty = 0;
  let hasInsufficientStock = false;
  let insufficientSummary = [];

  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const availableQty = matched ? (Number(matched.quantity) || 0) : 0;
    const reqQty = Number(item.quantity) || 0;
    const postStock = availableQty - reqQty;
    totalQty += reqQty;

    const isShort = postStock < 0;
    if (isShort) {
      hasInsufficientStock = true;
      insufficientSummary.push(`${item.inkCode}: Needs ${reqQty}, only ${availableQty} in stock`);
    }

    const checkIcon = isShort
      ? `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-rose-100 text-rose-800">Deficit (-${Math.abs(postStock)})</span>`
      : `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800">Available</span>`;

    return `
      <tr class="hover:bg-slate-50 ${isShort ? 'bg-rose-50/50' : ''}">
        <td class="px-4 py-3 font-bold font-mono text-slate-900">${escapeHTML(item.inkCode)}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.brand || (matched ? matched.brand : 'HP'))}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.color || (matched ? matched.color : 'Black'))}</td>
        <td class="px-4 py-3 text-right font-mono font-semibold ${availableQty === 0 ? 'text-rose-600' : 'text-slate-700'}">${availableQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-600 text-base">-${reqQty}</td>
        <td class="px-4 py-3 text-right font-mono font-bold ${isShort ? 'text-rose-600' : 'text-slate-900'}">${postStock}</td>
        <td class="px-4 py-3 text-center">${checkIcon}</td>
      </tr>
    `;
  }).join('');

  document.getElementById('rel-preview-total-items').textContent = ticket.items.length;
  document.getElementById('rel-preview-total-qty').textContent = totalQty;

  if (hasInsufficientStock) {
    errorBox.classList.remove('hidden');
    errorMsg.textContent = insufficientSummary.join(' | ');
    processBtn.disabled = true;
    processBtn.classList.add('opacity-50', 'cursor-not-allowed');
    validationBadge.textContent = 'Validation Failed: Insufficient Stock';
    validationBadge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-rose-200 text-rose-800';
  } else {
    errorBox.classList.add('hidden');
    processBtn.disabled = false;
    processBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    validationBadge.textContent = 'Stock Verified & Sufficient';
    validationBadge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-200 text-emerald-800';
  }

  previewBox.classList.remove('hidden');
  previewBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

export function recordManualIssuance() {
  const ref = normalizeRefNumber(document.getElementById('modal-rel-ref')?.value);
  const toner = (document.getElementById('modal-rel-toner')?.value || '').trim();
  const dept = (document.getElementById('modal-rel-dept')?.value || '').trim();
  const locSelect = document.getElementById('modal-rel-location');
  const location = (locSelect?.value || '').trim();
  const locationPrinter = (document.getElementById('modal-rel-printer')?.value || '').trim();
  const yieldRaw = document.getElementById('modal-rel-yield')?.value;
  const issuedBy = (document.getElementById('modal-rel-issued-by')?.value || '').trim();
  const qtyRaw = document.getElementById('modal-rel-qty')?.value;
  const qty = Math.max(1, Math.min(999, parseInt(qtyRaw, 10) || 1));
  // Issuance date is always the current day
  const date = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('modal-rel-date');
  if (dateInput) dateInput.value = date;

  if (!ref) { showToast('Issuance reference is required.', 'warning'); return; }
  if (!toner) { showToast('Select an item.', 'warning'); return; }
  if (!dept) { showToast('Select a department.', 'warning'); return; }
  if (!location) { showToast('Select a location.', 'warning'); return; }
  const hasYield = !!document.getElementById('modal-rel-has-yield')?.checked;
  let actualYield = null;
  if (hasYield) {
    if (yieldRaw === '' || yieldRaw === null || yieldRaw === undefined) {
      showToast('Enter actual yield (pages before change).', 'warning');
      document.getElementById('modal-rel-yield')?.focus();
      return;
    }
    actualYield = Math.max(0, parseInt(yieldRaw, 10) || 0);
  }
  if (!issuedBy) { showToast('Select who issued this toner.', 'warning'); return; }

  if (TicketService.isAlreadyProcessed(ref)) {
    openDuplicateModal(ref, 'RELEASE');
    return;
  }

  const totalQty = AppState.inks
    .filter(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase())
    .reduce((s, i) => s + (Number(i.quantity) || 0), 0);
  if (totalQty < qty) {
    showToast(`Insufficient stock for ${toner} (need ${qty}, only ${totalQty} on hand).`, 'error');
    return;
  }

  const matched = AppState.inks.find(i =>
    (i.inkCode || '').toUpperCase() === toner.toUpperCase() && (Number(i.quantity) || 0) > 0
  ) || AppState.inks.find(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase());

  AppState.selectedReleaseLocation = location;
  AppState.selectedReleaseDepartment = dept;

  const ticket = {
    referenceNumber: ref,
    type: 'RELEASE',
    status: 'RECORDED',
    date,
    givenTo: '',
    department: dept,
    purpose: 'Stock issuance',
    items: [{
      inkCode: toner,
      brand: matched?.brand || '',
      printerModel: matched?.printerModel || '',
      color: matched?.color || 'Black',
      quantity: qty,
      serialNumber: ''
    }]
  };

  (async () => {
    try {
      if (AppState.useBackend) {
        const okIssue = await appConfirm({
          title: 'Confirm stock issuance',
          message: `Record this toner issuance and deduct ${qty} unit(s) from inventory?`,
          confirmText: 'Issue toner'
        });
        if (!okIssue) return;

        // Keep loading visible for the whole issuance + refresh (email can take a while)
        showGlobalLoading('Issuing toner… sending alerts if needed');
        try {
          await apiRecordRelease({
            referenceNumber: ref,
            inkCode: toner,
            itemCode: toner,
            department: dept,
            location,
            locationPrinter: locationPrinter === '—' ? '' : locationPrinter,
            actualYield,
            issuedBy,
            quantity: qty
          }, { silent: true, loadingMessage: 'Issuing toner…' });
          await loadFromBackendSilent();
        } finally {
          forceHideGlobalLoading();
        }
        renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
        document.getElementById('m-rel-success-ref').textContent = ref;
        const successQtyEl = document.getElementById('m-rel-success-qty');
        if (successQtyEl) successQtyEl.textContent = String(qty);
        document.getElementById('release-step-form')?.classList.add('hidden');
        document.getElementById('release-step-success')?.classList.remove('hidden');
        showToast(`Issuance ${ref} recorded (${qty} unit(s)).`, 'success');
        // If API reported low-stock email, surface a soft reminder in UI
        try {
          const invItem = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase());
          const qty = invItem ? Number(invItem.quantity) : null;
          const reorder = invItem ? Number(invItem.reorderLevel) : 3;
          if (qty !== null && qty <= reorder) {
            pushNotification('warning', 'Low stock after issuance',
              `${toner} is still low (${qty} left; reorder at ${reorder}). Admins were emailed if configured.`,
              { source: 'action', inkCode: toner, action: { type: 'stockCard', payload: toner } });
          }
        } catch (_) {}
        pushNotification('success', 'Toner Released', `Ticket ${ref} processed. Toner stock decremented.`, { source: 'action', referenceNumber: ref, action: { type: 'issue' }, inkCode: toner });
      } else {
        const ok = TicketService.processReleaseTicket(ticket);
        if (ok) {
          document.getElementById('m-rel-success-ref').textContent = ref;
          const successQtyEl2 = document.getElementById('m-rel-success-qty');
          if (successQtyEl2) successQtyEl2.textContent = String(qty);
          document.getElementById('release-step-form')?.classList.add('hidden');
          document.getElementById('release-step-success')?.classList.remove('hidden');
        }
      }
    } catch (e) {
      if (e.data && e.data.duplicate) openDuplicateModal(ref, 'RELEASE');
      else showToast(e.message || 'Failed to record issuance.', 'error');
    }
  })();
}

export function initReleaseViewListeners() {
  // Release Search
  const relSearchInput = document.getElementById('input-rel-ref');

  const btnRelSearch = document.getElementById('btn-search-release');

  if (btnRelSearch && relSearchInput) {
    btnRelSearch.addEventListener('click', () => {
      searchAndDisplayRelease(relSearchInput.value);
    });
    relSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayRelease(relSearchInput.value);
      }
    });
  }

  ['btn-demo-rel-1', 'btn-demo-rel-2', 'btn-demo-rel-3'].forEach(btnId => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', () => {
        const ref = btn.getAttribute('data-ref');
        relSearchInput.value = ref;
        searchAndDisplayRelease(ref);
      });
    }
  });

  const btnProcessRel = document.getElementById('btn-process-release');

  if (btnProcessRel) {
    btnProcessRel.addEventListener('click', () => {
      if (!AppState.activeReleaseTicket) return;
      const success = TicketService.processReleaseTicket(AppState.activeReleaseTicket);
      if (success) {
        document.getElementById('rel-ticket-preview').classList.add('hidden');
        relSearchInput.value = '';
        AppState.activeReleaseTicket = null;
      }
    });
  }

  const btnRelCancel = document.getElementById('btn-cancel-rel');

  if (btnRelCancel) {
    btnRelCancel.addEventListener('click', () => {
      document.getElementById('rel-ticket-preview').classList.add('hidden');
      relSearchInput.value = '';
      AppState.activeReleaseTicket = null;
    });
  }
}

Object.assign(window, {
  searchAndDisplayRelease,
  recordManualIssuance,
  initReleaseViewListeners
});
