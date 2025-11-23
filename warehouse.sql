-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 04:04 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warehouse`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'วัสดุก่อสร้างหลัก', 'พวกอิฐ หิน ปูน ทราย'),
(5, 'อุปกรณ์เสริม', ''),
(8, 'อุปกรณ์ช่าง', '');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `base_unit` varchar(50) DEFAULT NULL COMMENT 'หน่วยหลัก (เช่น กระสอบ, กล่อง)',
  `sub_unit` varchar(50) DEFAULT NULL COMMENT 'หน่วยย่อย (เช่น กิโลกรัม, ชิ้น)',
  `unit_conversion_rate` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT 'อัตราแปลงจากหน่วยหลักไปหน่วยย่อย',
  `stock_in_sub_unit` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'สต็อกคงเหลือในหน่วยย่อยที่สุด',
  `selling_price` decimal(10,2) NOT NULL,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `supplier_id`, `base_unit`, `sub_unit`, `unit_conversion_rate`, `stock_in_sub_unit`, `selling_price`, `reorder_level`, `image_path`) VALUES
(7, 'ปูนซีเมนต์ SCG/ตราเสือ ปูนเสือ (แบ่งขายขนาด 2 กก.) \r\nบวกลบ ไม่เกิน 1ขีด สำหรับซ่อมแซมหรือต่อเติมเล็กๆน้อยๆ\r\n', 1, 1, 'ถุง', NULL, 1.00, 5.00, 55.00, 20.00, 'uploads/1759834971_ปูน.png'),
(8, 'ทราย', 1, 4, 'ถุง', NULL, 1.00, 4.00, 50.00, 20.00, 'uploads/1759834997_ทราย.jpg'),
(9, 'หิน', 1, 1, 'ถุง', NULL, 1.00, 212.00, 50.00, 15.00, 'uploads/1761633736_หิน.jpg'),
(10, 'อิฐมวลเบา', 1, 1, 'ก้อน', NULL, 1.00, 1545.00, 10.00, 50.00, 'uploads/อิฐ.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_number`, `user_id`, `supplier_id`, `purchase_date`, `total_amount`) VALUES
(9, '22154855', 1, 1, '2025-10-07 00:00:00', 5500.00),
(11, '22154856', 1, 1, '2025-10-07 00:00:00', 4400.00),
(12, '215484896654', 1, 4, '2025-10-28 00:00:00', 6450.00),
(13, '2221514455', 1, 1, '2025-10-28 00:00:00', 6300.00),
(20, '284243245245245', 1, 1, '2025-11-17 00:00:00', 13000.00),
(21, '5252545242542', 1, 1, '2025-11-18 00:00:00', 3450.00),
(22, '21542542542452', 1, 1, '2025-11-18 00:00:00', 3300.00),
(26, '1231331313', 1, 1, '2025-11-17 00:00:00', 3700.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `purchase_detail_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_details`
--

INSERT INTO `purchase_details` (`purchase_detail_id`, `purchase_id`, `product_id`, `quantity`, `purchase_price`) VALUES
(9, 9, 7, 50, 70.00),
(12, 11, 7, 40, 70.00),
(13, 11, 8, 40, 40.00),
(14, 12, 9, 70, 35.00),
(15, 12, 8, 40, 40.00),
(16, 12, 7, 40, 60.00),
(17, 13, 10, 500, 7.00),
(18, 13, 7, 60, 30.00),
(19, 13, 9, 50, 20.00),
(21, 20, 7, 50, 40.00),
(22, 20, 9, 50, 20.00),
(23, 20, 10, 1000, 10.00),
(24, 21, 10, 50, 20.00),
(25, 21, 7, 50, 25.00),
(26, 21, 9, 50, 24.00),
(27, 22, 7, 50, 50.00),
(28, 22, 9, 40, 20.00),
(29, 26, 7, 50, 50.00),
(30, 26, 9, 30, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `sale_number` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `sale_number`, `user_id`, `sale_date`, `total_amount`) VALUES
(2, 'SO20251023053157', 1, '2025-10-23 00:00:00', 5700.00),
(3, 'SO20251023053334', 1, '2025-10-23 00:00:00', 3900.00),
(5, 'SO20251028074440', 1, '2025-10-28 00:00:00', 5700.00),
(6, 'SO20251107080109', 1, '2025-11-07 00:00:00', 0.00),
(7, 'SO20251107080140', 1, '2025-11-06 00:00:00', 0.00),
(8, 'SO20251107080305', 1, '2025-11-05 00:00:00', 7000.00),
(9, 'SO20251118042212', 1, '2025-11-18 00:00:00', 1100.00),
(10, 'SO20251118100334', 1, '2025-11-18 00:00:00', 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_details`
--

CREATE TABLE `sale_details` (
  `sale_detail_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_details`
--

INSERT INTO `sale_details` (`sale_detail_id`, `sale_id`, `product_id`, `quantity`, `sale_price`) VALUES
(1, 2, 8, 50, 50.00),
(2, 2, 7, 40, 80.00),
(3, 3, 8, 30, 50.00),
(4, 3, 7, 30, 80.00),
(6, 5, 8, 20, 50.00),
(7, 5, 7, 40, 80.00),
(8, 5, 9, 30, 50.00),
(9, 6, 8, 0, 50.00),
(10, 6, 7, 0, 80.00),
(11, 7, 7, 0, 80.00),
(12, 7, 10, 0, 10.00),
(13, 7, 8, 0, 50.00),
(14, 8, 8, 20, 50.00),
(15, 8, 7, 50, 80.00),
(16, 8, 9, 40, 50.00),
(17, 9, 7, 5, 80.00),
(18, 9, 9, 8, 50.00),
(19, 9, 8, 6, 50.00),
(20, 10, 10, 5, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `address`, `phone`) VALUES
(1, 'บริษัท A', 'กรุงเทพ', '0999999999'),
(3, 'บริษัท B', 'กรุงเทพ', '0888888888'),
(4, 'บริษัท C', 'กรุงเทพ', '0777777777');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `password` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `password`, `username`) VALUES
(1, '1234', 'aniwat'),
(4, '1234', 'earth');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`purchase_detail_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`sale_detail_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchase_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `sale_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`),
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
