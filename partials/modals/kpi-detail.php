<div id="modal-kpi-detail" class="hidden" style="position:fixed;inset:0;z-index:10040;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-kpi-detail-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:36rem;max-height:85vh;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.3);border:1px solid #e4e4e7;overflow:hidden;display:flex;flex-direction:column;">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
      <div>
        <h3 id="kpi-detail-title" class="text-base font-bold text-slate-900">Details</h3>
        <p id="kpi-detail-sub" class="text-xs text-slate-500 mt-0.5"></p>
      </div>
      <button type="button" id="btn-close-kpi-detail" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div id="kpi-detail-body" class="px-5 py-4 overflow-y-auto text-sm text-slate-700 flex-1"></div>
  </div>
</div>

