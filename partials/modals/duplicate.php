<div id="modal-duplicate" class="hidden" style="position:fixed;inset:0;z-index:10050;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-duplicate-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:26rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e4e4e7;overflow:hidden;">
    <div class="p-6">
      <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 mb-4">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
          <div class="text-sm font-bold" id="modal-duplicate-title">Ticket Already Processed</div>
          <div class="text-xs opacity-80">This reference was already executed. Duplicate stock moves are blocked.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600" id="modal-duplicate-desc">This ticket reference has already been used. Stock cannot be modified twice.</p>
      <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs font-mono text-slate-700" id="modal-duplicate-details">
        Reference Number: <span id="modal-dup-ref" class="font-bold text-slate-900"></span>
      </div>
      <div class="mt-6 flex justify-end">
        <button id="btn-close-dup-modal" type="button" class="px-4 py-2 text-sm font-semibold bg-zinc-900 text-white hover:bg-black rounded-xl transition-colors">Understood</button>
      </div>
    </div>
  </div>
</div>

<!-- KPI detail modal -->
