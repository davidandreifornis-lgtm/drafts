import { escapeHTML } from "./format.js";

export function showToast(message, type = "info") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `p-4 rounded-xl shadow-lg border text-sm flex items-start gap-3 transform transition-all duration-300 pointer-events-auto max-w-sm ${
    type === "success" ? "bg-white border-emerald-200 text-emerald-900 shadow-emerald-100" :
    type === "error" ? "bg-white border-rose-200 text-rose-900 shadow-rose-100" :
    type === "warning" ? "bg-white border-amber-200 text-amber-900 shadow-amber-100" :
    "bg-white border-blue-200 text-blue-900 shadow-blue-100"
  }`;

  const icon = type === "success"
    ? `<svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    : type === "error"
    ? `<svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    : `<svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;

  toast.innerHTML = `
    ${icon}
    <div class="flex-1 text-xs font-medium leading-relaxed">${escapeHTML(message)}</div>
    <button type="button" class="text-slate-400 hover:text-slate-600 ml-2" onclick="this.parentElement.remove()">&times;</button>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add("opacity-0", "translate-y-2");
    setTimeout(() => toast.remove(), 300);
  }, 4500);
}

window.showToast = showToast;
