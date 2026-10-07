<div id="modal-user-saved" class="hidden" style="position:fixed;inset:0;z-index:10080;display:none;align-items:center;justify-content:center;padding:1.25rem;">
  <div id="modal-user-saved-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);"></div>
  <div id="modal-user-saved-card" style="position:relative;z-index:1;width:100%;max-width:24rem;background:#fff;border-radius:1.25rem;border:1px solid rgba(255,255,255,0.8);box-shadow:0 25px 60px -12px rgba(0,0,0,0.45), 0 0 0 1px rgba(15,23,42,0.06);overflow:hidden;transform:scale(1);animation:userSavedPop 0.22s ease-out;">
    <div class="p-6 text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 ring-8 ring-emerald-50/80">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
      </div>
      <h3 id="user-saved-title" class="text-lg font-bold text-slate-900 tracking-tight">Admin updated</h3>
      <p id="user-saved-message" class="text-sm text-slate-500 mt-2 leading-relaxed"></p>
      <div class="mt-4 rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-left text-sm space-y-1.5">
        <div class="flex justify-between gap-3"><span class="text-slate-500">Name</span><span id="user-saved-name" class="font-semibold text-slate-900 text-right"></span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Email</span><span id="user-saved-email" class="font-mono text-xs text-slate-800 text-right break-all"></span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Status</span><span id="user-saved-status" class="font-semibold text-slate-900"></span></div>
      </div>
      <button type="button" id="btn-close-user-saved" class="mt-5 w-full px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black transition-colors">Done</button>
    </div>
  </div>
</div>
<style>
@keyframes userSavedPop {
  from { opacity: 0; transform: scale(0.94) translateY(8px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

