-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2026 at 09:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toner_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `ink_code` varchar(64) NOT NULL,
  `brand` varchar(64) NOT NULL DEFAULT '',
  `printer_model` varchar(255) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 3,
  `supplier` varchar(128) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `ink_code`, `brand`, `printer_model`, `quantity`, `reorder_level`, `supplier`, `created_at`, `updated_at`) VALUES
(1, 'CRG-737', 'Canon', 'Canon MF237W', 24, 3, 'INKRITE', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(2, 'CF276A', 'HP', 'HP LaserJet Pro MFP M428fdn', 8, 3, 'JAN A', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(3, 'Q2612A', 'Canon', 'Canon LBP 2900', 29, 3, 'JMD', '2026-09-12 12:20:12', '2026-09-12 14:11:07'),
(4, 'CAN 045 HBK', 'Canon', 'Canon MF633DW', 1, 3, 'INKRITE', '2026-09-12 12:20:12', '2026-09-12 14:48:11'),
(5, 'CAN 045 HC', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(6, 'CAN 045 HY', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(7, 'CAN 045 HM', 'Canon', 'Canon MF633DW', 4, 3, 'INKRITE', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(8, 'CF280A', 'HP', 'HP Color LaserJet Pro 400 MFP M425dn, HP LaserJet Pro M401n', 3, 3, 'JMD', '2026-09-12 12:20:12', '2026-09-12 12:20:12'),
(10, 'SDAW', '', 'hatdog, footlong, kornbep', 24, 4, 'puregold', '2026-09-12 15:19:18', '2026-09-12 15:19:18'),
(11, 'HHAHAHAHAAH', '', 'hatdog, footlong, kornbep', 32, 3, 'HAHA', '2026-09-12 15:20:13', '2026-09-12 15:20:13'),
(12, 'ASDAWDAADFE', '', 'ASDAW · ASDAW · ASDAWDAS', 34, 3, 'ASDAWD', '2026-09-12 15:25:18', '2026-09-12 15:25:18');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `txn_code` varchar(32) NOT NULL,
  `type` enum('RECEIVED','RELEASED','DEFECTIVE') NOT NULL,
  `reference_number` varchar(64) NOT NULL,
  `ink_code` varchar(64) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `txn_date` date NOT NULL,
  `supplier` varchar(128) DEFAULT NULL,
  `department` varchar(64) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `given_to` varchar(128) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'RECORDED',
  `defective` tinyint(1) NOT NULL DEFAULT 0,
  `defective_at` datetime DEFAULT NULL,
  `defective_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `txn_code`, `type`, `reference_number`, `ink_code`, `quantity`, `txn_date`, `supplier`, `department`, `location`, `given_to`, `purpose`, `status`, `defective`, `defective_at`, `defective_notes`, `created_at`) VALUES
(1, 'TXN-00001', 'RECEIVED', 'DEL-2026-00100', 'CRG-737', 5, '2026-09-01', 'INKRITE', NULL, NULL, NULL, 'Initial stock receipt', 'RECORDED', 0, NULL, NULL, '2026-09-12 12:20:12'),
(2, 'TXN-00002', 'RELEASED', 'REL-2026-00451', 'CRG-737', 1, '2026-09-09', NULL, 'LOGISTICS', 'Logistics Office', NULL, 'Sample issuance', 'RECORDED', 0, NULL, NULL, '2026-09-12 12:20:12'),
(3, 'TXN-00003', 'RELEASED', 'REL-2026-00452', 'CRG-737', 1, '2026-09-09', NULL, 'ACCT', 'Acctg Office', NULL, 'Sample issuance', 'RECORDED', 0, NULL, NULL, '2026-09-12 12:20:12'),
(4, 'TXN-00004-11AB', 'RECEIVED', 'ASDWAD', 'Q2612A', 12, '2026-09-12', 'ASDAWASDAW', NULL, NULL, NULL, 'Stock delivery', 'RECORDED', 0, NULL, NULL, '2026-09-12 14:11:07'),
(5, 'TXN-00005-D58F', 'RELEASED', 'ASDADASDAW', 'CAN 045 HBK', 1, '2026-09-12', NULL, 'BD', 'BD Office', NULL, 'Stock issuance', 'RECORDED', 0, NULL, NULL, '2026-09-12 14:12:13'),
(6, 'TXN-00006-56FB', 'RELEASED', 'ASDAWDASDAW', 'CAN 045 HBK', 1, '2026-09-12', NULL, 'QC', 'QC Laboratory', NULL, 'Stock issuance', 'RECORDED', 0, NULL, NULL, '2026-09-12 14:12:29'),
(7, 'TXN-00007-1C3B', 'RELEASED', 'DAWASDAWDASDAW', 'CAN 045 HBK', 1, '2026-09-12', NULL, 'QC', 'QC Laboratory', NULL, 'Stock issuance', 'RECORDED', 1, '2026-09-12 14:13:23', 'ASDAWASDA', '2026-09-12 14:12:44'),
(8, 'TXN-00008-80AA', 'DEFECTIVE', 'DAWASDAWDASDAW', 'CAN 045 HBK', 1, '2026-09-12', NULL, 'QC', 'QC Laboratory', NULL, 'ASDAWASDA', 'DEFECTIVE', 1, NULL, NULL, '2026-09-12 14:13:23'),
(9, 'TXN-00009-A848', 'RELEASED', 'ASDAWASDAWASDAWD', 'CAN 045 HBK', 1, '2026-09-12', NULL, 'PRODUCTION', 'Production Office', NULL, 'Stock issuance', 'RECORDED', 0, NULL, NULL, '2026-09-12 14:48:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ink_code` (`ink_code`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_txn_code` (`txn_code`),
  ADD KEY `idx_ref` (`reference_number`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_ink` (`ink_code`),
  ADD KEY `idx_date` (`txn_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
