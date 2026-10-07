/**
 * Shared modal manager.
 * - openModal(id) / closeModal(id)
 * - backdrop click closes
 * - ESC closes topmost
 * - focus returns to trigger
 * - first focusable field autofocuses
 */

const stack = []; // { id, trigger }
const SHARED_BACKDROP_ID = "modal-backdrop";

/** Modals that live inside #modal-backdrop as .modal-card children */
const SHARED_BACKDROP_MODALS = new Set([
  "modal-not-found", "modal-reset-confirm", "modal-receive", "modal-release",
  "modal-defective", "modal-stock-card", "modal-locations", "modal-add-toner",
  "modal-remove-toner", "modal-user", "modal-logout"
]);

function getEl(id) {
  return document.getElementById(id);
}

function isVisible(el) {
  if (!el) return false;
  if (el.classList.contains("hidden")) return false;
  const style = window.getComputedStyle(el);
  if (style.display === "none" || style.visibility === "hidden") return false;
  return true;
}

function showEl(el) {
  if (!el) return;
  el.classList.remove("hidden");
  if (el.style && el.style.display === "none") el.style.display = "flex";
  // standalone shells use inline display:none
  if (el.getAttribute("style") && /display:\s*none/.test(el.getAttribute("style"))) {
    el.style.display = "flex";
  }
}

function hideEl(el) {
  if (!el) return;
  el.classList.add("hidden");
  if (el.style) {
    // keep flex-capable shells consistent when reopened
    if (el.id && el.id.startsWith("modal-") && el.querySelector("[id$='-backdrop']")) {
      el.style.display = "none";
    }
  }
}

function focusableSelector() {
  return 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
}

function autofocusFirst(el) {
  if (!el) return;
  const preferred = el.querySelector("[data-autofocus], [autofocus]");
  const target = preferred || el.querySelector(focusableSelector());
  if (target && typeof target.focus === "function") {
    setTimeout(() => target.focus(), 20);
  }
}

function syncSharedBackdrop() {
  const backdrop = getEl(SHARED_BACKDROP_ID);
  if (!backdrop) return;
  const anyOpen = [...SHARED_BACKDROP_MODALS].some((id) => isVisible(getEl(id)));
  if (anyOpen) {
    backdrop.classList.remove("hidden");
  } else {
    backdrop.classList.add("hidden");
  }
}

export function openModal(id, triggerEl) {
  const el = getEl(id);
  if (!el) {
    console.warn("[modal] not found:", id);
    return;
  }
  // alias: modal-def-replace → modal-defective-replace
  if (id === "modal-def-replace") {
    return openModal("modal-defective-replace", triggerEl);
  }

  const trigger = triggerEl || document.activeElement;
  stack.push({ id, trigger });
  showEl(el);

  // show inner dialog pieces for standalone modals
  if (SHARED_BACKDROP_MODALS.has(id)) {
    syncSharedBackdrop();
  } else {
    // standalone: ensure flex display
    el.style.display = "flex";
    el.classList.remove("hidden");
  }

  document.body.classList.add("overflow-hidden");
  autofocusFirst(el);
}

export function closeModal(id) {
  if (id === "modal-def-replace") id = "modal-defective-replace";
  const el = getEl(id);
  if (!el) return;

  hideEl(el);
  if (SHARED_BACKDROP_MODALS.has(id)) {
    syncSharedBackdrop();
  } else {
    el.style.display = "none";
    el.classList.add("hidden");
  }

  // pop matching stack entry (or last)
  let restored = null;
  for (let i = stack.length - 1; i >= 0; i--) {
    if (stack[i].id === id) {
      restored = stack.splice(i, 1)[0];
      break;
    }
  }
  if (!restored && stack.length) restored = stack.pop();

  if (stack.length === 0) {
    document.body.classList.remove("overflow-hidden");
  }

  if (restored && restored.trigger && typeof restored.trigger.focus === "function") {
    try { restored.trigger.focus(); } catch (_) {}
  }
}

export function closeTopModal() {
  if (!stack.length) {
    // fallback: find any visible modal
    const candidates = [
      "modal-app-confirm", "modal-user-saved", "modal-defective-detail",
      "modal-release-detail", "modal-mail-log", "modal-kpi-detail",
      "modal-lifespan-detail", "modal-duplicate", "modal-defective-replace",
      "modal-edit-location", "modal-def-replace",
      ...SHARED_BACKDROP_MODALS
    ];
    for (const id of candidates) {
      const el = getEl(id);
      if (isVisible(el)) {
        closeModal(id);
        return true;
      }
    }
    return false;
  }
  const top = stack[stack.length - 1];
  closeModal(top.id);
  return true;
}

export function bindModalChrome() {
  // ESC → close topmost
  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    if (closeTopModal()) {
      e.preventDefault();
      e.stopPropagation();
    }
  });

  // Backdrop click (shared)
  const shared = getEl(SHARED_BACKDROP_ID);
  if (shared) {
    shared.addEventListener("click", (e) => {
      if (e.target !== shared) return;
      // close topmost shared-backdrop modal
      for (let i = stack.length - 1; i >= 0; i--) {
        if (SHARED_BACKDROP_MODALS.has(stack[i].id)) {
          closeModal(stack[i].id);
          return;
        }
      }
      // fallback close any visible shared modal
      for (const id of SHARED_BACKDROP_MODALS) {
        if (isVisible(getEl(id))) {
          closeModal(id);
          return;
        }
      }
    });
  }

  // Standalone modal backdrops (id ends with -backdrop)
  document.addEventListener("click", (e) => {
    const t = e.target;
    if (!(t instanceof Element)) return;
    if (t.id && t.id.endsWith("-backdrop")) {
      const modalId = t.id.replace(/-backdrop$/, "");
      // parent is often the shell
      const shell = t.closest("[id^='modal-']") || getEl(modalId);
      if (shell && shell.id) closeModal(shell.id);
      else closeModal(modalId);
    }
    // data-modal-close buttons
    const closer = t.closest("[data-modal-close]");
    if (closer) {
      const mid = closer.getAttribute("data-modal-close");
      if (mid) closeModal(mid);
    }
  });
}

// Compatibility bridges used by existing open*/close* wrappers
window.openModal = openModal;
window.closeModal = closeModal;
window.closeTopModal = closeTopModal;
