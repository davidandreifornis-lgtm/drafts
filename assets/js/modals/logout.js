/**
 * Modal: logout
 * Split out of the former app-logic.js — behavior unchanged.
 */

// ==========================================
// 19. TOAST NOTIFICATIONS
// ==========================================


// ==========================================
// ACTION MODALS (Receive / Release)
// ==========================================

export function openLogoutModal() {
  const modal = document.getElementById('modal-logout');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

export function closeLogoutModal() {
  const modal = document.getElementById('modal-logout');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

Object.assign(window, {
  openLogoutModal,
  closeLogoutModal
});
