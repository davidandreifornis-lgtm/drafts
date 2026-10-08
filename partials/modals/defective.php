    <div id="modal-defective" class="keep-color modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Return Defective Toner</h3>
            <p class="text-xs text-slate-500">Flag an issued ticket as a defective return</p>
          </div>
        </div>
        <button type="button" id="btn-close-defective-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-5 overflow-y-auto flex-1 min-h-0">
        <div id="defective-step-search">
          <label class="block text-sm font-semibold text-slate-800 mb-1">Issuance Ticket Number</label>
          <p class="text-xs text-slate-500 mb-3">Enter the stock issuance ticket that was already released. That ticket will be flagged as defective.</p>
          <div class="flex gap-2">
            <input id="modal-def-ref" type="text" placeholder="e.g. REL-2026-00451" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <button id="btn-modal-search-def" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors">Search</button>
          </div>
          <div class="mt-4 p-3.5 rounded-xl bg-rose-50 border border-rose-100 hidden" id="defective-sample-tickets" aria-hidden="true" style="display:none">
            <div class="text-xs font-semibold text-rose-800 mb-2">Sample issued tickets (click to load)</div>
            <div class="flex flex-wrap gap-2">
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors" data-ref="REL-2026-00451">
                REL-2026-00451 · LOGISTICS
              </button>
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors" data-ref="REL-2026-00452">
                REL-2026-00452 · ACCT
              </button>
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors" data-ref="REL-2026-99999">
                REL-2026-99999 (not found)
              </button>
            </div>
            <p class="text-[11px] text-rose-600/80 mt-2">These are pre-completed issuances. Use one to try flagging as defective.</p>
          </div>
        </div>

        <div id="defective-step-error" class="hidden space-y-3 relative z-10 rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-lg">
          <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
              <div class="text-sm font-bold" id="defective-error-title">Not Found</div>
              <div class="text-xs opacity-80" id="defective-error-desc">No matching issuance found.</div>
            </div>
          </div>
          <div class="p-3 bg-rose-50 rounded-xl border border-rose-100 text-center">
            <div class="text-[10px] uppercase tracking-wider text-rose-500 font-semibold mb-1">Reference</div>
            <div class="text-base font-mono font-bold text-rose-700" id="defective-error-ref">—</div>
          </div>
          <div class="flex justify-end">
            <button type="button" id="btn-defective-error-back" class="px-5 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl">Try another ticket</button>
          </div>
        </div>

        <div id="defective-step-preview" class="hidden space-y-4 relative z-10 rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-lg">
          <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
              <div class="text-sm font-bold">Issuance Found — Mark as Defective</div>
              <div class="text-xs opacity-80">This will flag the issued toner as defective. Stock is not returned to usable inventory.</div>
            </div>
          </div>
          <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-sm">
            <div class="flex justify-between gap-2"><span class="text-slate-500">Ticket</span><span id="m-def-ref" class="font-mono font-bold text-rose-700"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Toner Code</span><span id="m-def-code" class="font-mono font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Department</span><span id="m-def-dept" class="font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Location</span><span id="m-def-loc" class="font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Qty issued</span><span id="m-def-qty" class="font-mono font-bold">1</span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Issued on</span><span id="m-def-date" class="text-slate-700"></span></div>
          </div>
          <div>
            <label for="modal-def-notes" class="block text-sm font-semibold text-slate-800 mb-1">Defect notes (optional)</label>
            <textarea id="modal-def-notes" rows="2" placeholder="e.g. Leaking cartridge, print quality failure..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-1">
            <button type="button" id="btn-modal-cancel-def" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-def" class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">
              Flag as Defective
            </button>
          </div>
        </div>

        <div id="defective-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Marked as Defective</h4>
          <p class="text-sm text-slate-500 mb-1">Issuance <span id="m-def-success-ref" class="font-mono font-semibold text-rose-700"></span> flagged.</p>
          <p class="text-xs text-slate-400 mb-6">Logged under Defective Returns · usable stock not increased</p>
          <button type="button" id="btn-defective-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>


    <!-- ===================== STOCK CARD / MOVEMENT HISTORY ===================== -->
