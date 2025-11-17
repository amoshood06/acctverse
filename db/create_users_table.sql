-- Create users table for Acctverse
-- Adjust column names/types to match your app schema if needed

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `country` VARCHAR(2) DEFAULT NULL COMMENT 'ISO 2-letter country code',
  `mobile` VARCHAR(32) DEFAULT NULL COMMENT 'E.164-ish normalized phone (e.g. +1234567890)',
  `referral` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_username` (`username`),
  KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example usage (from shell):
-- mysql -u root -p acctverse < db/create_users_table.sql
