-- ============================================================
-- MIGRATION 001: CREATE CLANS TABLE
-- ============================================================
-- This migration creates the central clans/tenants table
-- for multi-tenant SaaS architecture.
-- ============================================================

CREATE TABLE IF NOT EXISTS `clans` (
    `clan_id` INT(11) NOT NULL AUTO_INCREMENT,
    `clan_name` VARCHAR(255) NOT NULL,
    `clan_code` VARCHAR(50) NOT NULL,
    `clan_email` VARCHAR(150) DEFAULT NULL,
    `clan_phone` VARCHAR(50) DEFAULT NULL,
    `clan_address` TEXT DEFAULT NULL,
    `clan_motto` VARCHAR(255) DEFAULT NULL,
    `clan_logo` VARCHAR(255) DEFAULT NULL,
    `clan_description` TEXT DEFAULT NULL,
    `clan_website` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active','suspended','expired','cancelled') NOT NULL DEFAULT 'active',
    `subscription_status` ENUM('trial','active','expired','suspended','cancelled') NOT NULL DEFAULT 'trial',
    `subscription_expires_at` DATETIME DEFAULT NULL,
    `max_members` INT(11) DEFAULT 500,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`clan_id`),
    UNIQUE KEY `uq_clan_code` (`clan_code`),
    KEY `idx_status` (`status`),
    KEY `idx_subscription_status` (`subscription_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default clan (Clan ID 1) to hold existing data
INSERT INTO `clans` (`clan_name`, `clan_code`, `status`, `subscription_status`)
VALUES ('Default Clan', 'CLN-000001', 'active', 'active')
ON DUPLICATE KEY UPDATE `clan_name` = `clan_name`;
