/**
 * View: dashboard
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { RELEASE_LOCATIONS, STOCK_STATUS } from "../data/constants.js";
import { closeKpiDetail, openKpiDetail } from "../modals/kpi-detail.js";
import { closeLifespanDetail, openLifespanDetail } from "../modals/lifespan-detail.js";
import { getKnownDepartments, getStockStatus, isDateInFilter, resolveTonerDescription } from "../services/stock-utils.js";

// ==========================================
// 7. NAVIGATION
// ==========================================

// ==========================================
// 8. DASHBOARD
// ==========================================
export function renderDashboard() {
  syncDashCustomRangeUI();
  const inks = AppState.inks;
  // Inventory levels = current snapshot (not period-filtered)
  const totalSkus = inks.length;
  const totalStock = inks.reduce((acc, item) => acc + (Number(item.quantity) || 0), 0);

  let lowStockCount = 0;
  let outStockCount = 0;
  inks.forEach(item => {
    const status = getStockStatus(item.quantity, item.reorderLevel);
    if (status === STOCK_STATUS.OUT_OF_STOCK) outStockCount++;
    else if (status === STOCK_STATUS.LOW_STOCK) lowStockCount++;
  });

  // Movement KPIs / activity use selected dashboard period
  const transactions = getDashboardFilteredTransactions();
  const periodDeliveries = transactions.filter(t => t.type === 'RECEIVED');
  const periodReleases = transactions.filter(t => t.type === 'RELEASED');

  const todayDelUnits = periodDeliveries.reduce((sum, t) => sum + (Number(t.quantity) || 0), 0);
  const todayRelUnits = periodReleases.reduce((sum, t) => sum + (Number(t.quantity) || 0), 0);

  const uniqueDelTickets = new Set(periodDeliveries.map(t => t.referenceNumber)).size;
  const uniqueRelTickets = new Set(periodReleases.map(t => t.referenceNumber)).size;

  const totalTicketsProcessed = new Set(transactions.map(t => t.referenceNumber)).size;

  const latestTxn = transactions.length > 0
    ? [...transactions].sort((a, b) => String(b.date || b.createdAt).localeCompare(String(a.date || a.createdAt)))[0]
    : null;

  document.getElementById('kpi-total-skus').textContent = totalSkus;
  document.getElementById('kpi-total-stock').textContent = totalStock.toLocaleString();
  document.getElementById('kpi-low-stock').textContent = lowStockCount;
  document.getElementById('kpi-out-stock').textContent = outStockCount;
  document.getElementById('kpi-today-deliveries').textContent = `${todayDelUnits} units`;
  document.getElementById('kpi-today-del-tickets').textContent = `${uniqueDelTickets} ticket(s) in period`;
  document.getElementById('kpi-today-releases').textContent = `${todayRelUnits} units`;
  document.getElementById('kpi-today-rel-tickets').textContent = `${uniqueRelTickets} ticket(s) in period`;
  document.getElementById('kpi-total-tickets').textContent = totalTicketsProcessed;

  if (latestTxn) {
    document.getElementById('kpi-last-ticket').textContent = latestTxn.referenceNumber;
    document.getElementById('kpi-last-ticket-time').textContent = `${latestTxn.type}: ${latestTxn.quantity}x ${latestTxn.inkCode}`;
  } else {
    document.getElementById('kpi-last-ticket').textContent = 'None';
    document.getElementById('kpi-last-ticket-time').textContent = 'Awaiting first transaction';
  }

  // Movement totals (all time)
  let totalReceived = 0, totalReleased = 0;
  transactions.forEach(t => {
    const q = Number(t.quantity) || 0;
    if (t.type === 'RECEIVED') totalReceived += q;
    else if (t.type === 'RELEASED') totalReleased += q;
  });
  const net = totalReceived - totalReleased;
  const elRec = document.getElementById('dash-sum-received');
  const elRel = document.getElementById('dash-sum-released');
  const elNet = document.getElementById('dash-sum-net');
  if (elRec) elRec.textContent = '+' + totalReceived.toLocaleString();
  if (elRel) elRel.textContent = '-' + totalReleased.toLocaleString();
  if (elNet) {
    elNet.textContent = (net >= 0 ? '+' : '') + net.toLocaleString();
    elNet.className = 'text-3xl font-bold mt-2 ' + (net >= 0 ? 'text-blue-600' : 'text-amber-600');
  }

  // Attention table (aggregate by toner code)
  const attBody = document.getElementById('dash-attention-tbody');
  if (attBody) {
    const byCode = {};
    AppState.inks.forEach(item => {
      const code = (item.inkCode || '').trim();
      if (!code) return;
      const k = code.toUpperCase();
      if (!byCode[k]) {
        byCode[k] = { inkCode: code, quantity: 0, reorderLevel: 0, supplier: item.supplier || '—' };
      }
      byCode[k].quantity += Number(item.quantity) || 0;
      byCode[k].reorderLevel = Math.max(byCode[k].reorderLevel, Number(item.reorderLevel) || 0);
      if (item.supplier) byCode[k].supplier = item.supplier;
    });
    const lowOrOut = Object.values(byCode).filter(item => {
      const s = getStockStatus(item.quantity, item.reorderLevel);
      return s === STOCK_STATUS.OUT_OF_STOCK || s === STOCK_STATUS.LOW_STOCK;
    }).sort((a, b) => a.quantity - b.quantity);

    if (lowOrOut.length === 0) {
      attBody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-emerald-600 font-medium">All toners are currently well-stocked.</td></tr>`;
    } else {
      attBody.innerHTML = lowOrOut.map(item => {
        const isOut = item.quantity <= 0;
        const badge = isOut
          ? `<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-800">OUT OF STOCK</span>`
          : `<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800">LOW STOCK</span>`;
        return `<tr class="hover:bg-slate-50">
          <td class="px-4 py-3 font-mono font-bold text-slate-900">${escapeHTML(item.inkCode)}</td>
          <td class="px-4 py-3 text-right font-mono font-bold ${isOut ? 'text-rose-600' : 'text-amber-600'}">${item.quantity}</td>
          <td class="px-4 py-3 text-right font-mono text-slate-500">${item.reorderLevel}</td>
          <td class="px-4 py-3">${badge}</td>
          <td class="px-4 py-3 text-xs text-slate-600">${escapeHTML(item.supplier)}</td>
        </tr>`;
      }).join('');
    }
  }

  renderCharts();


  // Render recent 5 transactions
  const recentTxns = [...transactions].reverse().slice(0, 5);
  const tbody = document.getElementById('dash-recent-txns-tbody');
  if (tbody) {
    if (recentTxns.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-6 text-slate-400">No toner movements recorded yet.</td></tr>`;
    } else {
      tbody.innerHTML = recentTxns.map(t => {
        const isReceived = t.type === 'RECEIVED';
        const typeBadge = isReceived
          ? `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-blue-100 text-blue-800">RECEIVED</span>`
          : `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800">RELEASED</span>`;
        return `
          <tr class="hover:bg-slate-50/70 transition-colors">
            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-xs">${escapeHTML(t.referenceNumber)}</td>
            <td class="px-4 py-3">${typeBadge}</td>
            <td class="px-4 py-3 font-semibold font-mono text-slate-900">${escapeHTML(t.inkCode)}</td>
            <td class="px-4 py-3 text-xs">${escapeHTML(t.department || '—')}</td>
            <td class="px-4 py-3 text-xs text-slate-600">${escapeHTML(t.location || t.supplier || '—')}</td>
            <td class="px-4 py-3 text-right font-bold ${isReceived ? 'text-blue-600' : 'text-emerald-600'}">${isReceived ? '+' : '-'}${t.quantity}</td>
            <td class="px-4 py-3 text-xs text-slate-500">${formatDate(t.date)}</td>
          </tr>
        `;
      }).join('');
    }
  }
}

export function getDashboardFilteredTransactions() {
  const filterType = AppState.filters.dashboardDate || 'ALL';
  const from = AppState.filters.dashboardDateFrom || '';
  const to = AppState.filters.dashboardDateTo || '';
  return (AppState.transactions || []).filter(t =>
    isDateInFilter(t.date || t.createdAt, filterType, from, to)
  );
}

export function syncDashCustomRangeUI() {
  const wrap = document.getElementById('dash-custom-range');
  const isCustom = AppState.filters.dashboardDate === 'CUSTOM';
  if (wrap) {
    if (isCustom) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
  const from = document.getElementById('filter-dash-from');
  const to = document.getElementById('filter-dash-to');
  if (from) from.value = AppState.filters.dashboardDateFrom || '';
  if (to) to.value = AppState.filters.dashboardDateTo || '';
  const sel = document.getElementById('filter-dash-date');
  if (sel && sel.value !== AppState.filters.dashboardDate) {
    sel.value = AppState.filters.dashboardDate || 'ALL';
  }
  const label = document.getElementById('dash-period-label');
  if (label) {
    const map = { ALL: 'All time', TODAY: 'Today', WEEK: 'Last 7 days', MONTH: 'This month', CUSTOM: 'Custom range' };
    let text = map[AppState.filters.dashboardDate] || 'This month';
    if (AppState.filters.dashboardDate === 'CUSTOM' && (AppState.filters.dashboardDateFrom || AppState.filters.dashboardDateTo)) {
      text = (AppState.filters.dashboardDateFrom || '…') + ' → ' + (AppState.filters.dashboardDateTo || '…');
    }
    label.textContent = text;
  }
}

// ==========================================
// 14. REPORTS
// ==========================================
export function renderReports() {
  // Reports page removed — analytics live on Dashboard
  if (typeof renderDashboard === 'function' && AppState.currentPage === 'dashboard') {
    /* no-op; dashboard already refreshes */
  }
}

