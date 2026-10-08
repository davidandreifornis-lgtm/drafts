      <section id="view-inventory" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Toner Inventory</h2>
            <p class="text-sm text-slate-500 mt-0.5">Click any toner to open its stock card with compatible printers, supplier and movement history.</p>
          </div>
          <button type="button" id="btn-add-toner" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Toner
          </button>
        </div>

        <!-- Filters Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-3">
          <div class="flex-1 min-w-[200px]">
            <label for="filter-inv-search" class="sr-only">Toner Code</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              </div>
              <input id="filter-inv-search" type="text" placeholder="Search toner code, description, printer or supplier..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>
          <div class="w-44">
            <select id="filter-inv-status" class="w-full py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="ALL">All Statuses</option>
              <option value="IN_STOCK">In Stock</option>
              <option value="LOW_STOCK">Low Stock</option>
              <option value="OUT_OF_STOCK">Out of Stock</option>
            </select>
          </div>
          <button type="button" id="btn-clear-inv-filters" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            Reset Filters
          </button>
        </div>

        <!-- Inventory Table Container -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-white">
            <p class="text-sm text-slate-500"><span id="inv-result-count" class="font-semibold text-slate-800">0</span> toners</p>
            <p class="keep-color hidden sm:flex items-center gap-4 text-xs text-slate-500">
              <span class="inline-flex items-center gap-1.5"><i class="w-2 h-2 rounded-full bg-emerald-500"></i>In stock</span>
              <span class="inline-flex items-center gap-1.5"><i class="w-2 h-2 rounded-full bg-amber-500"></i>Low</span>
              <span class="inline-flex items-center gap-1.5"><i class="w-2 h-2 rounded-full bg-rose-500"></i>Out</span>
            </p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50/70 text-slate-500 text-[11px] uppercase font-semibold tracking-wider border-b border-slate-100">
                <tr>
                  <th class="px-5 py-3 w-[22%]">Toner Code</th>
                  <th class="px-5 py-3 w-[34%]">Description</th>
                  <th class="px-5 py-3 w-[18%]">Quantity</th>
                  <th class="px-5 py-3 w-[14%]">Stock Status</th>
                  <th class="px-5 py-3 w-[12%] text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="inventory-tbody" class="divide-y divide-slate-100 text-slate-700">
                <!-- Dynamically populated -->
              </tbody>
            </table>
          </div>
          <div id="inventory-empty-state" class="hidden p-8 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <h4 class="font-bold text-slate-800">No Inventory Items Found</h4>
            <p class="text-sm text-slate-500 mt-1">No toner items match the active search or filters.</p>
          </div>
        </div>
      </section>

