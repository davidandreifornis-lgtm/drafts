# PRINTER TONER & INK INVENTORY MANAGEMENT SYSTEM
## Comprehensive System & Technical Documentation

---

### 1. System Overview
The **Printer Toner & Ink Inventory Management System** is an enterprise-grade administrative web application designed to govern the full lifecycle of printer consumables across organizations. Unlike conventional inventory software that relies on error-prone manual quantity adjustments, this system strictly enforces **approved delivery tickets** and **approved release tickets** as the single source of truth for stock increments and decrements.

---

### 2. System Objectives
- **Eliminate Manual Quantity Errors**: Remove manual typing of incoming/outgoing counts during receiving and releasing.
- **Enforce Traceability**: Link every physical unit of toner/ink back to its delivery ticket, supplier, purchase order/reference number, and onward to the requesting department and personnel.
- **Maintain Stock Integrity**: Prevent phantom stock through atomic multi-item validations and duplicate-entry blocks.
- **Real-Time Monitoring**: Provide immediate visibility into stock status, low-inventory thresholds, and consumption velocity.

---

### 3. Features
- **Ticket Reference Lookup**: Instant retrieval of approved deliveries (`DEL-YYYY-NNNNN`) and releases (`REL-YYYY-NNNNN`).
- **Automatic Stock Adjustments**: Instant mathematical increments and decrements applied upon administrative confirmation.
- **Atomic Multi-Item Release**: Enforces all-or-nothing transactions—if one item on a release ticket lacks sufficient balance, the entire transaction is rejected.
- **Serial Number Registry**: Tracks serial numbers associated with individual batches and cartridges.
- **Analytical Dashboards**: Real-time visual metrics powered by Chart.js (Stock by Brand, Stock Status Distribution, Transaction Timeline, Department Utilization).
- **Audit Logging**: Comprehensive transaction ledger with multi-variable filtering, CSV export, and print formatting.
- **Zero-Dependency Architecture**: Entire frontend packaged into a single, self-contained HTML file utilizing Tailwind CSS and Chart.js via CDN.

---

### 4. Project Structure
```text
printer-ink-inventory-system/
│
├── index.html                     # Standalone application containing all UI, CSS, and JS
├── README.md                      # Quickstart guide and demo reference dictionary
│
└── documentation/
    └── SYSTEM_DOCUMENTATION.md    # Detailed technical and architectural documentation
```

---

### 5. User Workflow
```text
[Administrator]
       │
       ├─► Receives Inbound Shipment ──► Enters DEL Ref # ──► Reviews Ticket ──► Confirms ──► Stock Incremented
       │
       └─► Receives Release Request  ──► Enters REL Ref # ──► Reviews Ticket ──► Stock Validated ──► Confirms ──► Stock Decremented
```

---

### 6. Dashboard
The dashboard acts as the command center for the stock controller:
- **Metric Cards**:
  - `Total Ink Types`: Number of unique ink/toner codes registered in the catalog.
  - `Total Stock On-Hand`: Cumulative sum of all physical cartridges available across all SKUs.
  - `Low Stock Items`: Count of SKUs with quantity $\le$ reorder level (and $>0$).
  - `Out of Stock Items`: Count of SKUs with quantity $= 0$.
  - `Today's Deliveries`: Count of delivery tickets and total units ingested on the current date.
  - `Today's Releases`: Count of release tickets and total units issued on the current date.
  - `Tickets Processed`: Grand total of all approved tickets executed.
  - `Last Processed Ticket`: Reference number and timestamp of the latest stock adjustment.
- **Inventory Alerts**: Prominently highlights critical stock shortages with direct "View in Inventory" shortcuts.
- **Chart Visualizations**: Responsive graphical breakdowns of brand distribution, health status, and historical volume.

---

### 7. Inventory Management
The inventory catalog provides a detailed view of every managed SKU:
- **Fields**:
  - `Ink Code`: Unique manufacturer SKU (e.g., `HP-682-BLK`, `Canon PG-47`).
  - `Brand`: Equipment manufacturer (e.g., `HP`, `Canon`, `Epson`, `Brother`).
  - `Printer Model`: Supported hardware (e.g., `HP DeskJet 2775`, `Epson L3110`).
  - `Color`: Ink cartridge color classification (`Black`, `Cyan`, `Magenta`, `Yellow`, `Tri-color`).
  - `Quantity`: Current physical balance on hand.
  - `Reorder Level`: Threshold triggering low-stock alert status.
  - `Status`: Computed badge (`IN STOCK`, `LOW STOCK`, `OUT OF STOCK`).
  - `Serial Numbers`: Expandable list of physical serial identifiers tracked for that SKU.
  - `Supplier`: Default procurement partner.
  - `Location`: Physical storage aisle, shelf, or bin.
  - `Last Updated`: Timestamp of most recent transaction.
