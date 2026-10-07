/**
 * Modal: edit-location
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { showToast } from "../core/toast.js";
import { loadReleaseLocations, populateDeptSelect } from "./release.js";
import { renderCharts } from "../views/dashboard.js";
import { assertClientLocationUnique, renderLocationsTable, renderPageLocationsTable } from "../views/locations.js";
import { renderTxnDeptTabs } from "../views/transactions.js";

export function openEditLocationModal(row) {
  if (!row) return;
  document.getElementById('edit-loc-id').value = String(row.id);
  document.getElementById('edit-loc-dept').value = row.department || '';
  document.getElementById('edit-loc-location').value = row.location || '';
  document.getElementById('edit-loc-printer').value = row.printerName || '';
  const ipField = document.getElementById('edit-loc-ip');
  if (ipField) ipField.value = row.ipAddress || row.ip_address || '';
  const modal = document.getElementById('modal-edit-location');
  const dialog = document.getElementById('modal-edit-location-dialog');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
  // Pop-in animation
  requestAnimationFrame(() => {
    if (dialog) {
      dialog.style.transform = 'scale(1)';
      dialog.style.opacity = '1';
    }
  });
  setTimeout(() => document.getElementById('edit-loc-dept')?.focus(), 80);
}

export function closeEditLocationModal() {
  const modal = document.getElementById('modal-edit-location');
  const dialog = document.getElementById('modal-edit-location-dialog');
  if (dialog) {
    dialog.style.transform = 'scale(0.96)';
    dialog.style.opacity = '0';
  }
  setTimeout(() => {
    if (modal) {
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }
    document.body.classList.remove('overflow-hidden');
  }, 150);
}

export async function saveEditLocationModal() {
  const id = parseInt(document.getElementById('edit-loc-id')?.value || '0', 10);
  const department = (document.getElementById('edit-loc-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('edit-loc-location')?.value || '').trim();
  const printerName = (document.getElementById('edit-loc-printer')?.value || '').trim();
  const ipAddress = (document.getElementById('edit-loc-ip')?.value || '').trim();
  if (!id) { showToast('Missing location id.', 'error'); return; }
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  if (!assertClientLocationUnique(department, location, printerName, id)) return;
  try {
    await apiRequest('locations.php', { method: 'PUT', body: { id, department, location, printerName, ipAddress } });
    showToast('Location updated.', 'success');
    closeEditLocationModal();
    await loadReleaseLocations();
    renderPageLocationsTable();
    if (typeof renderLocationsTable === 'function') renderLocationsTable();
    populateDeptSelect();
    if (typeof renderCharts === 'function') renderCharts();
    if (typeof renderTxnDeptTabs === 'function') renderTxnDeptTabs();
  } catch (e) {
    showToast(e.message || 'Update failed', 'error');
  }
}

export function initEditLocationModalListeners() {
  const btnCloseEditLoc = document.getElementById('btn-close-edit-location');

  if (btnCloseEditLoc) btnCloseEditLoc.addEventListener('click', closeEditLocationModal);

  const btnCancelEditLoc = document.getElementById('btn-cancel-edit-location');

  if (btnCancelEditLoc) btnCancelEditLoc.addEventListener('click', closeEditLocationModal);

  const btnSaveEditLoc = document.getElementById('btn-save-edit-location');

  if (btnSaveEditLoc) btnSaveEditLoc.addEventListener('click', saveEditLocationModal);

  const editLocBackdrop = document.getElementById('modal-edit-location-backdrop');

  if (editLocBackdrop) editLocBackdrop.addEventListener('click', closeEditLocationModal);
}

Object.assign(window, {
  openEditLocationModal,
  closeEditLocationModal,
  saveEditLocationModal,
  initEditLocationModalListeners
});
