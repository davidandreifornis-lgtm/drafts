-- Toner Inventory Management System
-- MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS toner_inventory
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE toner_inventory;

CREATE TABLE IF NOT EXISTS inventory (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ink_code      VARCHAR(64)  NOT NULL,
  brand         VARCHAR(64)  NOT NULL DEFAULT '',
  printer_model VARCHAR(255) NOT NULL DEFAULT '',
  quantity      INT          NOT NULL DEFAULT 0,
  reorder_level INT          NOT NULL DEFAULT 3,
  supplier      VARCHAR(128) NOT NULL DEFAULT '',
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ink_code (ink_code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  txn_code         VARCHAR(32)  NOT NULL,
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
  UNIQUE KEY uq_txn_code (txn_code),
  KEY idx_ref (reference_number),
  KEY idx_type (type),
  KEY idx_ink (ink_code),
  KEY idx_date (txn_date)
) ENGINE=InnoDB;

INSERT INTO inventory (ink_code, brand, printer_model, quantity, reorder_level, supplier) VALUES
('CRG-737',    'Canon', 'Canon MF237W', 24, 3, 'INKRITE'),
('CF276A',     'HP',    'HP LaserJet Pro MFP M428fdn', 8, 3, 'JAN A'),
('Q2612A',     'Canon', 'Canon LBP 2900', 17, 3, 'JMD'),
('CAN 045 HBK','Canon', 'Canon MF633DW', 5, 3, 'INKRITE'),
('CAN 045 HC', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE'),
('CAN 045 HY', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE'),
('CAN 045 HM', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE'),
('CF280A',     'HP',    'HP Color LaserJet Pro 400 MFP M425dn, HP LaserJet Pro M401n', 3, 3, 'JMD')
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);

INSERT INTO transactions (txn_code, type, reference_number, ink_code, quantity, txn_date, supplier, department, location, purpose, status) VALUES
('TXN-00001', 'RECEIVED', 'DEL-2026-00100', 'CRG-737', 5, '2026-09-01', 'INKRITE', NULL, NULL, 'Initial stock receipt', 'RECORDED'),
('TXN-00002', 'RELEASED', 'REL-2026-00451', 'CRG-737', 1, '2026-09-09', NULL, 'LOGISTICS', 'Logistics Office', 'Sample issuance', 'RECORDED'),
('TXN-00003', 'RELEASED', 'REL-2026-00452', 'CRG-737', 1, '2026-09-09', NULL, 'ACCT', 'Acctg Office', 'Sample issuance', 'RECORDED')
ON DUPLICATE KEY UPDATE reference_number = VALUES(reference_number);

