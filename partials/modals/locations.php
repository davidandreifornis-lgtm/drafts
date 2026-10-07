    <div id="modal-locations" class="modal-card hidden bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col z-[90]">
      <div class="shrink-0 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Departments, locations &amp; printers</h3>
          <p class="text-xs text-slate-500">These options appear on Stock Issuance</p>
        </div>
        <button type="button" id="btn-close-locations" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-4 overflow-y-auto flex-1 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
          <input id="loc-edit-dept" type="text" placeholder="Department e.g. ACCT" class="px-3 py-2 text-sm rounded-xl border border-slate-200 uppercase font-mono">
          <input id="loc-edit-location" type="text" placeholder="Location e.g. Acctg Office" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
          <input id="loc-edit-printer" type="text" placeholder="Printer name (required)" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
          <input id="loc-edit-ip" type="text" placeholder="IP e.g. 192.168.1.50" class="px-3 py-2 text-sm rounded-xl border border-slate-200 font-mono">
        </div>
        <input type="hidden" id="loc-edit-id" value="">
        <div class="flex gap-2">
          <button type="button" id="btn-loc-save" class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700">Add / Update</button>
          <button type="button" id="btn-loc-clear" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Clear form</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b">
              <tr>
                <th class="px-3 py-2">Department</th>
                <th class="px-3 py-2">Location</th>
                <th class="px-3 py-2">Printer</th>
                <th class="px-3 py-2">IP</th>
                <th class="px-3 py-2 w-24">Actions</th>
              </tr>
            </thead>
            <tbody id="locations-tbody" class="divide-y divide-slate-100"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===================== ADD TONER MODAL ===================== -->
