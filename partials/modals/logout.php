    <div id="modal-logout" class="modal-card hidden bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
      <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-900">Log out?</h3>
      <p class="text-sm text-slate-600 mt-2">
        You will be signed out of the Toner Inventory Manager. Unsaved work is already stored locally.
      </p>
      <div class="mt-6 flex justify-end gap-3">
        <button id="btn-cancel-logout" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">
          Cancel
        </button>
        <button id="btn-confirm-logout" type="button" class="px-4 py-2 text-sm font-bold bg-slate-800 text-white hover:bg-slate-900 rounded-xl transition-colors">
          Log out
        </button>
      </div>
    </div>

