/**
 * Service: backend
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { AppState } from "../core/state.js";
import { renderAlerts, triggerLowStockEmailCheck } from "../layout/notifications-panel.js";

// ==========================================
// 2. APPLICATION STATE
// ==========================================

// ==========================================
// 3. STORAGE SERVICE
// ==========================================
// Centralized persistence interface. Future PHP/MySQL backend migration replaces
// these methods with standard Fetch API calls without altering calling business logic.

// ==========================================
// BACKEND API LAYER (PHP + MySQL)
// Falls back to localStorage if API is offline
// ==========================================

export async function detectBackend() {
  try {
    const data = await apiRequest('health.php', { silent: true });
    AppState.useBackend = !!(data && data.ok);
  } catch (_) {
    AppState.useBackend = false;
  }
  return AppState.useBackend;
}

export async function loadFromBackendSilent() {
  const [inv, tx] = await Promise.all([
    apiRequest('inventory.php', { silent: true }),
    apiRequest('transactions.php', { silent: true }),
  ]);
  AppState.inks = inv.items || [];
  AppState.transactions = tx.transactions || [];
  AppState.tickets = [];
  renderAlerts();
}

export async function loadFromBackend() {
  const [inv, tx] = await Promise.all([
    apiRequest('inventory.php', { loadingMessage: 'Refreshing data…' }),
    apiRequest('transactions.php', { silent: true }),
  ]);
  AppState.inks = inv.items || [];
  AppState.transactions = tx.transactions || [];
  AppState.tickets = [];
  renderAlerts();
  // Quiet email check: still alerts admins if stock remains low (respects 12h cooldown)
  /* low-stock email on load optional - primary trigger is issuance */
  triggerLowStockEmailCheck(false).catch(() => {});
}

export async function apiAddToner(payload) {
  return apiRequest('inventory.php', { method: 'POST', body: payload });
}

export async function apiUpdateToner(payload) {
  return apiRequest('inventory.php', { method: 'PUT', body: payload });
}

export async function apiRemoveToner(inkCode) {
  return apiRequest('inventory.php', {
    method: 'DELETE',
    body: { inkCode },
  });
}

export async function apiRecordDelivery(payload) {
  return apiRequest('delivery.php', { method: 'POST', body: payload });
}

export async function apiRecordRelease(payload, options = {}) {
  return apiRequest('release.php', {
    method: 'POST',
    body: payload,
    loadingMessage: options.loadingMessage || 'Issuing toner…',
    silent: !!options.silent,
  });
}

export async function apiRecordDefective(payload) {
  return apiRequest('defective.php', { method: 'POST', body: payload });
}

Object.assign(window, {
  detectBackend,
  loadFromBackendSilent,
  loadFromBackend,
  apiAddToner,
  apiUpdateToner,
  apiRemoveToner,
  apiRecordDelivery,
  apiRecordRelease,
  apiRecordDefective
});
