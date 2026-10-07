    <div id="modal-not-found" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
      <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
          <div class="text-sm font-bold">Ticket Not Found</div>
          <div class="text-xs opacity-80">No approved ticket matches this reference in the registry.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-3">
        The system could not locate an approved delivery or release ticket for:
      </p>
      <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-100 text-center">
        <div class="text-[10px] uppercase tracking-wider text-rose-500 font-semibold mb-1">Reference Number</div>
        <div class="text-base font-mono font-bold text-rose-700" id="modal-notfound-ref">REF-UNKNOWN</div>
      </div>
      <ul class="mt-4 space-y-1.5 text-xs text-slate-500">
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Check for typos in the ticket number</li>
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Confirm the ticket was approved in the system</li>
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Use a sample valid ticket from the list if testing</li>
      </ul>
      <div class="mt-6 flex justify-end">
        <button id="btn-close-notfound-modal" type="button" class="px-5 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl transition-colors">
          Close
        </button>
      </div>
    </div>

    <!-- Reset Demo Confirmation Modal -->
