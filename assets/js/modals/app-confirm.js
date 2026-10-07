/**
 * Modal: app-confirm
 * Split out of the former app-logic.js — behavior unchanged.
 */

export let _appConfirmResolve = null;

export function appConfirm({ title = 'Confirm', message = 'Are you sure?', confirmText = 'Confirm', cancelText = 'Cancel', danger = false } = {}) {
  return new Promise((resolve) => {
    _appConfirmResolve = resolve;
    const titleEl = document.getElementById('app-confirm-title');
    const msgEl = document.getElementById('app-confirm-message');
    const okBtn = document.getElementById('btn-app-confirm-ok');
    const cancelBtn = document.getElementById('btn-app-confirm-cancel');
    if (titleEl) titleEl.textContent = title;
    if (msgEl) msgEl.textContent = message;
    if (okBtn) {
      okBtn.textContent = confirmText;
      okBtn.className = danger
        ? 'px-4 py-2.5 text-sm font-semibold rounded-xl bg-rose-600 text-white hover:bg-rose-700'
        : 'px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black';
    }
    if (cancelBtn) cancelBtn.textContent = cancelText;
    const modal = document.getElementById('modal-app-confirm');
    if (modal) {
      modal.classList.remove('hidden');
      modal.style.display = 'flex';
    }
    document.body.classList.add('overflow-hidden');
  });
}

export function closeAppConfirm(result) {
  const modal = document.getElementById('modal-app-confirm');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
  if (typeof _appConfirmResolve === 'function') {
    const r = _appConfirmResolve;
    _appConfirmResolve = null;
    r(!!result);
  }
}

export function initAppConfirmModalListeners() {
  const btnAppConfirmOk = document.getElementById('btn-app-confirm-ok');

  if (btnAppConfirmOk) btnAppConfirmOk.addEventListener('click', () => closeAppConfirm(true));

  const btnAppConfirmCancel = document.getElementById('btn-app-confirm-cancel');

  if (btnAppConfirmCancel) btnAppConfirmCancel.addEventListener('click', () => closeAppConfirm(false));

  const appConfirmBackdrop = document.getElementById('modal-app-confirm-backdrop');

  if (appConfirmBackdrop) appConfirmBackdrop.addEventListener('click', () => closeAppConfirm(false));
}

Object.assign(window, {
  appConfirm,
  closeAppConfirm,
  initAppConfirmModalListeners
});
