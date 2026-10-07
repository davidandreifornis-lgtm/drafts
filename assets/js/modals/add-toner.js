/**
 * Modal: add-toner
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { apiAddToner, loadFromBackend } from "../services/backend.js";
import { generateInkId } from "../services/storage-service.js";
import { renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";

// ==========================================
// STOCK CARD / TONER MOVEMENT HISTORY
// ==========================================

// ==========================================
// ADD / REMOVE TONER
// ==========================================
export function renderAddPrinterRows(names) {
  const list = document.getElementById('add-toner-printer-list');
  if (!list) return;
  const values = (names && names.length) ? names : [''];
  list.innerHTML = values.map((name, idx) => `
    <div class="flex gap-2 items-center add-printer-row">
      <input type="text" class="add-printer-input flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Canon MF237W" value="${escapeHTML(name || '')}">
      <button type="button" class="btn-remove-printer-row p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors ${values.length === 1 ? 'invisible' : ''}" title="Remove printer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
  `).join('');

  list.querySelectorAll('.btn-remove-printer-row').forEach(btn => {
    btn.addEventListener('click', () => {
      const rows = getAddPrinterValues();
      const row = btn.closest('.add-printer-row');
      const inputs = [...list.querySelectorAll('.add-printer-input')];
      const idx = inputs.indexOf(row.querySelector('.add-printer-input'));
      const next = rows.filter((_, i) => i !== idx);
      renderAddPrinterRows(next.length ? next : ['']);
    });
  });
}

export function getAddPrinterValues() {
  return [...document.querySelectorAll('#add-toner-printer-list .add-printer-input')]
    .map(el => (el.value || '').trim())
    .filter(Boolean);
}

export function openAddTonerModal() {
  ['add-toner-code','add-toner-description'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const qty = document.getElementById('add-toner-qty');
  const re = document.getElementById('add-toner-reorder');
  if (qty) qty.value = '0';
  if (re) re.value = '3';
  renderAddPrinterRows(['']);
  const modal = document.getElementById('modal-add-toner');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => document.getElementById('add-toner-code')?.focus(), 80);
}

export function closeAddTonerModal() {
  const modal = document.getElementById('modal-add-toner');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

export function saveNewToner() {
  const code = (document.getElementById('add-toner-code')?.value || '').trim().toUpperCase();
  const description = (document.getElementById('add-toner-description')?.value || '').trim();
  const brand = '';
  const supplier = '';
  const qtyRaw = document.getElementById('add-toner-qty')?.value;
  const reorder = 3;

  if (!code) {
    showToast('Item code is required (same as MRR Item_code).', 'warning');
    document.getElementById('add-toner-code')?.focus();
    return;
  }
  if (!description) {
    showToast('Description is required (same as MRR Item_Desc).', 'warning');
    document.getElementById('add-toner-description')?.focus();
    return;
  }
  if (qtyRaw === '' || qtyRaw === null || qtyRaw === undefined) {
    showToast('Quantity is required (same as MRR_Qty).', 'warning');
    document.getElementById('add-toner-qty')?.focus();
    return;
  }

  const exists = AppState.inks.some(i => (i.inkCode || '').toUpperCase() === code);
  if (exists) {
    showToast(`Item ${code} already exists in inventory.`, 'warning');
    return;
  }

  const qty = Math.max(0, parseInt(qtyRaw, 10) || 0);
  const now = new Date().toISOString();
  const printerModelStored = '';

  const item = {
    id: generateInkId(),
    inkCode: code,
    brand: brand,
    description: description,
    printerModel: printerModelStored,
    color: 'Black',
    department: '',
    serialNumbers: [],
    quantity: qty,
    reorderLevel: reorder,
    supplier,
    location: '',
    createdAt: now,
    updatedAt: now
  };

  (async () => {
    try {
      // Always try MySQL API first (even if health check failed earlier)
      try {
        await apiAddToner({
          inkCode: code,
          itemCode: code,
          description,
          printerModel: '',
          supplier: '',
          quantity: qty,
          reorderLevel: 3
        });
        AppState.useBackend = true;
        await loadFromBackend();
        renderInventory();
        renderDashboard();
        renderAlerts();
        closeAddTonerModal();
        showToast(`Toner ${code} saved to database.`, 'success');
        pushNotification('success', 'Toner Added', `${code} was saved to inventory.`, { source: 'action', inkCode: code, action: { type: 'stockCard', payload: code } });
        return;
      } catch (apiErr) {
        console.error('[Toner] API add failed:', apiErr);
        // If explicitly offline mode preference after API failure, fall back with warning
        showToast((apiErr && apiErr.message) ? apiErr.message : 'Database save failed.', 'error');
        // Do NOT silently write only to localStorage — user expects DB
        return;
      }
    } catch (e) {
      showToast(e.message || 'Failed to add toner.', 'error');
    }
  })();
}

export function initAddTonerModalListeners() {
  const btnAddToner = document.getElementById('btn-add-toner');

  if (btnAddToner) btnAddToner.addEventListener('click', openAddTonerModal);

  const btnAddPrinterRow = document.getElementById('btn-add-printer-row');

  if (btnAddPrinterRow) {
    btnAddPrinterRow.addEventListener('click', () => {
      const current = [...document.querySelectorAll('#add-toner-printer-list .add-printer-input')].map(el => el.value || '');
      current.push('');
      renderAddPrinterRows(current);
      const inputs = document.querySelectorAll('#add-toner-printer-list .add-printer-input');
      inputs[inputs.length - 1]?.focus();
    });
  }

  const btnCloseAddToner = document.getElementById('btn-close-add-toner');

  if (btnCloseAddToner) btnCloseAddToner.addEventListener('click', closeAddTonerModal);

  const btnCancelAddToner = document.getElementById('btn-cancel-add-toner');

  if (btnCancelAddToner) btnCancelAddToner.addEventListener('click', closeAddTonerModal);

  const btnSaveAddToner = document.getElementById('btn-save-add-toner');

  if (btnSaveAddToner) btnSaveAddToner.addEventListener('click', saveNewToner);
}

Object.assign(window, {
  renderAddPrinterRows,
  getAddPrinterValues,
  openAddTonerModal,
  closeAddTonerModal,
  saveNewToner,
  initAddTonerModalListeners
});
