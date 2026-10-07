/**
 * Bootstrap — ES module entry.
 * Loads core utilities, then app.js (which wires all layout/view/modal modules).
 */
import "./core/state.js";
import "./core/format.js";
import "./core/toast.js";
import "./core/api.js";
import "./core/router.js";
import { bindModalChrome } from "./core/modal.js";
import "./core/notifications.js";

// Application bootstrap: imports every layout/view/modal/service module and starts the app
import "./app.js";

// View / modal extension modules (filled in subsequent steps)
import "./views/dashboard.js";
import "./views/inventory.js";
import "./views/transactions.js";
import "./views/locations.js";
import "./views/logs.js";
import "./views/email-config.js";
import "./views/users.js";
import "./views/receive.js";
import "./views/release.js";

import "./modals/receive.js";
import "./modals/release.js";
import "./modals/defective.js";
import "./modals/stock-card.js";
import "./modals/add-toner.js";
import "./modals/remove-toner.js";
import "./modals/user.js";
import "./modals/app-confirm.js";
import "./modals/logout.js";
import "./modals/mail-log.js";
import "./modals/kpi-detail.js";
import "./modals/duplicate.js";
import "./modals/not-found.js";
import "./modals/defective-detail.js";
import "./modals/defective-replace.js";
import "./modals/release-detail.js";
import "./modals/user-saved.js";
import "./modals/edit-location.js";
import "./modals/reset.js";
import "./modals/lifespan-detail.js";

bindModalChrome();
console.info("[Toner] boot complete — API base", window.TONER_API_BASE);
