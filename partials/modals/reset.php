    <div id="modal-reset-confirm" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
      <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-900">Reset Demo Data?</h3>
      <p class="text-sm text-slate-600 mt-2">
        This action will wipe all current LocalStorage transactions and stock changes, restoring the pristine demo dataset with all approved delivery and release tickets ready for test runs.
      </p>
      <div class="mt-6 flex justify-end gap-3">
        <button id="btn-cancel-reset" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
          Cancel
        </button>
        <button id="btn-confirm-reset" type="button" class="px-4 py-2 text-sm font-bold bg-rose-600 text-white hover:bg-rose-700 rounded-lg transition-colors">
          Yes, Reset All
        </button>
      </div>
    </div>

        <!-- ===================== RECEIVE DELIVERY MODAL ===================== -->
