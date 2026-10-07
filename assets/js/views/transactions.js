/**
 * View: transactions
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { openDefectiveDetailModal, sendDefectiveToSupplier } from "../modals/defective-detail.js";
import { openReceiveReplacementModal } from "../modals/defective-replace.js";
import { openReleaseDetailModal } from "../modals/release-detail.js";
import { getKnownDepartments, isDateInFilter, resolveDepartmentCode, resolveTonerDescription, resolveTonerSupplier } from "../services/stock-utils.js";

export function syncTxnCustomRangeUI() {
  const wrap = document.getElementById('txn-custom-range');
  const isCustom = AppState.filters.transactionDate === 'CUSTOM';
  if (wrap) {
    if (isCustom) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
  const from = document.getElementById('filter-txn-from');
  const to = document.getElementById('filter-txn-to');
  if (from && from.value !== (AppState.filters.transactionDateFrom || '')) {
    from.value = AppState.filters.transactionDateFrom || '';
  }
  if (to && to.value !== (AppState.filters.transactionDateTo || '')) {
    to.value = AppState.filters.transactionDateTo || '';
  }
}

// ==========================================
// 13. TRANSACTIONS
// ==========================================
export function updateTxnTabUI() {
  const type = AppState.filters.transactionType;
  document.querySelectorAll('.txn-main-tab').forEach(btn => {
    const active = btn.getAttribute('data-txn-tab') === type;
    if (active) {
      if (type === 'RECEIVED') {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-blue-50 border-b-2 border-blue-600 transition-colors';
      } else if (type === 'DEFECTIVE') {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-rose-700 bg-rose-50 border-b-2 border-rose-600 transition-colors';
      } else {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-emerald-700 bg-emerald-50 border-b-2 border-emerald-600 transition-colors';
      }
    } else {
      btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors';
    }
  });

  const deptTabs = document.getElementById('txn-dept-tabs');
  if (deptTabs) {
    if (type === 'RELEASED') {
      deptTabs.classList.remove('hidden');
      renderTxnDeptTabs();
    } else {
      deptTabs.classList.add('hidden');
    }
  }

  const receivedCount = AppState.transactions.filter(t => t.type === 'RECEIVED').length;
  const releasedCount = AppState.transactions.filter(t => t.type === 'RELEASED').length;
  const defectiveCount = AppState.transactions.filter(t => t.type === 'DEFECTIVE').length;
  const elR = document.getElementById('txn-tab-count-received');
  const elL = document.getElementById('txn-tab-count-released');
  const elD = document.getElementById('txn-tab-count-defective');
  if (elR) elR.textContent = receivedCount;
  if (elL) elL.textContent = releasedCount;
  if (elD) elD.textContent = defectiveCount;
}

export function renderTxnDeptTabs() {
  const wrap = document.getElementById('txn-dept-tabs');
  if (!wrap) return;
  const current = AppState.filters.transactionDept || 'ALL';
  const depts = getKnownDepartments();
  let html = `<button type="button" data-txn-dept="ALL" class="txn-dept-tab px-3 py-1.5 text-xs font-semibold rounded-lg ${current === 'ALL' ? 'bg-zinc-900 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'}">All Depts</button>`;
  depts.forEach(d => {
    const active = current === d;
    html += `<button type="button" data-txn-dept="${escapeHTML(d)}" class="txn-dept-tab px-3 py-1.5 text-xs font-semibold rounded-lg ${active ? 'bg-zinc-900 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'}">${escapeHTML(d)}</button>`;
  });
  wrap.innerHTML = html;
  wrap.querySelectorAll('.txn-dept-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      AppState.filters.transactionDept = btn.getAttribute('data-txn-dept') || 'ALL';
      renderTxnDeptTabs();
      renderTransactions();
    });
  });
}

export function renderTransactions() {
  const tbody = document.getElementById('txns-tbody');
  const emptyState = document.getElementById('txns-empty-state');
  if (!tbody) return;

  updateTxnTabUI();
  syncTxnCustomRangeUI();

  const search = AppState.filters.transactionSearch.toLowerCase().trim();
  const typeFilter = AppState.filters.transactionType; // RECEIVED | RELEASED
  const deptFilter = AppState.filters.transactionDept || 'ALL';
  const dateFilter = AppState.filters.transactionDate;

  const filtered = AppState.transactions.filter(t => {
    if (t.type !== typeFilter) return false;

    if (typeFilter === 'RELEASED' && deptFilter !== 'ALL') {
      const code = resolveDepartmentCode(t.department) || (t.department || '').toUpperCase();
      if (code !== deptFilter) return false;
    }

    if (search) {
      const matchRef = (t.referenceNumber || '').toLowerCase().includes(search);
      const matchInk = (t.inkCode || '').toLowerCase().includes(search);
      const matchLoc = (t.location || '').toLowerCase().includes(search);
      const matchSupplier = (t.supplier || '').toLowerCase().includes(search);
      const matchDept = (t.department || '').toLowerCase().includes(search);
      const matchPurpose = (t.purpose || '').toLowerCase().includes(search);
      const matchDesc = (resolveTonerDescription(t.inkCode) || t.description || '').toLowerCase().includes(search);
      const matchIssued = (t.issuedBy || '').toLowerCase().includes(search);
      const matchRecorded = (t.recordedBy || '').toLowerCase().includes(search);
      if (!matchRef && !matchInk && !matchLoc && !matchSupplier && !matchDept && !matchPurpose && !matchDesc && !matchIssued && !matchRecorded) return false;
    }

    if (!isDateInFilter(t.date || t.createdAt, dateFilter)) return false;
    return true;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '';
    emptyState.classList.remove('hidden');
    const msg = document.getElementById('txns-empty-msg');
    if (msg) {
      if (typeFilter === 'RECEIVED') msg.textContent = 'No incoming delivery transactions yet.';
      else if (typeFilter === 'DEFECTIVE') msg.textContent = 'No defective returns recorded yet.';
      else msg.textContent = deptFilter === 'ALL' ? 'No release transactions yet.' : `No releases for ${deptFilter} yet.`;
    }
    return;
  }

  emptyState.classList.add('hidden');
  const isReceived = typeFilter === 'RECEIVED';
  const theadRow = document.getElementById('txns-thead-row');

  // Equal-width columns; different headers per tab
  if (theadRow) {
    if (isReceived) {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5 text-center">Qty</th>
        <th class="px-4 py-3.5">Supplier</th>`;
    } else if (typeFilter === 'DEFECTIVE') {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5">Status</th>
        <th class="px-4 py-3.5">Action</th>`;
    } else {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5">Department</th>
        <th class="px-4 py-3.5">Action</th>`;
    }
  }

  const isDefective = typeFilter === 'DEFECTIVE';

  tbody.innerHTML = [...filtered].reverse().map(t => {
    if (isReceived) {
      const desc = resolveTonerDescription(t.inkCode) || t.description || '';
      // Prefer current inventory (stock card) supplier so edits show immediately;
      // fall back to what was stored on the transaction (e.g. old ADJ rows).
      const supplier = resolveTonerSupplier(t.inkCode) || (t.supplier || '').trim() || '';
      return `
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3.5 font-mono font-bold text-blue-700">${escapeHTML(t.referenceNumber)}</td>
          <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
          <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
          <td class="px-4 py-3.5 text-center font-mono font-bold text-blue-600">+${t.quantity}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(supplier || '—')}</td>
        </tr>`;
    }
        if (isDefective) {
      const desc = resolveTonerDescription(t.inkCode) || t.description || '';
      const st = String(t.status || 'DEFECTIVE').toUpperCase();
      let statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 border border-rose-200">DEFECTIVE</span>';
      if (st === 'SENT_TO_SUPPLIER') {
        statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-900 border border-amber-200">SENT TO SUPPLIER</span>';
      } else if (st === 'REPLACED') {
        statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">REPLACED</span>';
      }
      const actions = `<button type="button" class="btn-def-view text-xs font-semibold text-blue-700 hover:underline" data-ref="${escapeHTML(t.referenceNumber)}">View</button>`;
      return `
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3.5 font-mono font-bold text-rose-700">${escapeHTML(t.referenceNumber)}</td>
          <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
          <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
          <td class="px-4 py-3.5">${statusBadge}</td>
          <td class="px-4 py-3.5">${actions}</td>
        </tr>`;
    }
    const desc = resolveTonerDescription(t.inkCode) || t.description || '';
    const defBadge = t.defective
      ? ' <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-700">DEFECTIVE</span>'
      : '';
    return `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-4 py-3.5 font-mono font-bold text-emerald-700">${escapeHTML(t.referenceNumber)}${defBadge}</td>
        <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
        <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
        <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
        <td class="px-4 py-3.5">
          <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-700 border border-slate-200">${escapeHTML(t.department || '—')}</span>
        </td>
        <td class="px-4 py-3.5">
          <button type="button" class="btn-rel-view text-xs font-semibold text-blue-700 hover:underline" data-ref="${escapeHTML(t.referenceNumber)}" data-id="${escapeHTML(t.id || '')}">View</button>
        </td>
      </tr>`;
  }).join('');
}

export function getFilteredTransactionsForExport() {
  const typeFilter = AppState.filters.transactionType || 'RECEIVED';
  const search = (AppState.filters.transactionSearch || '').toLowerCase().trim();
  const dateFilter = AppState.filters.transactionDate || 'ALL';
  const deptFilter = AppState.filters.transactionDept || 'ALL';

  return (AppState.transactions || []).filter(t => {
    if (typeFilter === 'RECEIVED' && t.type !== 'RECEIVED') return false;
    if (typeFilter === 'RELEASED' && t.type !== 'RELEASED') return false;
    if (typeFilter === 'DEFECTIVE' && t.type !== 'DEFECTIVE') return false;

    if (!isDateInFilter(t.date || t.createdAt, dateFilter)) return false;

    if (typeFilter === 'RELEASED' && deptFilter !== 'ALL') {
      if ((t.department || '').toUpperCase() !== deptFilter.toUpperCase()) return false;
    }

    if (search) {
      const hay = [
        t.referenceNumber, t.inkCode, t.supplier, t.department, t.location, t.purpose, t.givenTo
      ].join(' ').toLowerCase();
      if (!hay.includes(search)) return false;
    }
    return true;
  });
}

export function exportTransactionsToCSV() {
  const txns = getFilteredTransactionsForExport();
  if (!txns || txns.length === 0) {
    showToast('No transactions match the current filters.', 'warning');
    return;
  }

  const headers = [
    'Transaction ID', 'Reference Number', 'Type', 'Date', 'Toner Code', 'Description',
    'Quantity', 'Supplier', 'Department', 'Location', 'Issued By', 'Recorded By', 'Purpose', 'Status', 'Defective'
  ];
  const rows = txns.map(t => [
    t.id,
    t.referenceNumber,
    t.type,
    t.date || (t.createdAt || '').split('T')[0],
    t.inkCode,
    resolveTonerDescription(t.inkCode) || t.description || '',
    t.quantity,
    resolveTonerSupplier(t.inkCode) || (t.supplier || '').trim() || '',
    t.department || '',
    t.location || '',
    t.issuedBy || '',
    t.recordedBy || '',
    t.purpose || '',
    t.status || 'RECORDED',
    t.defective ? 'YES' : ''
  ]);

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(val => `"${String(val ?? '').replace(/"/g, '""')}"`).join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  const dateTag = new Date().toISOString().split('T')[0];
  const filterTag = (AppState.filters.transactionDate || 'ALL').toLowerCase();
  link.setAttribute('href', url);
  link.setAttribute('download', `transactions_${filterTag}_${dateTag}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast(`Exported ${txns.length} filtered transaction(s).`, 'success');
}

export function initTransactionsViewListeners() {
  // Transactions Filters, Tabs & Export
  document.querySelectorAll('.txn-main-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      AppState.filters.transactionType = btn.getAttribute('data-txn-tab');
      if (AppState.filters.transactionType === 'RECEIVED') {
        AppState.filters.transactionDept = 'ALL';
      }
      try {
        localStorage.setItem('toner_ui_txn_tab', AppState.filters.transactionType || 'RECEIVED');
        localStorage.setItem('toner_ui_txn_dept', AppState.filters.transactionDept || 'ALL');
      } catch (_) {}
      renderTransactions();
    });
  });

  // Dept tabs are rebuilt dynamically — use delegated click
  const deptTabsHost = document.getElementById('txn-dept-tabs');

  if (deptTabsHost && !deptTabsHost._deptBound) {
    deptTabsHost._deptBound = true;
    deptTabsHost.addEventListener('click', (e) => {
      const btn = e.target.closest('.txn-dept-tab');
      if (!btn) return;
      AppState.filters.transactionDept = btn.getAttribute('data-txn-dept') || 'ALL';
      try { localStorage.setItem('toner_ui_txn_dept', AppState.filters.transactionDept); } catch (_) {}
      renderTransactions();
    });
  }

  const txnsTbody = document.getElementById('txns-tbody');

  if (txnsTbody && !txnsTbody._defActionsBound) {
    txnsTbody._defActionsBound = true;
    txnsTbody.addEventListener('click', (e) => {
      const relViewBtn = e.target.closest('.btn-rel-view');
      if (relViewBtn) {
        openReleaseDetailModal(relViewBtn.getAttribute('data-ref'), relViewBtn.getAttribute('data-id'));
        return;
      }
      const viewBtn = e.target.closest('.btn-def-view');
      if (viewBtn) {
        openDefectiveDetailModal(viewBtn.getAttribute('data-ref'));
        return;
      }
      const sendBtn = e.target.closest('.btn-def-send');
      if (sendBtn) {
        e.preventDefault();
        sendDefectiveToSupplier(sendBtn.getAttribute('data-ref'));
        return;
      }
      const recvBtn = e.target.closest('.btn-def-receive');
      if (recvBtn) {
        e.preventDefault();
        openReceiveReplacementModal(recvBtn.getAttribute('data-ref'), recvBtn.getAttribute('data-code'));
      }
    });
  }

  const txnSearch = document.getElementById('filter-txn-search');

  if (txnSearch) {
    const applyTxnSearch = () => {
      AppState.filters.transactionSearch = txnSearch.value || '';
      renderTransactions();
    };
    txnSearch.addEventListener('input', applyTxnSearch);
    txnSearch.addEventListener('keyup', applyTxnSearch);
    txnSearch.addEventListener('search', applyTxnSearch);
  }

  const txnDate = document.getElementById('filter-txn-date');

  if (txnDate) {
    txnDate.addEventListener('change', (e) => {
      AppState.filters.transactionDate = e.target.value;
      if (e.target.value !== 'CUSTOM') {
        AppState.filters.transactionDateFrom = '';
        AppState.filters.transactionDateTo = '';
      }
      try { localStorage.setItem('toner_ui_txn_date', AppState.filters.transactionDate || 'ALL'); } catch (_) {}
      renderTransactions();
    });
  }

  const btnTxnExport = document.getElementById('btn-export-txns');

  if (btnTxnExport) {
    btnTxnExport.addEventListener('click', exportTransactionsToCSV);
  }

  const btnClearTxnFilters = document.getElementById('btn-clear-txn-filters');

  if (btnClearTxnFilters) {
    btnClearTxnFilters.addEventListener('click', () => {
      AppState.filters.transactionSearch = '';
      AppState.filters.transactionDate = 'ALL';
      AppState.filters.transactionDateFrom = '';
      AppState.filters.transactionDateTo = '';
      AppState.filters.transactionDept = 'ALL';
      const s = document.getElementById('filter-txn-search');
      const d = document.getElementById('filter-txn-date');
      const f = document.getElementById('filter-txn-from');
      const to = document.getElementById('filter-txn-to');
      if (s) s.value = '';
      if (d) d.value = 'ALL';
      if (f) f.value = '';
      if (to) to.value = '';
      if (typeof syncTxnCustomRangeUI === 'function') syncTxnCustomRangeUI();
      if (typeof updateTxnTabUI === 'function') updateTxnTabUI();
      renderTransactions();
    });
  }

  const txnFrom = document.getElementById('filter-txn-from');

  if (txnFrom) {
    txnFrom.addEventListener('change', () => {
      AppState.filters.transactionDateFrom = txnFrom.value || '';
    });
  }

  const txnTo = document.getElementById('filter-txn-to');

  if (txnTo) {
    txnTo.addEventListener('change', () => {
      AppState.filters.transactionDateTo = txnTo.value || '';
    });
  }

  const btnTxnApplyRange = document.getElementById('btn-txn-apply-range');

  if (btnTxnApplyRange) {
    btnTxnApplyRange.addEventListener('click', () => {
      AppState.filters.transactionDate = 'CUSTOM';
      AppState.filters.transactionDateFrom = document.getElementById('filter-txn-from')?.value || '';
      AppState.filters.transactionDateTo = document.getElementById('filter-txn-to')?.value || '';
      const sel = document.getElementById('filter-txn-date');
      if (sel) sel.value = 'CUSTOM';
      if (!AppState.filters.transactionDateFrom && !AppState.filters.transactionDateTo) {
        showToast('Select a From and/or To date for the custom range.', 'warning');
        return;
      }
      if (AppState.filters.transactionDateFrom && AppState.filters.transactionDateTo
          && AppState.filters.transactionDateFrom > AppState.filters.transactionDateTo) {
        showToast('From date cannot be after To date.', 'warning');
        return;
      }
      renderTransactions();
    });
  }
}

Object.assign(window, {
  syncTxnCustomRangeUI,
  updateTxnTabUI,
  renderTxnDeptTabs,
  renderTransactions,
  getFilteredTransactionsForExport,
  exportTransactionsToCSV,
  initTransactionsViewListeners
});
