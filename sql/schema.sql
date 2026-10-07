-- Toner Inventory — SQL Server schema (idempotent: safe to run more than once).
-- Rebuilt from the live table exports and the columns the PHP API reads/writes.
-- Create the database first, e.g.:  CREATE DATABASE toner_inventory;  then USE it.
-- Set the same database name in config/database.php.

IF OBJECT_ID('dbo.toner_suppliers') IS NULL
CREATE TABLE dbo.toner_suppliers (
  id         INT IDENTITY(1,1) PRIMARY KEY,
  name       NVARCHAR(100) NOT NULL,
  is_active  BIT NOT NULL CONSTRAINT DF_sup_active DEFAULT 1,
  created_at DATETIME2(6) NOT NULL CONSTRAINT DF_sup_created DEFAULT SYSUTCDATETIME(),
  updated_at DATETIME2(6) NOT NULL CONSTRAINT DF_sup_updated DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_sup_name UNIQUE (name)
);

IF OBJECT_ID('dbo.toner_users') IS NULL
CREATE TABLE dbo.toner_users (
  id            INT IDENTITY(1,1) PRIMARY KEY,
  username      NVARCHAR(150) NOT NULL,
  password_hash NVARCHAR(255) NOT NULL,
  full_name     NVARCHAR(150) NULL,
  role          NVARCHAR(30) NOT NULL CONSTRAINT DF_usr_role DEFAULT 'admin',
  is_active     BIT NOT NULL CONSTRAINT DF_usr_active DEFAULT 1,
  created_at    DATETIME2(6) NOT NULL CONSTRAINT DF_usr_created DEFAULT SYSUTCDATETIME(),
  updated_at    DATETIME2(6) NOT NULL CONSTRAINT DF_usr_updated DEFAULT SYSUTCDATETIME(),
  email         NVARCHAR(150) NULL,
  CONSTRAINT UQ_usr_username UNIQUE (username)
);

IF OBJECT_ID('dbo.toner_locations') IS NULL
CREATE TABLE dbo.toner_locations (
  id           INT IDENTITY(1,1) PRIMARY KEY,
  department   NVARCHAR(50)  NOT NULL,
  location     NVARCHAR(100) NOT NULL,
  printer_name NVARCHAR(200) NULL,
  is_active    BIT NOT NULL CONSTRAINT DF_loc_active DEFAULT 1,
  created_at   DATETIME2(6) NOT NULL CONSTRAINT DF_loc_created DEFAULT SYSUTCDATETIME(),
  updated_at   DATETIME2(6) NOT NULL CONSTRAINT DF_loc_updated DEFAULT SYSUTCDATETIME(),
  printer_code NVARCHAR(50) NULL,
  ip_address   NVARCHAR(45) NULL
);

IF OBJECT_ID('dbo.toner_inventory') IS NULL
CREATE TABLE dbo.toner_inventory (
  id                 INT IDENTITY(1,1) PRIMARY KEY,
  item_code          NVARCHAR(50)  NOT NULL,
  description        NVARCHAR(255) NULL,
  printer_model      NVARCHAR(500) NULL,
  quantity           INT NOT NULL CONSTRAINT DF_inv_qty DEFAULT 0,
  reorder_level      INT NOT NULL CONSTRAINT DF_inv_reorder DEFAULT 3,
  supplier           NVARCHAR(100) NULL,
  created_at         DATETIME2(6) NOT NULL CONSTRAINT DF_inv_created DEFAULT SYSUTCDATETIME(),
  updated_at         DATETIME2(6) NOT NULL CONSTRAINT DF_inv_updated DEFAULT SYSUTCDATETIME(),
  last_mrr_no        NVARCHAR(50) NULL,
  last_received_qty  INT NULL,
  last_received_date DATE NULL,
  CONSTRAINT UQ_inv_item UNIQUE (item_code),
  CONSTRAINT CK_inv_qty CHECK (quantity >= 0)
);

