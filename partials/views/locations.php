      <section id="view-locations" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-slate-900">Locations, Printers &amp; Suppliers</h2>
            <p class="text-sm text-slate-500 mt-0.5">Department / location / printer for issuance, and suppliers for stock card edit.</p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="flex border-b border-slate-200 bg-slate-50">
            <button type="button" id="tab-locations" data-loc-tab="locations" class="loc-sup-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-white border-b-2 border-blue-600 transition-colors">
              Locations &amp; Printers
            </button>
            <button type="button" id="tab-suppliers" data-loc-tab="suppliers" class="loc-sup-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Suppliers
            </button>
          </div>

          <!-- TAB: Locations -->
          <div id="panel-locations" class="loc-sup-panel p-5 space-y-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Add location and printer</h3>
              <p class="text-xs text-slate-500 mt-0.5">Creates a new option for Stock Issuance. To change an existing row, use <strong>Edit</strong> in the table.</p>
            </div>
            <input type="hidden" id="page-loc-edit-id" value="">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-dept">Department <span class="text-rose-500">*</span></label>
                <input id="page-loc-dept" type="text" placeholder="e.g. ACCT" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-location">Location <span class="text-rose-500">*</span></label>
                <input id="page-loc-location" type="text" placeholder="e.g. Acctg Office" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-printer">Printer assigned <span class="text-rose-500">*</span></label>
                <input id="page-loc-printer" type="text" placeholder="e.g. Canon MF237W" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-ip">IP address</label>
                <input id="page-loc-ip" type="text" placeholder="e.g. 192.168.1.50" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <p class="text-[11px] text-slate-400">Same department, location, or printer name is allowed in different combinations. Only an exact duplicate of department + location + printer is blocked.</p>
            <div class="flex flex-wrap gap-2">
              <button type="button" id="btn-page-loc-save" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add location
              </button>
              <button type="button" id="btn-page-loc-clear" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200">Clear</button>
            </div>

            <div class="border-t border-slate-100 pt-4">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-bold text-slate-900">All locations</h3>
                  <span id="page-loc-count" class="text-xs font-semibold text-slate-500">0</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                  <input id="filter-locations-search" type="search" placeholder="Search department, location, printer, IP…" class="flex-1 sm:w-64 px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <button type="button" id="btn-locations-search-clear" class="px-3 py-2 text-xs font-medium text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">Clear</button>
                </div>
              </div>
              <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left">
                  <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                      <th class="px-4 py-3 font-semibold">Department</th>
                      <th class="px-4 py-3 font-semibold">Location</th>
                      <th class="px-4 py-3 font-semibold">Printer</th>
                      <th class="px-4 py-3 font-semibold">IP address</th>
                      <th class="px-4 py-3 font-semibold w-36">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="page-locations-tbody" class="divide-y divide-slate-100 text-slate-700">
                  </tbody>
                </table>
              </div>
              <div id="page-locations-empty" class="hidden px-5 py-12 text-center text-slate-400 text-sm">
                No locations yet. Add department, location, and optional printer above.
              </div>
            </div>
          </div>

          <!-- TAB: Suppliers -->
          <div id="panel-suppliers" class="loc-sup-panel hidden p-5 space-y-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Add supplier</h3>
              <p class="text-xs text-slate-500 mt-0.5">Suppliers appear as a dropdown when you edit a stock card.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-supplier-name">Supplier name <span class="text-rose-500">*</span></label>
                <input id="page-supplier-name" type="text" placeholder="e.g. INKRITE" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <button type="button" id="btn-page-supplier-add" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add supplier
              </button>
            </div>

            <div class="border-t border-slate-100 pt-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-900">All suppliers</h3>
                <span id="page-supplier-count" class="text-xs font-semibold text-slate-500">0</span>
              </div>
              <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left">
                  <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                      <th class="px-4 py-3 font-semibold">Supplier</th>
                      <th class="px-4 py-3 font-semibold w-36">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="page-suppliers-tbody" class="divide-y divide-slate-100 text-slate-700"></tbody>
                </table>
              </div>
              <div id="page-suppliers-empty" class="hidden px-5 py-10 text-center text-slate-400 text-sm">No suppliers yet. Add one above.</div>
            </div>
          </div>
        </div>
      </section>



