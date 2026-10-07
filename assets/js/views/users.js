/**
 * View: users
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { openUserModal, saveUser } from "../modals/user.js";

// ==========================================
// 21. APPLICATION INITIALIZATION
// ==========================================

// ---------- User Management (API: users.php) ----------
export async function loadUsers() {
  const tbody = document.getElementById('users-tbody');
  try {
    const data = await apiRequest('users.php');
    AppState.users = data.users || [];
    renderUsers();
  } catch (e) {
    if (tbody) {
      tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-rose-600 text-sm">${escapeHTML(e.message || 'Failed to load users')}</td></tr>`;
    }
  }
}

export function renderUsers() {
  const tbody = document.getElementById('users-tbody');
  if (!tbody) return;
  const users = AppState.users || [];
  if (!users.length) {
    tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No admin users yet. Click Add Admin.</td></tr>`;
    return;
  }
  tbody.innerHTML = users.map(u => {
    const status = u.isActive
      ? '<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">Active</span>'
      : '<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-600">Inactive</span>';
    return `<tr class="hover:bg-slate-50">
      <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-900">${escapeHTML(u.username)}</td>
      <td class="px-4 py-3 text-slate-700">${escapeHTML(u.fullName || '—')}</td>
      <td class="px-4 py-3">${status}</td>
      <td class="px-4 py-3">
        <div class="flex flex-wrap gap-2">
          <button type="button" class="btn-edit-user text-xs font-semibold text-blue-600 hover:underline" data-id="${u.id}">Edit</button>
          <button type="button" class="btn-delete-user text-xs font-semibold text-rose-600 hover:underline" data-id="${u.id}" data-username="${escapeHTML(u.username)}">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  tbody.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const u = (AppState.users || []).find(x => x.id === id);
      if (u) openUserModal(u);
    });
  });
  tbody.querySelectorAll('.btn-delete-user').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const name = btn.getAttribute('data-username') || '';
      if (!confirm(`Delete admin "${name}"? They will no longer be able to sign in.`)) return;
      try {
        await apiRequest('users.php', { method: 'DELETE', body: { id } });
        showToast('User deleted.', 'success');
        await loadUsers();
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

export function initUsersViewListeners() {
  const btnAddUser = document.getElementById('btn-add-user');

  if (btnAddUser) btnAddUser.addEventListener('click', () => openUserModal(null));

  const btnSaveUser = document.getElementById('btn-save-user');

  if (btnSaveUser) btnSaveUser.addEventListener('click', saveUser);
}

Object.assign(window, {
  loadUsers,
  renderUsers,
  initUsersViewListeners
});
