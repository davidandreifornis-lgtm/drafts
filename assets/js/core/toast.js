import { escapeHTML } from "./format.js";

const ICONS = {
  success: '<path d="M9 12.5l2.2 2.2L15.5 10M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
  error: '<path d="M15 9l-6 6M9 9l6 6M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
  warning: '<path d="M12 8v4m0 4h.01M10.3 3.9L2.4 17.6A2 2 0 004.1 20.6h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>',
  info: '<path d="M12 8h.01M11 12h1v4h1m8-4a9 9 0 11-18 0 9 9 0 0118 0z"/>'
};
const MAX_VISIBLE = 4;

export function showToast(message, type = "info") {
  const container = document.getElementById("toast-container");
  if (!container) return;
  const kind = ICONS[type] ? type : "info";
  const duration = kind === "error" ? 7000 : kind === "warning" ? 6000 : 4000;

  // keep the stack tidy: drop the oldest when too many
  const live = container.querySelectorAll(".t-toast");
  if (live.length >= MAX_VISIBLE) live[0].remove();

  const toast = document.createElement("div");
  toast.className = `t-toast t-${kind}`;
  toast.setAttribute("role", kind === "error" ? "alert" : "status");
  toast.innerHTML = `
    <svg class="t-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">${ICONS[kind]}</svg>
    <div class="t-msg">${escapeHTML(message)}</div>
    <button type="button" class="t-x" aria-label="Dismiss">&times;</button>
    <span class="t-bar" style="animation-duration:${duration}ms"></span>`;
  container.appendChild(toast);

  let remaining = duration, started = Date.now(), timer;
  const close = () => { clearTimeout(timer); toast.classList.add("t-out"); setTimeout(() => toast.remove(), 220); };
  const start = () => { started = Date.now(); timer = setTimeout(close, remaining); };
  toast.addEventListener("mouseenter", () => { clearTimeout(timer); remaining -= Date.now() - started; toast.classList.add("t-paused"); });
  toast.addEventListener("mouseleave", () => { toast.classList.remove("t-paused"); start(); });
  toast.querySelector(".t-x").addEventListener("click", close);
  start();
}

window.showToast = showToast;
