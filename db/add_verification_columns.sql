-- Add verification columns to users table for email verification
ALTER TABLE `users`
  ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`,
  ADD COLUMN `verification_token` VARCHAR(100) DEFAULT NULL AFTER `is_verified`,
  ADD COLUMN `verification_expires` DATETIME DEFAULT NULL AFTER `verification_token`,
  ADD COLUMN `email_verified_at` DATETIME DEFAULT NULL AFTER `verification_expires`;

-- Apply with:
-- mysql -u root -p acctverse < db/add_verification_columns.sql
