/**
 * Layout: sidebar
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { AppState } from "../core/state.js";

export function updateSidebarUser() {
  const me = AppState.currentUser || {};
  const name = (me.fullName || me.name || '').trim();
  const user = (me.username || me.email || '').trim();
  const nameEl = document.getElementById('sidebar-user-name');
  const emailEl = document.getElementById('sidebar-user-email');
  const av = document.getElementById('sidebar-user-avatar');
  if (nameEl) nameEl.textContent = name || user || 'Admin';
  if (emailEl) emailEl.textContent = user && name ? user : (user || 'Signed in');
  if (av) {
    const label = name || user || 'A';
    const parts = label.split(/\s+/).filter(Boolean);
    let initials = parts.length >= 2
      ? (parts[0][0] + parts[1][0])
      : label.slice(0, 2);
    av.textContent = initials.toUpperCase();
  }
}

Object.assign(window, {
  updateSidebarUser
});
