/**
 * Modal: defective
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { formatDateTime, normalizeRefNumber } from "../core/format.js";
import { navigateTo } from "../core/router.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { apiRecordDefective, loadFromBackend } from "../services/backend.js";
import { TicketService } from "../services/ticket-service.js";
import { renderCharts, renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";
import { renderTransactions } from "../views/transactions.js";

// ==========================================
// DEFECTIVE RETURN MODAL
// ==========================================
export function resetDefectiveModal() {
  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.add('hidden');
  document.getElementById('defective-step-preview')?.classList.add('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
  AppState.activeDefectiveTxn = null;
  const notes = document.getElementById('modal-def-notes');
  if (notes) notes.value = '';
}

export function openDefectiveModal() {
  resetDefectiveModal();
  const modal = document.getElementById('modal-defective');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  const input = document.getElementById('modal-def-ref');
  if (input) { input.value = ''; setTimeout(() => input.focus(), 80); }
}

export function closeDefectiveModal() {
  const modal = document.getElementById('modal-defective');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeDefectiveTxn = null;
}

export function showDefectiveError(ref, title, desc) {
  const er = document.getElementById('defective-error-ref');
  const et = document.getElementById('defective-error-title');
  const ed = document.getElementById('defective-error-desc');
  if (er) er.textContent = ref || '—';
  if (et) et.textContent = title || 'Not Found';
  if (ed) ed.textContent = desc || '';
  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.remove('hidden');
  document.getElementById('defective-step-preview')?.classList.add('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
}

export function searchDefectiveIssuance(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter an issuance ticket number.', 'warning');
    return;
  }

  const released = AppState.transactions.filter(
    t => t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === normalized
  );

  if (released.length === 0) {
    showDefectiveError(
      normalized,
      'Issuance Not Found',
      'No completed stock issuance matches this ticket. Process the release first, then flag it.'
    );
    return;
  }

  const already = AppState.transactions.some(
    t => t.type === 'DEFECTIVE' && normalizeRefNumber(t.referenceNumber) === normalized
  );
  if (already) {
    showDefectiveError(
      normalized,
      'Already Flagged',
      'This issuance ticket was already marked as defective.'
    );
    return;
  }

  const primary = released[0];
  AppState.activeDefectiveTxn = primary;

  document.getElementById('m-def-ref').textContent = primary.referenceNumber;
  document.getElementById('m-def-code').textContent = primary.inkCode || '—';
  document.getElementById('m-def-dept').textContent = primary.department || '—';
  document.getElementById('m-def-loc').textContent = primary.location || '—';
  document.getElementById('m-def-qty').textContent = '1';
  document.getElementById('m-def-date').textContent = formatDateTime(primary.createdAt || primary.date);

  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.add('hidden');
  document.getElementById('defective-step-preview')?.classList.remove('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
}

export function initDefectiveModalListeners() {
  const quickDefective = document.getElementById('quick-btn-defective');

  if (quickDefective) quickDefective.addEventListener('click', openDefectiveModal);

  // Defective modal controls
  const btnCloseDef = document.getElementById('btn-close-defective-modal');

  if (btnCloseDef) btnCloseDef.addEventListener('click', closeDefectiveModal);

  const btnCancelDef = document.getElementById('btn-modal-cancel-def');

  if (btnCancelDef) btnCancelDef.addEventListener('click', closeDefectiveModal);

  const btnSearchDef = document.getElementById('btn-modal-search-def');

  const modalDefRef = document.getElementById('modal-def-ref');

  if (btnSearchDef && modalDefRef) {
    btnSearchDef.addEventListener('click', () => searchDefectiveIssuance(modalDefRef.value));
    modalDefRef.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); searchDefectiveIssuance(modalDefRef.value); }
    });
  }

  const btnProcessDef = document.getElementById('btn-modal-process-def');

  if (btnProcessDef) {
    btnProcessDef.addEventListener('click', () => {
      if (!AppState.activeDefectiveTxn) return;
      const notes = document.getElementById('modal-def-notes')?.value || '';
      const refNum = AppState.activeDefectiveTxn.referenceNumber;
      (async () => {
        try {
          if (AppState.useBackend) {
            await apiRecordDefective({ action: 'flag', referenceNumber: refNum, notes });
            await loadFromBackend();
            renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
            showToast(`Issuance ${refNum} flagged as defective.`, 'success');
            pushNotification('warning', 'Defective Return', `Ticket ${refNum} marked defective.`, { source: 'action', referenceNumber: refNum, action: { type: 'issue' } });
          } else {
            const ok = TicketService.processDefectiveReturn(refNum, notes);
            if (!ok) return;
          }
          document.getElementById('m-def-success-ref').textContent = refNum;
          document.getElementById('defective-step-search')?.classList.add('hidden');
          document.getElementById('defective-step-error')?.classList.add('hidden');
          document.getElementById('defective-step-preview')?.classList.add('hidden');
          document.getElementById('defective-step-success')?.classList.remove('hidden');
        } catch (e) {
          showToast(e.message || 'Failed to flag defective.', 'error');
        }
      })();
    });
  }

  const btnDefDone = document.getElementById('btn-defective-done');

  if (btnDefDone) btnDefDone.addEventListener('click', () => {
    closeDefectiveModal();
    AppState.filters.transactionType = 'DEFECTIVE';
    navigateTo('transactions');
  });

  const btnDefErrBack = document.getElementById('btn-defective-error-back');

  if (btnDefErrBack) btnDefErrBack.addEventListener('click', () => {
    resetDefectiveModal();
    const input = document.getElementById('modal-def-ref');
    if (input) { input.value = ''; input.focus(); }
  });

  document.querySelectorAll('.sample-def-ref').forEach(btn => {
    btn.addEventListener('click', () => {
      const ref = btn.getAttribute('data-ref');
      const input = document.getElementById('modal-def-ref');
      if (input) input.value = ref;
      searchDefectiveIssuance(ref);
    });
  });
}

Object.assign(window, {
  resetDefectiveModal,
  openDefectiveModal,
  closeDefectiveModal,
  showDefectiveError,
  searchDefectiveIssuance,
  initDefectiveModalListeners
});
