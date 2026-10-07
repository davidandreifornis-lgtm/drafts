# Updates in this package (2026-10-07)

## 1. Quantity on release View modal
- Transaction History → Releases by Department → View now shows **Quantity issued**.

## 2. Optional Notes on Stock Issuance
- Stock Issuance modal has an optional **Notes** textarea (not required).
- Notes are stored on `dbo.toner_transactions.notes` (NVARCHAR(500), nullable).
- View modal shows Notes when present.

### Required DB change (run once)
```sql
-- See also: sql/add_notes_to_toner_transactions.sql
IF NOT EXISTS (
    SELECT 1 FROM sys.columns
    WHERE object_id = OBJECT_ID(N'dbo.toner_transactions') AND name = N'notes'
)
BEGIN
    ALTER TABLE dbo.toner_transactions ADD notes NVARCHAR(500) NULL;
END
GO
```

### Files changed
- partials/modals/release.php
- assets/js/views/release.js
- assets/js/modals/release.js
- assets/js/modals/release-detail.js
- api/release.php
- api/transactions.php
- sql/add_notes_to_toner_transactions.sql (new)
