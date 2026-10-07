    <div id="modal-remove-toner" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
      <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
          <div class="text-sm font-bold">Remove toner from inventory?</div>
          <div class="text-xs opacity-80">This deletes the stock card entry for this code.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-2">You are about to remove:</p>
      <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-center mb-2">
        <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Toner Code</div>
        <div class="text-lg font-mono font-bold text-rose-700" id="remove-toner-code-label">—</div>
      </div>
      <p class="text-xs text-slate-500 mb-5">Past transaction history is kept for audit. Only the inventory master record is removed.</p>
      <div class="flex justify-end gap-3">
        <button type="button" id="btn-cancel-remove-toner" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-confirm-remove-toner" class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Yes, Remove</button>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    
