/**
 * Modal: user
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { showToast } from "../core/toast.js";
import { appConfirm } from "./app-confirm.js";
import { openUserSavedModal } from "./user-saved.js";
import { loadUsers } from "../views/users.js";

export function openUserModal(user) {
  const modal = document.getElementById('modal-user');
  const backdrop = document.getElementById('modal-backdrop');
  const title = document.getElementById('modal-user-title');
  const idEl = document.getElementById('user-edit-id');
  const userEl = document.getElementById('user-username');
  const nameEl = document.getElementById('user-fullname');
  const passEl = document.getElementById('user-password');
  const activeWrap = document.getElementById('user-active-wrap');
  const activeEl = document.getElementById('user-active');
  const passHint = document.getElementById('user-pass-hint');
  const passReq = document.getElementById('user-pass-req');

  if (user) {
    title.textContent = 'Edit Admin';
    idEl.value = user.id;
    userEl.value = user.username;
    userEl.disabled = true;
    nameEl.value = user.fullName || '';
    passEl.value = '';
    passHint.classList.remove('hidden');
    passReq.classList.add('hidden');
    activeWrap.classList.remove('hidden');
    activeEl.checked = !!user.isActive;
  } else {
    title.textContent = 'Add Admin';
    idEl.value = '';
    userEl.value = '';
    userEl.disabled = false;
    nameEl.value = '';
    passEl.value = '';
    passHint.classList.add('hidden');
    passReq.classList.remove('hidden');
    activeWrap.classList.add('hidden');
  }
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

export function closeUserModal() {
  const modal = document.getElementById('modal-user');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

export async function saveUser() {
  const id = document.getElementById('user-edit-id')?.value;
  const ok = await appConfirm({
    title: id ? 'Update admin' : 'Create admin',
    message: id ? 'Save changes to this admin account?' : 'Create this new admin account?',
    confirmText: 'Save'
  });
  if (!ok) return;
  const username = (document.getElementById('user-username')?.value || '').trim().toLowerCase();
  const fullName = (document.getElementById('user-fullname')?.value || '').trim();
  const password = document.getElementById('user-password')?.value || '';
  const isActive = document.getElementById('user-active')?.checked;

  try {
    let isNew = false;
    if (id) {
      const body = { id: parseInt(id, 10), fullName, isActive };
      if (password) body.password = password;
      await apiRequest('users.php', { method: 'PUT', body });
    } else {
      if (!username) { showToast('Email is required.', 'warning'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username)) { showToast('Enter a valid email address.', 'warning'); return; }
      if (!password || password.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
      await apiRequest('users.php', { method: 'POST', body: { username, password, fullName, role: 'admin' } });
      isNew = true;
    }
    closeUserModal();
    await loadUsers();
    openUserSavedModal({
      isNew,
      fullName: fullName || '—',
      username: username || '—',
      isActive: isActive !== false
    });
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}

export function initUserModalListeners() {
  const btnCloseUser = document.getElementById('btn-close-user-modal');

  if (btnCloseUser) btnCloseUser.addEventListener('click', closeUserModal);

  const btnCancelUser = document.getElementById('btn-cancel-user');

  if (btnCancelUser) btnCancelUser.addEventListener('click', closeUserModal);
}

Object.assign(window, {
  openUserModal,
  closeUserModal,
  saveUser,
  initUserModalListeners
});
