/**
 * View: email-config
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";

/* formatDateTime: use global PH 12-hour helper above */




export async function fillAlertRecipientSelect(selected) {
  const sel = document.getElementById('cfg-alert-recipient');
  if (!sel) return;
  let users = AppState.adminUsers || [];
  if (!users.length) {
    try {
      const data = await apiRequest('users.php');
      users = (data.users || []).filter(u => u.isActive !== false);
      AppState.adminUsers = users;
    } catch (_) { users = []; }
  }
  const emails = users
    .map(u => String(u.username || '').trim().toLowerCase())
    .filter(e => e && e.includes('@'));
  const current = (selected || '').trim().toLowerCase();
  if (current && !emails.includes(current)) emails.unshift(current);
  sel.innerHTML = '<option value="">— Select registered admin email —</option>' +
    emails.map(e => {
      const u = users.find(x => String(x.username || '').toLowerCase() === e);
      const label = u && u.fullName ? `${u.fullName} (${e})` : e;
      return `<option value="${escapeHTML(e)}">${escapeHTML(label)}</option>`;
    }).join('');
  if (current) sel.value = current;
}

export async function loadEmailSettings() {
  const status = document.getElementById('cfg-email-status');
  try {
    const data = await apiRequest('settings.php');
    const e = data.email || {};
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    set('cfg-smtp-host', e.smtp_host || '');
    set('cfg-smtp-port', e.smtp_port != null ? e.smtp_port : 465);
    set('cfg-smtp-enc', (e.smtp_encryption || 'ssl').toLowerCase());
    set('cfg-smtp-user', e.smtp_user || '');
    await fillAlertRecipientSelect(e.alert_recipient || e.admin_email || '');
    set('cfg-smtp-pass', '');
    set('cfg-cooldown', e.cooldown_hours != null ? e.cooldown_hours : 12);
    const hint = document.getElementById('cfg-smtp-pass-hint');
    if (hint) {
      hint.textContent = e.smtp_pass_set
        ? 'SMTP password is stored encrypted in the database. Leave blank to keep it, or enter a new password to replace it.'
        : 'No SMTP password saved yet — enter the mailbox password; it will be encrypted in the database.';
    }
    if (status) status.textContent = '';
  } catch (err) {
    if (status) status.textContent = err.message || 'Failed to load settings';
  }
}

export async function saveEmailSettings(ev) {
  if (ev) ev.preventDefault();
  const status = document.getElementById('cfg-email-status');
  const btn = document.getElementById('btn-save-email-settings');
  const smtpUser = (document.getElementById('cfg-smtp-user')?.value || '').trim();
  const payload = {
    email: {
      driver: 'smtp',
      smtp_host: document.getElementById('cfg-smtp-host')?.value || '',
      smtp_port: parseInt(document.getElementById('cfg-smtp-port')?.value || '465', 10),
      smtp_encryption: document.getElementById('cfg-smtp-enc')?.value || 'ssl',
      smtp_user: smtpUser,
      smtp_pass: document.getElementById('cfg-smtp-pass')?.value || '',
      from_email: smtpUser,
      from_name: 'Toner Inventory System',
      alert_recipient: (document.getElementById('cfg-alert-recipient')?.value || '').trim().toLowerCase(),
      admin_email: (document.getElementById('cfg-alert-recipient')?.value || '').trim().toLowerCase(),
      cooldown_hours: parseInt(document.getElementById('cfg-cooldown')?.value || '12', 10),
      subject_prefix: '[Toner Alert]',
    }
  };
  try {
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    await apiRequest('settings.php', { method: 'POST', body: payload });
    showToast('Email settings saved to database.', 'success');
    if (status) status.textContent = 'Saved to database. Outbound alerts will use this SMTP configuration.';
    document.getElementById('cfg-smtp-pass').value = '';
    await loadEmailSettings();
  } catch (err) {
    showToast(err.message || 'Save failed', 'error');
    if (status) status.textContent = err.message || 'Save failed';
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save email settings'; }
  }
}

export async function testEmailSettings() {
  const status = document.getElementById('cfg-email-status');
  try {
    if (status) status.textContent = 'Sending low-stock test using current SMTP settings…';
    const data = await apiRequest('check_low_stock.php?force=1');
    const r = data.result || {};
    if (r.sent) {
      showToast('Test/alert email sent.', 'success');
      if (status) status.textContent = 'Email sent to ' + (r.to || 'admins') + '. Check Mail log.';
    } else {
      showToast(r.error || r.reason || 'Check completed — see status', r.error ? 'error' : 'info');
      if (status) status.textContent = JSON.stringify(r).slice(0, 200);
    }
  } catch (err) {
    showToast(err.message || 'Test failed', 'error');
    if (status) status.textContent = err.message || 'Test failed';
  }
}

export function initEmailConfigViewListeners() {
  const formEmailSettings = document.getElementById('form-email-settings');

  if (formEmailSettings) formEmailSettings.addEventListener('submit', saveEmailSettings);

  const btnTestEmail = document.getElementById('btn-test-email-settings');

  if (btnTestEmail) btnTestEmail.addEventListener('click', testEmailSettings);
}

Object.assign(window, {
  fillAlertRecipientSelect,
  loadEmailSettings,
  saveEmailSettings,
  testEmailSettings,
  initEmailConfigViewListeners
});
