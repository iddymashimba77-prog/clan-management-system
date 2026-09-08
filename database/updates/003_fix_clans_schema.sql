-- ============================================================
-- MIGRATION 003: SYNCHRONIZE clans TABLE WITH APPLICATION CODE
-- ============================================================
-- The application code (owner panel, config/tenant.php) expects
-- columns: contact_email, activation_status, activated_at, etc.
-- This migration aligns the DB schema to match the code without
-- destroying existing data.
--
-- This is a NON-DESTRUCTIVE migration: it adds/renames columns
-- and copies data where necessary.
-- ============================================================

-- ------------------------------------------------------------
-- Step 1: Add contact_email (copy from clan_email if present,
-- otherwise add empty column)
-- ------------------------------------------------------------
SET @has_clan_email := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'clan_email'
);

SET @has_contact_email := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'contact_email'
);

-- Add contact_email if it does not already exist
SET @sql := IF(
    @has_contact_email = 0,
    'ALTER TABLE `clans` ADD COLUMN `contact_email` VARCHAR(150) DEFAULT NULL AFTER `clan_name`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Copy data from clan_email to contact_email if clan_email exists
-- and contact_email is empty
SET @sql := IF(
    @has_clan_email = 1,
    'UPDATE `clans` SET `contact_email` = `clan_email` WHERE `contact_email` IS NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Step 2: Add activation_status (copy from status if present)
-- ------------------------------------------------------------
SET @has_status := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'status'
);

SET @has_activation_status := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'activation_status'
);

-- Add activation_status if it does not already exist
SET @sql := IF(
    @has_activation_status = 0,
    'ALTER TABLE `clans` ADD COLUMN `activation_status` ENUM(''pending'',''active'',''suspended'',''revoked'') NOT NULL DEFAULT ''pending'' AFTER `clan_name`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Copy data from status to activation_status if status exists
-- (status uses 'active','suspended','expired','cancelled')
SET @sql := IF(
    @has_status = 1,
    'UPDATE `clans` SET `activation_status` = CASE
        WHEN `status` = ''active'' THEN ''active''
        WHEN `status` = ''suspended'' THEN ''suspended''
        WHEN `status` = ''expired'' THEN ''pending''
        WHEN `status` = ''cancelled'' THEN ''revoked''
        ELSE `activation_status`
     END
     WHERE `activation_status` = ''pending'' OR `activation_status` = ''''',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Step 3: Add activated_at column
-- ------------------------------------------------------------
SET @has_activated_at := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'activated_at'
);

SET @sql := IF(
    @has_activated_at = 0,
    'ALTER TABLE `clans` ADD COLUMN `activated_at` DATETIME DEFAULT NULL AFTER `subscription_expires_at`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Step 4: Add started_at column for subscription tracking
-- ------------------------------------------------------------
SET @has_started_at := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'started_at'
);

SET @sql := IF(
    @has_started_at = 0,
    'ALTER TABLE `clans` ADD COLUMN `started_at` DATE DEFAULT NULL AFTER `created_at`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Step 5: Add plan_id / plan_name for subscription management
-- ------------------------------------------------------------
SET @has_plan_id := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'plan_id'
);

SET @sql := IF(
    @has_plan_id = 0,
    'ALTER TABLE `clans` ADD COLUMN `plan_id` INT(11) DEFAULT NULL AFTER `subscription_status`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_plan_name := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'clans'
      AND COLUMN_NAME = 'plan_name'
);

SET @sql := IF(
    @has_plan_name = 0,
    'ALTER TABLE `clans` ADD COLUMN `plan_name` VARCHAR(100) DEFAULT NULL AFTER `plan_id`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- Step 6: Set default values for existing rows
-- ------------------------------------------------------------
-- Ensure every clan has a valid activation_status
UPDATE `clans`
SET `activation_status` = 'active'
WHERE `activation_status` IS NULL OR `activation_status` = '';

-- Set default plan name for clans without a plan
UPDATE `clans`
SET `plan_name` = 'Basic'
WHERE `plan_name` IS NULL;
