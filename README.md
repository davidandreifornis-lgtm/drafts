# Printer Toner & Ink Inventory Management System

A professional, responsive, and robust frontend-only web application for managing printer toner and ink inventory. The system prioritizes **approved delivery and release tickets as the source of truth for all inventory movements**, guaranteeing that administrators do not manually guess or type stock quantities.

---

## 🌟 Key Features

- **Approved Ticket-Driven Inventory Movements**:
  - **Receive Delivery**: Automatic stock increments derived strictly from approved delivery tickets (`DEL-YYYY-NNNNN`).
  - **Release / Give Ink**: Automatic stock decrements derived strictly from approved release tickets (`REL-YYYY-NNNNN`).
- **Atomic Stock Validation**:
  - Validates stock availability across all items on a multi-item release ticket *before* executing any deduction. If any item is insufficient, the entire ticket is rejected to prevent negative stock and inventory skew.
- **Duplicate Transaction Prevention**:
  - Strict tracking of processed reference numbers. Duplicate attempts are blocked with clear informative alerts, preventing double additions or double deductions.
- **Serial Number Traceability**:
  - Tracks individual physical unit serial numbers (`SN-XXXX-XXX`) from delivery receipt through distribution to specific departments and personnel.
- **Comprehensive Stock Monitoring**:
  - Real-time stock status calculations (`IN STOCK`, `LOW STOCK`, `OUT OF STOCK`).
  - Visual inventory alert banners with immediate shortcuts to affected items.
- **Interactive Analytics (Chart.js)**:
  - Stock distribution by brand (Doughnut).
  - Overall stock status breakdown (Doughnut).
  - Inbound vs. Outbound transaction velocity over time (Bar/Line).
  - Departmental ink consumption analysis.
- **Full Transaction Audit Trail**:
  - Every single item movement is recorded with timestamp, reference number, serial number, ink code, color, quantity, recipient/supplier, department, and purpose.
  - Multi-criteria filtering by search keyword, transaction type, brand, color, and date ranges.
  - CSV export and print-ready reports.
- **Data Persistence**:
  - Centralized `StorageService` utilizing browser `LocalStorage`.
  - Zero server, zero npm, zero build dependencies required.
  - Data reset utility to restore sample datasets at any time.
- **Future PHP & MySQL Architecture**:
  - Designed with clean service abstractions (`StorageService`, `TicketService`) ready for seamless RESTful API migration to PHP backend endpoints and MySQL database tables.

---

## 🛠 Technology Stack

- **HTML5 & Semantic Markup**: Single-page application architecture enclosed cleanly within `index.html`.
- **Styling**: [Tailwind CSS](https://tailwindcss.com/) loaded via high-speed CDN (`cdn.tailwindcss.com`).
- **Data Visualizations**: [Chart.js](https://www.chartjs.org/) loaded via CDN (`cdn.jsdelivr.net/npm/chart.js`).
- **Storage**: Browser `LocalStorage` via unified `StorageService`.
- **Icons**: Inline SVG symbols for responsive, zero-dependency icon rendering.
- **Font**: Inter and system font stack for clean administrative typography.

---

## 🚀 How to Run the Application

Because this application is built with a zero-build, zero-dependency philosophy:

1. Locate `index.html` in the project directory:
   ```bash
   printer-ink-inventory-system/index.html
   ```
2. Double-click `index.html` to open it directly in Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari.
3. Alternatively, when running in the dev container, it is served automatically at port `3000`.

*No Node.js, npm, or PHP server is required to run this version.*

---

## 📋 Demo Reference Numbers for Testing

The system automatically initializes realistic demo data on first launch. You can test each core workflow immediately using these pre-loaded approved tickets:

### Delivery Tickets (Inbound Stock Increase)
- **`DEL-2026-00125`**: Multi-item delivery from *ABC Office Supplies*
  - HP-682-BLK (20 units, Serial: `SN-H682-001`)
  - HP-682-CYN (15 units, Serial: `SN-H682-002`)
- **`DEL-2026-00126`**: Canon batch delivery from *TechInk Distributors*
  - Canon PG-47 Black (25 units, Serial: `SN-CPG47-101`)
  - Canon CL-57 Color (20 units, Serial: `SN-CCL57-201`)
- **`DEL-2026-00127`**: Epson EcoTank Trio delivery from *Universal Supplies Co.*
  - Epson 003 Black (30 units, Serial: `SN-EP003-BK1`)
  - Epson 003 Magenta (15 units, Serial: `SN-EP003-MG1`)
  - Epson 003 Yellow (15 units, Serial: `SN-EP003-YL1`)
- **`DEL-2026-00128`**: Brother toner shipment from *Prime Office Gear*
  - Brother BTD60BK (18 units, Serial: `SN-BR60-01`)
  - Brother BT5000C (12 units, Serial: `SN-BR50-01`)

### Release Tickets (Outbound Stock Deduction)
- **`REL-2026-00451`**: Release to *Juan Dela Cruz* (*Human Resources*) for printer replacement
  - HP-682-BLK (2 units) -> *Tests successful release and stock decrement*
- **`REL-2026-00452`**: Release to *Maria Santos* (*Accounting*) for financial year-end reporting
  - Canon PG-47 Black (1 unit) & Canon CL-57 Color (1 unit) -> *Tests multi-item release*
- **`REL-2026-00453`**: Release to *Alex Rivera* (*Engineering*) for CAD blueprint plotters
  - Brother BTD60BK (50 units) -> *Tests ATOMIC INSUFFICIENT STOCK validation. The available stock is less than 50, so the system cleanly rejects the entire ticket.*
- **`REL-2026-00454`**: Release to *Sarah Gomez* (*Marketing*) for product catalog print run
  - Epson 003 Black (3 units), Epson 003 Magenta (2 units), Epson 003 Yellow (2 units)

---

## 🔒 Core Business Rules

1. **Ticket Authority**: The approved ticket is the sole source of truth. Administrators never type stock numbers manually.
2. **Single-Use Tickets**: A reference number can only be processed once. Reprocessing is blocked with a duplicate notification.
3. **Atomic Multi-Item Release**: If a release ticket requests 5 items and 4 have adequate stock but 1 is short, the entire ticket is rejected.
4. **Non-Negative Stock**: Stock balances cannot drop below 0.
5. **Traceability**: Every transaction maintains reference number, item code, serial numbers, date, department, and recipient.

---

## 🔮 Future PHP/MySQL Backend Roadmap

The current version isolates storage operations in `StorageService` and validation logic in `TicketService`. When upgrading to a production server environment:
- `StorageService.getInks()` -> `GET /api/inventory.php`
- `StorageService.getTickets()` -> `GET /api/tickets.php`
- `TicketService.processDeliveryTicket()` -> `POST /api/deliveries.php`
- `TicketService.processReleaseTicket()` -> `POST /api/releases.php`
- Low-stock triggers -> Server-side dispatch to `PHPMailer` for instant administrator notification.

See `documentation/SYSTEM_DOCUMENTATION.md` for the complete technical blueprint and database schema specifications.
