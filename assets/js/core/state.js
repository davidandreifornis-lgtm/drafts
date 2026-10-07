/**
 * Shared application state (single source of truth).
 */
export const AppState = {
  activeMrrLookup: null,
  releaseLocations: [],
  suppliers: [],
  adminUsers: [],
  currentUser: null,
  users: [],
  currentPage: "dashboard",
  inks: [],
  transactions: [],
  tickets: [],
  settings: {
    systemName: "Printer Toner & Ink Inventory Management System",
    reorderNotificationEmail: "admin@organization.com"
  },
  activeDeliveryTicket: null,
  activeReleaseTicket: null,
  activeDefectiveTxn: null,
  pendingRemoveTonerCode: null,
  selectedReleaseLocation: "",
  selectedReleaseDepartment: "",
  charts: {
    activity: null,
    brand: null,
    reportStatus: null,
    reportDept: null
  },
  filters: {
    inventorySearch: "",
    inventoryBrand: "ALL",
    inventoryColor: "ALL",
    inventoryStatus: "ALL",
    transactionSearch: "",
    transactionType: "RECEIVED",
    transactionBrand: "ALL",
    transactionDate: "ALL",
    transactionDateFrom: "",
    transactionDateTo: "",
    transactionDept: "ALL",
    reportDate: "ALL",
    dashboardDate: "ALL",
    dashboardDateFrom: "",
    dashboardDateTo: ""
  },
  notifications: [],
  useBackend: false
};

export function getApiBase() {
  return (window.TONER_API_BASE || (() => {
    const p = location.pathname.replace(/\/[^/]*$/, "");
    return (p || "") + "/api";
  })()).replace(/\/$/, "");
}

// Bridge for classic code still reading global AppState during migration
if (typeof window !== "undefined") {
  window.AppState = AppState;
}
