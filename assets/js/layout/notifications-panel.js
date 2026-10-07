/**
 * Layout: notifications-panel
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML, formatDateTime } from "../core/format.js";
import { navigateTo } from "../core/router.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { STOCK_STATUS } from "../data/constants.js";
import { openStockCard } from "../modals/stock-card.js";
import { getStockStatus, parseLocalDate } from "../services/stock-utils.js";
import { renderInventory } from "../views/inventory.js";
import { renderTransactions } from "../views/transactions.js";

// ==========================================
// 16. ALERTS / NOTIFICATIONS
// ==========================================
// Persisted action notifications + live stock alerts
AppState.notifications = AppState.notifications || [];

AppState.notifFilter = AppState.notifFilter || 'ALL';

export const NOTIF_STORAGE_KEY = 'toner_notifications_v1';

export const NOTIF_MAX = 50;

export function loadPersistedNotifications() {
  try {
    const raw = localStorage.getItem(NOTIF_STORAGE_KEY);
    if (!raw) return [];
    const arr = JSON.parse(raw);
    return Array.isArray(arr) ? arr.filter(n => n && n.source !== 'stock') : [];
  } catch (_) {
    return [];
  }
}

export function persistNotifications() {
  try {
    const toSave = (AppState.notifications || [])
      .filter(n => n.source !== 'stock')
      .slice(0, NOTIF_MAX);
    localStorage.setItem(NOTIF_STORAGE_KEY, JSON.stringify(toSave));
  } catch (_) {}
}

export function relativeTime(iso) {
  if (!iso) return '';
  const t = new Date(iso).getTime();
  if (Number.isNaN(t)) return formatDateTime(iso);
  const sec = Math.round((Date.now() - t) / 1000);
  if (sec < 45) return 'Just now';
  if (sec < 3600) return Math.floor(sec / 60) + ' min ago';
  if (sec < 86400) return Math.floor(sec / 3600) + ' hr ago';
  if (sec < 86400 * 7) return Math.floor(sec / 86400) + ' day(s) ago';
  return formatDateTime(iso);
}

export function pushNotification(type, title, message, meta = {}) {
  const n = {
    id: 'n-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
    type: type || 'info', // 'warning' | 'danger' | 'success' | 'info'
    title: title || 'Notification',
    message: message || '',
    time: new Date().toISOString(),
    read: false,
    source: meta.source || 'action',
    action: meta.action || null, // { type: 'inventory'|'stockCard'|'transactions'|'issue', payload }
    inkCode: meta.inkCode || null,
    referenceNumber: meta.referenceNumber || null,
    ...meta
  };
  AppState.notifications = AppState.notifications || [];
  // Dedupe identical action alerts within 30s
  const dup = AppState.notifications.find(x =>
    x.source === n.source && x.title === n.title && x.message === n.message &&
    Math.abs(new Date(x.time) - new Date(n.time)) < 30000
  );
  if (dup) {
    dup.time = n.time;
    dup.read = false;
  } else {
    AppState.notifications.unshift(n);
  }
  if (AppState.notifications.length > NOTIF_MAX) {
    AppState.notifications = AppState.notifications.slice(0, NOTIF_MAX);
  }
  persistNotifications();
  renderNotifications();
  // Browser notification for critical out-of-stock (when tab not focused)
  if (n.type === 'danger' && document.hidden && typeof Notification !== 'undefined' && Notification.permission === 'granted') {
    try {
      new Notification(n.title, { body: n.message, tag: n.id });
    } catch (_) {}
  }
  return n;
}

export function markNotificationRead(id) {
  const n = (AppState.notifications || []).find(x => x.id === id);
  if (n) {
    n.read = true;
    persistNotifications();
    renderNotifications();
  }
}

export function markAllNotificationsRead() {
  (AppState.notifications || []).forEach(n => { n.read = true; });
  persistNotifications();
  renderNotifications();
}

export function getDismissedStockMap() {
  try {
    const raw = localStorage.getItem('toner_notif_dismissed_stock');
    return raw ? (JSON.parse(raw) || {}) : {};
  } catch (_) { return {}; }
}

export function setDismissedStockMap(map) {
  try {
    localStorage.setItem('toner_notif_dismissed_stock', JSON.stringify(map || {}));
  } catch (_) {}
}

export function clearAllNotifications() {
  // Clear everything the user sees, including stock alerts for now
  AppState.notifications = [];
  // Remember current low/out items so they don't immediately reappear after Clear
  const dismissed = getDismissedStockMap();
  (AppState.inks || []).forEach(item => {
    const s = getStockStatus(item.quantity, item.reorderLevel);
    if (s === STOCK_STATUS.OUT_OF_STOCK || s === STOCK_STATUS.LOW_STOCK) {
      const code = (item.inkCode || '').toUpperCase();
      if (code) dismissed[code] = Number(item.quantity);
    }
  });
  setDismissedStockMap(dismissed);
  // Also hide today's digest until tomorrow / new activity
  try { localStorage.setItem('toner_notif_digest_cleared', new Date().toISOString().slice(0, 10)); } catch (_) {}
  persistNotifications();
  renderNotifications();
}

export function handleNotificationClick(n) {
  if (!n) return;
  markNotificationRead(n.id);
  // Dismiss stock alert for current quantity (won't reappear until stock changes)
  if (n.source === 'stock' && n.inkCode) {
    const dismissed = getDismissedStockMap();
    const item = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === String(n.inkCode).toUpperCase());
    dismissed[String(n.inkCode).toUpperCase()] = item ? Number(item.quantity) || 0 : 0;
    setDismissedStockMap(dismissed);
    AppState.notifications = (AppState.notifications || []).filter(x => x.id !== n.id);
    persistNotifications();
  }
  const panel = document.getElementById('notif-panel');
  if (panel) panel.classList.add('hidden');

  const action = n.action || {};
  const type = action.type || (n.source === 'stock' ? 'stockCard' : null);
  const code = action.payload || n.inkCode;

  if (type === 'stockCard' && code && typeof openStockCard === 'function') {
    navigateTo('inventory');
    setTimeout(() => openStockCard(code), 80);
    return;
  }
  if (type === 'inventory' || n.source === 'stock') {
    navigateTo('inventory');
    if (code) {
      const search = document.getElementById('filter-inv-search');
      if (search) {
        search.value = code;
        AppState.filters.inventorySearch = code;
        if (typeof renderInventory === 'function') renderInventory();
      }
    }
    return;
  }
  if (type === 'transactions' || type === 'issue' || type === 'delivery') {
    AppState.filters.transactionType = type === 'delivery' ? 'RECEIVED' : 'RELEASED';
    AppState.filters.transactionDate = 'ALL';
    if (n.referenceNumber) AppState.filters.transactionSearch = n.referenceNumber;
    navigateTo('transactions');
    const dateSel = document.getElementById('filter-txn-date');
    if (dateSel) dateSel.value = 'ALL';
    if (typeof renderTransactions === 'function') renderTransactions();
    return;
  }
}

export function buildTodayActivityNotifications() {
  // One digest card for today's activity (not duplicated every render)
  const todayStr = new Date().toISOString().slice(0, 10);
  try {
    if (localStorage.getItem('toner_notif_digest_cleared') === todayStr) {
      AppState.notifications = (AppState.notifications || []).filter(n => n.id !== 'digest-today');
      return;
    }
  } catch (_) {}

  const existing = (AppState.notifications || []).find(n => n.id === 'digest-today');
  const today = new Date();
  const y = today.getFullYear(), m = today.getMonth(), d = today.getDate();
  const txns = (AppState.transactions || []).filter(t => {
    const dt = parseLocalDate(t.date || t.createdAt);
    return dt && dt.getFullYear() === y && dt.getMonth() === m && dt.getDate() === d;
  });
  const released = txns.filter(t => t.type === 'RELEASED');
  const received = txns.filter(t => t.type === 'RECEIVED');
  const unitsOut = released.reduce((s, t) => s + (Number(t.quantity) || 0), 0);
  const unitsIn = received.reduce((s, t) => s + (Number(t.quantity) || 0), 0);
  if (released.length === 0 && received.length === 0) {
    AppState.notifications = (AppState.notifications || []).filter(n => n.id !== 'digest-today');
    return;
  }
  const msg = [
    received.length ? `${received.length} delivery ticket(s) (+${unitsIn} units)` : null,
    released.length ? `${released.length} issuance(s) (−${unitsOut} units)` : null
  ].filter(Boolean).join(' · ');
  const digest = {
    id: 'digest-today',
    type: 'info',
    title: "Today's activity",
    message: msg,
    time: new Date().toISOString(),
    read: existing ? existing.read : false,
    source: 'digest',
    action: { type: 'transactions' }
  };
  AppState.notifications = (AppState.notifications || []).filter(n => n.id !== 'digest-today');
  AppState.notifications.push(digest);
}

export function renderAlerts() {
  // Rebuild stock-based alerts; keep action / digest notifications
  const lowOrOut = (AppState.inks || []).filter(item => {
    const s = getStockStatus(item.quantity, item.reorderLevel);
    return s === STOCK_STATUS.OUT_OF_STOCK || s === STOCK_STATUS.LOW_STOCK;
  });

  AppState.notifications = (AppState.notifications || []).filter(n => n.source !== 'stock');

  const dismissed = getDismissedStockMap();
  lowOrOut.forEach(item => {
    const isOut = Number(item.quantity) <= 0;
    const code = item.inkCode || '';
    const codeKey = code.toUpperCase();
    const qty = Number(item.quantity) || 0;
    // If user cleared/dismissed this item, only re-show when stock changes (e.g. goes lower or was restocked then low again)
    if (Object.prototype.hasOwnProperty.call(dismissed, codeKey)) {
      const prevQty = Number(dismissed[codeKey]);
      if (qty === prevQty) return; // still same level — stay dismissed
      // stock changed → remove dismiss so alert can show again
      delete dismissed[codeKey];
      setDismissedStockMap(dismissed);
    }
    const desc = (item.description || '').trim();
    const label = desc ? `${code} — ${desc}` : code;
    AppState.notifications.push({
      id: 'stock-' + code,
      type: isOut ? 'danger' : 'warning',
      title: isOut ? 'Out of stock' : 'Low stock alert',
      message: isOut
        ? `${label} has 0 units. Order replacement soon.`
        : `${label} is low (${qty} on hand; reorder at ${item.reorderLevel}).`,
      time: new Date().toISOString(),
      read: false,
      source: 'stock',
      inkCode: code,
      action: { type: 'stockCard', payload: code }
    });
  });

  buildTodayActivityNotifications();

  AppState.notifications.sort((a, b) => {
    // Danger stock first, then unread, then time
    const rank = (n) => {
      if (n.source === 'stock' && n.type === 'danger') return 0;
      if (n.source === 'stock') return 1;
      if (!n.read) return 2;
      return 3;
    };
    const ra = rank(a), rb = rank(b);
    if (ra !== rb) return ra - rb;
    return new Date(b.time) - new Date(a.time);
  });

  renderNotifications();
}

export async function triggerLowStockEmailCheck(force = false) {
  try {
    const q = force ? 'check_low_stock.php?force=1' : 'check_low_stock.php';
    const data = await apiRequest(q);
    const r = data.result || {};
    if (r.sent) {
      showToast('Low-stock email sent to admins.', 'success');
      pushNotification('warning', 'Low-stock email sent', `Alerted ${r.alerted || 0} item(s).`, { source: 'action' });
    } else if ((r.low_count || 0) + (r.out_count || 0) === 0) {
      if (force) showToast('Stock levels are OK — no alert needed.', 'info');
    } else if (r.reason === 'no_recipients') {
      showToast(r.error || 'No admin emails configured.', 'warning');
    } else if (r.error) {
      showToast(r.error, 'error');
    } else if (force) {
      showToast('Alert checked (may be in cooldown or already notified).', 'info');
    }
  } catch (e) {
    if (force) showToast(e.message || 'Email check failed', 'error');
    console.warn('[Toner] low-stock email check', e);
  }
}

export function renderNotifications() {
  const list = document.getElementById('notif-list');
  const badge = document.getElementById('notif-badge');
  const summary = document.getElementById('notif-summary');
  if (!list) return;

  const all = AppState.notifications || [];
  const filter = AppState.notifFilter || 'ALL';
  let notifs = all;
  if (filter === 'stock') notifs = all.filter(n => n.source === 'stock');
  else if (filter === 'action') notifs = all.filter(n => n.source === 'action' || n.source === 'digest');
  else if (filter === 'unread') notifs = all.filter(n => !n.read);

  const unread = all.filter(n => !n.read).length;
  const lowCount = all.filter(n => n.source === 'stock' && n.type === 'warning').length;
  const outCount = all.filter(n => n.source === 'stock' && n.type === 'danger').length;

  const dot = document.getElementById('notif-dot');
  const hasAlerts = (lowCount + outCount) > 0 || unread > 0 || all.some(n => !n.read);

  // Red dot: any stock alert or unread notification
  if (dot) {
    if (hasAlerts) dot.classList.remove('hidden');
    else dot.classList.add('hidden');
  }

  // Count badge: only when unread > 0 (optional number)
  if (badge) {
    if (unread > 0) {
      badge.textContent = unread > 99 ? '99+' : String(unread);
      badge.classList.remove('hidden');
      if (dot) dot.classList.add('hidden'); // prefer number over plain dot when counting
    } else if (hasAlerts) {
      badge.classList.add('hidden');
      if (dot) dot.classList.remove('hidden');
    } else {
      badge.classList.add('hidden');
      if (dot) dot.classList.add('hidden');
    }
  }

  if (summary) {
    if (lowCount + outCount > 0) {
      summary.classList.remove('hidden');
      const parts = [];
      if (outCount) parts.push(`<span class="font-bold text-rose-700">${outCount} out of stock</span>`);
      if (lowCount) parts.push(`<span class="font-bold text-amber-700">${lowCount} low stock</span>`);
      summary.innerHTML = parts.join(' · ') + ' — click an item to open its stock card';
    } else {
      summary.classList.add('hidden');
      summary.innerHTML = '';
    }
  }

  // Filter tab styles
  document.querySelectorAll('.notif-filter-btn').forEach(btn => {
    const active = btn.getAttribute('data-notif-filter') === filter;
    btn.className = active
      ? 'notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-slate-800 text-white'
      : 'notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50';
  });

  if (notifs.length === 0) {
    list.innerHTML = '<div class="p-6 text-center text-sm text-slate-400" id="notif-empty">No notifications in this view</div>';
    return;
  }

  const colors = {
    danger: 'bg-rose-100 text-rose-700',
    warning: 'bg-amber-100 text-amber-700',
    success: 'bg-emerald-100 text-emerald-700',
    info: 'bg-blue-100 text-blue-700'
  };
  const icons = {
    danger: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    success: 'M5 13l4 4L19 7',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
  };

  list.innerHTML = notifs.map(n => {
    const iconBg = colors[n.type] || colors.info;
    const path = icons[n.type] || icons.info;
    const timeStr = relativeTime(n.time);
    const clickable = n.source === 'stock' || n.action || n.inkCode || n.referenceNumber;
    return `
      <div class="p-3.5 hover:bg-slate-50 transition-colors cursor-pointer ${n.read ? 'opacity-70' : 'bg-blue-50/30'}" data-notif-id="${escapeHTML(n.id)}" role="button" tabindex="0">
        <div class="flex gap-3">
          <div class="w-9 h-9 rounded-lg ${iconBg} flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${path}"></path></svg>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-semibold text-slate-900 leading-snug">${escapeHTML(n.title)}</p>
              ${n.read ? '' : '<span class="mt-1 w-2 h-2 rounded-full bg-blue-500 shrink-0" title="Unread"></span>'}
            </div>
            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">${escapeHTML(n.message)}</p>
            <div class="flex items-center justify-between gap-2 mt-1.5">
              <span class="text-[10px] text-slate-400">${escapeHTML(timeStr)}</span>
              ${clickable ? '<span class="text-[10px] font-semibold text-blue-600">Open →</span>' : ''}
            </div>
          </div>
        </div>
      </div>`;
  }).join('');

  list.querySelectorAll('[data-notif-id]').forEach(el => {
    el.addEventListener('click', () => {
      const id = el.getAttribute('data-notif-id');
      const n = (AppState.notifications || []).find(x => x.id === id);
      handleNotificationClick(n);
    });
  });
}

export function initNotificationsPanelListeners() {
  // Restore notifications from previous session
  AppState.notifications = loadPersistedNotifications();

  // ---------- Notification bell ----------
  const btnNotif = document.getElementById('btn-notifications');

  const notifPanel = document.getElementById('notif-panel');

  if (btnNotif && notifPanel) {
    btnNotif.addEventListener('click', (e) => {
      e.stopPropagation();
      notifPanel.classList.toggle('hidden');
      // Do not auto-mark read — user can Mark all read
    });
    document.addEventListener('click', (e) => {
      if (!notifPanel.classList.contains('hidden') && !notifPanel.contains(e.target) && e.target !== btnNotif && !btnNotif.contains(e.target)) {
        notifPanel.classList.add('hidden');
      }
    });
  }

  try {
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
      // soft prompt only after user opens bell once
      btnNotif?.addEventListener('click', function askPerm() {
        Notification.requestPermission().catch(() => {});
        btnNotif.removeEventListener('click', askPerm);
      });
    }
  } catch (_) {}

  const btnClearNotifs = document.getElementById('btn-clear-notifs');

  if (btnClearNotifs) {
    btnClearNotifs.addEventListener('click', (e) => {
      e.stopPropagation();
      clearAllNotifications();
      showToast('Notifications cleared.', 'info');
    });
  }

  const btnMarkAllRead = document.getElementById('btn-mark-all-read');

  if (btnMarkAllRead) {
    btnMarkAllRead.addEventListener('click', (e) => {
      e.stopPropagation();
      markAllNotificationsRead();
    });
  }

  document.querySelectorAll('.notif-filter-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      AppState.notifFilter = btn.getAttribute('data-notif-filter') || 'ALL';
      renderNotifications();
    });
  });

  const btnNotifInv = document.getElementById('btn-notif-goto-inventory');

  if (btnNotifInv) {
    btnNotifInv.addEventListener('click', (e) => {
      e.stopPropagation();
      document.getElementById('notif-panel')?.classList.add('hidden');
      navigateTo('inventory');
    });
  }

  const btnNotifEmail = document.getElementById('btn-notif-email-low');

  if (btnNotifEmail) {
    btnNotifEmail.addEventListener('click', async (e) => {
      e.stopPropagation();
      try {
        await triggerLowStockEmailCheck(true);
      } catch (err) {
        showToast(err.message || 'Could not send low-stock email.', 'error');
      }
    });
  }
}

Object.assign(window, {
  loadPersistedNotifications,
  persistNotifications,
  relativeTime,
  pushNotification,
  markNotificationRead,
  markAllNotificationsRead,
  getDismissedStockMap,
  setDismissedStockMap,
  clearAllNotifications,
  handleNotificationClick,
  buildTodayActivityNotifications,
  renderAlerts,
  triggerLowStockEmailCheck,
  renderNotifications,
  initNotificationsPanelListeners
});
