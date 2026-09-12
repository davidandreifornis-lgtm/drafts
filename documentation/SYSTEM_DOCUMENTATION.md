# PRINTER TONER INVENTORY MANAGEMENT SYSTEM
## Comprehensive System & Technical Documentation

---

### 1. System Overview

The **Printer Toner Inventory Management System** is an administrative web application for tracking toner consumables across departments. Administrators record **deliveries**, **stock issuances**, and **defective returns** using a **ticket reference number** plus details they enter manually.

Data is stored in **MySQL** and accessed through a **PHP API**. Access is restricted to a single **admin** account via PHP sessions. Optional **SMTP email** notifies the admin when stock is low or out.

This is **not** an approval workflow. Tickets are treated as **reference numbers** for audit and duplicate control, not as pre-loaded approved payloads that drive automatic quantities.

---

### 2. System Objectives

- **Accurate stock tracking** — every change is a recorded transaction tied to a reference number.
- **Operational simplicity** — receive, issue, and flag defective items with clear forms.
- **Prevent double posting** — the same reference number cannot be processed twice.
- **Visibility** — dashboard KPIs, charts, stock cards, and filtered transaction history.
- **Alerts** — in-app low/out-of-stock notifications and optional email to the admin.
- **Server-backed data** — MySQL persistence suitable for XAMPP or production PHP hosting.

---

### 3. Features

- **Admin login / logout** (`login.php`, `logout.php`, session-based).
- **Receive Delivery** — reference, toner code, quantity, date, supplier → stock increases.
- **Stock Issuance** — reference, toner, department, location → always **1 unit**; date fixed to **today**.
- **Return Defective** — flag a completed issuance; usable stock is **not** restored.
- **Toner Inventory** — one row per unique toner code; compatible printers as chips; add/remove toner; multi-row printer entry.
- **Stock Card** — click a toner for full movement history (in / out / running balance).
- **Transaction History** — tabs: Incoming Deliveries, Releases by Department, Defective Returns; search; date filters; **Export Filtered CSV**.
- **Dashboard** — SKU count, total stock, low/out counts, today’s activity, department demand chart, stock status chart.
- **Low-stock email** — SMTP (e.g. Gmail App Password) when quantity ≤ reorder level.
- **API auth** — inventory/transaction endpoints require an admin session.

---

### 4. Project Structure

```text
toner-system/   (or htdocs/drafts/)
│
├── index.php                 # Main SPA (requires login)
├── login.php                 # Admin sign-in
├── logout.php                # End session → login.php
├── README.md
│
├── config/
│   ├── database.php          # MySQL connection
│   ├── bootstrap.php         # PDO, JSON helpers, API auth
│   ├── auth.php              # Admin username/password
│   ├── auth_lib.php          # Session helpers
│   ├── mail.php              # Admin email + SMTP settings
│   └── mailer.php            # Send mail + low-stock check
│
├── api/
│   ├── health.php
│   ├── inventory.php         # GET list / POST add / DELETE remove
│   ├── transactions.php      # GET list (?type=&from=&to=)
│   ├── delivery.php          # POST record delivery
│   ├── release.php           # POST record issuance
│   ├── defective.php         # POST flag defective
│   └── check_low_stock.php   # Manual/cron low-stock email
│
├── sql/
│   └── schema.sql            # Database + seed data
│
├── storage/
│   ├── mail.log
│   └── low_stock_alerts.json # Email cooldown tracker
│
└── documentation/
    └── SYSTEM_DOCUMENTATION.md
```

---

### 5. User Workflow

```text
[Admin]
   │
   ├─► Login (login.php)
   │
   ├─► Receive Delivery
   │      Enter ref + toner + qty + date + supplier
   │      → Stock UP + RECEIVED transaction
   │
   ├─► Stock Issuance
   │      Enter ref + toner + department + location
   │      → Stock DOWN by 1 + RELEASED transaction
   │
   ├─► Return Defective
   │      Enter issued ref (+ notes)
   │      → DEFECTIVE record (stock unchanged)
   │
   ├─► Inventory / Stock Card / History / Dashboard
   │
   └─► Logout → login.php
```

---

### 6. Dashboard

- **Metric cards**
  - Total Toner SKUs (unique codes)
  - Total stock on hand
  - Low stock count
  - Out of stock count
  - Today’s deliveries / releases
  - Tickets processed (reference count)
  - Last processed ticket
