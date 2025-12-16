-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 02:10 PM
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
-- Database: `9tech`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `id` int(11) NOT NULL,
  `main_heading` text DEFAULT NULL,
  `main_paragraph` text DEFAULT NULL,
  `feature_1` varchar(255) DEFAULT NULL,
  `feature_2` varchar(255) DEFAULT NULL,
  `feature_3` varchar(255) DEFAULT NULL,
  `feature_4` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stat_1_value` varchar(50) DEFAULT NULL,
  `stat_1_label` varchar(100) DEFAULT NULL,
  `stat_2_value` varchar(50) DEFAULT NULL,
  `stat_2_label` varchar(100) DEFAULT NULL,
  `stat_3_value` varchar(50) DEFAULT NULL,
  `stat_3_label` varchar(100) DEFAULT NULL,
  `stat_4_value` varchar(50) DEFAULT NULL,
  `stat_4_label` varchar(100) DEFAULT NULL,
  `sub_heading` text DEFAULT NULL,
  `sub_paragraph` text DEFAULT NULL,
  `cta_heading` text DEFAULT NULL,
  `cta_paragraph` text DEFAULT NULL,
  `cta_button_text` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `created_at`) VALUES
(1, 'us facebook', NULL, '2025-12-04 08:43:19'),
(2, 'English', NULL, '2025-12-04 08:53:42'),
(3, 'good', 2, '2025-12-04 09:04:39'),
(4, 'ball', NULL, '2025-12-04 09:15:42'),
(5, 'facebook', NULL, '2025-12-04 13:41:56'),
(6, 'us facebook', NULL, '2025-12-04 13:42:09'),
(7, 'us facebook', NULL, '2025-12-04 13:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `cookie_policy`
--

CREATE TABLE `cookie_policy` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `help_articles`
--

CREATE TABLE `help_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monnify_settings`
--

CREATE TABLE `monnify_settings` (
  `id` int(11) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `contract_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monnify_settings`
--

INSERT INTO `monnify_settings` (`id`, `api_key`, `secret_key`, `contract_code`) VALUES
(1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `admin_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `product_name`, `image`, `price`, `quantity`, `total_amount`, `status`, `created_at`, `admin_note`) VALUES
(0, 3, 5, '', NULL, 0.00, 2, 614.00, 'completed', '2025-12-04 08:39:28', NULL),
(1, 3, 4, '', NULL, 700.00, 1, 700.00, 'pending', '2025-11-19 21:54:36', NULL),
(2, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 22:39:49', NULL),
(3, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 22:51:29', NULL),
(4, 3, 3, '', NULL, 400.00, 1, 400.00, 'pending', '2025-11-19 22:51:55', NULL),
(5, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 22:56:12', NULL),
(6, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 22:57:28', NULL),
(7, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 22:57:44', NULL),
(8, 3, 4, '', NULL, 700.00, 1, 700.00, 'pending', '2025-11-19 22:58:05', NULL),
(9, 3, 1, '', NULL, 200.00, 1, 200.00, 'pending', '2025-11-19 23:00:04', NULL),
(10, 3, 4, '', NULL, 700.00, 1, 700.00, 'pending', '2025-11-19 23:00:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `privacy_policy`
--

CREATE TABLE `privacy_policy` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `sub_category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `description`, `price`, `image`, `admin_note`, `created_at`, `sub_category`) VALUES
(0, 'USA vs FB | 0-100 friends | 2025', 'facebook', 'Active', 200.00, '1765803703_Fire razes NYSC Store in Benin.jpeg', 'yyuweyweyt', '2025-12-15 13:01:43', 'us facebook');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `referred_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_tiers`
--

CREATE TABLE `referral_tiers` (
  `id` int(11) NOT NULL,
  `tier_name` varchar(100) NOT NULL,
  `min_referrals` int(11) NOT NULL,
  `commission_rate` decimal(5,4) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referral_tiers`
--

INSERT INTO `referral_tiers` (`id`, `tier_name`, `min_referrals`, `commission_rate`, `description`) VALUES
(1, 'Bronze', 0, 0.1000, 'Applies to referrers with 0-5 successful referrals.'),
(2, 'Silver', 6, 0.1500, 'Applies to referrers with 6 or more successful referrals.');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_name`, `setting_value`, `updated_at`) VALUES
(1, 'site_logo', 'acctverse.png', '2025-11-28 19:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_orders`
--

CREATE TABLE `sms_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_per_sms` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `phone_number_received` varchar(50) DEFAULT NULL,
  `sms_code` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_services`
--

CREATE TABLE `sms_services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL,
  `country_code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_sms` decimal(10,2) NOT NULL DEFAULT 0.00,
  `available_credits` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `service_provider` varchar(100) DEFAULT NULL,
  `min_sms_per_order` int(11) DEFAULT 1,
  `max_sms_per_order` int(11) DEFAULT NULL,
  `avg_delivery_time` int(11) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `restock_alert_level` int(11) DEFAULT 10,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `name`, `created_at`) VALUES
(1, 'Laptops', '2025-12-04 08:53:00'),
(2, 'Smartphones', '2025-12-04 08:53:00'),
(3, 'Gaming Consoles', '2025-12-04 08:53:00'),
(4, 'Accessories', '2025-12-04 08:53:00'),
(5, 'us facebook', '2025-12-04 14:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `terms_and_conditions`
--

CREATE TABLE `terms_and_conditions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed','pending') DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `description`, `status`, `created_at`, `updated_at`) VALUES
(0, 3, 'purchase', 614.00, 'Purchase of 2 units of USA vs FB | 0-100 friends | 2025', 'completed', '2025-12-04 08:39:28', '2025-12-04 08:39:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `username` varchar(255) DEFAULT NULL,
  `mobile_code` varchar(10) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `referral_code` varchar(255) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `referral_earnings` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pending_earnings` decimal(12,2) NOT NULL DEFAULT 0.00,
  `withdrawn_amount` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `verification_token`, `reset_token`, `created_at`, `username`, `mobile_code`, `mobile`, `country`, `referral_code`, `referred_by`, `first_name`, `last_name`, `address`, `state`, `zip_code`, `city`, `balance`, `referral_earnings`, `pending_earnings`, `withdrawn_amount`) VALUES
(0, 'MOSHOOD OLALEKAN AJOSE', 'moshood.ajose6@gmail.com', '$2y$10$qXwni/bVuQRx6kDusu.k..pAThK7q3w393qAYfG8F36dBajIZ18Q6', 'user', NULL, NULL, '2025-11-30 21:24:35', 'moshood.ajose6', '+234', '8146883993', 'Nigeria', '7b440408a1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(1, 'Administrator', 'admin@example.com', '$2y$10$ZHA2nEpdaAMErsx6yVmY0u2qEuMohnsXFiC2IgsDZBQstlMvOtCX.', 'admin', NULL, NULL, '2025-11-17 19:38:17', NULL, NULL, NULL, NULL, NULL, NULL, 'ajose', 'moshood', NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(2, 'John Doe', 'user@example.com', '$2y$10$EXAMPLEHASHEDPASSWORDHERE', 'user', NULL, NULL, '2025-11-17 19:38:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(3, 'Ajose Moshood', 'amoshood06@gmail.com', '$2y$10$ZHA2nEpdaAMErsx6yVmY0u2qEuMohnsXFiC2IgsDZBQstlMvOtCX.', 'user', NULL, NULL, '2025-11-17 21:24:11', 'amoshood06', '+234', '8146883083', 'Nigeria', 'eerre', NULL, 'Moshood', 'Ajose', 'Limca, Badagry, Lagos', 'Outside US', '188088', 'Lagos', 5686.00, 0.00, 0.00, 0.00),
(4, 'Danny', 'kevinkaka303@gmail.com', '$2y$10$4DAoNb9tdV10BkivbndBf.0aaRvVLT5MdOmdo18vd.hBV.QAT.GzG', 'user', NULL, NULL, '2025-11-18 15:27:30', 'Dannykings099', '+234', '9065984648', 'Nigeria', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `cookie_policy`
--
ALTER TABLE `cookie_policy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `help_articles`
--
ALTER TABLE `help_articles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monnify_settings`
--
ALTER TABLE `monnify_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `product_id_idx` (`product_id`);

--
-- Indexes for table `privacy_policy`
--
ALTER TABLE `privacy_policy`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `referral_tiers`
--
ALTER TABLE `referral_tiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_ibfk_1` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdrawals_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `monnify_settings`
--
ALTER TABLE `monnify_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `referral_tiers`
--
ALTER TABLE `referral_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
