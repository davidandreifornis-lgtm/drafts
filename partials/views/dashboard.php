      <section id="view-dashboard" class="page-view space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Dashboard</h2>
            <p class="text-sm text-slate-500 mt-0.5">Overview of toner stock levels, movements, and ticket statuses.</p>
          </div>
          <div class="flex items-center gap-2">
            <button id="quick-btn-receive" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              Receive Delivery
            </button>
            <button id="quick-btn-release" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
              Stock Issuance
            </button>
            <button id="quick-btn-defective" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              Return Defective
            </button>
          </div>
        </div>

        <!-- Dashboard date filter -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs px-4 py-3 flex flex-wrap items-center gap-3">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Period</span>
          <select id="filter-dash-date" class="py-2 px-3 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-400 min-w-[10rem]">
            <option value="ALL" selected>All time</option>
            <option value="TODAY">Today</option>
            <option value="WEEK">Last 7 days</option>
            <option value="MONTH">This month</option>
            <option value="CUSTOM">Custom range</option>
          </select>
          <div id="dash-custom-range" class="hidden flex flex-wrap items-center gap-2">
            <label class="text-xs font-semibold text-slate-500" for="filter-dash-from">From</label>
            <input id="filter-dash-from" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
            <label class="text-xs font-semibold text-slate-500" for="filter-dash-to">To</label>
            <input id="filter-dash-to" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
            <button type="button" id="btn-dash-apply-range" class="px-3 py-2 text-sm font-semibold rounded-xl bg-slate-900 text-white hover:bg-black">Apply</button>
          </div>
          <span id="dash-period-label" class="text-xs text-slate-500 ml-auto"></span>
          <button type="button" id="btn-open-mail-log" class="text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-3 py-1.5 hover:bg-slate-50">Mail log</button>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="skus" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Toner SKUs</div>
            <div id="kpi-total-skus" class="text-3xl font-bold text-slate-900 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Toner models in master list</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="stock" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Stock On-Hand</div>
            <div id="kpi-total-stock" class="text-3xl font-bold text-blue-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Available physical units</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-amber-200 transition-colors" data-kpi="low" title="View details">
            <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Low Stock Items</div>
            <div id="kpi-low-stock" class="text-3xl font-bold text-amber-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">At or below reorder level</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-rose-200 transition-colors" data-kpi="out" title="View details">
            <div class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Out of Stock</div>
            <div id="kpi-out-stock" class="text-3xl font-bold text-rose-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Zero units remaining</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="deliveries" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deliveries (period)</div>
            <div id="kpi-today-deliveries" class="text-2xl font-bold text-slate-900 mt-2">0 units</div>
            <div id="kpi-today-del-tickets" class="text-xs text-slate-500 mt-1">0 tickets processed</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="releases" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Releases (period)</div>
            <div id="kpi-today-releases" class="text-2xl font-bold text-slate-900 mt-2">0 units</div>
            <div id="kpi-today-rel-tickets" class="text-xs text-slate-500 mt-1">0 tickets processed</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="tickets" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tickets Processed</div>
            <div id="kpi-total-tickets" class="text-2xl font-bold text-slate-900 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Ticket refs in selected period</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="last" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Last Processed Ticket</div>
            <div id="kpi-last-ticket" class="text-lg font-bold text-slate-900 mt-2 truncate">None</div>
            <div id="kpi-last-ticket-time" class="text-xs text-slate-500 mt-1">Awaiting first transaction</div>
          </div>
        </div>

        <!-- Charts row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs lg:col-span-2">
            <div class="mb-4">
              <h3 class="font-bold text-slate-900">Departments Needing Toner Most Often</h3>
              <p class="text-xs text-slate-500">Based on issuance volume (units issued per department)</p>
            </div>
            <div class="relative h-72">
              <canvas id="chart-activity"></canvas>
            </div>
          </div>
          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="mb-4">
              <h3 class="font-bold text-slate-900">Stock Status</h3>
              <p class="text-xs text-slate-500">Share of toner codes by stock level</p>
            </div>
            <div class="relative h-72">
              <canvas id="chart-stock-status"></canvas>
            </div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
          <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
              <h3 class="font-bold text-slate-900">Toner lifespan by department</h3>
              <p class="text-xs text-slate-500">Average days between changes and average page yield per department — selected dashboard period. Click a department for location breakdown.</p>
            </div>
            <div id="kpi-avg-yield" class="text-sm font-semibold text-slate-700">Overall: —</div>
          </div>
          <div id="dept-lifespan-grid" class="keep-color grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <div class="col-span-full py-10 text-center text-sm text-slate-400">Loading departments…</div>
          </div>
        </div>

      </section>

