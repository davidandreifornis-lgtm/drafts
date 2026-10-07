    <div id="modal-add-toner" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Add Toner</h3>
            <p class="text-xs text-slate-500">Register a new toner code in inventory</p>
          </div>
        </div>
        <button type="button" id="btn-close-add-toner" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <p class="text-xs text-slate-500 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
          Same fields as an <strong>MRR line</strong> (except dates). Saved to <code class="font-mono text-[11px]">dbo.toner_inventory</code>.
        </p>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-code">Item Code <span class="text-rose-500">*</span></label>
          <input id="add-toner-code" type="text" required placeholder="e.g. OS00000251" class="w-full px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">Item_code</span> → column <span class="font-mono">item_code</span>.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-description">Description <span class="text-rose-500">*</span></label>
          <input id="add-toner-description" type="text" required placeholder="e.g. INK CRG-737 - CANON" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">Item_Desc</span> → column <span class="font-mono">description</span>.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-qty">Quantity <span class="text-rose-500">*</span></label>
          <input id="add-toner-qty" type="number" min="0" value="0" required class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">MRR_Qty</span> → column <span class="font-mono">quantity</span>.</p>
        </div>
        <!-- Hidden defaults (not on MRR; system defaults) -->
        <input type="hidden" id="add-toner-supplier" value="">
        <input type="hidden" id="add-toner-brand" value="">
        <input type="hidden" id="add-toner-reorder" value="3">
        <div id="add-toner-printer-list" class="hidden">
          <input type="text" class="add-printer-input" value="">
        </div>
      </div>
      <div class="shrink-0 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
        <button type="button" id="btn-cancel-add-toner" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-save-add-toner" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Toner</button>
      </div>
    </div>

    <!-- ===================== REMOVE TONER CONFIRM ===================== -->
