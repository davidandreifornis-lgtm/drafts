/**
 * Modal: kpi-detail
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML, formatDate } from "../core/format.js";
import { AppState } from "../core/state.js";
import { STOCK_STATUS } from "../data/constants.js";
import { getStockStatus, resolveTonerDescription } from "../services/stock-utils.js";
import { getDashboardFilteredTransactions } from "../views/dashboard.js";

export function openKpiDetail(kind) {
  const titleEl = document.getElementById('kpi-detail-title');
  const subEl = document.getElementById('kpi-detail-sub');
  const bodyEl = document.getElementById('kpi-detail-body');
  if (!bodyEl) return;
  const periodTxns = typeof getDashboardFilteredTransactions === 'function' ? getDashboardFilteredTransactions() : (AppState.transactions || []);
  const inks = AppState.inks || [];
  let title = 'Details';
  let sub = '';
  let html = '';

  if (kind === 'skus' || kind === 'stock') {
    title = kind === 'skus' ? 'Toner SKUs' : 'Stock on hand';
    sub = 'Current inventory master list';
    html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Code</th><th class="px-3 py-2">Description</th><th class="px-3 py-2 text-right">Qty</th></tr></thead><tbody class="divide-y">` +
      inks.map(i => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(i.inkCode)}</td><td class="px-3 py-2">${escapeHTML(i.description || '—')}</td><td class="px-3 py-2 text-right font-mono">${Number(i.quantity)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'low' || kind === 'out') {
    title = kind === 'low' ? 'Low stock items' : 'Out of stock';
    sub = 'Based on reorder level';
    const rows = inks.filter(i => {
      const st = getStockStatus(i.quantity, i.reorderLevel);
      return kind === 'low' ? st === STOCK_STATUS.LOW_STOCK : st === STOCK_STATUS.OUT_OF_STOCK;
    });
    if (!rows.length) html = '<p class="text-slate-500">None in this category.</p>';
    else html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Code</th><th class="px-3 py-2">Description</th><th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2 text-right">Reorder</th></tr></thead><tbody class="divide-y">` +
      rows.map(i => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(i.inkCode)}</td><td class="px-3 py-2">${escapeHTML(i.description || '—')}</td><td class="px-3 py-2 text-right font-mono">${Number(i.quantity)||0}</td><td class="px-3 py-2 text-right font-mono">${Number(i.reorderLevel)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'deliveries' || kind === 'releases' || kind === 'tickets') {
    const type = kind === 'deliveries' ? 'RECEIVED' : (kind === 'releases' ? 'RELEASED' : null);
    title = kind === 'deliveries' ? 'Deliveries in period' : (kind === 'releases' ? 'Releases in period' : 'Tickets in period');
    sub = 'Filtered by dashboard period';
    let rows = periodTxns;
    if (type) rows = rows.filter(t => t.type === type);
    if (!rows.length) html = '<p class="text-slate-500">No transactions in this period.</p>';
    else html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Ref</th><th class="px-3 py-2">Date</th><th class="px-3 py-2">Code</th><th class="px-3 py-2">Type</th><th class="px-3 py-2 text-right">Qty</th></tr></thead><tbody class="divide-y">` +
      [...rows].reverse().slice(0, 50).map(t => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(t.referenceNumber)}</td><td class="px-3 py-2 text-xs">${escapeHTML(formatDate(t.date||t.createdAt))}</td><td class="px-3 py-2 font-mono">${escapeHTML(t.inkCode)}</td><td class="px-3 py-2">${escapeHTML(t.type)}</td><td class="px-3 py-2 text-right font-mono">${Number(t.quantity)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'last') {
    title = 'Last processed ticket';
    const latest = periodTxns.length ? [...periodTxns].sort((a,b)=>String(b.date||b.createdAt).localeCompare(String(a.date||a.createdAt)))[0] : null;
    if (!latest) html = '<p class="text-slate-500">No transactions in this period.</p>';
    else {
      sub = latest.referenceNumber || '';
      html = `<dl class="space-y-2 text-sm">
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Reference</dt><dd class="font-mono font-semibold">${escapeHTML(latest.referenceNumber)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Type</dt><dd>${escapeHTML(latest.type)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Item</dt><dd class="font-mono">${escapeHTML(latest.inkCode)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Description</dt><dd>${escapeHTML(resolveTonerDescription(latest.inkCode)||'—')}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Qty</dt><dd class="font-mono">${Number(latest.quantity)||0}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Date</dt><dd>${escapeHTML(formatDate(latest.date||latest.createdAt))}</dd></div>
        <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Department</dt><dd>${escapeHTML(latest.department||'—')}</dd></div>
      </dl>`;
    }
  }
  if (titleEl) titleEl.textContent = title;
  if (subEl) subEl.textContent = sub;
  bodyEl.innerHTML = html;
  const modal = document.getElementById('modal-kpi-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

export function closeKpiDetail() {
  const modal = document.getElementById('modal-kpi-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

Object.assign(window, {
  openKpiDetail,
  closeKpiDetail
});
