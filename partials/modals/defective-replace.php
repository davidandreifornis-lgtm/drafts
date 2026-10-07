<!-- alias: modal-def-replace → modal-defective-replace (see core/modal.js) -->
    <div id="modal-defective-replace" class="hidden" style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;">
      <div id="modal-defective-replace-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
      <div id="modal-defective-replace-dialog" style="position:relative;z-index:1;width:100%;max-width:28rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;overflow:hidden;">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3" style="background:linear-gradient(to right,#fff1f2,#ffffff);">
          <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-base font-bold text-slate-900">Receive supplier replacement</h3>
            <p class="text-xs text-slate-500">Stock will increase by 1 for this item</p>
          </div>
          <button type="button" id="btn-close-def-replace" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="px-5 py-4 space-y-3">
          <input type="hidden" id="def-replace-ref" value="">
          <input type="hidden" id="def-replace-code" value="">
          <div class="rounded-xl bg-slate-50 border border-slate-100 px-3.5 py-2.5 space-y-1 text-sm">
            <div><span class="text-slate-500">Reference:</span> <span id="def-replace-ref-label" class="font-mono font-bold text-rose-700"></span></div>
            <div><span class="text-slate-500">Item:</span> <span id="def-replace-code-label" class="font-mono font-semibold"></span></div>
            <div><span class="text-slate-500">Description:</span> <span id="def-replace-desc-label" class="text-slate-800"></span></div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1">Recorded by (logged in)</label>
            <input id="def-replace-recorded-by" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="def-replace-accepted-by">Accepted by (admin) <span class="text-rose-500">*</span></label>
            <select id="def-replace-accepted-by" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
              <option value="">— Select admin —</option>
            </select>
            <p class="text-[11px] text-slate-500 mt-1">Admin who received the good unit from the supplier.</p>
          </div>
        </div>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2">
          <button type="button" id="btn-cancel-def-replace" class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-xl hover:bg-white">Cancel</button>
          <button type="button" id="btn-confirm-def-replace" class="px-5 py-2 text-sm font-semibold bg-rose-600 text-white rounded-xl hover:bg-rose-700">Confirm &amp; add to stock</button>
        </div>
      </div>
    </div>



<div id="modal-def-replace" class="hidden" hidden data-alias-of="modal-defective-replace" aria-hidden="true"></div>
