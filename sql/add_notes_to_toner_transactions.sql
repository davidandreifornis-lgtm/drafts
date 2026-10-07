-- Optional notes on stock issuance (and other transactions).
-- Run once against your toner_inventory database (SQL Server).
-- Safe to re-run: skips if column already exists.

IF NOT EXISTS (
    SELECT 1
    FROM sys.columns
    WHERE object_id = OBJECT_ID(N'dbo.toner_transactions')
      AND name = N'notes'
)
BEGIN
    ALTER TABLE dbo.toner_transactions
        ADD notes NVARCHAR(500) NULL;
END
GO
