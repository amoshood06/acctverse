-- Migration: add password reset token and expiry to users
ALTER TABLE `users`
  ADD COLUMN `password_reset_token` VARCHAR(100) DEFAULT NULL AFTER `verification_token`,
  ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL AFTER `password_reset_token`;

-- Apply with:
-- mysql -u root -p acctverse < db/add_password_reset_columns.sql
