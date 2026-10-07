/**
 * View: locations
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { appConfirm } from "../modals/app-confirm.js";
import { openEditLocationModal } from "../modals/edit-location.js";
import { loadReleaseLocations, populateDeptSelect } from "../modals/release.js";
import { renderCharts } from "./dashboard.js";
import { renderTxnDeptTabs } from "./transactions.js";

export function renderLocationsTable() {
  const tbody = document.getElementById('locations-tbody');
  if (!tbody) return;
  const rows = AppState.releaseLocations || [];
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-6 text-center text-slate-400">No locations yet. Add one above.</td></tr>';
    return;
  }
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(r.department)}</td>
      <td class="px-3 py-2">${escapeHTML(r.location)}</td>
      <td class="px-3 py-2 text-slate-600">${escapeHTML(r.printerName || '—')}</td>
      <td class="px-3 py-2 font-mono text-slate-600">${escapeHTML(r.ipAddress || r.ip_address || '—')}</td>
      <td class="px-3 py-2 space-x-2">
        <button type="button" class="btn-loc-edit text-xs font-semibold text-blue-600 hover:underline" data-id="${r.id}">Edit</button>
        <button type="button" class="btn-loc-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Delete</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-loc-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const row = (AppState.releaseLocations || []).find(x => x.id === id);
      if (!row) return;
      document.getElementById('loc-edit-id').value = row.id;
      document.getElementById('loc-edit-dept').value = row.department;
      document.getElementById('loc-edit-location').value = row.location;
      document.getElementById('loc-edit-printer').value = row.printerName || '';
    });
  });
  tbody.querySelectorAll('.btn-loc-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this location from issuance options?')) return;
      try {
        await apiRequest('locations.php', { method: 'DELETE', body: { id } });
        await loadReleaseLocations();
        renderLocationsTable();
        populateDeptSelect();
        showToast('Location removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

export async function loadSuppliers() {
  try {
    const data = await apiRequest('suppliers.php');
    AppState.suppliers = data.suppliers || [];
  } catch (e) {
    console.warn('[Toner] suppliers load failed', e);
    AppState.suppliers = AppState.suppliers || [];
  }
}

export function renderPageSuppliersTable() {
  const tbody = document.getElementById('page-suppliers-tbody');
  const empty = document.getElementById('page-suppliers-empty');
  const countEl = document.getElementById('page-supplier-count');
  if (!tbody) return;
  const rows = AppState.suppliers || [];
  if (countEl) countEl.textContent = rows.length + (rows.length === 1 ? ' supplier' : ' suppliers');
  if (!rows.length) {
    tbody.innerHTML = '';
    if (empty) empty.classList.remove('hidden');
    return;
  }
  if (empty) empty.classList.add('hidden');
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-semibold text-slate-900">${escapeHTML(r.name)}</td>
      <td class="px-5 py-3">
        <button type="button" class="btn-page-sup-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Remove</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-page-sup-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this supplier from the list?')) return;
      try {
        await apiRequest('suppliers.php', { method: 'DELETE', body: { id } });
        await loadSuppliers();
        renderPageSuppliersTable();
        showToast('Supplier removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Remove failed', 'error');
      }
    });
  });
}

export async function addPageSupplier() {
  const name = (document.getElementById('page-supplier-name')?.value || '').trim();
  if (!name) {
    showToast('Supplier name is required.', 'warning');
    return;
  }
  try {
    await apiRequest('suppliers.php', { method: 'POST', body: { name } });
    document.getElementById('page-supplier-name').value = '';
    await loadSuppliers();
    renderPageSuppliersTable();
    showToast('Supplier added.', 'success');
  } catch (e) {
    showToast(e.message || 'Add failed', 'error');
  }
}

export function switchLocSupTab(tab) {
  tab = tab === 'suppliers' ? 'suppliers' : 'locations';
  try { localStorage.setItem('toner_ui_loc_tab', tab); } catch (_) {}
  const isLoc = tab === 'locations';
  const panelLoc = document.getElementById('panel-locations');
  const panelSup = document.getElementById('panel-suppliers');
  const tabLoc = document.getElementById('tab-locations');
  const tabSup = document.getElementById('tab-suppliers');
  if (panelLoc) panelLoc.classList.toggle('hidden', !isLoc);
  if (panelSup) panelSup.classList.toggle('hidden', isLoc);
  const active = 'flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-white border-b-2 border-blue-600 transition-colors';
  const idle = 'flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors';
  if (tabLoc) tabLoc.className = 'loc-sup-tab ' + (isLoc ? active : idle);
  if (tabSup) tabSup.className = 'loc-sup-tab ' + (!isLoc ? active : idle);
  if (!isLoc) renderPageSuppliersTable();
  else renderPageLocationsTable();
}

export async function loadLocationsPage() {
  await loadReleaseLocations();
  await loadSuppliers();
  let tab = 'locations';
  try {
    const saved = localStorage.getItem('toner_ui_loc_tab');
    if (saved === 'suppliers' || saved === 'locations') tab = saved;
  } catch (_) {}
  switchLocSupTab(tab);
  renderPageLocationsTable();
  renderPageSuppliersTable();
}

/** Client-side location uniqueness (mirrors API rules). excludeId for edits.
 * Same dept / location / printer name alone is OK.
 * Only exact department + location + printer triple is blocked. */