- **Filtering & Search**: Real-time filtering by keyword, brand, color, and stock health status.

---

### 8. Delivery Ticket Workflow
1. Administrator navigates to **Receive Delivery**.
2. Enters an approved delivery reference number (e.g., `DEL-2026-00125`).
3. Clicks **Search Ticket**.
4. System looks up the reference in `StorageService.getTickets()`.
5. If found, the system verifies:
   - Is ticket status `APPROVED`?
   - Has this ticket already been processed?
6. Ticket details are rendered: Supplier name, date, item table with Serial Numbers, Ink Codes, Current Stock, Incoming Quantity, and Projected Final Stock.
7. Administrator reviews the ticket and clicks **Process Delivery**.
8. Inventory quantities increase, serial numbers are appended, transaction audit records are logged, and the ticket is flagged as processed.

---

### 9. Release Ticket Workflow
1. Administrator navigates to **Release / Give Ink**.
2. Enters an approved release reference number (e.g., `REL-2026-00451`).
3. Clicks **Search Ticket**.
4. System retrieves ticket details: Recipient name, Department, Purpose, Requested items and quantities.
5. System executes **Atomic Stock Pre-Validation**:
   - For every item on the ticket, verifies that `Available Stock >= Requested Quantity`.
6. If any item fails, a prominent warning informs the user of the exact deficit and disables completion.
7. If all items pass, Administrator clicks **Process Release**.
8. Inventory quantities decrement, allocated serial numbers are logged and removed from active pool, transaction audit records are created, and the ticket is flagged as processed.

---

### 10. Automatic Stock Increment
Calculated strictly as:
$$\text{Stock}_{\text{new}} = \text{Stock}_{\text{current}} + \text{Ticket Quantity}$$
If an ink code does not exist in inventory when processing a delivery, the system auto-registers the SKU using metadata from the ticket.

---

### 11. Automatic Stock Decrement
Calculated strictly as:
$$\text{Stock}_{\text{new}} = \text{Stock}_{\text{current}} - \text{Ticket Quantity}$$
Under no circumstances can $\text{Stock}_{\text{new}} < 0$. If an SKU does not exist in the inventory catalog during a release attempt, the transaction is immediately aborted.

---

### 12. Duplicate Prevention
Every executed ticket is permanently recorded in the processed tickets registry and transaction history.
When an administrator searches for a reference number:
1. `TicketService.isAlreadyProcessed(refNumber)` evaluates whether a transaction already exists with that reference.
2. If already processed, the system triggers the **Duplicate Warning Modal** and prevents re-execution.

---

### 13. Serial Number Tracking
Each physical consumable unit can carry a unique serial identifier (e.g., `SN-H682-001`).
- During **Delivery**, ticket serial numbers are concatenated to the inventory item's active `serialNumbers` array.
- During **Release**, the allocated serial numbers are transferred to the transaction record and removed from active on-hand inventory to prevent duplicate assignments.

---

### 14. Transaction History
Maintains a chronological, immutable ledger of all stock movements:
- `Transaction ID`: Standardized identifier (`TXN-00001`).
- `Reference Number`: The originating approved ticket (`DEL-YYYY-XXXXX` or `REL-YYYY-XXXXX`).
- `Date & Time`: Exact execution timestamp.
- `Type`: `RECEIVED` (Inbound stock increase) or `RELEASED` (Outbound stock decrease).
- `Ink Code & Brand`: Consumable specification.
- `Color`: Color identifier.
- `Quantity`: Number of units moved.
- `Counterparty`: Supplier name (for inbound) or Recipient & Department (for outbound).
- `Purpose`: Stated operational justification.
- `Status`: Authority status (`APPROVED`).

---

