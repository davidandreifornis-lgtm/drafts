    <div id="modal-receive" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Receive Delivery</h3>
            <p class="text-xs text-slate-500">Look up MRR number from ERP, then confirm to post stock</p>
          </div>
        </div>
        <button type="button" id="btn-close-receive-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <div id="receive-step-form" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-del-ref">MRR Number <span class="text-rose-500">*</span></label>
            <div class="flex gap-2">
              <input id="modal-del-ref" type="text" placeholder="e.g. MG009105" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="button" id="btn-modal-search-mrr" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shrink-0">Search MRR</button>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">Details are loaded from ERP (VM-EGNSERVER) using the MRR number.</p>
          </div>

          <div id="mrr-lookup-status" class="hidden text-sm px-3 py-2 rounded-xl border"></div>

          <div id="mrr-preview" class="hidden space-y-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-100 font-semibold">MRR <span id="mrr-preview-no" class="font-mono"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Date <span id="mrr-preview-date" class="font-semibold"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Lines <span id="mrr-preview-lines" class="font-semibold"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Total qty <span id="mrr-preview-qty" class="font-semibold font-mono"></span></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-3 py-2">Item code</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-right">On hand</th>
                    <th class="px-3 py-2 text-right">After</th>
                  </tr>
                </thead>
                <tbody id="mrr-preview-tbody" class="divide-y divide-slate-100"></tbody>
              </table>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-del-supplier">Supplier <span class="text-slate-400 font-normal">(optional)</span></label>
              <input id="modal-del-supplier" type="text" placeholder="Optional — leave blank if not needed" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <p id="mrr-already-warn" class="hidden text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">This MRR was already posted in toner inventory.</p>
          </div>

          <div class="flex justify-end gap-2 pt-1">
            <button type="button" id="btn-cancel-receive" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-del" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed" disabled>Confirm &amp; Post Stock</button>
          </div>
        </div>

        <div id="receive-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Delivery Recorded</h4>
          <p class="text-sm text-slate-500 mb-1">Reference <span id="m-del-success-ref" class="font-mono font-semibold text-blue-700"></span></p>
          <p class="text-xs text-slate-400 mb-6">Stock increased · Transaction logged</p>
          <button type="button" id="btn-receive-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>

    <!-- ===================== RELEASE TONER MODAL ===================== -->
