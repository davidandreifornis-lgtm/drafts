/**
 * Modal: duplicate
 * Split out of the former app-logic.js — behavior unchanged.
 */

export function openDuplicateModal(ref, type) {
  const modal = document.getElementById('modal-duplicate');
  const refEl = document.getElementById('modal-dup-ref');
  if (refEl) refEl.textContent = ref || '—';
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
}

export function closeDuplicateModal() {
  const modal = document.getElementById('modal-duplicate');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

export function initDuplicateModalListeners() {
  const btnCloseDup = document.getElementById('btn-close-dup-modal');

  if (btnCloseDup) btnCloseDup.addEventListener('click', closeDuplicateModal);

  const dupBackdrop = document.getElementById('modal-duplicate-backdrop');

  if (dupBackdrop) dupBackdrop.addEventListener('click', closeDuplicateModal);
}

Object.assign(window, {
  openDuplicateModal,
  closeDuplicateModal,
  initDuplicateModalListeners
});