// 17. CHARTS (Chart.js Integration)
// ==========================================
export function renderCharts() {
  // Lifespan cards do not need Chart.js
  if (typeof renderAvgYieldChart === 'function') renderAvgYieldChart();
  if (typeof Chart === 'undefined') return;
  renderDepartmentDemandChart();
  renderStockStatusChart();
}

export function renderAvgYieldChart() {
  if (AppState.charts && AppState.charts.avgYield) {
    try { AppState.charts.avgYield.destroy(); } catch (_) {}
    AppState.charts.avgYield = null;
  }

  const grid = document.getElementById('dept-lifespan-grid');
  const kpi = document.getElementById('kpi-avg-yield');
  if (!grid) return;

  const filterType = (AppState.filters && AppState.filters.dashboardDate) || 'ALL';
  const from = (AppState.filters && AppState.filters.dashboardDateFrom) || '';
  const to = (AppState.filters && AppState.filters.dashboardDateTo) || '';
  const MS_DAY = 86400000;
  const esc = (s) => (typeof escapeHTML === 'function' ? escapeHTML(s) : String(s || '').replace(/</g, '&lt;'));

  // Master locations → departments list (show all departments immediately)
  const master = (AppState.releaseLocations && AppState.releaseLocations.length)
    ? AppState.releaseLocations.filter(r => r.isActive !== false)
    : ((typeof RELEASE_LOCATIONS !== 'undefined' ? RELEASE_LOCATIONS : []).map(r => ({
        department: r.department,
        location: r.location,
        printerName: r.printerName || '',
        ipAddress: r.ipAddress || ''
      })));

  // deptKey -> { department, locations: Map(locKey -> { location, printerName, ipAddress }) }
  const deptMap = new Map();
  function ensureDept(dept) {
    const dk = (dept || '—').trim().toUpperCase() || '—';
    if (!deptMap.has(dk)) {
      deptMap.set(dk, {
        department: (dept || '—').trim() || '—',
        locations: new Map()
      });
    }
    return deptMap.get(dk);
  }
  function ensureLoc(deptEntry, loc, printerName, ipAddress) {
    const lk = (loc || '—').trim().toUpperCase() || '—';
    if (!deptEntry.locations.has(lk)) {
      deptEntry.locations.set(lk, {
        location: (loc || '—').trim() || '—',
        printerName: (printerName || '').trim(),
        ipAddress: (ipAddress || '').trim()
      });
    } else {
      const existing = deptEntry.locations.get(lk);
      if (!existing.printerName && printerName) existing.printerName = String(printerName).trim();
      if (!existing.ipAddress && ipAddress) existing.ipAddress = String(ipAddress).trim();
    }
  }

  master.forEach(r => {
    const dept = (r.department || '').trim() || '—';
    const loc = (r.location || '').trim();
    if (!loc) return;
    const d = ensureDept(dept);
    ensureLoc(d, loc, r.printerName || r.printer_name, r.ipAddress || r.ip_address);
  });

  (AppState.transactions || []).forEach(t => {
    if (t.type !== 'RELEASED') return;
    const loc = (t.location || '').trim();
    if (!loc) return;
    const dept = (t.department || '').trim() || '—';
    const d = ensureDept(dept);
    ensureLoc(d, loc, t.locationPrinter || '', '');
  });

  // Pair consecutive RELEASED same toner + same location → day intervals
  const allReleased = (AppState.transactions || [])
    .filter(t => t.type === 'RELEASED')
    .map(t => {
      const d = (t.date || (t.createdAt || '').slice(0, 10) || '').trim();
      const yRaw = t.actualYield != null ? t.actualYield : (t.actual_yield != null ? t.actual_yield : null);
      const yieldPages = yRaw != null && yRaw !== '' && Number.isFinite(Number(yRaw)) ? Number(yRaw) : null;
      return {
        code: ((t.inkCode || '').toUpperCase() || 'UNKNOWN'),
        location: ((t.location || '').trim() || '—'),
        department: ((t.department || '').trim() || '—'),
        date: d,
        ts: d ? new Date(d + 'T12:00:00').getTime() : NaN,
        ref: t.referenceNumber || t.id || '',
        yieldPages
      };
    })
    .filter(t => t.date && Number.isFinite(t.ts));

  const pairs = {};
  allReleased.forEach(t => {
    const key = t.code + '|||' + t.location.toUpperCase();
    if (!pairs[key]) pairs[key] = [];
    pairs[key].push(t);
  });

  // locationStats: deptKey|||locKey -> { sum, n, yieldSum, yieldN, intervals: [] }
  const locationStats = {};
  let totalSum = 0, totalN = 0;
  let totalYieldSum = 0, totalYieldN = 0;

  Object.values(pairs).forEach(list => {
    list.sort((a, b) => a.ts - b.ts);
    for (let i = 1; i < list.length; i++) {
      const prev = list[i - 1];
      const curr = list[i];
      if (typeof isDateInFilter === 'function') {
        if (!isDateInFilter(curr.date, filterType, from, to)) continue;
      }
      const days = Math.round((curr.ts - prev.ts) / MS_DAY);
      if (!Number.isFinite(days) || days < 0) continue;
      const deptKey = curr.department.toUpperCase();
      const locKey = curr.location.toUpperCase();
      const k = deptKey + '|||' + locKey;
      if (!locationStats[k]) locationStats[k] = { sum: 0, n: 0, yieldSum: 0, yieldN: 0, intervals: [] };
      locationStats[k].sum += days;
      locationStats[k].n += 1;
      totalSum += days;
      totalN += 1;
      // Page yield for this change cycle: prefer yield logged on the replacement (curr), else previous
      const yPages = curr.yieldPages != null ? curr.yieldPages : (prev.yieldPages != null ? prev.yieldPages : null);
      const tonerDesc = (typeof resolveTonerDescription === 'function'
        ? resolveTonerDescription(curr.code)
        : '') || curr.code;
      locationStats[k].intervals.push({
        toner: curr.code,
        description: tonerDesc,
        from: prev.date,
        to: curr.date,
        days,
        yieldPages: yPages,
        fromRef: prev.ref,
        toRef: curr.ref
      });
      // ensure location exists under department
      const d = ensureDept(curr.department);
      ensureLoc(d, curr.location, '', '');
    }
  });

  // Aggregate page yield from ALL released rows in period (works even with a single issuance)
  allReleased.forEach(t => {
    if (typeof isDateInFilter === 'function') {
      if (!isDateInFilter(t.date, filterType, from, to)) return;
    }
    if (t.yieldPages == null || !Number.isFinite(t.yieldPages) || t.yieldPages < 0) return;
    const deptKey = t.department.toUpperCase();
    const locKey = t.location.toUpperCase();
    const k = deptKey + '|||' + locKey;
    if (!locationStats[k]) locationStats[k] = { sum: 0, n: 0, yieldSum: 0, yieldN: 0, intervals: [] };
    locationStats[k].yieldSum += t.yieldPages;
    locationStats[k].yieldN += 1;
    totalYieldSum += t.yieldPages;
    totalYieldN += 1;
    const d = ensureDept(t.department);
    ensureLoc(d, t.location, '', '');
  });

  // Build department aggregates
  const byDept = {}; // deptKey -> { department, sum, n, yieldSum, yieldN, locations: [...] }
  deptMap.forEach((meta, deptKey) => {
    byDept[deptKey] = {
      department: meta.department,
      sum: 0,
      n: 0,
      yieldSum: 0,
      yieldN: 0,
      locations: []
    };
    meta.locations.forEach((locMeta, locKey) => {
      const sk = deptKey + '|||' + locKey;
      const st = locationStats[sk];
      const avg = st && st.n > 0 ? Math.round(st.sum / st.n) : null;
      const avgYield = st && st.yieldN > 0 ? Math.round(st.yieldSum / st.yieldN) : null;
      byDept[deptKey].locations.push({
        location: locMeta.location,
        printerName: locMeta.printerName,
        ipAddress: locMeta.ipAddress,
        avg,
        avgYield,
        n: st ? st.n : 0,
        yieldN: st ? st.yieldN : 0,
        intervals: st ? st.intervals.slice() : []
      });
      if (st) {
        byDept[deptKey].sum += st.sum;
        byDept[deptKey].n += st.n;
        byDept[deptKey].yieldSum += st.yieldSum || 0;
        byDept[deptKey].yieldN += st.yieldN || 0;
      }
    });
    byDept[deptKey].locations.sort((a, b) => {
      if (a.avg != null && b.avg != null) return b.avg - a.avg;
      if (a.avg != null) return -1;
      if (b.avg != null) return 1;
      return a.location.localeCompare(b.location);
    });
  });

  window.__lifespanByDept = byDept;

  if (kpi) {
    const dayPart = totalN > 0
      ? `${Math.round(totalSum / totalN).toLocaleString()} days (${totalN} change${totalN === 1 ? '' : 's'})`
      : null;
    const yieldPart = totalYieldN > 0
      ? `${Math.round(totalYieldSum / totalYieldN).toLocaleString()} pages avg (${totalYieldN} reading${totalYieldN === 1 ? '' : 's'})`
      : null;
    if (dayPart && yieldPart) kpi.textContent = `Overall: ${dayPart} · ${yieldPart}`;
    else if (dayPart) kpi.textContent = `Overall: ${dayPart}`;
    else if (yieldPart) kpi.textContent = `Overall: ${yieldPart}`;
    else kpi.textContent = 'Overall: —';
  }

  const entries = Object.keys(byDept).map(dk => {
    const d = byDept[dk];
    return {
      key: dk,
      department: d.department,
      avg: d.n > 0 ? Math.round(d.sum / d.n) : null,
      avgYield: d.yieldN > 0 ? Math.round(d.yieldSum / d.yieldN) : null,
      n: d.n,
      yieldN: d.yieldN,
      locCount: d.locations.length,
      locWithData: d.locations.filter(l => l.avg != null).length,
      locWithYield: d.locations.filter(l => l.avgYield != null).length
    };
  }).sort((a, b) => {
    if (a.avg != null && b.avg != null) return b.avg - a.avg;
    if (a.avg != null) return -1;
    if (b.avg != null) return 1;
    if (a.avgYield != null && b.avgYield != null) return b.avgYield - a.avgYield;
    if (a.avgYield != null) return -1;
    if (b.avgYield != null) return 1;
    return a.department.localeCompare(b.department);
  });

  if (entries.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full py-10 text-center">
        <p class="text-sm text-slate-500 font-medium">No departments yet</p>
        <p class="text-xs text-slate-400 mt-1">Add locations under Departments &amp; Locations so they appear here.</p>
      </div>`;
    return;
  }

  const withData = entries.filter(e => e.avg != null);
  const maxAvg = withData.length ? Math.max(...withData.map(e => e.avg), 1) : 1;

  grid.innerHTML = entries.map(e => {
    const hasData = e.avg != null;
    let badge = 'bg-slate-100 text-slate-600';
    let numColor = 'text-slate-400';
    let numText = '—';
    if (hasData) {
      numText = String(e.avg);
      const ratio = e.avg / maxAvg;
      if (ratio <= 0.33) { badge = 'bg-amber-50 text-amber-800'; numColor = 'text-amber-700'; }
      else if (ratio <= 0.66) { badge = 'bg-sky-50 text-sky-800'; numColor = 'text-sky-700'; }
      else { badge = 'bg-emerald-50 text-emerald-800'; numColor = 'text-emerald-700'; }
    }
    const dept = esc(e.department);
    const hasYield = e.avgYield != null;
    const sub = hasData
      ? `${e.locWithData} of ${e.locCount} location${e.locCount === 1 ? '' : 's'} · click for breakdown`
      : (hasYield
        ? `${e.locWithYield} location${e.locWithYield === 1 ? '' : 's'} with yield · click for details`
        : `${e.locCount} location${e.locCount === 1 ? '' : 's'} · click for details`);
    const yieldLine = hasYield
      ? `<div class="flex items-baseline gap-1.5">
          <span class="text-lg font-bold tracking-tight text-indigo-700 leading-none">${Number(e.avgYield).toLocaleString()}</span>
          <span class="text-xs font-medium text-slate-500">pages avg yield</span>
        </div>`
      : `<div class="text-xs text-slate-400">Page yield: —</div>`;
    return `
      <button type="button" data-lifespan-dept="${esc(e.key)}"
        class="lifespan-dept-card text-left rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col gap-1.5 min-h-[8.5rem] w-full hover:border-slate-400 hover:bg-white hover:shadow-sm transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400">
        <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Department</div>
        <div class="text-sm font-bold text-slate-800 truncate" title="${dept}">${dept}</div>
        <div class="flex items-baseline gap-1.5 mt-auto pt-1">
          <span class="text-3xl font-bold tracking-tight ${numColor} leading-none">${numText}</span>
          <span class="text-sm font-medium text-slate-500">days avg</span>
        </div>
        ${yieldLine}
        <div class="flex items-center justify-between gap-2">
          <span class="text-[11px] text-slate-400">${sub}</span>
          <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${badge}">${hasData || hasYield ? 'dept lifespan' : 'awaiting data'}</span>
        </div>
      </button>`;
  }).join('');
}

export function renderStockStatusChart() {
  const canvas = document.getElementById('chart-stock-status');
  if (!canvas) return;
  if (AppState.charts.brand) {
    AppState.charts.brand.destroy();
  }

  // Aggregate unique toner codes
  const byCode = {};
  AppState.inks.forEach(item => {
    const code = (item.inkCode || '').trim();
    if (!code) return;
    const k = code.toUpperCase();
    if (!byCode[k]) byCode[k] = { quantity: 0, reorderLevel: 0 };
    byCode[k].quantity += Number(item.quantity) || 0;
    byCode[k].reorderLevel = Math.max(byCode[k].reorderLevel, Number(item.reorderLevel) || 0);
  });

  let inStock = 0, lowStock = 0, outStock = 0;
  Object.values(byCode).forEach(item => {
    const s = getStockStatus(item.quantity, item.reorderLevel);
    if (s === STOCK_STATUS.IN_STOCK) inStock++;
    else if (s === STOCK_STATUS.LOW_STOCK) lowStock++;
    else outStock++;
  });

  AppState.charts.brand = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['In Stock', 'Low Stock', 'Out of Stock'],
      datasets: [{
        data: [inStock, lowStock, outStock],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 }, padding: 12 } }
      }
    }
  });
}

export function renderDepartmentDemandChart() {
  const canvas = document.getElementById('chart-activity');
  if (!canvas) return;

  if (AppState.charts.activity) {
    AppState.charts.activity.destroy();
  }

  // Frequency of toner need = units RELEASED per department (dashboard period)
  const periodTxns = (typeof getDashboardFilteredTransactions === 'function')
    ? getDashboardFilteredTransactions()
    : (AppState.transactions || []);
  const demand = {};
  periodTxns
    .filter(t => t.type === 'RELEASED')
    .forEach(t => {
      const d = (t.department || '').trim();
      if (!d) return; // skip — no "Other"
      demand[d] = (demand[d] || 0) + (Number(t.quantity) || 0);
    });

  const releaseEvents = {};
  const seen = new Set();
  periodTxns
    .filter(t => t.type === 'RELEASED')
    .forEach(t => {
      const d = (t.department || '').trim();
      if (!d) return; // skip — no "Other"
      const ref = normalizeRefNumber(t.referenceNumber);
      const key = d + '::' + ref;
      if (!seen.has(key)) {
        seen.add(key);
        releaseEvents[d] = (releaseEvents[d] || 0) + 1;
      }
    });

  // Include departments from Locations master even with 0 releases (new locations)
  getKnownDepartments().forEach(d => {
    if (demand[d] === undefined) demand[d] = 0;
  });
  // Sort by units issued (most frequent demand first)
  const labels = Object.keys(demand).sort((a, b) => demand[b] - demand[a]);
  const data = labels.map(l => demand[l]);
  const eventData = labels.map(l => releaseEvents[l] || 0);

  // If no release history yet, show empty state message on chart
  if (labels.length === 0) {
    AppState.charts.activity = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: ['No release data yet'],
        datasets: [{ label: 'Units issued', data: [0], backgroundColor: '#cbd5e1', borderRadius: 6 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 5 } }
      }
    });
    return;
  }

  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#84cc16'];

  AppState.charts.activity = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Units issued',
          data: data,
          backgroundColor: labels.map((_, i) => colors[i % colors.length]),
          borderRadius: 6
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const dept = ctx.label;
              const units = ctx.parsed.x;
              const events = releaseEvents[dept] || 0;
              return [
                ` Units issued: ${units}`,
                ` Release tickets: ${events}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { stepSize: 1 },
          title: { display: true, text: 'Toner units issued', font: { size: 11 } }
        },
        y: {
          ticks: { font: { size: 12, weight: '600' } }
        }
      }
    }
  });
}

