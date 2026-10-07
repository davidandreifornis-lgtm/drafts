      <section id="view-receive" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Receive Delivery</h2>
          <p class="text-sm text-slate-500 mt-0.5">Ingest approved deliveries to automatically increment stock. The ticket is the sole source of truth.</p>
        </div>

        <!-- Search Ticket Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs max-w-2xl">
          <label for="input-del-ref" class="block text-sm font-semibold text-slate-800 mb-1">
            Delivery Reference Number
          </label>
          <p class="text-xs text-slate-500 mb-3">
            Enter the pre-approved ticket reference code (e.g. <span class="font-mono text-blue-600 font-semibold cursor-pointer sample-ref" data-ref="DEL-2026-00125">DEL-2026-00125</span> or <span class="font-mono text-blue-600 font-semibold cursor-pointer sample-ref" data-ref="DEL-2026-00126">DEL-2026-00126</span>).
          </p>
          <div class="flex gap-2">
            <input id="input-del-ref" type="text" placeholder="DEL-2026-00125" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button id="btn-search-delivery" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              Search Ticket
            </button>
          </div>
        </div>

        <!-- Delivery Ticket Preview Container -->
        <div id="del-ticket-preview" class="hidden bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden max-w-4xl">
          <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-blue-100 text-blue-800">
                  Delivery Ticket
                </span>
                <span id="del-preview-status" class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  APPROVED
                </span>
              </div>
              <h3 id="del-preview-ref" class="text-xl font-bold text-slate-900 font-mono mt-1">DEL-2026-00125</h3>
            </div>
            <div class="text-right text-sm">
              <div class="text-slate-500">Delivery Date</div>
              <div id="del-preview-date" class="font-semibold text-slate-800">September 9, 2026</div>
              <div class="text-xs text-slate-500 mt-1">Supplier: <span id="del-preview-supplier" class="font-semibold text-slate-700">ABC Supplies</span></div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Ticket Items & Quantities</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Ink Code</th>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Printer Model</th>
                    <th class="px-4 py-3">Color</th>
                    <th class="px-4 py-3">Serial Number</th>
                    <th class="px-4 py-3 text-right">Current Stock</th>
                    <th class="px-4 py-3 text-right font-bold text-blue-600">Incoming Qty</th>
                    <th class="px-4 py-3 text-right">New Stock</th>
                  </tr>
                </thead>
                <tbody id="del-preview-tbody" class="divide-y divide-slate-100 text-slate-700">
                  <!-- Injected via JavaScript -->
                </tbody>
              </table>
            </div>

            <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
              <div class="text-sm text-blue-900">
                <span class="font-bold">Total Items:</span> <span id="del-preview-total-items" class="font-mono">0</span> | 
                <span class="font-bold">Total Quantity to Ingest:</span> <span id="del-preview-total-qty" class="font-bold font-mono">0</span> units
              </div>
              <div class="text-xs text-blue-700 font-medium">Automatic stock increment ready</div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button id="btn-cancel-del" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                Cancel
              </button>
              <button id="btn-process-delivery" type="button" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Process Delivery
              </button>
            </div>
          </div>
        </div>
      </section>

