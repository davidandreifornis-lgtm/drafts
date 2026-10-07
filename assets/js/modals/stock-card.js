/**
 * Modal: stock-card
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate, phTodayYmd, toManilaDate } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { renderAlerts } from "../layout/notifications-panel.js";
import { appConfirm } from "./app-confirm.js";
import { loadReleaseLocations } from "./release.js";
import { apiUpdateToner, loadFromBackend } from "../services/backend.js";
import { txnDateYmd } from "../services/stock-utils.js";
import { StorageService } from "../services/storage-service.js";
import { renderDashboard } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";
import { loadSuppliers } from "../views/locations.js";

export function getLocationPrinterOptions() {
  const names = [];
  const seen = new Set();
  (AppState.releaseLocations || []).forEach(r => {
    const p = (r.printerName || '').trim();
    if (!p) return;
    const key = p.toUpperCase();
    if (seen.has(key)) return;
    seen.add(key);
    names.push(p);
  });
  return names.sort((a, b) => a.localeCompare(b));
}

export function populateStockCardPrinterSelect(selectedList) {
  const box = document.getElementById('stock-card-printers-checkboxes');
  if (!box) return;
  const options = getLocationPrinterOptions();
  const selected = new Set((selectedList || []).map(s => String(s).trim().toUpperCase()).filter(Boolean));
  // Keep printers already on the item even if not in locations list
  (selectedList || []).forEach(p => {
    const name = String(p || '').trim();
    if (!name) return;
    if (!options.some(o => o.toUpperCase() === name.toUpperCase())) options.push(name);
  });
  options.sort((a, b) => a.localeCompare(b));

  if (!options.length) {
    box.innerHTML = '<p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-2">No printers saved yet. Add printers under <strong>Locations &amp; Printers</strong>.</p>';
    return;
  }

  box.innerHTML = options.map((p, i) => {
    const id = 'sc-printer-cb-' + i;
    const checked = selected.has(p.toUpperCase()) ? ' checked' : '';
    return `<label for="${id}" class="flex items-center gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50">
      <input type="checkbox" id="${id}" class="stock-card-printer-cb w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="${escapeHTML(p)}"${checked}>
      <span class="text-sm text-slate-800">${escapeHTML(p)}</span>
    </label>`;
  }).join('');
}

export function getSelectedStockCardPrinters() {
  return [...document.querySelectorAll('.stock-card-printer-cb:checked')].map(cb => cb.value).filter(Boolean);
}

export async function showStockCardEditPanel() {
  await loadReleaseLocations().catch(() => {});
  await loadSuppliers().catch(() => {});
  const code = (document.getElementById('stock-card-ink-code')?.value || '').trim();
  const item = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
  let printers = [];
  if (item) {
    if (Array.isArray(item.printerList) && item.printerList.length) printers = item.printerList;
    else if (item.printerModel) {
      printers = String(item.printerModel).split(/\s*·\s*/).map(s => s.trim()).filter(Boolean);
    }
  }
  populateStockCardPrinterSelect(printers);
  populateStockCardSupplierSelect(item ? (item.supplier || '') : '');
  const panel = document.getElementById('stock-card-edit-panel');
  if (panel) {
    panel.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  setTimeout(() => document.getElementById('stock-card-qty')?.focus(), 50);
}

export function hideStockCardEditPanel() {
  const panel = document.getElementById('stock-card-edit-panel');
  if (panel) panel.classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
}

