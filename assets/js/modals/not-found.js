/**
 * Modal: not-found
 * Split out of the former app-logic.js — behavior unchanged.
 */

export function openNotFoundModal(refNumber) {
  const modal = document.getElementById('modal-not-found');
  if (!modal) return;
  const nfRef = document.getElementById('modal-notfound-ref') || document.getElementById('modal-nf-ref');
  if (nfRef) nfRef.textContent = refNumber;
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  modal.classList.remove('hidden');
}

export function closeNotFoundModal() {
  const modal = document.getElementById('modal-not-found');
  if (modal) modal.classList.add('hidden');
  const backdrop = document.getElementById('modal-backdrop');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

export function initNotFoundModalListeners() {
  // Modal Buttons (duplicate modal wired with blur backdrop elsewhere)
  const btnCloseNf = document.getElementById('btn-close-notfound-modal') || document.getElementById('btn-close-nf-modal');

  if (btnCloseNf) btnCloseNf.addEventListener('click', closeNotFoundModal);
}

Object.assign(window, {
  openNotFoundModal,
  closeNotFoundModal,
  initNotFoundModalListeners
});
