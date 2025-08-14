-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 01:09 AM
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
-- Database: `invoice_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cnic` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `strn` varchar(50) DEFAULT NULL,
  `ntn` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `code`, `name`, `cnic`, `email`, `phone`, `address`, `city`, `strn`, `ntn`, `created_at`) VALUES
(22, '001', 'ITT Foods Pvt Ltd', '4210158526585', 'uzairkhanvt@gmail.com', '03122483654', 'Karachi, Pakistan.', 'Karachi, Pakistan', '3277876147523', '41012583-7', '2025-08-14 18:34:14');

-- --------------------------------------------------------

--
-- Table structure for table `hs_codes`
--

CREATE TABLE `hs_codes` (
  `id` int(11) NOT NULL,
  `hs_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hs_codes`
--

INSERT INTO `hs_codes` (`id`, `hs_code`, `name`) VALUES
(10, '8422.3000', 'HS Code');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `serial_no` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `invoice_type` varchar(100) DEFAULT NULL,
  `fbr_invoice_no` varchar(100) DEFAULT NULL,
  `po_no` varchar(100) DEFAULT NULL,
  `terms_of_payment` varchar(100) DEFAULT NULL,
  `scenario_id` varchar(100) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 18.00,
  `gross_total` decimal(12,2) DEFAULT NULL,
  `grand_total` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `serial_no`, `date`, `invoice_type`, `fbr_invoice_no`, `po_no`, `terms_of_payment`, `scenario_id`, `customer_id`, `discount`, `tax`, `gross_total`, `grand_total`, `created_at`) VALUES
(58, '001', '1988-07-24', 'Aspernatur dolorem d', 'Irure ut deserunt co', 'Error enim rem culpa', 'Ullamco at dolores a', 'Est mollitia nisi no', 22, 1.00, 0.00, 15906.00, 15746.94, '2025-08-15 06:49:06'),
(59, '002', '1982-10-02', 'Adipisicing possimus', 'Fugiat quia et dolo', 'In ducimus ullamco ', 'Minus placeat esse', 'Commodi vero quia eu', 22, 1.00, 0.00, 2097.00, 2076.03, '2025-08-15 07:36:07'),
(60, '003', '2015-11-02', 'Beatae velit elit a', 'Minus aliquam in qua', 'Laboris natus nihil ', 'Et perspiciatis et ', 'Pariatur Amet cumq', 22, 0.00, 18.00, 10064.25, 13184.17, '2025-08-15 07:53:40');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `hs_code` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `rate` decimal(12,2) DEFAULT NULL,
  `disc_perc` decimal(5,2) DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `excl_tax_amt` decimal(12,2) DEFAULT NULL,
  `tax_perc` decimal(5,2) DEFAULT NULL,
  `tax_amt` decimal(12,2) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_code`, `hs_code`, `item_name`, `qty`, `unit`, `rate`, `disc_perc`, `discount`, `excl_tax_amt`, `tax_perc`, `tax_amt`, `amount`) VALUES
(290, 58, NULL, '8422.3000', 'Tana Haynes', 704, 'Qui qui veniam odit', 29.00, 85.00, 17353.60, 3062.40, 44.00, 1347.46, 4409.86),
(291, 58, NULL, '8422.3000', 'Kenneth Villarreal', 417, 'Consequatur Expedit', 70.00, 56.00, 16346.40, 12843.60, 72.00, 9247.39, 22090.99),
(292, 59, NULL, '8422.3000', 'Yoshio Patton', 233, 'Quo sit ex rerum et', 100.00, 91.00, 21203.00, 2097.00, 99.00, 2076.03, 4173.03),
(293, 60, NULL, '8422.3000', 'Judith Wallace', 525, 'Laborum Labore ex v', 27.00, 29.00, 4110.75, 10064.25, 31.00, 3119.92, 13184.17);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `hs_codes`
--
ALTER TABLE `hs_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `hs_codes`
--
ALTER TABLE `hs_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