export async function saveStockCard() {
  const ok = await appConfirm({ title: 'Save stock card', message: 'Save changes to quantity, printers, and supplier?', confirmText: 'Save' });
  if (!ok) return;
  const code = (document.getElementById('stock-card-ink-code')?.value || document.getElementById('stock-card-code')?.textContent || '').trim();
  if (!code) return;
  const qty = Math.max(0, parseInt(document.getElementById('stock-card-qty')?.value, 10) || 0);
  const reorder = Math.max(0, parseInt(document.getElementById('stock-card-reorder')?.value, 10) || 0);
  // Description is read-only — keep existing master value
  const description = (document.getElementById('stock-card-description-input')?.value || '').trim();
  const printerList = getSelectedStockCardPrinters();
  const printerModel = printerList.join(' · ');
  const supplier = (document.getElementById('stock-card-supplier-input')?.value || '').trim();

  try {
    if (AppState.useBackend) {
      await apiUpdateToner({
        inkCode: code,
        itemCode: code,
        description,
        quantity: qty,
        reorderLevel: reorder,
        printerModel,
        supplier
      });
      await loadFromBackend();
    } else {
      const item = AppState.inks.find(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
      if (item) {
        item.quantity = qty;
        item.reorderLevel = reorder;
        item.printerModel = printerModel;
        item.supplier = supplier;
        item.description = description;
        item.updatedAt = new Date().toISOString();
        if (typeof StorageService !== 'undefined') StorageService.saveInks(AppState.inks);
      }
    }
    hideStockCardEditPanel();
    showToast(`Stock card for ${code} saved.`, 'success');
    renderInventory();
    renderDashboard();
    renderAlerts();
    openStockCard(code);
  } catch (e) {
    showToast(e.message || 'Failed to save stock card.', 'error');
  }
}

export function closeStockCard() {
  const modal = document.getElementById('modal-stock-card');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

export function getStockCardDateBounds() {
  const period = document.getElementById('stock-card-period')?.value || 'ALL';
  const customFrom = document.getElementById('stock-card-from')?.value || '';
  const customTo = document.getElementById('stock-card-to')?.value || '';
  const today = phTodayYmd();
  let from = null;
  let to = null;
  if (period === 'TODAY') {
    from = today;
    to = today;
  } else if (period === 'WEEK') {
    const d = toManilaDate(today + 'T12:00:00+08:00') || new Date();
    d.setDate(d.getDate() - 6);
    from = d.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
    to = today;
  } else if (period === 'MONTH') {
    from = today.slice(0, 8) + '01';
    to = today;
  } else if (period === 'CUSTOM') {
    from = customFrom || null;
    to = customTo || null;
  }
  return { period, from, to };
}

export function syncStockCardCustomRangeUI() {
  const period = document.getElementById('stock-card-period')?.value || 'ALL';
  const wrap = document.getElementById('stock-card-custom-range');
  if (wrap) {
    if (period === 'CUSTOM') wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
}

export function renderStockCardMovements() {
  const code = (AppState._stockCardCode || document.getElementById('stock-card-ink-code')?.value || '').toUpperCase();
  const onHand = Number(AppState._stockCardOnHand != null ? AppState._stockCardOnHand : (document.getElementById('stock-card-onhand')?.textContent || 0)) || 0;
  if (!code) return;

  syncStockCardCustomRangeUI();
  const { from, to } = getStockCardDateBounds();

  const allTxns = (AppState.transactions || [])
    .filter(t => (t.inkCode || '').toUpperCase() === code)
    .slice()
    .sort((a, b) => {
      const da = new Date(a.createdAt || a.date || 0).getTime();
      const db = new Date(b.createdAt || b.date || 0).getTime();
      return da - db;
    });

  let netFromTxns = 0;
  allTxns.forEach(t => {
    if (t.type === 'RECEIVED') netFromTxns += Number(t.quantity) || 0;
    else if (t.type === 'RELEASED') netFromTxns -= Number(t.quantity) || 0;
  });
  let balance = onHand - netFromTxns;

  const tbody = document.getElementById('stock-card-tbody');
  const empty = document.getElementById('stock-card-empty');
  const rows = [];

  rows.push(`
    <tr class="bg-slate-50/80">
      <td class="px-3 py-2.5 text-xs text-slate-500">—</td>
      <td class="px-3 py-2.5 font-semibold text-slate-800">Beginning Balance</td>
      <td class="px-3 py-2.5 font-mono text-xs text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900">${balance}</td>
      <td class="px-3 py-2.5 text-xs text-slate-400">—</td>
    </tr>
  `);

  let shown = 0;
  allTxns.forEach(t => {
    const ymd = txnDateYmd(t);
    const inRange = (!from || (ymd && ymd >= from)) && (!to || (ymd && ymd <= to));

    const dateLabel = formatDate(t.date || (t.createdAt || '').toString().split('T')[0]);
    let label = t.type;
    let stockIn = '—';
    let stockOut = '—';
    let note = '';
    const qty = Number(t.quantity) || 0;

    if (t.type === 'RECEIVED') {
      label = 'Delivery Received';
      stockIn = String(qty);
      balance += qty;
      note = t.supplier || '';
    } else if (t.type === 'RELEASED') {
      label = t.defective ? 'Issued (later defective)' : 'Issued';
      stockOut = String(qty);
      balance -= qty;
      note = [t.department, t.location].filter(Boolean).join(' · ');
    } else if (t.type === 'DEFECTIVE') {
      label = 'Defective Return';
      note = t.purpose || 'Flagged defective';
    }

    if (!inRange) return;
    shown++;
    rows.push(`
      <tr class="hover:bg-slate-50">
        <td class="px-3 py-2.5 text-xs text-slate-600 whitespace-nowrap">${escapeHTML(dateLabel)}</td>
        <td class="px-3 py-2.5 font-medium text-slate-800">${escapeHTML(label)}</td>
        <td class="px-3 py-2.5 font-mono text-xs font-semibold text-slate-900">${escapeHTML(t.referenceNumber || '—')}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-blue-600">${stockIn === '—' ? '—' : '+' + stockIn}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-600">${stockOut === '—' ? '—' : '-' + stockOut}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900">${balance}</td>
        <td class="px-3 py-2.5 text-xs text-slate-500 truncate" title="${escapeHTML(note)}">${escapeHTML(note || '—')}</td>
      </tr>
    `);
  });

  if (tbody) tbody.innerHTML = rows.join('');
  if (empty) {
    if (shown === 0 && allTxns.length === 0) empty.classList.remove('hidden');
    else if (shown === 0) {
      empty.textContent = 'No movements in the selected date range.';
      empty.classList.remove('hidden');
    } else {
      empty.classList.add('hidden');
      empty.textContent = 'No movement history for this toner yet.';
    }
  }
}

export function openStockCard(inkCode) {
  const code = (inkCode || '').trim();
  if (!code) return;
  hideStockCardEditPanel();

  const items = AppState.inks.filter(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
  const onHand = items.reduce((s, i) => s + (Number(i.quantity) || 0), 0);
  const sample = items[0] || {};
  const printer = [...new Set(items.flatMap(i => String(i.printerModel || '').split(/\s*·\s*/).map(s => s.trim()).filter(Boolean)))].join(' · ') || sample.printerModel || '—';
  const supplier = [...new Set(items.map(i => i.supplier).filter(Boolean))].join(', ') || sample.supplier || '—';

    document.getElementById('stock-card-code').textContent = code;
  document.getElementById('stock-card-meta').textContent = items.length > 1
    ? `${items.length} inventory lines · full movement history`
    : 'Complete stock movement history — editable master data';
  const codeEl = document.getElementById('stock-card-ink-code');
  if (codeEl) codeEl.value = code;
  const editCodeDisp = document.getElementById('stock-card-edit-code-display');
  if (editCodeDisp) editCodeDisp.textContent = code;
  const descInput = document.getElementById('stock-card-description-input');
  if (descInput) descInput.value = sample.description || '';
  const qtyEl = document.getElementById('stock-card-qty');
  const reorderEl = document.getElementById('stock-card-reorder');
  const printersInput = document.getElementById('stock-card-printers-input');
  const supplierInput = document.getElementById('stock-card-supplier-input');
  const onHandEl = document.getElementById('stock-card-onhand');
  if (onHandEl) onHandEl.textContent = onHand;
  if (qtyEl) qtyEl.value = onHand;
  if (reorderEl) reorderEl.value = Number(sample.reorderLevel) || 0;
  // Printers: show one per line for editing (split only on ·)
  const printerNames = (printer && printer !== '—')
    ? printer.split(/\s*·\s*/).map(s => s.trim()).filter(Boolean)
    : [];
  if (printersInput) printersInput.value = printerNames.join('\n');
  if (supplierInput) supplierInput.value = (supplier && supplier !== '—') ? supplier.split(',')[0].trim() : '';
  const printerEl = document.getElementById('stock-card-printer');
  const supplierEl = document.getElementById('stock-card-supplier');
  if (printerEl) {
    printerEl.innerHTML = printerNames.length
      ? printerNames.map(p => `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-800 border border-indigo-100">${escapeHTML(p)}</span>`).join('')
      : '<span class="text-xs text-slate-400 italic">No printer listed</span>';
  }
  if (supplierEl) {
    const sup = (supplier && supplier !== '—') ? supplier : '—';
    supplierEl.innerHTML = sup === '—'
      ? '<span class="text-xs text-slate-400">—</span>'
      : `<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white text-slate-700 border border-slate-200">${escapeHTML(sup)}</span>`;
  }


  AppState._stockCardCode = code;
  AppState._stockCardOnHand = onHand;
  renderStockCardMovements();

  const backdrop = document.getElementById('modal-backdrop');
  const modal = document.getElementById('modal-stock-card');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

export function populateStockCardSupplierSelect(current) {
  const sel = document.getElementById('stock-card-supplier-input');
  if (!sel) return;
  const list = (AppState.suppliers || []).map(s => s.name).filter(Boolean);
  const cur = (current || '').trim();
  if (cur && !list.some(n => n.toUpperCase() === cur.toUpperCase())) list.push(cur);
  list.sort((a, b) => a.localeCompare(b));
  sel.innerHTML = '<option value="">— Select supplier —</option>' +
    list.map(n => {
      const selAttr = cur && n.toUpperCase() === cur.toUpperCase() ? ' selected' : '';
      return `<option value="${escapeHTML(n)}"${selAttr}>${escapeHTML(n)}</option>`;
    }).join('');
}

export function initStockCardModalListeners() {
  const btnCloseStock = document.getElementById('btn-close-stock-card');

  if (btnCloseStock) btnCloseStock.addEventListener('click', closeStockCard);

  const btnStockEdit = document.getElementById('btn-stock-card-edit');

  if (btnStockEdit) btnStockEdit.addEventListener('click', showStockCardEditPanel);

  const stockEditBackdrop = document.getElementById('stock-card-edit-backdrop');

  if (stockEditBackdrop) {
    stockEditBackdrop.addEventListener('click', hideStockCardEditPanel);
  }

  const btnStockEditCancel = document.getElementById('btn-stock-card-edit-cancel');

  if (btnStockEditCancel) btnStockEditCancel.addEventListener('click', hideStockCardEditPanel);

  const btnStockEditCancel2 = document.getElementById('btn-stock-card-edit-cancel-2');

  if (btnStockEditCancel2) btnStockEditCancel2.addEventListener('click', hideStockCardEditPanel);

  const btnStockDone = document.getElementById('btn-stock-card-done');

  if (btnStockDone) btnStockDone.addEventListener('click', closeStockCard);

  const btnStockSave = document.getElementById('btn-stock-card-save');

  if (btnStockSave) btnStockSave.addEventListener('click', saveStockCard);

  const stockCardPeriod = document.getElementById('stock-card-period');

  if (stockCardPeriod) stockCardPeriod.addEventListener('change', () => {
    syncStockCardCustomRangeUI();
    if (stockCardPeriod.value !== 'CUSTOM') renderStockCardMovements();
  });

  const btnStockCardApplyDates = document.getElementById('btn-stock-card-apply-dates');

  if (btnStockCardApplyDates) btnStockCardApplyDates.addEventListener('click', renderStockCardMovements);
}

Object.assign(window, {
  getLocationPrinterOptions,
  populateStockCardPrinterSelect,
  getSelectedStockCardPrinters,
  showStockCardEditPanel,
  hideStockCardEditPanel,
  saveStockCard,
  closeStockCard,
  getStockCardDateBounds,
  syncStockCardCustomRangeUI,
  renderStockCardMovements,
  openStockCard,
  populateStockCardSupplierSelect,
  initStockCardModalListeners
});
