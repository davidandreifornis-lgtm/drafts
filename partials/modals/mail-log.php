<div id="modal-mail-log" class="hidden" style="position:fixed;inset:0;z-index:10040;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-mail-log-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:32rem;max-height:88vh;background:#fafafa;border-radius:1.25rem;border:1px solid #e4e4e7;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">
    <div class="px-5 pt-5 pb-4 bg-white border-b border-zinc-100 shrink-0">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-11 h-11 rounded-2xl bg-zinc-900 text-white flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-semibold text-zinc-900 tracking-tight">Email activity</h3>
            <p class="text-xs text-zinc-500 mt-0.5">Low-stock alerts and system notifications</p>
          </div>
        </div>
        <button type="button" id="btn-close-mail-log" class="p-2 rounded-xl text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 transition-colors" title="Close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="mt-4 flex items-center gap-2">
        <button type="button" id="btn-refresh-mail-log" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          Refresh
        </button>
        <button type="button" id="btn-clear-mail-log" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full text-rose-600 hover:bg-rose-50 transition-colors">
          Clear history
        </button>
        <span id="mail-log-count" class="ml-auto text-[11px] font-medium text-zinc-400"></span>
      </div>
    </div>
    <div id="mail-log-body" class="px-4 py-4 overflow-y-auto flex-1 space-y-3"></div>
  </div>
</div>


