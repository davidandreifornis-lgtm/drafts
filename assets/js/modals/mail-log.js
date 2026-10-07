/**
 * Modal: mail-log
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML } from "../core/format.js";
import { showToast } from "../core/toast.js";

export async function openMailLogModal() {
  const modal = document.getElementById('modal-mail-log');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
  await refreshMailLog();
}

export function closeMailLogModal() {
  const modal = document.getElementById('modal-mail-log');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

export function summarizeMailPreview(text) {
  const raw = String(text || '').replace(/\s+/g, ' ').trim();
  if (!raw) return 'No message preview available.';
  // Strip technical bits and duplicate timestamps (time is shown once on the card)
  let s = raw
    .replace(/driver=\S+/gi, '')
    .replace(/to=\S+/gi, '')
    .replace(/subject=/gi, '')
    .replace(/SMTP ERROR:?/gi, '')
    .replace(/---/g, '')
    .replace(/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?/g, '')
    .replace(/\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]* \d{1,2},? \d{4},? \d{1,2}:\d{2}\s*(?:AM|PM)?/gi, '')
    .replace(/\(Philippine Time\)/gi, '')
    .replace(/Philippine Time/gi, '')
    .replace(/\s{2,}/g, ' ')
    .trim();
  if (!s) s = 'Low-stock notification';
  if (s.length > 140) s = s.slice(0, 137) + '…';
  return s;
}

/** Mail log timestamps are written in Asia/Manila — format once in 12-hour PH time */
export function formatMailLogTime(loggedAt) {
  if (!loggedAt) return '';
  try {
    let s = String(loggedAt).trim().replace(' ', 'T');
    // Drop fractional seconds but keep timezone if present
    s = s.replace(/(\.\d+)(?=[zZ]|[+-]\d{2}:?\d{2}$)/, '');
    if (!/[zZ]$|[+-]\d{2}:?\d{2}$/.test(s)) {
      // Bare stamp from our logger = already Asia/Manila wall clock (not UTC)
      s = s.split('.')[0] + '+08:00';
    }
    const d = new Date(s);
    if (isNaN(d.getTime())) return String(loggedAt);
    return d.toLocaleString('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (_) {
    return String(loggedAt);
  }
}

export function guessMailKind(entry) {
  const blob = ((entry.subject || '') + ' ' + (entry.bodyPreview || '') + ' ' + (entry.meta || '')).toLowerCase();
  if (blob.includes('low-stock') || blob.includes('low stock') || blob.includes('reorder')) return 'Low stock alert';
  if (blob.includes('failed') || blob.includes('error')) return 'Delivery issue';
  return 'System notification';
}

export async function refreshMailLog() {
  const body = document.getElementById('mail-log-body');
  const countEl = document.getElementById('mail-log-count');
  if (!body) return;
  body.innerHTML = `
    <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
      <div class="w-8 h-8 border-2 border-zinc-200 border-t-zinc-500 rounded-full animate-spin mb-3"></div>
      <p class="text-sm">Loading activity…</p>
    </div>`;
  try {
    const data = await apiRequest('mail_log.php?limit=80');
    const entries = data.entries || [];
    if (countEl) countEl.textContent = entries.length ? entries.length + (entries.length === 1 ? ' message' : ' messages') : '';
    if (!entries.length) {
      body.innerHTML = `
        <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
          <div class="w-14 h-14 rounded-2xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-300 mb-4 shadow-sm">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <p class="text-sm font-medium text-zinc-800">No emails yet</p>
          <p class="text-xs text-zinc-500 mt-1.5 max-w-[16rem] leading-relaxed">When low-stock alerts are sent, they will show up here with status and recipient.</p>
        </div>`;
      return;
    }
    body.innerHTML = entries.map((e, idx) => {
      const ok = !!e.ok;
      const kind = guessMailKind(e);
      const preview = summarizeMailPreview(e.bodyPreview || e.meta || '');
      const to = (e.to || '').trim() || 'Unknown recipient';
      const subject = (e.subject || '').trim() || kind;
      const driver = (e.driver || '').toUpperCase();
      const statusLabel = ok ? 'Delivered' : 'Failed';
      const statusClass = ok
        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/15'
        : 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/15';
      const iconBg = ok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
      const icon = ok
        ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
      const whenLabel = formatMailLogTime(e.loggedAt || '');
      return `
        <article class="group bg-white rounded-2xl border border-zinc-200/80 shadow-sm hover:shadow-md hover:border-zinc-300 transition-all p-4">
          <div class="flex gap-3">
            <div class="w-9 h-9 rounded-xl ${iconBg} flex items-center justify-center shrink-0">${icon}</div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2 justify-between">
                <h4 class="text-sm font-semibold text-zinc-900 tracking-tight truncate">${escapeHTML(subject)}</h4>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide ${statusClass}">${statusLabel}</span>
              </div>
              <p class="text-xs text-zinc-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                ${whenLabel ? `<span class="text-zinc-500">${escapeHTML(whenLabel)}</span><span class="text-zinc-300">·</span>` : ''}
                <span class="font-medium text-zinc-600">${escapeHTML(kind)}</span>
                <span class="text-zinc-300">·</span>
                <span class="truncate">${escapeHTML(to)}</span>
                ${driver ? `<span class="text-zinc-300">·</span><span class="text-[10px] uppercase tracking-wider text-zinc-400">${escapeHTML(driver)}</span>` : ''}
              </p>
              <p class="mt-2.5 text-xs text-zinc-600 leading-relaxed line-clamp-3">${escapeHTML(preview)}</p>
            </div>
          </div>
        </article>`;
    }).join('');
  } catch (err) {
    if (countEl) countEl.textContent = '';
    body.innerHTML = `
      <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-6 text-center">
        <p class="text-sm font-medium text-rose-800">Couldn’t load email activity</p>
        <p class="text-xs text-rose-600 mt-1">${escapeHTML(err.message || 'Unknown error')}</p>
      </div>`;
  }
}

export async function clearMailLog() {
  if (!confirm('Clear the entire mail log file?')) return;
  try {
    await apiRequest('mail_log.php', { method: 'DELETE' });
    await refreshMailLog();
    showToast('Mail log cleared.', 'success');
  } catch (e) {
    showToast(e.message || 'Clear failed', 'error');
  }
}

export function initMailLogModalListeners() {
  const btnMailLog = document.getElementById('btn-open-mail-log');

  if (btnMailLog) btnMailLog.addEventListener('click', openMailLogModal);

  const btnCloseMail = document.getElementById('btn-close-mail-log');

  if (btnCloseMail) btnCloseMail.addEventListener('click', closeMailLogModal);

  const btnRefreshMail = document.getElementById('btn-refresh-mail-log');

  if (btnRefreshMail) btnRefreshMail.addEventListener('click', refreshMailLog);

  const btnClearMail = document.getElementById('btn-clear-mail-log');

  if (btnClearMail) btnClearMail.addEventListener('click', clearMailLog);

  const mailBackdrop = document.getElementById('modal-mail-log-backdrop');

  if (mailBackdrop) mailBackdrop.addEventListener('click', closeMailLogModal);
}

Object.assign(window, {
  openMailLogModal,
  closeMailLogModal,
  summarizeMailPreview,
  formatMailLogTime,
  guessMailKind,
  refreshMailLog,
  clearMailLog,
  initMailLogModalListeners
});
