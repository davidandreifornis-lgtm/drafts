/**
 * Modal: locations
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { showToast } from "../core/toast.js";
import { loadReleaseLocations, populateDeptSelect } from "./release.js";
import { assertClientLocationUnique, renderLocationsTable } from "../views/locations.js";

export async function openLocationsModal() {
  await loadReleaseLocations();
  renderLocationsTable();
  document.getElementById('loc-edit-id').value = '';
  document.getElementById('loc-edit-dept').value = '';
  document.getElementById('loc-edit-location').value = '';
  document.getElementById('loc-edit-printer').value = '';
  const lip0 = document.getElementById('loc-edit-ip');
  if (lip0) lip0.value = '';
  const modal = document.getElementById('modal-locations');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

export function closeLocationsModal() {
  const modal = document.getElementById('modal-locations');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  populateDeptSelect();
}

export async function saveLocationRow() {
  const id = document.getElementById('loc-edit-id')?.value;
  const department = (document.getElementById('loc-edit-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('loc-edit-location')?.value || '').trim();
  const printerName = (document.getElementById('loc-edit-printer')?.value || '').trim();
  const ipAddress = (document.getElementById('loc-edit-ip')?.value || '').trim();
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  if (!assertClientLocationUnique(department, location, printerName, id ? parseInt(id, 10) : null)) return;
  try {
    if (id) {
      await apiRequest('locations.php', { method: 'PUT', body: { id: parseInt(id, 10), department, location, printerName, ipAddress } });
      showToast('Location updated.', 'success');
    } else {
      await apiRequest('locations.php', { method: 'POST', body: { department, location, printerName, ipAddress } });
      showToast('Location added.', 'success');
    }
    await loadReleaseLocations();
    renderLocationsTable();
    document.getElementById('loc-edit-id').value = '';
    document.getElementById('loc-edit-dept').value = '';
    document.getElementById('loc-edit-location').value = '';
    document.getElementById('loc-edit-printer').value = '';
    const ipEl = document.getElementById('loc-edit-ip');
    if (ipEl) ipEl.value = '';
    populateDeptSelect();
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}

Object.assign(window, {
  openLocationsModal,
  closeLocationsModal,
  saveLocationRow
});
