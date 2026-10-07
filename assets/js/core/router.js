import { AppState } from "./state.js";

/** Page refresh handlers registered by view modules */
const pageHandlers = {};

export function registerPageHandler(pageId, fn) {
  pageHandlers[pageId] = fn;
}

export function navigateTo(pageId) {
  const validPages = ["dashboard", "inventory", "transactions", "locations", "logs", "email-config", "users"];
  if (!validPages.includes(pageId)) pageId = "dashboard";
  AppState.currentPage = pageId;
  try {
    localStorage.setItem("toner_ui_page", pageId);
    localStorage.setItem("toner_ui_txn_tab", AppState.filters.transactionType || "RECEIVED");
    localStorage.setItem("toner_ui_txn_date", AppState.filters.transactionDate || "ALL");
    localStorage.setItem("toner_ui_txn_dept", AppState.filters.transactionDept || "ALL");
  } catch (_) {}

  document.querySelectorAll(".page-view").forEach((view) => view.classList.add("hidden"));
  const targetView = document.getElementById(`view-${pageId}`);
  if (targetView) targetView.classList.remove("hidden");

  document.querySelectorAll(".nav-link").forEach((link) => {
    link.classList.remove("bg-blue-50", "text-blue-700", "font-semibold");
    link.classList.add("text-slate-600");
  });
  const activeLink = document.getElementById(`nav-${pageId}`);
  if (activeLink) {
    activeLink.classList.add("bg-blue-50", "text-blue-700", "font-semibold");
    activeLink.classList.remove("text-slate-600");
  }

  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  if (sidebar && backdrop) {
    sidebar.classList.add("-translate-x-full");
    backdrop.classList.add("hidden");
  }

  // Prefer registered handler; fall back to globals bridged onto window by each module
  if (typeof pageHandlers[pageId] === "function") {
    pageHandlers[pageId]();
  } else if (pageId === "dashboard") {
    if (typeof window.renderDashboard === "function") window.renderDashboard();
    if (typeof window.renderCharts === "function") window.renderCharts();
  } else if (pageId === "inventory" && typeof window.renderInventory === "function") {
    window.renderInventory();
  } else if (pageId === "transactions" && typeof window.renderTransactions === "function") {
    window.renderTransactions();
  } else if (pageId === "locations" && typeof window.loadLocationsPage === "function") {
    window.loadLocationsPage();
  } else if (pageId === "logs" && typeof window.loadSystemLogs === "function") {
    window.loadSystemLogs();
  } else if (pageId === "email-config" && typeof window.loadEmailSettings === "function") {
    window.loadEmailSettings();
  } else if (pageId === "users" && typeof window.loadUsers === "function") {
    window.loadUsers();
  }

  if (typeof window.renderAlerts === "function") window.renderAlerts();
  window.scrollTo({ top: 0, behavior: "smooth" });
}

window.navigateTo = navigateTo;
window.registerPageHandler = registerPageHandler;