- **Charts**
  - Departments needing toner most often (issuance volume)
  - Stock status (In / Low / Out)
- **Notifications** — low/out stock and recent actions (bell icon)

---

### 7. Inventory Management

- **One row per toner code** (quantities aggregated if multiple lines existed historically).
- **Columns**: Toner Code, Compatible Printer(s), Supplier, Quantity, Stock Status, Actions.
- **Compatible printers** stored as separate values; displayed as chips.
- **Add Toner**: code, printers (one row each via “Add printer”), supplier, starting qty, reorder level.
- **Remove Toner**: removes master inventory row; past transactions kept for audit.
- **Stock Card**: beginning balance + deliveries + issuances + defective flags with running balance.
- **Filters**: search by toner code; status (In / Low / Out).

---

### 8. Delivery Workflow

1. Open **Receive Delivery**.
2. Enter **Delivery Reference No.** (any reference used by the organization).
3. Select **Toner Code**, **Quantity**, **Date**, **Supplier**.
4. **Record Delivery**.
5. System:
   - Rejects if reference already exists in transactions.
   - Adds quantity to that toner’s stock.
   - Inserts a `RECEIVED` row in `transactions`.

---

### 9. Stock Issuance Workflow

1. Open **Stock Issuance**.
2. Enter **Issuance Reference No.**
3. Select **Toner**, **Department**, **Location** (auto-filled if only one location for the department).
4. **Date** is always **today** (read-only).
5. Quantity is always **1**.
6. **Record Issuance**.
7. System:
   - Rejects duplicate reference.
   - Rejects if stock &lt; 1.
   - Decrements stock by 1.
   - Inserts a `RELEASED` row with department and location.
   - Triggers low-stock email check when applicable.

---

### 10. Defective Return Workflow

1. Open **Return Defective**.
2. Enter the **issuance ticket** already recorded as `RELEASED`.
3. Optional notes → confirm.
4. System flags the release and adds a `DEFECTIVE` transaction.
5. **Usable inventory quantity does not increase.**

---

### 11. Stock formulas

**Delivery**

$$\text{Stock}_{\text{new}} = \text{Stock}_{\text{current}} + \text{Quantity}$$

**Issuance**

$$\text{Stock}_{\text{new}} = \text{Stock}_{\text{current}} - 1$$

with $\text{Stock}_{\text{new}} \ge 0$.

**Defective** — no change to on-hand usable stock.

---

### 12. Duplicate Prevention

`reference_number` is checked against existing transactions before insert.  
If found, the API returns **409** and the UI shows a duplicate message. Processing stops.

---

### 13. Transaction History

| Tab | Content |
|-----|---------|
| Incoming Deliveries | Ref, short date, toner, qty, supplier |
| Releases by Department | Ref, date, toner, department, location (+ defective badge) |
| Defective Returns | Ref, date, toner, department, location, notes |

**Filters**: search text, date (All Time / Today / Week / **This Month default** / Custom From–To).  
**Export Filtered CSV** exports only rows matching the active tab and filters.

---

### 14. Stock Status Rules

- $\text{Quantity} \le 0$ → **OUT OF STOCK**
- $\text{Quantity} \le \text{Reorder Level}$ → **LOW STOCK**
- $\text{Quantity} > \text{Reorder Level}$ → **IN STOCK**

---

### 15. Authentication

| Item | Detail |
|------|--------|
| Entry | `login.php` |
| Guard | `auth_require_login()` on `index.php` |
| API | `auth_require_api()` via `bootstrap.php` (401 if not logged in) |
| Logout | `logout.php` clears session |
| Defaults | Username `admin` / Password `admin123` (change in `config/auth.php`) |

---

### 16. Database Schema (current)

