      <section id="view-logs" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">System Logs</h2>
            <p class="text-sm text-slate-500 mt-0.5">What each admin did in the system — newest first.</p>
          </div>
          <button type="button" id="btn-refresh-logs" class="px-3 py-2 text-sm font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 self-start">Refresh</button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4 flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-[140px]">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-search">Search</label>
            <input id="filter-logs-search" type="text" placeholder="Action, details, ref, item…" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
          </div>
          <div class="w-40">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-period">Period</label>
            <select id="filter-logs-period" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL" selected>All time</option>
              <option value="TODAY">Today</option>
              <option value="WEEK">Last 7 days</option>
              <option value="MONTH">This month</option>
              <option value="CUSTOM">Custom range</option>
            </select>
          </div>
          <div id="logs-custom-range" class="hidden flex flex-wrap items-end gap-2">
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-from">From</label>
              <input id="filter-logs-from" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-to">To</label>
              <input id="filter-logs-to" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
          </div>
          <div class="w-48">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-action">Action type</label>
            <select id="filter-logs-action" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL">All actions</option>
            </select>
          </div>
          <div class="w-48">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-actor">Admin</label>
            <select id="filter-logs-actor" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL">All admins</option>
            </select>
          </div>
          <button type="button" id="btn-apply-logs-filter" class="px-4 py-2 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Apply</button>
          <button type="button" id="btn-clear-logs-filter" class="px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl border border-slate-200">Reset</button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                <tr>
                  <th class="px-4 py-3 font-semibold">When</th>
                  <th class="px-4 py-3 font-semibold">Action</th>
                  <th class="px-4 py-3 font-semibold">Details</th>
                  <th class="px-4 py-3 font-semibold">Admin</th>
                </tr>
              </thead>
              <tbody id="logs-tbody" class="divide-y divide-slate-100 text-slate-700">
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