export function renderReportCharts(filteredTxns) {
  if (typeof Chart === 'undefined') return;

  // 1. Status Doughnut Chart
  const statusCanvas = document.getElementById('chart-report-status');
  if (statusCanvas) {
    if (AppState.charts.reportStatus) AppState.charts.reportStatus.destroy();

    let inStock = 0, lowStock = 0, outStock = 0;
    AppState.inks.forEach(item => {
      const s = getStockStatus(item.quantity, item.reorderLevel);
      if (s === STOCK_STATUS.IN_STOCK) inStock++;
      else if (s === STOCK_STATUS.LOW_STOCK) lowStock++;
      else if (s === STOCK_STATUS.OUT_OF_STOCK) outStock++;
    });

    AppState.charts.reportStatus = new Chart(statusCanvas, {
      type: 'pie',
      data: {
        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
          data: [inStock, lowStock, outStock],
          backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // 2. Department Consumption Chart
  const deptCanvas = document.getElementById('chart-report-dept');
  if (deptCanvas) {
    if (AppState.charts.reportDept) AppState.charts.reportDept.destroy();

    const deptCounts = {};
    filteredTxns.filter(t => t.type === 'RELEASED').forEach(t => {
      const dept = (t.department || '').trim();
      if (!dept) return; // skip — no "Other"
      deptCounts[dept] = (deptCounts[dept] || 0) + (Number(t.quantity) || 0);
    });

    const deptLabels = Object.keys(deptCounts);
    const deptData = Object.values(deptCounts);

    AppState.charts.reportDept = new Chart(deptCanvas, {
      type: 'polarArea',
      data: {
        labels: deptLabels.length > 0 ? deptLabels : ['No releases in period'],
        datasets: [{
          data: deptData.length > 0 ? deptData : [0],
          backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#10b981']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right' }
        }
      }
    });
  }
}

export function initDashboardViewListeners() {
  const dashDate = document.getElementById('filter-dash-date');

  if (dashDate) {
    dashDate.addEventListener('change', (e) => {
      AppState.filters.dashboardDate = e.target.value || 'ALL';
      if (AppState.filters.dashboardDate !== 'CUSTOM') {
        AppState.filters.dashboardDateFrom = '';
        AppState.filters.dashboardDateTo = '';
      }
      syncDashCustomRangeUI();
      renderDashboard();
      renderCharts();
    });
  }

  const dashFrom = document.getElementById('filter-dash-from');

  if (dashFrom) dashFrom.addEventListener('change', () => {
    AppState.filters.dashboardDateFrom = dashFrom.value || '';
  });

  const dashTo = document.getElementById('filter-dash-to');

  if (dashTo) dashTo.addEventListener('change', () => {
    AppState.filters.dashboardDateTo = dashTo.value || '';
  });

  const btnDashApply = document.getElementById('btn-dash-apply-range');

  if (btnDashApply) {
    btnDashApply.addEventListener('click', () => {
      AppState.filters.dashboardDate = 'CUSTOM';
      AppState.filters.dashboardDateFrom = document.getElementById('filter-dash-from')?.value || '';
      AppState.filters.dashboardDateTo = document.getElementById('filter-dash-to')?.value || '';
      const sel = document.getElementById('filter-dash-date');
      if (sel) sel.value = 'CUSTOM';
      syncDashCustomRangeUI();
      renderDashboard();
      renderCharts();
    });
  }

  document.querySelectorAll('.kpi-card').forEach(card => {
    card.addEventListener('click', () => openKpiDetail(card.getAttribute('data-kpi')));
  });

  const btnCloseKpi = document.getElementById('btn-close-kpi-detail');

  if (btnCloseKpi) btnCloseKpi.addEventListener('click', closeKpiDetail);

  const kpiBackdrop = document.getElementById('modal-kpi-detail-backdrop');

  if (kpiBackdrop) kpiBackdrop.addEventListener('click', closeKpiDetail);

  // Lifespan location cards → detail modal
  const lifespanGrid = document.getElementById('dept-lifespan-grid');

  if (lifespanGrid && !lifespanGrid.dataset.lifespanBound) {
    lifespanGrid.dataset.lifespanBound = '1';
    lifespanGrid.addEventListener('click', (ev) => {
      const card = ev.target.closest('[data-lifespan-dept]');
      if (!card) return;
      const key = card.getAttribute('data-lifespan-dept');
      if (key && typeof openLifespanDetail === 'function') openLifespanDetail(key);
    });
  }

  const btnCloseLifespan = document.getElementById('btn-close-lifespan-detail');

  if (btnCloseLifespan) btnCloseLifespan.addEventListener('click', closeLifespanDetail);

  const lifespanBackdrop = document.getElementById('modal-lifespan-detail-backdrop');

  if (lifespanBackdrop) lifespanBackdrop.addEventListener('click', closeLifespanDetail);

  // Reports
  const reportFilterDate = document.getElementById('report-date-filter') || document.getElementById('report-filter-date');

  if (reportFilterDate) {
    reportFilterDate.addEventListener('change', (e) => {
      AppState.filters.reportDate = e.target.value;
      renderReports();
    });
  }

  const btnPrintReport = document.getElementById('btn-print-report');

  if (btnPrintReport) {
    btnPrintReport.addEventListener('click', () => {
      window.print();
    });
  }
}

Object.assign(window, {
  renderDashboard,
  getDashboardFilteredTransactions,
  syncDashCustomRangeUI,
  renderReports,
  renderCharts,
  renderAvgYieldChart,
  renderStockStatusChart,
  renderDepartmentDemandChart,
  renderReportCharts,
  initDashboardViewListeners
});
