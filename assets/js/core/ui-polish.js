/**
 * UI polish + "stay where you were" persistence.
 *  - loading skeletons (tables + KPI numbers) until data is ready
 *  - sticky table headers, richer empty states
 *  - remembers filters/search fields, sub-tabs and scroll position across reloads
 * Page + transaction tab/date/dept are already remembered by core/router.js.
 */
const LS = (() => { try { return window.localStorage; } catch (_) { return null; } })();
const get = k => { try { return LS && LS.getItem(k); } catch (_) { return null; } };
const set = (k, v) => { try { LS && LS.setItem(k, v); } catch (_) {} };
const KEY = "toner_ui_";
let ready = false;

// ---------- 1. skeletons -------------------------------------------------------
const SKELETON_BODIES = ["inventory-tbody", "txns-tbody", "logs-tbody", "users-tbody", "page-locations-tbody", "page-suppliers-tbody"];
function skeletonRows(n = 6) {
  const bar = w => `<span class="sk-bar" style="width:${w}%"></span>`;
  return Array.from({ length: n }, (_, i) =>
    `<tr class="sk-row" aria-hidden="true"><td colspan="12" class="px-5 py-4"><div class="flex items-center gap-4">${bar(14)}${bar(30 + (i % 3) * 8)}${bar(12)}${bar(10)}</div></td></tr>`).join("");
}
function injectSkeletons() {
  SKELETON_BODIES.forEach(id => { const el = document.getElementById(id); if (el && !el.children.length) el.innerHTML = skeletonRows(); });
}

// ---------- 2. sticky headers + empty states -----------------------------------
function stickyHeaders() {
  document.querySelectorAll(".page-view thead").forEach(th => {
    const wrap = th.closest(".overflow-x-auto");
    if (wrap) wrap.classList.add("sticky-wrap");
  });
}
const EMPTY_ICON = '<svg class="empty-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>';
function enhanceEmptyStates() {
  [["inventory-empty-state", "inventory", "No toners match your filters"], ["txns-empty-state", "transactions", null]].forEach(([id, kind, title]) => {
    const el = document.getElementById(id); if (!el || el.dataset.polished) return;
    el.dataset.polished = "1"; el.classList.add("empty-card");
    if (!el.querySelector(".empty-ico")) el.insertAdjacentHTML("afterbegin", `<div class="empty-ico-wrap">${EMPTY_ICON}</div>`);
    el.insertAdjacentHTML("beforeend", `<button type="button" data-empty-clear="${kind}" class="empty-btn">Clear filters</button>`);
  });
}
document.addEventListener("click", e => {
  const b = e.target.closest("[data-empty-clear]"); if (!b) return;
  if (b.dataset.emptyClear === "inventory") { window.resetInventoryFilters && window.resetInventoryFilters(); window.renderInventory && window.renderInventory(); }
  else { const c = document.getElementById("btn-clear-txn-filters"); if (c) c.click(); }
});

// ---------- 3. persistence ------------------------------------------------------
const INCLUDE = /^(filter|search|inv|txn|dash|logs|loc|sup|page-loc|page-sup)|filter|search|range|period|custom/i;
const EXCLUDE = /^(modal|input-|email|smtp|user|pass|def-|rel-|del-)|password|file/i;
const persistable = () => [...document.querySelectorAll(".page-view input[id], .page-view select[id]")]
  .filter(el => INCLUDE.test(el.id) && !EXCLUDE.test(el.id) && !["password", "file", "hidden", "button", "submit"].includes(el.type));
const currentPage = () => (document.querySelector(".page-view:not(.hidden)") || {}).id || "";
const scroller = () => document.getElementById("main-area");

function snapshot() {
  if (!ready) return;
  const fields = {}; persistable().forEach(el => { fields[el.id] = el.type === "checkbox" ? el.checked : el.value; });
  set(KEY + "fields", JSON.stringify(fields));
  const act = document.querySelector("[data-loc-tab].active, [data-loc-tab][aria-selected='true']");
  if (act) set(KEY + "loc_tab", act.getAttribute("data-loc-tab"));
  const sc = scroller(), pg = currentPage();
  if (sc && pg) { const all = JSON.parse(get(KEY + "scroll") || "{}"); all[pg] = sc.scrollTop; set(KEY + "scroll", JSON.stringify(all)); }
}
function restore() {
  let fields = {}; try { fields = JSON.parse(get(KEY + "fields") || "{}"); } catch (_) {}
  persistable().forEach(el => {
    if (!(el.id in fields)) return;
    if (el.type === "checkbox") el.checked = !!fields[el.id]; else if (el.tagName === "SELECT" && ![...el.options].some(o => o.value === fields[el.id])) return; else el.value = fields[el.id];
    el.dispatchEvent(new Event("input", { bubbles: true })); el.dispatchEvent(new Event("change", { bubbles: true }));
  });
  const lt = get(KEY + "loc_tab"); if (lt) { const b = document.querySelector(`[data-loc-tab="${lt}"]`); if (b) b.click(); }
  setTimeout(() => {   // restore scroll after the page has rendered its rows
    let all = {}; try { all = JSON.parse(get(KEY + "scroll") || "{}"); } catch (_) {}
    const sc = scroller(), pg = currentPage(); if (sc && all[pg]) sc.scrollTop = all[pg];
  }, 120);
}
let st; scroller() && scroller().addEventListener("scroll", () => { clearTimeout(st); st = setTimeout(snapshot, 250); }, { passive: true });
window.addEventListener("pagehide", snapshot);
document.addEventListener("visibilitychange", () => { if (document.visibilityState === "hidden") snapshot(); });
window.addEventListener("beforeunload", snapshot);
document.addEventListener("click", e => { if (e.target.closest("[data-loc-tab], .nav-link")) setTimeout(snapshot, 80); });

// ---------- boot -----------------------------------------------------------------
injectSkeletons(); stickyHeaders(); enhanceEmptyStates();
const finish = () => { document.body.classList.remove("is-loading"); document.querySelectorAll(".sk-row").forEach(r => r.remove()); };
document.addEventListener("toner:ready", () => { finish(); stickyHeaders(); enhanceEmptyStates(); restore(); setTimeout(() => { ready = true; }, 400); });
setTimeout(finish, 9000); // safety: never leave the page in a loading state
window.__tonerPersist = { snapshot, restore };