IF OBJECT_ID('dbo.toner_transactions') IS NULL
CREATE TABLE dbo.toner_transactions (
  id               INT IDENTITY(1,1) PRIMARY KEY,
  txn_code         NVARCHAR(50)  NOT NULL,
  type             NVARCHAR(20)  NOT NULL,           -- RECEIVED | RELEASED | DEFECTIVE
  reference_number NVARCHAR(100) NULL,
  ink_code         NVARCHAR(50)  NOT NULL,
  quantity         INT NOT NULL,
  txn_date         DATE NOT NULL,
  supplier         NVARCHAR(100) NULL,
  department       NVARCHAR(50)  NULL,
  location         NVARCHAR(100) NULL,
  given_to         NVARCHAR(150) NULL,
  purpose          NVARCHAR(255) NULL,
  status           NVARCHAR(30)  NULL,               -- RECORDED, DEFECTIVE, SENT_TO_SUPPLIER, REPLACED
  defective        BIT NOT NULL CONSTRAINT DF_txn_def DEFAULT 0,
  defective_at     DATETIME2(6) NULL,
  defective_notes  NVARCHAR(500) NULL,
  created_at       DATETIME2(6) NOT NULL CONSTRAINT DF_txn_created DEFAULT SYSUTCDATETIME(),
  actual_yield     INT NULL,
  issued_by        NVARCHAR(150) NULL,
  location_printer NVARCHAR(200) NULL,
  recorded_by      NVARCHAR(150) NULL,
  notes            NVARCHAR(500) NULL,               -- optional remark on an issuance
  CONSTRAINT UQ_txn_code UNIQUE (txn_code)
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_txn_ref' AND object_id=OBJECT_ID('dbo.toner_transactions'))
  CREATE INDEX IX_txn_ref ON dbo.toner_transactions (reference_number, type);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_txn_ink' AND object_id=OBJECT_ID('dbo.toner_transactions'))
  CREATE INDEX IX_txn_ink ON dbo.toner_transactions (ink_code, txn_date);

IF OBJECT_ID('dbo.toner_system_logs') IS NULL
CREATE TABLE dbo.toner_system_logs (
  id               INT IDENTITY(1,1) PRIMARY KEY,
  action_key       NVARCHAR(60)  NOT NULL,
  action_label    NVARCHAR(150) NULL,
  details          NVARCHAR(1000) NULL,
  reference_number NVARCHAR(100) NULL,
  item_code        NVARCHAR(50)  NULL,
  actor_username   NVARCHAR(150) NULL,
  actor_name       NVARCHAR(150) NULL,
  created_at       DATETIME2(6) NOT NULL CONSTRAINT DF_log_created DEFAULT SYSUTCDATETIME()
);

IF OBJECT_ID('dbo.toner_email_settings') IS NULL
CREATE TABLE dbo.toner_email_settings (
  id              INT IDENTITY(1,1) PRIMARY KEY,
  admin_email     NVARCHAR(150) NULL,
  from_email      NVARCHAR(150) NULL,
  from_name       NVARCHAR(150) NULL,
  subject_prefix  NVARCHAR(100) NULL,
  cooldown_hours  INT NOT NULL CONSTRAINT DF_mail_cd DEFAULT 1,
  driver          NVARCHAR(20) NOT NULL CONSTRAINT DF_mail_driver DEFAULT 'smtp',
  smtp_host       NVARCHAR(150) NULL,
  smtp_port       INT NULL,
  smtp_encryption NVARCHAR(10) NULL,
  smtp_user       NVARCHAR(150) NULL,
  smtp_pass       NVARCHAR(500) NULL,                -- stored encrypted (enc:v1:...)
  updated_at      DATETIME2(6) NULL,
  updated_by      NVARCHAR(150) NULL,
  alert_recipient NVARCHAR(150) NULL
);
PRINT 'Schema ready.';
