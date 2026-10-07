/**
 * Modal: lifespan-detail
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { escapeHTML } from "../core/format.js";

export function openLifespanDetail(deptKey) {
  const modal = document.getElementById('modal-lifespan-detail');
  if (!deptKey) return;
  const data = (window.__lifespanByDept && window.__lifespanByDept[deptKey]) || null;
  const esc = (s) => (typeof escapeHTML === 'function' ? escapeHTML(s) : String(s || '').replace(/</g, '&lt;'));

  const titleEl = document.getElementById('lifespan-detail-title');
  const subEl = document.getElementById('lifespan-detail-sub');
  const bodyEl = document.getElementById('lifespan-detail-body');
  if (!modal || !bodyEl) return;

  const deptName = data ? data.department : deptKey;
  if (titleEl) titleEl.textContent = deptName;
  if (subEl) {
    const locN = data ? data.locations.length : 0;
    const deptAvg = data && data.n > 0 ? Math.round(data.sum / data.n) : null;
    const deptYield = data && data.yieldN > 0 ? Math.round(data.yieldSum / data.yieldN) : null;
    const bits = [];
    if (locN) bits.push(`${locN} location${locN === 1 ? '' : 's'}`);
    if (deptAvg != null) bits.push(`${deptAvg} days avg`);
    if (deptYield != null) bits.push(`${deptYield.toLocaleString()} pages avg yield`);
    subEl.textContent = bits.length ? bits.join(' · ') : 'Department lifespan breakdown';
  }

  if (!data || !data.locations.length) {
    bodyEl.innerHTML = `
      <div class="py-8 text-center">
        <p class="text-sm text-slate-500 font-medium">No locations in this department</p>
      </div>`;
  } else {
    const deptAvg = data.n > 0 ? Math.round(data.sum / data.n) : null;
    const locBlocks = data.locations.map(loc => {
      const has = loc.avg != null;
      const intervals = (loc.intervals || []).slice().sort((a, b) => (b.to || '').localeCompare(a.to || ''));
      const intervalHtml = intervals.length
        ? `<div class="mt-2 space-y-1.5 pl-1 border-l-2 border-slate-200">
            ${intervals.map(iv => `
              <div class="flex flex-wrap items-center justify-between gap-2 py-1.5 px-2 rounded-lg bg-white border border-slate-100">
                <div class="min-w-0">
                  <div class="text-[11px] font-semibold text-slate-700 truncate">${esc(iv.description || iv.toner)}</div>
                  <div class="text-xs text-slate-600 font-mono mt-0.5">
                    ${esc(iv.from)} <span class="text-slate-400">→</span> ${esc(iv.to)}
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <div>
                    <span class="text-lg font-bold text-slate-900">${iv.days}</span>
                    <span class="text-[10px] text-slate-500 ml-0.5">days</span>
                  </div>
                  ${iv.yieldPages != null
                    ? `<div class="text-[11px] font-semibold text-indigo-700 mt-0.5">${Number(iv.yieldPages).toLocaleString()} pages</div>`
                    : `<div class="text-[10px] text-slate-400 mt-0.5">no yield</div>`}
                </div>
              </div>
            `).join('')}
          </div>`
        : `<p class="mt-2 text-[11px] text-slate-400">No change intervals yet (need 2+ issuances of the same toner here).</p>`;

      const metaBits = [];
      if (loc.printerName) metaBits.push(esc(loc.printerName));
      if (loc.ipAddress) metaBits.push('IP ' + esc(loc.ipAddress));
      return `
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="text-sm font-bold text-slate-800">${esc(loc.location)}</div>
              ${metaBits.length ? `<div class="text-[11px] text-slate-400 mt-0.5 truncate">${metaBits.join(' · ')}</div>` : ''}
            </div>
            <div class="text-right shrink-0">
              ${has
                ? `<div class="text-2xl font-bold text-slate-900 leading-none">${loc.avg}</div>
                   <div class="text-[10px] text-slate-500 mt-0.5">${loc.n} interval${loc.n === 1 ? '' : 's'}</div>`
                : `<div class="text-xl font-bold text-slate-300 leading-none">—</div>
                   <div class="text-[10px] text-slate-400 mt-0.5">no days data</div>`}
              ${loc.avgYield != null
                ? `<div class="text-sm font-bold text-indigo-700 mt-1 leading-none">${Number(loc.avgYield).toLocaleString()} <span class="text-[10px] font-medium text-slate-500">pages avg</span></div>
                   <div class="text-[10px] text-slate-400">${loc.yieldN} yield reading${loc.yieldN === 1 ? '' : 's'}</div>`
                : ''}
            </div>
          </div>
          ${intervalHtml}
        </div>`;
    }).join('');

    const deptYieldAvg = data.yieldN > 0 ? Math.round(data.yieldSum / data.yieldN) : null;
    bodyEl.innerHTML = `
      <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 text-white text-xs font-semibold">
          Dept avg <strong>${deptAvg != null ? deptAvg : '—'}</strong> days
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-semibold">
          Page yield <strong>${deptYieldAvg != null ? Number(deptYieldAvg).toLocaleString() : '—'}</strong> pages avg
        </span>
        <span class="text-xs text-slate-400">${data.n} change interval${data.n === 1 ? '' : 's'}${data.yieldN ? ` · ${data.yieldN} yield reading${data.yieldN === 1 ? '' : 's'}` : ''}</span>
      </div>
      <div class="space-y-3">${locBlocks}</div>`;
  }

  modal.classList.remove('hidden');
  modal.style.display = 'flex';
}

export function closeLifespanDetail() {
  const modal = document.getElementById('modal-lifespan-detail');
  if (!modal) return;
  modal.classList.add('hidden');
  modal.style.display = 'none';
}

Object.assign(window, {
  openLifespanDetail,
  closeLifespanDetail
});
