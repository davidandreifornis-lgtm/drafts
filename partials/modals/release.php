    <div id="modal-release" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Stock Issuance</h3>
            <p class="text-xs text-slate-500">Enter ticket reference and issuance details manually</p>
          </div>
        </div>
        <button type="button" id="btn-close-release-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <div id="release-step-form" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-ref">Issuance Reference No. <span class="text-rose-500">*</span></label>
            <input id="modal-rel-ref" type="text" placeholder="e.g. AMEC-006353" class="w-full px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-toner">Item (Description) <span class="text-rose-500">*</span></label>
            <select id="modal-rel-toner" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <option value="">— Select toner —</option>
            </select>
            <p id="modal-rel-stock-hint" class="text-xs text-slate-500 mt-1"></p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-dept">Department <span class="text-rose-500">*</span></label>
              <select id="modal-rel-dept" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select department —</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-location">Location <span class="text-rose-500">*</span></label>
              <select id="modal-rel-location" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select department first —</option>
              </select>
              <div id="m-rel-location-auto" class="hidden mt-2">
                <div class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800" id="m-rel-location-auto-text">—</div>
                <p class="text-xs text-slate-500 mt-1">Only location for this department — applied automatically.</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-printer">Printer assigned</label>
            <input id="modal-rel-printer" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-700" placeholder="Select location to see printer">
            <p class="text-[11px] text-slate-500 mt-1">From Locations &amp; Suppliers (sidebar).</p>
          </div>

          <div>
            <label class="flex items-start gap-2.5 cursor-pointer select-none">
              <input id="modal-rel-has-yield" type="checkbox" class="mt-1 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-slate-800">
                <span class="font-semibold">Printer reports page yield</span>
                <span class="block text-xs font-normal text-slate-500 mt-0.5">Check only if this printer shows total pages printed before the toner ran out / was changed.</span>
              </span>
            </label>
            <div id="modal-rel-yield-wrap" class="hidden mt-2">
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-yield">Actual yield (pages before change) <span class="text-rose-500">*</span></label>
              <input id="modal-rel-yield" type="number" min="0" step="1" placeholder="e.g. 4500" class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <p class="text-[11px] text-slate-500 mt-1">How many pages the previous toner printed before replacement.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-issued-by">Issued by (admin) <span class="text-rose-500">*</span></label>
              <select id="modal-rel-issued-by" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select admin —</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1">Recorded by (logged in)</label>
              <input id="modal-rel-recorded-by" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed" value="">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-qty">Quantity <span class="text-rose-500">*</span></label>
              <input id="modal-rel-qty" type="number" min="1" max="999" step="1" value="1"
                class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <p class="text-[11px] text-slate-500 mt-1">Default 1. Increase if the department needs more.</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-date">Date</label>
              <input id="modal-rel-date" type="date" readonly tabindex="-1" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed">
              <p class="text-[11px] text-slate-500 mt-1">Always set to today — not editable</p>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-notes">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
            <textarea id="modal-rel-notes" rows="2" maxlength="500" placeholder="e.g. Urgent request, temporary printer swap, partial set…" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-y"></textarea>
            <p class="text-[11px] text-slate-500 mt-1">Optional remark for this issuance. Not required.</p>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" id="btn-modal-cancel-rel" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-rel" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl">Record Issuance</button>
          </div>
        </div>

        <div id="release-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Issuance Recorded</h4>
          <p class="text-sm text-slate-500 mb-1">Reference <span id="m-rel-success-ref" class="font-mono font-semibold text-emerald-700"></span></p>
          <p class="text-xs text-slate-400 mb-6"><span id="m-rel-success-qty">1</span> unit(s) deducted · Transaction logged</p>
          <button type="button" id="btn-release-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>

<!-- ===================== RETURN DEFECTIVE MODAL ===================== -->
