/**
 * Application bootstrap: wires every view/modal module and starts the app.
 * Each module lives in assets/js/{layout,views,modals,services,data}/.
 */
import { navigateTo } from "./core/router.js";
import { AppState } from "./core/state.js";
import { showToast } from "./core/toast.js";
import { initNotificationsPanelListeners, renderAlerts } from "./layout/notifications-panel.js";
import { initShellListeners } from "./layout/shell.js";
import { initAddTonerModalListeners } from "./modals/add-toner.js";
import { initAppConfirmModalListeners } from "./modals/app-confirm.js";
import { initDefectiveDetailModalListeners } from "./modals/defective-detail.js";
import { initDefectiveReplaceModalListeners } from "./modals/defective-replace.js";
import { initDefectiveModalListeners } from "./modals/defective.js";
import { initDuplicateModalListeners } from "./modals/duplicate.js";
import { initEditLocationModalListeners } from "./modals/edit-location.js";
import { initMailLogModalListeners } from "./modals/mail-log.js";
import { initNotFoundModalListeners } from "./modals/not-found.js";
import { initReceiveModalListeners } from "./modals/receive.js";
import { initReleaseDetailModalListeners } from "./modals/release-detail.js";
import { initReleaseModalListeners, loadAdminUsersForIssuance, loadReleaseLocations } from "./modals/release.js";
import { initRemoveTonerModalListeners } from "./modals/remove-toner.js";
import { initResetModalListeners } from "./modals/reset.js";
import { initStockCardModalListeners } from "./modals/stock-card.js";
import { initUserSavedModalListeners } from "./modals/user-saved.js";
import { initUserModalListeners } from "./modals/user.js";
import { detectBackend, loadFromBackend } from "./services/backend.js";
import { initializeDataSafely } from "./services/storage-service.js";
import { initDashboardViewListeners } from "./views/dashboard.js";
import { initEmailConfigViewListeners } from "./views/email-config.js";
import { initInventoryViewListeners } from "./views/inventory.js";
import { initLocationsViewListeners, loadSuppliers } from "./views/locations.js";
import { initReceiveViewListeners } from "./views/receive.js";
import { initReleaseViewListeners } from "./views/release.js";
import { initTransactionsViewListeners } from "./views/transactions.js";
import { initUsersViewListeners } from "./views/users.js";
import "./data/constants.js";
import "./data/demo-data.js";
import "./layout/sidebar.js";
import "./modals/kpi-detail.js";
import "./modals/lifespan-detail.js";
import "./modals/locations.js";
import "./modals/logout.js";
import "./services/stock-utils.js";
import "./services/ticket-service.js";

function setupEventListeners() {
  initShellListeners();
  initReceiveModalListeners();
  initReleaseModalListeners();
  initDefectiveModalListeners();
  initNotificationsPanelListeners();
  initReceiveViewListeners();
  initReleaseViewListeners();
  initInventoryViewListeners();
  initAddTonerModalListeners();
  initEmailConfigViewListeners();
  initLocationsViewListeners();
  initEditLocationModalListeners();
  initUsersViewListeners();
  initUserModalListeners();
  initUserSavedModalListeners();
  initRemoveTonerModalListeners();
  initReleaseDetailModalListeners();
  initDefectiveDetailModalListeners();
  initAppConfirmModalListeners();
  initStockCardModalListeners();
  initResetModalListeners();
  initTransactionsViewListeners();
  initDefectiveReplaceModalListeners();
  initDashboardViewListeners();
  initDuplicateModalListeners();
  initMailLogModalListeners();
  initNotFoundModalListeners();
}

document.addEventListener('DOMContentLoaded', async () => {
  document.body.classList.add('is-loading');
  setupEventListeners();
  loadReleaseLocations().catch(() => {});
  loadSuppliers().catch(() => {});
  loadAdminUsersForIssuance().catch(() => {});
  const online = await detectBackend();
  if (online) {
    try {
      await loadFromBackend();
      console.info('[Toner] Connected to PHP/SQL Server backend');
    } catch (e) {
      console.warn('[Toner] Backend load failed, using local demo', e);
      AppState.useBackend = false;
      initializeDataSafely();
    }
  } else {
    console.warn('[Toner] Backend offline — localStorage demo mode. Open api/health.php to debug.');
    showToast('Database offline — changes will NOT save to SQL Server.', 'warning');
    initializeDataSafely();
  }
  try {
    const savedPage = localStorage.getItem('toner_ui_page');
    const savedTab = localStorage.getItem('toner_ui_txn_tab');
    const savedDate = localStorage.getItem('toner_ui_txn_date');
    const savedDept = localStorage.getItem('toner_ui_txn_dept');
    if (savedTab && ['RECEIVED','RELEASED','DEFECTIVE'].includes(savedTab)) {
      AppState.filters.transactionType = savedTab;
    }
    if (savedDate) AppState.filters.transactionDate = savedDate;
    if (savedDept) AppState.filters.transactionDept = savedDept;
    // Sync date select UI
    const dateSel = document.getElementById('filter-txn-date');
    if (dateSel && savedDate) dateSel.value = savedDate;
    if (savedPage && ['dashboard','inventory','transactions','locations','logs','email-config','users'].includes(savedPage)) {
      navigateTo(savedPage);
    } else {
      navigateTo('dashboard');
    }
  } catch (_) { navigateTo('dashboard'); }

  renderAlerts();
  document.dispatchEvent(new CustomEvent('toner:ready'));
});