### 15. Reports
Dedicated reporting module providing aggregated operational analytics:
- **Date Range Filters**: `Today`, `This Week`, `This Month`, `Custom Range`, `All Time`.
- **Metrics**: Total Inbound Units, Total Outbound Units, Net Stock Balance Change, Top Ingested Brand, Top Consuming Department.
- **Detailed Sub-Reports**:
  1. Brand Inventory Breakdown.
  2. Low Stock & Out of Stock Action Checklist.
  3. Department Consumables Consumption Matrix.
  4. Color Distribution Ratios.

---

### 16. Stock Status Rules
Stock status is evaluated uniformly across the application via centralized utility logic:
- If $\text{Quantity} \le 0$: `OUT OF STOCK` (Red badge)
- If $\text{Quantity} \le \text{Reorder Level}$: `LOW STOCK` (Amber badge)
- If $\text{Quantity} > \text{Reorder Level}$: `IN STOCK` (Green badge)

---

### 17. LocalStorage Architecture
All client state is saved in browser LocalStorage under dedicated namespaces:
- `inventoryInks`: Serialized JSON array of inventory SKU objects.
- `inventoryTransactions`: Serialized JSON array of executed transaction objects.
- `inventoryTickets`: Serialized JSON array of delivery and release tickets (with processed flags).
- `inventorySettings`: Serialized configuration parameters (e.g., system title, alerts).

---

### 18. StorageService
A centralized JavaScript service object encapsulating all persistent storage reads and writes. Direct calls to `localStorage.getItem()` and `localStorage.setItem()` are strictly prohibited outside this module.

```javascript
const StorageService = {
  getInks() { /* ... */ },
  saveInks(inks) { /* ... */ },
  getTransactions() { /* ... */ },
  saveTransactions(transactions) { /* ... */ },
  getTickets() { /* ... */ },
  saveTickets(tickets) { /* ... */ },
  getSettings() { /* ... */ },
  saveSettings(settings) { /* ... */ }
};
```

---

### 19. TicketService
Encapsulates all ticket discovery, verification, and processing logic:
- `findByReferenceNumber(ref)`: Case-insensitive lookup.
- `isAlreadyProcessed(ref)`: Verifies against transaction registry.
- `processDeliveryTicket(ticket)`: Handles validation, SKU lookup/creation, stock addition, serial updates, transaction generation, and state persistence.
- `processReleaseTicket(ticket)`: Executes pre-flight stock balance verification across all items before applying decrements, serial releases, and transaction generation.

---

### 20. Application State
Maintained in a unified runtime `AppState` structure:
```javascript
const AppState = {
  currentPage: 'dashboard',
  inks: [],
  transactions: [],
  tickets: [],
  settings: {},
  activeDeliveryTicket: null,
  activeReleaseTicket: null,
  filters: {
    inventorySearch: '',
    inventoryBrand: 'ALL',
    inventoryColor: 'ALL',
    inventoryStatus: 'ALL',
    transactionSearch: '',
    transactionType: 'ALL',
    transactionBrand: 'ALL',
    transactionDateRange: 'ALL'
  }
};
```

---

### 21. Validation Rules
1. **Reference Number Format**: Must be non-empty and formatted appropriately (`DEL-*` or `REL-*`).
2. **Approval Gate**: Tickets with any status other than `APPROVED` cannot be executed.
3. **Multi-Item Atomicity**: In a release ticket with $N$ items, all $N$ items must satisfy $\text{Stock}_i \ge \text{Requested}_i$. If even one item fails, zero inventory records are modified.
4. **Duplicate Prohibition**: If a transaction record already exists with reference number $R$, no further transactions with $R$ are permitted.

---

### 22. Error Handling
The application intercepts potential failure modes gracefully:
- **Ticket Not Found**: Displays contextual modal guiding the user to check the reference number.
- **Insufficient Stock**: Displays an alert listing available versus requested units, with processing buttons disabled.
- **Duplicate Ticket**: Explains when and how the ticket was previously processed without corrupting state.

---

### 23. Chart.js Visualizations
Visualizations are managed through centralized initialization and safe re-render functions:
- Existing instances are destroyed prior to recreation to prevent canvas memory leaks.
- Charts dynamically refresh whenever delivery or release transactions are processed.

---

### 24. Responsive Design
Crafted using Tailwind CSS breakpoints:
- **Desktop ($\ge 1024\text{px}$)**: Fixed left navigation sidebar with primary content stage.
- **Tablet / Mobile ($< 1024\text{px}$)**: Collapsible mobile sidebar with backdrop overlay, horizontally scrollable data tables (`overflow-x-auto`), and responsive grid layouts.

