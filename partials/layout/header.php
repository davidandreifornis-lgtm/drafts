  <header id="app-header" class="fixed top-0 left-0 right-0 z-40 bg-white/90 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button id="mobile-menu-toggle" type="button" aria-label="Toggle navigation menu" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
          </div>
          <div>
            <h1 class="font-bold text-slate-900 leading-tight text-base sm:text-lg">Toner Inventory Manager</h1>
            <p class="text-xs text-slate-500 font-medium">Ticket-Governed Inventory Authority</p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Notification Bell -->
        <div class="relative">
          <button id="btn-notifications" type="button" class="relative p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors" aria-label="Notifications" title="Notifications">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <!-- Red alert dot (shown when there are stock alerts or unread) -->
            <span id="notif-dot" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white hidden" aria-hidden="true"></span>
            <span id="notif-badge" class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center hidden leading-none">0</span>
          </button>

          <!-- Notification Dropdown Panel -->
          <div id="notif-panel" class="hidden absolute right-0 mt-2 w-[22rem] sm:w-[26rem] bg-white rounded-xl border border-slate-200 shadow-xl z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 space-y-2.5">
              <div class="flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                <div class="flex items-center gap-2">
                  <button id="btn-mark-all-read" type="button" class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">Mark all read</button>
                  <span class="text-slate-300">|</span>
                  <button id="btn-clear-notifs" type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors">Clear all</button>
                </div>
              </div>
              <div id="notif-summary" class="hidden text-[11px] text-slate-600 bg-white/80 border border-slate-200 rounded-lg px-2.5 py-1.5"></div>
              <div class="flex flex-wrap gap-1.5" id="notif-filter-tabs">
                <button type="button" data-notif-filter="ALL" class="notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-slate-800 text-white">All</button>
                <button type="button" data-notif-filter="stock" class="notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">Stock</button>
                <button type="button" data-notif-filter="action" class="notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">Activity</button>
                <button type="button" data-notif-filter="unread" class="notif-filter-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">Unread</button>
              </div>
            </div>
            <div id="notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-100">
              <div class="p-6 text-center text-sm text-slate-400" id="notif-empty">No notifications yet</div>
            </div>
            <div class="px-3 py-2 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-2">
              <button type="button" id="btn-notif-goto-inventory" class="text-[11px] font-semibold text-slate-600 hover:text-blue-700">View inventory</button>
              <button type="button" id="btn-notif-email-low" class="text-[11px] font-semibold text-amber-700 hover:text-amber-900">Email low-stock alert</button>
            </div>
          </div>
        </div>

        <!-- Logout -->
        <button id="btn-logout" type="button" class="inline-flex items-center gap-2 p-2.5 sm:px-3.5 sm:py-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors text-sm font-medium" title="Log out" aria-label="Log out">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          <span class="hidden sm:inline">Log out</span>
        </button>
      </div>
    </div>
  </header>

