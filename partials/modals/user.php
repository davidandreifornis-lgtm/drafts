    <div id="modal-user" class="modal-card hidden bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div>
          <h3 id="modal-user-title" class="text-lg font-bold text-slate-900">Add Admin</h3>
          <p class="text-xs text-slate-500">Email is used to sign in and for low-stock alerts</p>
        </div>
        <button type="button" id="btn-close-user-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-6 space-y-4 overflow-y-auto flex-1">
        <input type="hidden" id="user-edit-id" value="">
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-username">Email <span class="text-rose-500">*</span></label>
          <input id="user-username" type="email" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="admin@company.com" autocomplete="off">
          <p class="text-[11px] text-slate-500 mt-1">This email is the login username and notification address.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-fullname">Full Name</label>
          <input id="user-fullname" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-password">Password <span id="user-pass-req" class="text-rose-500">*</span></label>
          <input id="user-password" type="password" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min. 6 characters" autocomplete="new-password">
          <p id="user-pass-hint" class="text-[11px] text-slate-500 mt-1 hidden">Leave blank to keep the current password</p>
        </div>
        <div id="user-active-wrap" class="hidden">
          <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input id="user-active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" checked>
            Active (can sign in)
          </label>
        </div>
      </div>
      <div class="shrink-0 border-t border-slate-200 px-6 py-4 flex justify-end gap-2">
        <button type="button" id="btn-cancel-user" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-save-user" class="px-4 py-2 text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 rounded-xl">Save</button>
      </div>
    </div>