---

### 25. Future PHP Integration
When migrating to a PHP backend:
1. `StorageService` methods will be updated to execute `fetch()` requests:
   - `GET /api/inventory.php` -> Returns ink inventory.
   - `GET /api/tickets.php?ref={refNumber}` -> Fetches approved ticket.
   - `POST /api/deliveries.php` -> Submits delivery confirmation.
   - `POST /api/releases.php` -> Submits release confirmation.
2. The UI code remains unchanged because business methods interact with abstract service contracts.

---

### 26. Future MySQL Integration
Proposed relational schema:
```sql
CREATE TABLE inks (
    id VARCHAR(50) PRIMARY KEY,
    ink_code VARCHAR(100) UNIQUE NOT NULL,
    brand VARCHAR(100) NOT NULL,
    printer_model VARCHAR(150),
    color VARCHAR(50) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    reorder_level INT UNSIGNED NOT NULL DEFAULT 5,
    supplier VARCHAR(150),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
    id VARCHAR(50) PRIMARY KEY,
    reference_number VARCHAR(100) UNIQUE NOT NULL,
    ticket_type ENUM('DELIVERY', 'RELEASE') NOT NULL,
    status ENUM('APPROVED', 'PENDING', 'REJECTED') DEFAULT 'APPROVED',
    supplier VARCHAR(150),
    given_to VARCHAR(150),
    department VARCHAR(100),
    purpose TEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ticket_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(50) NOT NULL,
    ink_code VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    serial_numbers JSON,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id VARCHAR(50) PRIMARY KEY,
    reference_number VARCHAR(100) NOT NULL,
    transaction_type ENUM('RECEIVED', 'RELEASED') NOT NULL,
    ink_id VARCHAR(50) NOT NULL,
    ink_code VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    serial_number VARCHAR(255),
    supplier VARCHAR(150),
    given_to VARCHAR(150),
    department VARCHAR(100),
    purpose TEXT,
    status VARCHAR(50) DEFAULT 'APPROVED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ref (reference_number),
    INDEX idx_ink (ink_code)
);
```

---

### 27. Future Email Alerts
```javascript
// FUTURE EMAIL ALERT:
// Send the low-stock event to a PHP API endpoint.
// The PHP backend can use PHPMailer to notify the administrator.
```
On the PHP backend, when an SKU hits $\le \text{reorderLevel}$, an asynchronous cURL call or cron worker invokes PHPMailer to dispatch an HTML notification to `admin@organization.com`.

---

### 28. Security Considerations
- **SQL Injection Prevention**: Prepared statements must be utilized in future PHP/MySQL routines.
- **Role-Based Access Control**: Future versions should enforce session tokens for warehouse stock administrators versus department requesters.
- **Input Sanitization**: User inputs are sanitized prior to DOM insertion to prevent Cross-Site Scripting (XSS).

---

### 29. Testing Scenarios & Verification Matrix
| Scenario ID | Test Case | Expected Result |
|---|---|---|
| **TEST-01** | Search valid delivery ticket `DEL-2026-00125` | Displays preview with 2 items (35 total units). |
| **TEST-02** | Process `DEL-2026-00125` | Stock increases: HP-682-BLK +20, HP-682-CYN +15. Transactions logged. |
| **TEST-03** | Search same delivery `DEL-2026-00125` again | Duplicate detected; re-processing blocked. |
| **TEST-04** | Search valid release ticket `REL-2026-00451` | Displays preview for Juan Dela Cruz (2 units HP-682-BLK). Validation passes. |
| **TEST-05** | Process `REL-2026-00451` | Stock decrements by 2. Transaction logged. |
| **TEST-06** | Search invalid reference `DEL-9999-00000` | Displays "Ticket Not Found" alert. No state changes. |
| **TEST-07** | Search `REL-2026-00453` (50 units requested) | Identifies available stock < 50; triggers Insufficient Stock rejection. |
| **TEST-08** | Browser Refresh | All updated inventory, transactions, and ticket statuses persist from LocalStorage. |
| **TEST-09** | Reset Demo Data | With confirmation, restores all baseline inventory and approved tickets. |

---

### 30. Known Limitations
- Operates client-side via LocalStorage in the current prototype iteration; clearing browser storage wipes state unless backed up.
- Does not connect to live mail servers or physical barcode laser scanners in the frontend-only edition.
