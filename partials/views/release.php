      <section id="view-release" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Release / Give Toner</h2>
          <p class="text-sm text-slate-500 mt-0.5">Fulfill approved toner release tickets. Stock is decremented automatically after atomic pre-validation.</p>
        </div>

        <!-- Search Ticket Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs max-w-2xl">
          <label for="input-rel-ref" class="block text-sm font-semibold text-slate-800 mb-1">
            Release Reference Number
          </label>
          <p class="text-xs text-slate-500 mb-3">
            Enter the pre-approved release ticket reference code (e.g. <span class="font-mono text-emerald-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00451">REL-2026-00451</span>, <span class="font-mono text-emerald-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00452">REL-2026-00452</span>, or insufficient test <span class="font-mono text-rose-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00453">REL-2026-00453</span>).
          </p>
          <div class="flex gap-2">
            <input id="input-rel-ref" type="text" placeholder="REL-2026-00451" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <button id="btn-search-release" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              Search Ticket
            </button>
          </div>
        </div>

        <!-- Release Ticket Preview Container -->
        <div id="rel-ticket-preview" class="hidden bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden max-w-4xl">
          <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  Release Ticket
                </span>
                <span id="rel-preview-status" class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  APPROVED
                </span>
              </div>
              <h3 id="rel-preview-ref" class="text-xl font-bold text-slate-900 font-mono mt-1">REL-2026-00451</h3>
            </div>
            <div class="text-right text-sm">
              <div class="text-slate-500">Release Date</div>
              <div id="rel-preview-date" class="font-semibold text-slate-800">September 9, 2026</div>
              <div class="text-xs text-slate-500 mt-1">Department: <span id="rel-preview-dept" class="font-semibold text-slate-700">Human Resources</span></div>
              <div class="text-xs text-slate-500">Given To: <span id="rel-preview-givento" class="font-semibold text-slate-700">Juan Dela Cruz</span></div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
              <span class="font-bold text-slate-700">Stated Purpose:</span>
              <span id="rel-preview-purpose" class="italic">Regular office printer replenishment</span>
            </div>

            <!-- Insufficient Stock Warning Alert Box -->
            <div id="rel-stock-error-box" class="hidden p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                  <h5 class="font-bold text-sm text-rose-900">Unable to Process Release: Insufficient Stock</h5>
                  <p id="rel-stock-error-msg" class="text-xs mt-1 text-rose-700"></p>
                  <p class="text-xs mt-1 font-semibold text-rose-800">Atomic Rule Enforced: No inventory changes were made. All items must be in stock.</p>
                </div>
              </div>
            </div>

            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Requested Items & Stock Validation</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Ink Code</th>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Color</th>
                    <th class="px-4 py-3 text-right">Available Stock</th>
                    <th class="px-4 py-3 text-right font-bold text-emerald-600">Release Qty</th>
                    <th class="px-4 py-3 text-right">Post-Release Stock</th>
                    <th class="px-4 py-3 text-center">Validation</th>
                  </tr>
                </thead>
                <tbody id="rel-preview-tbody" class="divide-y divide-slate-100 text-slate-700">
                  <!-- Dynamically injected -->
                </tbody>
              </table>
            </div>

            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-between">
              <div class="text-sm text-emerald-900">
                <span class="font-bold">Total Items:</span> <span id="rel-preview-total-items" class="font-mono">0</span> | 
                <span class="font-bold">Total Units to Release:</span> <span id="rel-preview-total-qty" class="font-bold font-mono">0</span> units
              </div>
              <div id="rel-validation-status-badge" class="text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-200 text-emerald-800">
                Stock Verified
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button id="btn-cancel-rel" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                Cancel
              </button>
              <button id="btn-process-release" type="button" class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Process Release
              </button>
            </div>
          </div>
        </div>
      </section>

