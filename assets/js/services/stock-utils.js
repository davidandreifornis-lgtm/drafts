/**
 * Service: stock-utils
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { AppState } from "../core/state.js";
import { RELEASE_LOCATIONS, STOCK_STATUS } from "../data/constants.js";

// ==========================================
// 9. DATE FILTERING
// ==========================================
export function parseLocalDate(dateStr) {
  if (!dateStr) return null;
  const s = String(dateStr).trim();
  // Prefer YYYY-MM-DD as local calendar date (avoid UTC shift that drops rows from "This Month")
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (m) {
    return new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
  }
  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return null;
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

export function isDateInFilter(dateStr, filterType, fromStrOverride, toStrOverride) {
  if (!filterType || filterType === 'ALL') return true;
  if (!dateStr) return true;

  const itemDay = parseLocalDate(dateStr);
  if (!itemDay) return true;

  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (filterType === 'TODAY') {
    return itemDay.getTime() === today.getTime();
  }

  if (filterType === 'WEEK') {
    const oneWeekAgo = new Date(today);
    oneWeekAgo.setDate(today.getDate() - 7);
    return itemDay >= oneWeekAgo && itemDay <= today;
  }

  if (filterType === 'MONTH') {
    return itemDay.getFullYear() === now.getFullYear() && itemDay.getMonth() === now.getMonth();
  }

  if (filterType === 'CUSTOM') {
    const fromStr = fromStrOverride !== undefined ? fromStrOverride : AppState.filters.transactionDateFrom;
    const toStr = toStrOverride !== undefined ? toStrOverride : AppState.filters.transactionDateTo;
    if (!fromStr && !toStr) return true;

    let ok = true;
    if (fromStr) {
      const fromDay = parseLocalDate(fromStr);
      ok = ok && fromDay && itemDay >= fromDay;
    }
    if (toStr) {
      const toDay = parseLocalDate(toStr);
      ok = ok && toDay && itemDay <= toDay;
    }
    return ok;
  }

  return true;
}

export function txnDateYmd(t) {
  const raw = (t.date || t.createdAt || '').toString();
  if (!raw) return '';
  const s = raw.replace(' ', 'T').split('T')[0];
  return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
}

export function getColorSwatch(color) {
  const c = (color || '').toLowerCase();
  if (c.includes('black')) return 'bg-slate-900 border border-slate-700';
  if (c.includes('cyan')) return 'bg-cyan-400';
  if (c.includes('magenta')) return 'bg-fuchsia-500';
  if (c.includes('yellow')) return 'bg-amber-300';
  return 'bg-gradient-to-r from-cyan-400 via-fuchsia-500 to-amber-300';
}

export function resolveTonerDescription(inkCode) {
  const code = (inkCode || '').toUpperCase().trim();
  if (!code) return '';
  const inv = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code);
  if (inv && inv.description) return String(inv.description).trim();
  return '';
}

export function resolveTonerSupplier(inkCode) {
  const code = (inkCode || '').toUpperCase().trim();
  if (!code) return '';
  const inv = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code);
  if (inv && inv.supplier) return String(inv.supplier).trim();
  return '';
}

export function getKnownDepartments() {
  const set = new Set();
  (AppState.releaseLocations || []).forEach(r => {
    const d = (r.department || '').toUpperCase().trim();
    if (d) set.add(d);
  });
  (AppState.transactions || []).forEach(t => {
    if (t.type !== 'RELEASED' && t.type !== 'DEFECTIVE') return;
    const d = (t.department || '').toUpperCase().trim();
    if (d) set.add(d);
  });
  return [...set].sort();
}

export function parseDefectiveMeta(notes) {
  const raw = String(notes || '');
  const get = (key) => {
    const m = raw.match(new RegExp('\\[' + key + '\\]\\s*(.+)', 'i'));
    return m ? m[1].trim() : '';
  };
  const human = raw.split(/\n/).filter(line => !/^\s*\[(SENT_AT|RECV_AT|ACCEPTED_BY|RECORDED_BY)\]/i.test(line)).join('\n').trim();
  return { sentAt: get('SENT_AT'), receivedAt: get('RECV_AT'), acceptedByMeta: get('ACCEPTED_BY'), recordedByMeta: get('RECORDED_BY'), humanNotes: human };
}

export function getStockStatus(quantity, reorderLevel) {
  const qty = Number(quantity) || 0;
  const reorder = Number(reorderLevel) || 0;
  if (qty <= 0) return STOCK_STATUS.OUT_OF_STOCK;
  if (qty <= reorder) return STOCK_STATUS.LOW_STOCK;
  return STOCK_STATUS.IN_STOCK;
}

export function getUniqueTonerCodes() {
  const map = {};
  (AppState.inks || []).forEach(i => {
    const c = (i.inkCode || i.itemCode || '').trim();
    if (!c) return;
    const k = c.toUpperCase();
    if (!map[k]) {
      map[k] = {
        code: c,
        description: (i.description || '').trim(),
        qty: 0
      };
    }
    if (!map[k].description && i.description) {
      map[k].description = String(i.description).trim();
    }
    map[k].qty += Number(i.quantity) || 0;
  });
  return Object.values(map).sort((a, b) => {
    const da = (a.description || a.code).toLowerCase();
    const db = (b.description || b.code).toLowerCase();
    return da.localeCompare(db);
  });
}

export function resolveDepartmentCode(ticketDept) {
  const d = (ticketDept || '').toUpperCase().trim();
  if (!d) return null;
  if (d === 'ACCT' || d.includes('ACCOUNT') || d.includes('ACCTG')) return 'ACCT';
  if (d === 'BD' || d.includes('BUSINESS DEV')) return 'BD';
  if (d === 'BMS') return 'BMS';
  if (d.includes('LOGISTIC') || d.includes('AMEC ICT') || d === 'LOGISTICS') return 'LOGISTICS';
  if (d.includes('PRODUCT') || d.includes('PACKAG')) return 'PRODUCTION';
  if (d.includes('PURCHAS')) return 'PURCHASING';
  if (d === 'QC' || d.includes('QUALITY') || d.includes('LAB')) return 'QC';
  // Exact department code match
  const codes = [...new Set(RELEASE_LOCATIONS.map(r => r.department))];
  if (codes.includes(d)) return d;
  return null;
}

Object.assign(window, {
  parseLocalDate,
  isDateInFilter,
  txnDateYmd,
  getColorSwatch,
  resolveTonerDescription,
  resolveTonerSupplier,
  getKnownDepartments,
  parseDefectiveMeta,
  getStockStatus,
  getUniqueTonerCodes,
  resolveDepartmentCode
});
