<section id="view-users" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">User Management</h2>
            <p class="text-sm text-slate-500 mt-0.5">Create admin accounts. Email is the login username and low-stock notification address.</p>
          </div>
          <button type="button" id="btn-add-user" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Admin
          </button>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3 w-[36%]">Email (login)</th>
                  <th class="px-4 py-3 w-[28%]">Full Name</th>
                  <th class="px-4 py-3 w-[14%]">Status</th>
                  <th class="px-4 py-3 w-[22%]">Actions</th>
                </tr>
              </thead>
              <tbody id="users-tbody" class="divide-y divide-slate-100 text-slate-700">
                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Loading users…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
