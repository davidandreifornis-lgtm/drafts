/**
 * View: inventory
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { STOCK_STATUS } from "../data/constants.js";
import { openRemoveTonerModal } from "../modals/remove-toner.js";
import { openStockCard } from "../modals/stock-card.js";
import { getStockStatus } from "../services/stock-utils.js";

// ==========================================
// 10. INVENTORY
// ==========================================
export function resetInventoryFilters() {
  if (!AppState.filters) AppState.filters = {};
  AppState.filters.inventorySearch = '';
  AppState.filters.inventoryStatus = 'ALL';
  const invSearch = document.getElementById('filter-inv-search');
  const invStatus = document.getElementById('filter-inv-status');
  if (invSearch) {
    invSearch.value = '';
    invSearch.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (invStatus) {
    invStatus.value = 'ALL';
    invStatus.selectedIndex = 0; // All Statuses
    invStatus.dispatchEvent(new Event('change', { bubbles: true }));
  }
  // Force state again (in case input handlers re-read stale values)
  AppState.filters.inventorySearch = '';
  AppState.filters.inventoryStatus = 'ALL';
  renderInventory();
}

export function renderInventory() {

  const tbody = document.getElementById('inventory-tbody');
  const emptyState = document.getElementById('inventory-empty-state');
  if (!tbody) return;

  const search = String(AppState.filters.inventorySearch || '').toLowerCase().trim();
  const statusFilter = AppState.filters.inventoryStatus || 'ALL';

  // Aggregate by toner code so each code appears only once
  const byCode = {};
  AppState.inks.forEach(item => {
    const code = (item.inkCode || '').trim();
    if (!code) return;
    const key = code.toUpperCase();
    if (!byCode[key]) {
      byCode[key] = {
        inkCode: code,
        description: item.description || '',
        brand: item.brand || '',
        printerModel: item.printerModel || '',
        supplier: item.supplier || '',
        quantity: 0,
        reorderLevel: Number(item.reorderLevel) || 0,
        printers: new Set(),
        suppliers: new Set()
      };
    }
    byCode[key].quantity += Number(item.quantity) || 0;
    if (!byCode[key].description && item.description) byCode[key].description = item.description;
    if (!byCode[key].brand && item.brand) byCode[key].brand = item.brand;
    // Use the highest reorder level among lines (safer alert threshold)
    byCode[key].reorderLevel = Math.max(byCode[key].reorderLevel, Number(item.reorderLevel) || 0);
    if (item.printerModel) {
      String(item.printerModel).split(/\s*·\s*/).map(s => s.trim()).filter(Boolean).forEach(p => byCode[key].printers.add(p));
    }
    if (item.supplier) byCode[key].suppliers.add(item.supplier);
  });

  let aggregated = Object.values(byCode).map(g => {
    const allPrinters = [];
    const seen = new Set();
    [...g.printers].forEach(p => {
      const norm = (p || '').trim();
      if (!norm) return;
      const key = norm.toUpperCase();
      if (seen.has(key)) return;
      seen.add(key);
      allPrinters.push(norm);
    });
    return {
      inkCode: g.inkCode,
      description: g.description || '',
      brand: g.brand || '',
      printerModel: allPrinters.length ? allPrinters.join(' · ') : (g.printerModel || '—'),
      printerList: allPrinters.length ? allPrinters : [],
      supplier: [...g.suppliers].join(', ') || g.supplier || '—',
      supplierList: [...g.suppliers],
      quantity: g.quantity,
      reorderLevel: g.reorderLevel || 3
    };
  });

  const filtered = aggregated.filter(item => {
    if (search) {
      const matchCode = (item.inkCode || '').toLowerCase().includes(search);
      const matchDesc = (item.description || '').toLowerCase().includes(search);
      const matchSupplier = (item.supplier || '').toLowerCase().includes(search);
      const matchPrinter = (item.printerModel || '').toLowerCase().includes(search);
      if (!matchCode && !matchDesc && !matchSupplier && !matchPrinter) return false;
    }
    const status = getStockStatus(item.quantity, item.reorderLevel);
    if (statusFilter === 'IN_STOCK' && status !== STOCK_STATUS.IN_STOCK) return false;
    if (statusFilter === 'LOW_STOCK' && status !== STOCK_STATUS.LOW_STOCK) return false;
    if (statusFilter === 'OUT_OF_STOCK' && status !== STOCK_STATUS.OUT_OF_STOCK) return false;
    return true;
  });

  // Sort by toner code
  filtered.sort((a, b) => (a.inkCode || '').localeCompare(b.inkCode || '', undefined, { sensitivity: 'base' }));

  if (filtered.length === 0) {
    tbody.innerHTML = '';
    emptyState.classList.remove('hidden');
    return;
  }

  emptyState.classList.add('hidden');
  const countEl = document.getElementById('inv-result-count');
  if (countEl) countEl.textContent = String(filtered.length);
  tbody.innerHTML = filtered.map(item => {
    const status = getStockStatus(item.quantity, item.reorderLevel);
    let tone = { badge: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-500', bar: 'bg-emerald-500', num: 'text-slate-900' };
    if (status === STOCK_STATUS.OUT_OF_STOCK) tone = { badge: 'bg-rose-50 text-rose-700 ring-rose-600/20', dot: 'bg-rose-500', bar: 'bg-rose-500', num: 'text-rose-600' };
    else if (status === STOCK_STATUS.LOW_STOCK) tone = { badge: 'bg-amber-50 text-amber-700 ring-amber-600/25', dot: 'bg-amber-500', bar: 'bg-amber-500', num: 'text-amber-600' };
    const pct = Math.min(100, Math.round((item.quantity / Math.max(item.reorderLevel * 3, 1)) * 100));

    return `
      <tr class="group hover:bg-zinc-50 transition-colors cursor-pointer inventory-row" data-ink-code="${escapeHTML(item.inkCode)}" title="Open stock card">
        <td class="px-5 py-4 whitespace-nowrap">
          <span class="inline-flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-zinc-100 text-zinc-700 ring-1 ring-zinc-200 flex items-center justify-center shrink-0">
              <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </span>
            <span class="font-semibold text-slate-900 font-mono tracking-tight">${escapeHTML(item.inkCode)}</span>
          </span>
        </td>
        <td class="px-5 py-4 text-slate-600 text-sm leading-snug">${escapeHTML(item.description || '—')}</td>
        <td class="keep-color px-5 py-4">
          <div class="flex items-center gap-3">
            <span class="w-9 text-right font-bold font-mono text-base tabular-nums ${tone.num}">${item.quantity}</span>
            <span class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden" title="Reorder level: ${item.reorderLevel}"><span class="block h-full rounded-full ${tone.bar}" style="width:${pct}%"></span></span>
          </div>
        </td>
        <td class="keep-color px-5 py-4">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full ring-1 ring-inset ${tone.badge}">
            <i class="w-1.5 h-1.5 rounded-full ${tone.dot}"></i>${status}
          </span>
        </td>
        <td class="px-5 py-4 text-center">
          <button type="button" class="btn-remove-toner inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-zinc-950 bg-white hover:bg-zinc-100 ring-1 ring-inset ring-zinc-200 hover:ring-zinc-400 transition" data-ink-code="${escapeHTML(item.inkCode)}" title="Remove this toner from inventory">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Remove
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

export function initInventoryViewListeners() {
  // Inventory Filters (IDs must match the HTML: filter-inv-search / filter-inv-status)
  const invSearch = document.getElementById('filter-inv-search');

  if (invSearch) {
    invSearch.addEventListener('input', (e) => {
      AppState.filters.inventorySearch = e.target.value || '';
      renderInventory();
    });
    invSearch.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        invSearch.value = '';
        AppState.filters.inventorySearch = '';
        renderInventory();
      }
    });
  }

  const invStatus = document.getElementById('filter-inv-status');

  if (invStatus) {
    invStatus.addEventListener('change', (e) => {
      AppState.filters.inventoryStatus = e.target.value || 'ALL';
      renderInventory();
    });
  }

  const btnInvClear = document.getElementById('btn-clear-inv-filters') || document.getElementById('btn-inv-clear');

  if (btnInvClear) {
    btnInvClear.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      resetInventoryFilters();
    });
  }

  // Safety: also bind by id in case DOM was re-rendered
  document.getElementById('btn-clear-inv-filters')?.addEventListener('click', (e) => {
    e.preventDefault();
    resetInventoryFilters();
  });

  // Stock card: click inventory row
  const invTbody = document.getElementById('inventory-tbody');

  if (invTbody) {
    invTbody.addEventListener('click', (e) => {
      const removeBtn = e.target.closest('.btn-remove-toner');
      if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();
        const code = removeBtn.getAttribute('data-ink-code');
        if (code) openRemoveTonerModal(code);
        return;
      }
      const row = e.target.closest('tr.inventory-row');
      if (!row) return;
      const code = row.getAttribute('data-ink-code');
      if (code) openStockCard(code);
    });
  }
}

Object.assign(window, {
  resetInventoryFilters,
  renderInventory,
  initInventoryViewListeners
});