```sql
-- Database: toner_inventory

CREATE TABLE inventory (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ink_code      VARCHAR(64)  NOT NULL UNIQUE,
  brand         VARCHAR(64)  NOT NULL DEFAULT '',
  printer_model VARCHAR(255) NOT NULL DEFAULT '',  -- may list multiple printers
  quantity      INT          NOT NULL DEFAULT 0,
  reorder_level INT          NOT NULL DEFAULT 3,
  supplier      VARCHAR(128) NOT NULL DEFAULT '',
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  txn_code         VARCHAR(32)  NOT NULL UNIQUE,
  type             ENUM('RECEIVED','RELEASED','DEFECTIVE') NOT NULL,
  reference_number VARCHAR(64)  NOT NULL,
  ink_code         VARCHAR(64)  NOT NULL,
  quantity         INT          NOT NULL DEFAULT 1,
  txn_date         DATE         NOT NULL,
  supplier         VARCHAR(128) NULL,
  department       VARCHAR(64)  NULL,
  location         VARCHAR(128) NULL,
  given_to         VARCHAR(128) NULL,
  purpose          VARCHAR(255) NULL,
  status           VARCHAR(32)  NOT NULL DEFAULT 'RECORDED',
  defective        TINYINT(1)   NOT NULL DEFAULT 0,
  defective_at     DATETIME     NULL,
  defective_notes  TEXT         NULL,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ref (reference_number),
  KEY idx_type (type),
  KEY idx_ink (ink_code),
  KEY idx_date (txn_date)
);
```

See `sql/schema.sql` for seed data.

---

### 17. API Endpoints

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/health.php` | DB ping |
| GET | `/api/inventory.php` | List inventory |
| POST | `/api/inventory.php` | Add toner |
| DELETE | `/api/inventory.php` | Remove toner (`inkCode`) |
| GET | `/api/transactions.php` | List (`type`, `from`, `to`) |
| POST | `/api/delivery.php` | Record delivery |
| POST | `/api/release.php` | Record issuance |
| POST | `/api/defective.php` | Flag defective |
| GET | `/api/check_low_stock.php` | Low-stock email (`?force=1` clears cooldown) |

All API routes require an admin session cookie from login.

---

### 18. Frontend Architecture

- **SPA** inside `index.php` (Tailwind CDN + Chart.js + vanilla JS).
- **`AppState`** holds inks, transactions, filters, charts, notifications.
- **`apiRequest` / `loadFromBackend`** talk to PHP when online.
- **localStorage** remains as a **demo fallback** if the API/health check fails (UI warns that MySQL is offline).

---

### 19. Email Alerts

- Config: `config/mail.php` (`driver`: `smtp` | `log` | `mail`).
- Logic: `notify_low_stock()` in `config/mailer.php`.
- Triggered after release/delivery and via `check_low_stock.php`.
- Cooldown file: `storage/low_stock_alerts.json` (default 12 hours per toner code).
- XAMPP: use **SMTP + Gmail App Password**; plain `mail()` usually fails.

---

### 20. Validation Rules

1. Delivery: reference, toner, qty ≥ 1, date, supplier required.  
2. Issuance: reference, toner, department, location required; qty = 1; date = today.  
3. Duplicate reference blocked.  
4. Issuance blocked if on-hand &lt; 1.  
5. Defective only against an existing `RELEASED` reference; cannot flag twice.  
6. Add toner: unique code; at least one printer; supplier; qty and reorder required.

---

### 21. Security Considerations

- PDO **prepared statements** for SQL.
- Session login required for UI and API.
- Change default admin password before production.
- Escape HTML in the UI for XSS mitigation.
- Do not expose `storage/` publicly (`.htaccess` deny where applicable).
- SMTP App Passwords should not be committed to public repos.

---

### 22. Testing Scenarios

| ID | Test | Expected |
|----|------|----------|
| T01 | Login with wrong password | Error; stay on login |
| T02 | Login as admin | Redirect to index.php |
| T03 | Open index.php logged out | Redirect to login.php |
| T04 | Record delivery new ref | Stock up; row in `transactions` |
| T05 | Same delivery ref again | Duplicate blocked |
| T06 | Issuance with stock ≥ 1 | Stock −1; RELEASED row |
| T07 | Issuance with stock 0 | Rejected |
| T08 | Defective on issued ref | DEFECTIVE row; stock unchanged |
| T09 | Add toner + two printers | Saved; chips show both |
| T10 | Export with Month filter | CSV only filtered rows |
| T11 | Low stock + check_low_stock.php | `sent: true` when SMTP configured |
| T12 | Logout | Session cleared; login page |

---

### 23. Known Limitations

- Single admin account (no multi-role users yet).
- Printer list stored in one `printer_model` field (delimiter-separated), not a separate printers table.
- Email depends on correct SMTP configuration on the host.
- Client still contains localStorage demo paths if the API is unreachable.

---

### 24. Default Credentials

| Item | Value |
|------|--------|
| Admin username | `admin` |
| Admin password | `admin123` |
| Database name | `toner_inventory` |

Update `config/auth.php` and `config/database.php` for your environment.
