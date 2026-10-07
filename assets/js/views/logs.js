/**
 * View: System Logs
 * Markup: partials/views/logs.php
 * Moved from app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML, formatDateTime } from "../core/format.js";
import { registerPageHandler } from "../core/router.js";

export function syncLogsCustomRangeUI() {
  const period = document.getElementById("filter-logs-period")?.value || "ALL";
  const wrap = document.getElementById("logs-custom-range");
  if (wrap) {
    if (period === "CUSTOM") wrap.classList.remove("hidden");
    else wrap.classList.add("hidden");
  }
}

export function populateLogsFilterMeta(meta, keepSelection) {
  const actionSel = document.getElementById("filter-logs-action");
  const actorSel = document.getElementById("filter-logs-actor");
  const prevAction = keepSelection ? (actionSel?.value || "ALL") : "ALL";
  const prevActor = keepSelection ? (actorSel?.value || "ALL") : "ALL";
  if (actionSel) {
    const actions = (meta && meta.actions) || [];
    actionSel.innerHTML =
      '<option value="ALL">All actions</option>' +
      actions
        .map(
          (a) =>
            `<option value="${escapeHTML(a.key)}">${escapeHTML(a.label || a.key)}</option>`
        )
        .join("");
    if ([...actionSel.options].some((o) => o.value === prevAction)) actionSel.value = prevAction;
  }
  if (actorSel) {
    const actors = (meta && meta.actors) || [];
    actorSel.innerHTML =
      '<option value="ALL">All admins</option>' +
      actors
        .map((a) => {
          const val = a.username || a.name || "";
          const label =
            a.name && a.username && a.name !== a.username
              ? `${a.name} (${a.username})`
              : a.name || a.username || "";
          return `<option value="${escapeHTML(val)}">${escapeHTML(label)}</option>`;
        })
        .join("");
    if ([...actorSel.options].some((o) => o.value === prevActor)) actorSel.value = prevActor;
  }
}

export async function loadSystemLogs() {
  const tbody = document.getElementById("logs-tbody");
  const q = (document.getElementById("filter-logs-search")?.value || "").trim();
  const period = document.getElementById("filter-logs-period")?.value || "ALL";
  const action = document.getElementById("filter-logs-action")?.value || "ALL";
  const actor = document.getElementById("filter-logs-actor")?.value || "ALL";
  const from = document.getElementById("filter-logs-from")?.value || "";
  const to = document.getElementById("filter-logs-to")?.value || "";
  syncLogsCustomRangeUI();
  if (tbody)
    tbody.innerHTML =
      '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading…</td></tr>';
  try {
    const params = new URLSearchParams();
    params.set("limit", "200");
    if (q) params.set("q", q);
    if (period && period !== "ALL") params.set("period", period);
    if (period === "CUSTOM") {
      if (from) params.set("from", from);
      if (to) params.set("to", to);
    }
    if (action && action !== "ALL") params.set("action", action);
    if (actor && actor !== "ALL") params.set("actor", actor);
    const data = await apiRequest("logs.php?" + params.toString());
    populateLogsFilterMeta(data.meta, true);
    const logs = data.logs || [];
    if (!logs.length) {
      if (tbody)
        tbody.innerHTML =
          '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No logs match these filters.</td></tr>';
      return;
    }
    if (tbody) {
      tbody.innerHTML = logs
        .map((L) => {
          const when = L.createdAt ? formatDateTime(L.createdAt) : "—";
          let admin = escapeHTML(L.actorName || L.actorUsername || "—");
          if (L.actorName && L.actorUsername && L.actorName !== L.actorUsername) {
            admin = `${escapeHTML(L.actorName)} <span class="text-xs text-slate-400">(${escapeHTML(L.actorUsername)})</span>`;
          }
          const parts = [];
          if (L.details) parts.push(escapeHTML(L.details));
          if (L.referenceNumber)
            parts.push(
              '<span class="font-mono text-xs">' + escapeHTML(L.referenceNumber) + "</span>"
            );
          if (L.itemCode)
            parts.push('<span class="font-mono text-xs">' + escapeHTML(L.itemCode) + "</span>");
          const details = parts.length ? parts.join(" · ") : "—";
          return `<tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">${escapeHTML(when)}</td>
          <td class="px-4 py-3 font-semibold text-slate-900">${escapeHTML(L.actionLabel || L.actionKey || "—")}</td>
          <td class="px-4 py-3 text-sm text-slate-600">${details}</td>
          <td class="px-4 py-3 text-sm">${admin}</td>
        </tr>`;
        })
        .join("");
    }
  } catch (e) {
    if (tbody)
      tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-rose-600 text-sm">${escapeHTML(e.message || "Failed to load logs")}</td></tr>`;
  }
}

export function resetLogsFilters() {
  const s = document.getElementById("filter-logs-search");
  const p = document.getElementById("filter-logs-period");
  const a = document.getElementById("filter-logs-action");
  const u = document.getElementById("filter-logs-actor");
  const f = document.getElementById("filter-logs-from");
  const to = document.getElementById("filter-logs-to");
  if (s) s.value = "";
  if (p) p.value = "ALL";
  if (a) a.value = "ALL";
  if (u) u.value = "ALL";
  if (f) f.value = "";
  if (to) to.value = "";
  syncLogsCustomRangeUI();
  loadSystemLogs();
}

/** Wire Logs view controls (same handlers as original). */
export function initLogsView() {
  const btnRefreshLogs = document.getElementById("btn-refresh-logs");
  if (btnRefreshLogs) btnRefreshLogs.addEventListener("click", loadSystemLogs);

  const btnApplyLogs = document.getElementById("btn-apply-logs-filter");
  if (btnApplyLogs) btnApplyLogs.addEventListener("click", loadSystemLogs);

  const btnClearLogs = document.getElementById("btn-clear-logs-filter");
  if (btnClearLogs) btnClearLogs.addEventListener("click", resetLogsFilters);

  const logsPeriod = document.getElementById("filter-logs-period");
  if (logsPeriod) {
    logsPeriod.addEventListener("change", () => {
      syncLogsCustomRangeUI();
      if (logsPeriod.value !== "CUSTOM") loadSystemLogs();
    });
  }

  const logsAction = document.getElementById("filter-logs-action");
  if (logsAction) logsAction.addEventListener("change", loadSystemLogs);

  const logsActor = document.getElementById("filter-logs-actor");
  if (logsActor) logsActor.addEventListener("change", loadSystemLogs);

  const filterLogs = document.getElementById("filter-logs-search");
  if (filterLogs) {
    let logTimer = null;
    filterLogs.addEventListener("input", () => {
      clearTimeout(logTimer);
      logTimer = setTimeout(loadSystemLogs, 300);
    });
  }
}

// Register with router; expose on window for any remaining callers
registerPageHandler("logs", loadSystemLogs);
Object.assign(window, {
  syncLogsCustomRangeUI,
  populateLogsFilterMeta,
  loadSystemLogs,
  resetLogsFilters,
  initLogsView
});

initLogsView();

export default { name: "logs" };
