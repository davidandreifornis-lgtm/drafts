      <section id="view-email-config" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-xl font-bold text-slate-900 tracking-tight">Email Configuration</h2>
          <p class="text-sm text-slate-500 mt-0.5">SMTP and alert settings used when the system sends low-stock and system emails.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden max-w-2xl">
          <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
              <span class="w-9 h-9 rounded-xl bg-zinc-900 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              </span>
              <div>
                <h3 class="text-sm font-bold text-slate-900">SMTP &amp; outbound mail</h3>
                <p class="text-xs text-slate-500">Host, port, security, and credentials for alert delivery</p>
              </div>
            </div>
          </div>
          <form id="form-email-settings" class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-host">SMTP Host</label>
                <input id="cfg-smtp-host" type="text" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="mail.example.com" required>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-port">Port</label>
                <input id="cfg-smtp-port" type="number" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="465" required>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-enc">Security</label>
                <select id="cfg-smtp-enc" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white">
                  <option value="ssl">SSL</option>
                  <option value="tls">TLS</option>
                  <option value="none">None</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-user">SMTP username</label>
                <input id="cfg-smtp-user" type="text" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="you@company.com" required>
                <p class="text-[11px] text-slate-400 mt-1">Also used as the From address (and fallback alert recipient if needed).</p>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-pass">SMTP password</label>
                <input id="cfg-smtp-pass" type="password" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="Leave blank to keep the saved SMTP password" autocomplete="new-password">
                <p id="cfg-smtp-pass-hint" class="text-[11px] text-slate-400 mt-1"></p>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-alert-recipient">Alert recipient <span class="text-rose-500">*</span></label>
                <select id="cfg-alert-recipient" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white" required>
                  <option value="">— Select registered admin email —</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Low-stock and system alerts are sent to this admin. Usernames in User Management must be email addresses.</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-cooldown">Low-stock alert cooldown (hours)</label>
                <input id="cfg-cooldown" type="number" min="0" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200">
              </div>
              </div>
            <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
              <button type="submit" id="btn-save-email-settings" class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Save email settings</button>
              <button type="button" id="btn-test-email-settings" class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Test low-stock email</button>
            </div>
            <p id="cfg-email-status" class="text-xs text-slate-500"></p>
          </form>
        </div>
      </section>
