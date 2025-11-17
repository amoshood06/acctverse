-- Migration: add is_admin column to users
-- Adds a boolean flag to mark admin users

ALTER TABLE `users`
  ADD COLUMN `is_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_verified`;

-- Apply with:
-- mysql -u root -p acctverse < db/add_is_admin.sql
