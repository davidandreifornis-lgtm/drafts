/**
 * Modal: user-saved
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { closeUserModal } from "./user.js";

export function openUserSavedModal({ isNew, fullName, username, isActive }) {
  const title = document.getElementById('user-saved-title');
  const msg = document.getElementById('user-saved-message');
  const nameEl = document.getElementById('user-saved-name');
  const emailEl = document.getElementById('user-saved-email');
  const statusEl = document.getElementById('user-saved-status');
  if (title) title.textContent = isNew ? 'Admin created' : 'Admin updated';
  if (msg) {
    msg.textContent = isNew
      ? 'The new admin account was saved successfully.'
      : 'The admin details were updated successfully.';
  }
  if (nameEl) nameEl.textContent = fullName || '—';
  if (emailEl) emailEl.textContent = username || '—';
  if (statusEl) statusEl.textContent = isActive === false ? 'Inactive' : 'Active';
  // Close edit modal/backdrop so only success modal is highlighted
  try { closeUserModal(); } catch (_) {}
  const sharedBackdrop = document.getElementById('modal-backdrop');
  if (sharedBackdrop) sharedBackdrop.classList.add('hidden');
  const modal = document.getElementById('modal-user-saved');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    modal.style.zIndex = '10080';
  }
  document.body.classList.add('overflow-hidden');
}

export function closeUserSavedModal() {
  const modal = document.getElementById('modal-user-saved');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

export function initUserSavedModalListeners() {
  const btnCloseUserSaved = document.getElementById('btn-close-user-saved');

  if (btnCloseUserSaved) btnCloseUserSaved.addEventListener('click', closeUserSavedModal);

  const userSavedBackdrop = document.getElementById('modal-user-saved-backdrop');

  if (userSavedBackdrop) userSavedBackdrop.addEventListener('click', closeUserSavedModal);
}

Object.assign(window, {
  openUserSavedModal,
  closeUserSavedModal,
  initUserSavedModalListeners
});
