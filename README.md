# Toner Management System

Refactored structure of the Printer Toner Inventory Management System.

## Structure

```
toner-management-system/
├── index.php                 # Thin shell (<100 lines): auth, head, includes
├── login.php / logout.php
├── partials/                 # PHP markup, one file per piece
│   ├── layout/               # header, sidebar, toast, global-loading
│   ├── views/                # one file per page (view-*)
│   ├── modals/               # one file per modal
│   └── components/           # reusable PHP helpers (not yet wired in)
├── assets/
│   ├── css/app.css           # design tokens + shared styles
│   └── js/                   # native ES modules, no build step
│       ├── main.js           # entry: core + app.js
│       ├── app.js            # bootstrap: registers listeners, loads data, starts app
│       ├── core/             # state, api, router, modal manager, toast, format
│       ├── data/             # constants + offline demo data
│       ├── services/         # backend (API) layer, local storage, tickets, stock helpers
│       ├── layout/           # shell (nav, sidebar, logout), sidebar user, notifications panel
│       ├── views/            # one module per page (dashboard, inventory, ...)
│       └── modals/           # one module per modal (receive, release, defective, ...)
├── api/                      # PHP API (unchanged)
├── config/                   # DB, auth, mail (unchanged)
└── storage/
```

Each view/modal module owns its functions and exports an `init...Listeners()`
that `app.js` calls on startup. Functions are also bridged onto `window`
(as before) because `core/router.js` calls some of them by name.

## Setup (XAMPP)

1. Start Apache + MySQL.
2. Copy this folder to `C:\xampp\htdocs\toner-management-system\`.
3. Database is **SQL Server** (not MySQL): create a database, then run `sql/schema.sql`, then (optional) `sql/seed_data.sql` to load your existing data. Set the database name in `config/database.php`.
4. Edit `config/database.php` and `config/auth.php` as needed.
5. Open `http://localhost/toner-management-system/login.php`

Default admin: `admin` / `admin123`

## Notes

- Behavior, API contracts, and element IDs match the original monolith.
- View/modal JS modules are extension points for further modularization.
- No npm build required; ES modules load natively in the browser.
