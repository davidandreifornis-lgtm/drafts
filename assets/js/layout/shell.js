/**
 * Layout: shell
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { navigateTo } from "../core/router.js";
import { showToast } from "../core/toast.js";
import { closeLogoutModal, openLogoutModal } from "../modals/logout.js";

export function initShellListeners() {
  // ---------- Sidebar Navigation ----------
  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const pageId = link.id.replace('nav-', '');
      if (['dashboard', 'inventory', 'transactions', 'reports'].includes(pageId)) {
        navigateTo(pageId);
      }
    });
  });

  // ---------- Mobile Sidebar Toggle ----------
  const btnMobileMenu = document.getElementById('mobile-menu-toggle');

  const sidebar = document.getElementById('sidebar');

  const sidebarBackdrop = document.getElementById('sidebar-backdrop');

  if (btnMobileMenu && sidebar && sidebarBackdrop) {
    btnMobileMenu.addEventListener('click', () => {
      sidebar.classList.remove('-translate-x-full');
      sidebarBackdrop.classList.remove('hidden');
    });
    sidebarBackdrop.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      sidebarBackdrop.classList.add('hidden');
    });
  }

  // ---------- Logout ----------
  const btnLogout = document.getElementById('btn-logout');

  if (btnLogout) btnLogout.addEventListener('click', openLogoutModal);

  const btnCancelLogout = document.getElementById('btn-cancel-logout');

  if (btnCancelLogout) btnCancelLogout.addEventListener('click', closeLogoutModal);

  const btnConfirmLogout = document.getElementById('btn-confirm-logout');

  if (btnConfirmLogout) {
    btnConfirmLogout.addEventListener('click', () => {
      closeLogoutModal();
      showToast('Signing out…', 'info');
      window.location.href = 'logout.php';
    });
  }

  const navLocations = document.getElementById('nav-locations');

  if (navLocations) navLocations.addEventListener('click', () => navigateTo('locations'));

  const navLogs = document.getElementById('nav-logs');

  if (navLogs) navLogs.addEventListener('click', () => navigateTo('logs'));

  const navSettings = document.getElementById('nav-email-config');

  if (navSettings) navSettings.addEventListener('click', () => navigateTo('email-config'));

  const navUsers = document.getElementById('nav-users');

  if (navUsers) navUsers.addEventListener('click', () => navigateTo('users'));
}

Object.assign(window, {
  initShellListeners
});
