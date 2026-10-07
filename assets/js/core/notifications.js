/** Notification helpers — panel logic lives in layout/notifications-panel.js */
export function renderNotifications() {
  if (typeof window.renderNotificationsImpl === "function") return window.renderNotificationsImpl();
  // fallback: original global after app-logic loads may overwrite; no-op here
}
export function pushNotification(n) {
  if (typeof window.pushNotification === "function" && window.pushNotification !== pushNotification) {
    return window.pushNotification(n);
  }
}
