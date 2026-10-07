      <section id="view-transactions" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Transaction History</h2>
            <p class="text-sm text-slate-500 mt-0.5">Separate logs for incoming deliveries and toner releases by department.</p>
          </div>
          <button id="btn-export-txns" title="Exports only the rows matching the current tab and date filter" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Filtered CSV
          </button>
        </div>

        <!-- Main tabs: Deliveries vs Releases -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="flex border-b border-slate-200" id="txn-main-tabs">
            <button type="button" data-txn-tab="RECEIVED" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-blue-50 border-b-2 border-blue-600 transition-colors">
              Incoming Deliveries
              <span id="txn-tab-count-received" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">0</span>
            </button>
            <button type="button" data-txn-tab="RELEASED" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Releases by Department
              <span id="txn-tab-count-released" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">0</span>
            </button>
            <button type="button" data-txn-tab="DEFECTIVE" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Defective Returns
              <span id="txn-tab-count-defective" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">0</span>
            </button>
          </div>

          <!-- Department sub-tabs (releases only) -->
          <div id="txn-dept-tabs" class="hidden px-4 py-3 border-b border-slate-100 bg-slate-50/80 flex flex-wrap gap-2">
            <!-- Filled dynamically from locations + release history -->
          </div>

          <!-- Search + date filters -->
          <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[200px]">
              <input id="filter-txn-search" type="text" placeholder="Search ref, item code, description, location, department..." class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-44">
              <select id="filter-txn-date" class="w-full py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="ALL" selected>All Time</option>
                <option value="TODAY">Today</option>
                <option value="WEEK">This Week</option>
                <option value="MONTH">This Month</option>
                <option value="CUSTOM">Custom Range</option>
              </select>
            </div>
            <div id="txn-custom-range" class="hidden flex flex-wrap items-center gap-2">
              <label class="text-xs font-semibold text-slate-500" for="filter-txn-from">From</label>
              <input id="filter-txn-from" type="date" class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <label class="text-xs font-semibold text-slate-500" for="filter-txn-to">To</label>
              <input id="filter-txn-to" type="date" class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="button" id="btn-txn-apply-range" class="px-3 py-2 text-sm font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-colors">Apply</button>
            </div>
            <button type="button" id="btn-clear-txn-filters" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">Reset Filters</button>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr id="txns-thead-row">
                  <!-- Headers set by renderTransactions() -->
                </tr>
              </thead>
              <tbody id="txns-tbody" class="divide-y divide-slate-100 text-slate-700">
              </tbody>
            </table>
          </div>
          <div id="txns-empty-state" class="hidden p-8 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <h4 class="font-bold text-slate-800">No Transactions Found</h4>
            <p class="text-sm text-slate-500 mt-1" id="txns-empty-msg">There are no records in this tab yet.</p>
          </div>
        </div>
      </section>

        