export function assertClientLocationUnique(department, location, printerName, excludeId) {
  const dept = (department || '').trim().toUpperCase();
  const loc = (location || '').trim().toLowerCase();
  const printer = (printerName || '').trim().toLowerCase();
  if (!printer) {
    showToast('Printer assigned is required.', 'warning');
    return false;
  }
  const rows = AppState.releaseLocations || [];
  for (const r of rows) {
    if (excludeId && Number(r.id) === Number(excludeId)) continue;
    if (r.isActive === false) continue;
    const rd = String(r.department || '').trim().toUpperCase();
    const rl = String(r.location || '').trim().toLowerCase();
    const rp = String(r.printerName || '').trim().toLowerCase();
    if (rd === dept && rl === loc && rp === printer) {
      showToast('This department + location + printer already exists. The same printer name can be used in other departments or locations.', 'warning');
      return false;
    }
  }
  return true;
}

export function renderPageLocationsTable() {
  const tbody = document.getElementById('page-locations-tbody');
  const empty = document.getElementById('page-locations-empty');
  const countEl = document.getElementById('page-loc-count');
  if (!tbody) return;
  const q = (document.getElementById('filter-locations-search')?.value || '').trim().toLowerCase();
  const allRows = AppState.releaseLocations || [];
  const rows = !q ? allRows : allRows.filter(r => {
    const blob = [r.department, r.location, r.printerName, r.printer, r.ipAddress, r.ip_address]
      .map(x => String(x || '').toLowerCase())
      .join(' ');
    return blob.includes(q);
  });
  if (countEl) {
    countEl.textContent = q
      ? `${rows.length} of ${allRows.length} location${allRows.length === 1 ? '' : 's'}`
      : (allRows.length + (allRows.length === 1 ? ' location' : ' locations'));
  }
  if (!allRows.length) {
    tbody.innerHTML = '';
    if (empty) {
      empty.textContent = 'No locations yet. Add department, location, and optional printer above.';
      empty.classList.remove('hidden');
    }
    return;
  }
  if (!rows.length) {
    tbody.innerHTML = '';
    if (empty) {
      empty.textContent = 'No locations match your search.';
      empty.classList.remove('hidden');
    }
    return;
  }
  if (empty) empty.classList.add('hidden');
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-mono font-semibold text-slate-900">${escapeHTML(r.department)}</td>
      <td class="px-5 py-3">${escapeHTML(r.location)}</td>
      <td class="px-5 py-3 text-slate-600">${escapeHTML(r.printerName || '—')}</td>
      <td class="px-5 py-3 font-mono text-slate-600">${escapeHTML(r.ipAddress || r.ip_address || '—')}</td>
      <td class="px-5 py-3 space-x-3">
        <button type="button" class="btn-page-loc-edit text-xs font-semibold text-blue-600 hover:underline" data-id="${r.id}">Edit</button>
        <button type="button" class="btn-page-loc-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Delete</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-page-loc-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const row = (AppState.releaseLocations || []).find(x => x.id === id);
      if (!row) return;
      openEditLocationModal(row);
    });
  });
  tbody.querySelectorAll('.btn-page-loc-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this location from issuance options?')) return;
      try {
        await apiRequest('locations.php', { method: 'DELETE', body: { id } });
        await loadReleaseLocations();
        renderPageLocationsTable();
        if (typeof renderLocationsTable === 'function') renderLocationsTable();
        populateDeptSelect();
        showToast('Location removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

export async function savePageLocationRow() {
  const ok = await appConfirm({ title: 'Save location', message: 'Add this location, printer, and IP?', confirmText: 'Save' });
  if (!ok) return;

  const department = (document.getElementById('page-loc-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('page-loc-location')?.value || '').trim();
  const printerName = (document.getElementById('page-loc-printer')?.value || '').trim();
  const ipAddress = (document.getElementById('page-loc-ip')?.value || '').trim();
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  if (!assertClientLocationUnique(department, location, printerName, null)) return;
  try {
    await apiRequest('locations.php', { method: 'POST', body: { department, location, printerName, ipAddress } });
    showToast('Location added.', 'success');
    document.getElementById('page-loc-edit-id').value = '';
    document.getElementById('page-loc-dept').value = '';
    document.getElementById('page-loc-location').value = '';
    document.getElementById('page-loc-printer').value = '';
    const ipEl = document.getElementById('page-loc-ip');
    if (ipEl) ipEl.value = '';
    await loadReleaseLocations();
    renderPageLocationsTable();
    if (typeof renderCharts === "function") renderCharts();
    if (typeof renderTxnDeptTabs === "function") renderTxnDeptTabs();
    if (typeof populateDeptSelect === "function") populateDeptSelect();
    if (typeof renderLocationsTable === 'function') renderLocationsTable();
    populateDeptSelect();
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}

export function initLocationsViewListeners() {
  document.querySelectorAll('.loc-sup-tab').forEach(btn => {
    btn.addEventListener('click', () => switchLocSupTab(btn.getAttribute('data-loc-tab')));
  });

  const btnPageLocSave = document.getElementById('btn-page-loc-save');

  if (btnPageLocSave) btnPageLocSave.addEventListener('click', savePageLocationRow);

  const btnPageSupAdd = document.getElementById('btn-page-supplier-add');

  if (btnPageSupAdd) btnPageSupAdd.addEventListener('click', addPageSupplier);

  const pageSupInput = document.getElementById('page-supplier-name');

  if (pageSupInput) pageSupInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addPageSupplier(); } });

  const btnPageLocClear = document.getElementById('btn-page-loc-clear');

  if (btnPageLocClear) btnPageLocClear.addEventListener('click', () => {
    document.getElementById('page-loc-edit-id').value = '';
    document.getElementById('page-loc-dept').value = '';
    document.getElementById('page-loc-location').value = '';
    document.getElementById('page-loc-printer').value = '';
  });

  const filterLocSearch = document.getElementById('filter-locations-search');

  if (filterLocSearch) {
    let locSearchTimer;
    filterLocSearch.addEventListener('input', () => {
      clearTimeout(locSearchTimer);
      locSearchTimer = setTimeout(() => renderPageLocationsTable(), 200);
    });
  }

  const btnLocSearchClear = document.getElementById('btn-locations-search-clear');

  if (btnLocSearchClear) {
    btnLocSearchClear.addEventListener('click', () => {
      const inp = document.getElementById('filter-locations-search');
      if (inp) inp.value = '';
      renderPageLocationsTable();
    });
  }
}

Object.assign(window, {
  renderLocationsTable,
  loadSuppliers,
  renderPageSuppliersTable,
  addPageSupplier,
  switchLocSupTab,
  loadLocationsPage,
  assertClientLocationUnique,
  renderPageLocationsTable,
  savePageLocationRow,
  initLocationsViewListeners
});
