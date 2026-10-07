    <div id="modal-stock-card" class="modal-card hidden bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-bold text-slate-900 truncate">Stock Card — <span id="stock-card-code" class="font-mono text-blue-700">—</span></h3>
            <p class="text-xs text-slate-500 truncate" id="stock-card-meta">Edit quantity, printers & supplier · view movement history</p>
          </div>
        </div>
        <button type="button" id="btn-close-stock-card" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="px-6 py-3 border-b border-slate-100 bg-slate-50 space-y-2 relative">
        <input type="hidden" id="stock-card-ink-code" value="">
        <div class="flex flex-wrap items-center gap-3 text-sm">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">On hand</span>
            <span id="stock-card-onhand" class="font-bold font-mono text-lg text-slate-900">0</span>
          </div>
        </div>
        <div>
          <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Compatible printers</div>
          <div id="stock-card-printer" class="flex flex-wrap gap-1.5 text-sm"></div>
        </div>
        <div>
          <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Supplier(s)</div>
          <div id="stock-card-supplier" class="flex flex-wrap gap-1.5 text-sm"></div>
        </div>

              </div>

      <div class="p-4 overflow-y-auto flex-1 min-h-0 space-y-3">
        <div class="flex flex-wrap items-end gap-2">
          <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-period">Period</label>
            <select id="stock-card-period" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
              <option value="ALL" selected>All time</option>
              <option value="TODAY">Today</option>
              <option value="WEEK">Last 7 days</option>
              <option value="MONTH">This month</option>
              <option value="CUSTOM">Custom range</option>
            </select>
          </div>
          <div id="stock-card-custom-range" class="hidden flex flex-wrap items-end gap-2">
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-from">From</label>
              <input id="stock-card-from" type="date" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200">
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-to">To</label>
              <input id="stock-card-to" type="date" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200">
            </div>
          </div>
          <button type="button" id="btn-stock-card-apply-dates" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-black">Apply</button>
          <button type="button" id="btn-stock-card-reset-dates" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-left text-sm table-fixed">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
              <tr>
                <th class="px-3 py-3 w-[16%]">Date</th>
                <th class="px-3 py-3 w-[22%]">Transaction</th>
                <th class="px-3 py-3 w-[18%]">Reference</th>
                <th class="px-3 py-3 w-[12%] text-right">Stock In</th>
                <th class="px-3 py-3 w-[12%] text-right">Stock Out</th>
                <th class="px-3 py-3 w-[12%] text-right">Balance</th>
                <th class="px-3 py-3 w-[8%]">Notes</th>
              </tr>
            </thead>
            <tbody id="stock-card-tbody" class="divide-y divide-slate-100 text-slate-700">
            </tbody>
          </table>
        </div>
        <p id="stock-card-empty" class="hidden text-center text-sm text-slate-400 py-8">No movement history for this toner yet.</p>
      </div>

      <div class="shrink-0 px-6 py-3 border-t border-slate-200 flex flex-wrap justify-end gap-2">
        <button type="button" id="btn-stock-card-edit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          Edit details
        </button>
        <button type="button" id="btn-stock-card-done" class="px-5 py-2 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl">Close</button>
      </div>
    </div>


    
    <!-- STOCK CARD EDIT — full-screen pop-out with blur -->
    <div id="stock-card-edit-panel" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6">
      <!-- blurred dim backdrop -->
      <div id="stock-card-edit-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-md"></div>
      <!-- dialog -->
      <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden ring-1 ring-black/5">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 via-white to-white flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-600/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h4 class="text-base font-bold text-slate-900">Edit item details</h4>
            <p class="text-xs text-slate-500">Changes save to toner inventory</p>
          </div>
          <button type="button" id="btn-stock-card-edit-cancel" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <div class="px-5 py-4 space-y-4 max-h-[min(60vh,28rem)] overflow-y-auto">
          <div class="rounded-xl bg-slate-50 border border-slate-100 px-3.5 py-2.5 flex items-center justify-between gap-2">
            <div>
              <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Item code</div>
              <div id="stock-card-edit-code-display" class="font-mono font-bold text-slate-900 text-sm mt-0.5">—</div>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-md bg-slate-200/80 text-slate-600">Read-only</span>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-description-input">Description</label>
            <input id="stock-card-description-input" type="text" readonly tabindex="-1" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed shadow-xs" placeholder="—">
            <p class="text-[11px] text-slate-500 mt-1">From MRR / master data — not editable here.</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-qty">Quantity on hand</label>
              <input id="stock-card-qty" type="number" min="0" step="1" class="w-full px-3.5 py-2.5 text-sm font-mono font-bold rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-reorder">Reorder level</label>
              <input id="stock-card-reorder" type="number" min="0" step="1" class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-supplier-input">Supplier</label>
            <select id="stock-card-supplier-input" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
              <option value="">— Select supplier —</option>
            </select>
            <p class="text-[11px] text-slate-500 mt-1">Managed under <strong>Locations &amp; Suppliers</strong>.</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5">Compatible printer(s)</label>
            <div id="stock-card-printers-checkboxes" class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-white p-3 space-y-2 shadow-xs">
              <p class="text-xs text-slate-400">Loading printers…</p>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">Check all printers this toner works with. List comes from <strong>Locations &amp; Printers</strong>.</p>
            <input type="hidden" id="stock-card-printers-input" value="">
            <select id="stock-card-printers-select" class="hidden" multiple></select>
          </div>

          <p class="text-[11px] text-amber-900 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5 leading-relaxed">
            Changing <strong>quantity</strong> records a stock adjustment in transaction history.
          </p>
        </div>

        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/90 flex justify-end gap-2">
          <button type="button" id="btn-stock-card-edit-cancel-2" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white border border-slate-200 rounded-xl transition-colors">Cancel</button>
          <button type="button" id="btn-stock-card-save" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl shadow-md shadow-blue-600/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Save changes
          </button>
        </div>
      </div>
    </div>
