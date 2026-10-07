    <div id="modal-edit-location" class="hidden" style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;">
      <div id="modal-edit-location-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
      <div id="modal-edit-location-dialog" style="position:relative;z-index:1;width:100%;max-width:28rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;overflow:hidden;transform:scale(0.96);opacity:0;transition:transform 0.15s ease,opacity 0.15s ease;">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between" style="background:linear-gradient(to right,#eff6ff,#ffffff);">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-bold text-slate-900">Edit location</h3>
              <p class="text-xs text-slate-500">Update department, location, or printer</p>
            </div>
          </div>
          <button type="button" id="btn-close-edit-location" class="p-2 rounded-xl text-slate-400 hover:bg-white hover:text-slate-700 border border-transparent hover:border-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="px-5 py-4 space-y-3">
          <input type="hidden" id="edit-loc-id" value="">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-dept">Department <span class="text-rose-500">*</span></label>
            <input id="edit-loc-dept" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-location">Location <span class="text-rose-500">*</span></label>
            <input id="edit-loc-location" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-printer">Printer assigned <span class="text-rose-500">*</span></label>
            <input id="edit-loc-printer" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Canon MF237W">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-ip">IP address</label>
            <input id="edit-loc-ip" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 192.168.1.50">
          </div>
        </div>
        <p class="px-5 text-[11px] text-slate-400 -mt-2 mb-2">Same department, location, or printer name is allowed elsewhere. Only the exact department + location + printer set must be unique.</p>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2">
          <button type="button" id="btn-cancel-edit-location" class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-xl hover:bg-white">Cancel</button>
          <button type="button" id="btn-save-edit-location" class="px-5 py-2 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-sm">Save changes</button>
        </div>
      </div>
    </div>

