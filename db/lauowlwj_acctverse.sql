-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 21, 2025 at 08:44 PM
-- Server version: 8.4.7
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lauowlwj_acctverse`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `price`, `quantity`, `total_amount`, `status`, `created_at`) VALUES
(1, 3, 4, 700.00, 1, 700.00, 'pending', '2025-11-19 21:54:36'),
(2, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 22:39:49'),
(3, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 22:51:29'),
(4, 3, 3, 400.00, 1, 400.00, 'pending', '2025-11-19 22:51:55'),
(5, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 22:56:12'),
(6, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 22:57:28'),
(7, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 22:57:44'),
(8, 3, 4, 700.00, 1, 700.00, 'pending', '2025-11-19 22:58:05'),
(9, 3, 1, 200.00, 1, 200.00, 'pending', '2025-11-19 23:00:04'),
(10, 3, 4, 700.00, 1, 700.00, 'pending', '2025-11-19 23:00:19');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `description`, `price`, `stock`, `image`, `created_at`) VALUES
(1, 'USA vs FB | 0-100 friends | 2025', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 200.00, 299, '1763551802_pp.PNG', '2025-11-19 11:30:02'),
(2, 'USA vs Instagram | 0-100 friends | 2025', 'Instagram', 'USA vs Instagram | 0-100 friends | 2025', 3000.00, 499, '1763571499_Capture.PNG', '2025-11-19 16:58:19'),
(3, 'USA vs FB | 0-100 friends | 2025', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 400.00, 33, '1763571632_pp.PNG', '2025-11-19 17:00:32'),
(4, 'USA vs FB | 0-100 friends | 2025', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 700.00, 33, '1763571660_pp.PNG', '2025-11-19 17:01:00'),
(5, 'USA vs FB | 0-100 friends | 2025', 'Facebook', 'USA vs FB | 0-100 friends | 2025', 307.00, 40, '1763571688_pp.PNG', '2025-11-19 17:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `referred_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_orders`
--

CREATE TABLE `sms_orders` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `service_id` int NOT NULL,
  `quantity` int NOT NULL,
  `cost_per_sms` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `phone_number_received` varchar(50) DEFAULT NULL,
  `sms_code` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `admin_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_services`
--

CREATE TABLE `sms_services` (
  `id` int NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL,
  `country_code` varchar(10) NOT NULL,
  `description` text,
  `price_per_sms` decimal(10,2) NOT NULL DEFAULT '0.00',
  `available_credits` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `service_provider` varchar(100) DEFAULT NULL,
  `min_sms_per_order` int DEFAULT '1',
  `max_sms_per_order` int DEFAULT NULL,
  `avg_delivery_time` int DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `restock_alert_level` int DEFAULT '10',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed','pending') DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(255) DEFAULT NULL,
  `mobile_code` varchar(10) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `verify_token` text,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `referral_earnings` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pending_earnings` decimal(12,2) NOT NULL DEFAULT '0.00',
  `withdrawn_amount` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_verified`, `verification_token`, `reset_token`, `created_at`, `username`, `mobile_code`, `mobile`, `country`, `verify_token`, `first_name`, `last_name`, `address`, `state`, `zip_code`, `city`, `balance`, `referral_earnings`, `pending_earnings`, `withdrawn_amount`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$ZHA2nEpdaAMErsx6yVmY0u2qEuMohnsXFiC2IgsDZBQstlMvOtCX.', 'admin', 1, NULL, NULL, '2025-11-17 19:38:17', NULL, NULL, NULL, NULL, NULL, 'ajose', 'moshood', NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(2, 'John Doe', 'user@example.com', '$2y$10$EXAMPLEHASHEDPASSWORDHERE', 'user', 1, NULL, NULL, '2025-11-17 19:38:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(3, 'Ajose Moshood', 'amoshood06@gmail.com', '$2y$10$ZHA2nEpdaAMErsx6yVmY0u2qEuMohnsXFiC2IgsDZBQstlMvOtCX.', 'user', 1, NULL, NULL, '2025-11-17 21:24:11', 'amoshood06', '+234', '8146883083', 'Nigeria', '4ef1f32a7c7fb54e38373e4860ce81c12ecb8857363699588047f9890eec2a8f', 'Moshood', 'Ajose', 'Limca, Badagry, Lagos', 'Outside US', '188088', 'Lagos', 6300.00, 0.00, 0.00, 0.00),
(4, 'Danny', 'kevinkaka303@gmail.com', '$2y$10$4DAoNb9tdV10BkivbndBf.0aaRvVLT5MdOmdo18vd.hBV.QAT.GzG', 'user', 0, NULL, NULL, '2025-11-18 15:27:30', 'Dannykings099', '+234', '9065984648', 'Nigeria', 'b848fc1ac27d27ce319ad88684ab167fedf9aa08ee50db5050b326d2de76bf8a', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `product_id_idx` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `referred_by` (`referred_by`);

--
-- Indexes for table `sms_orders`
--
ALTER TABLE `sms_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_services`
--
ALTER TABLE `sms_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_ibfk_1` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_orders`
--
ALTER TABLE `sms_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_services`
--
ALTER TABLE `sms_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
