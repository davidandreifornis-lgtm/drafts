<div id="modal-app-confirm" class="hidden" style="position:fixed;inset:0;z-index:10090;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-app-confirm-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:24rem;background:#fff;border-radius:1.25rem;border:1px solid #e4e4e7;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);overflow:hidden;">
    <div class="p-6">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h3 id="app-confirm-title" class="text-lg font-bold text-slate-900">Confirm</h3>
      <p id="app-confirm-message" class="text-sm text-slate-600 mt-2 leading-relaxed"></p>
      <div class="mt-6 flex gap-2 justify-end">
        <button type="button" id="btn-app-confirm-cancel" class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Cancel</button>
        <button type="button" id="btn-app-confirm-ok" class="px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Confirm</button>
      </div>
    </div>
  </div>
</div>

