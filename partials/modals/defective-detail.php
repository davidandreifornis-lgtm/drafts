<div id="modal-defective-detail" class="hidden" style="position:fixed;inset:0;z-index:10070;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-defective-detail-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:28rem;max-height:90vh;overflow:auto;background:#fff;border-radius:1.25rem;border:1px solid #e4e4e7;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">
    <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 sticky top-0 bg-white z-10">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Defective return details</h3>
        <p class="text-xs text-slate-500 mt-0.5 font-mono" id="def-detail-ref">—</p>
      </div>
      <button type="button" id="btn-close-defective-detail" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div class="p-5 space-y-1 text-sm" id="def-detail-body"></div>
    <div class="px-5 py-4 border-t border-slate-100 flex flex-wrap gap-2 justify-end sticky bottom-0 bg-white">
      <div id="def-detail-actions" class="flex flex-wrap gap-2 mr-auto"></div>
      <button type="button" id="btn-defective-detail-done" class="px-4 py-2 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Close</button>
    </div>
  </div>
</div>


