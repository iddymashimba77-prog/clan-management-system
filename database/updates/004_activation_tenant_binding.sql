-- ============================================================
-- MIGRATION 004: ACTIVATION TENANT BINDING (owner DB)
-- ============================================================
-- Run this on the OWNER database (clan_owner).
--
-- Adds clan_id to owner_activations so that every activation
-- token is directly associated with a clan (tenant) in the
-- central database. This enables the new SaaS activation flow
-- where activation belongs to clan_id, not a separate domain.
--
-- Also adds clan_id to owner_installations to link an owner
-- client record to a central clan tenant.
-- ============================================================

-- ------------------------------------------------------------
-- owner_activations: add clan_id
-- ------------------------------------------------------------
SET @has_activation_clan := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'owner_activations'
      AND COLUMN_NAME = 'clan_id'
);

SET @sql := IF(
    @has_activation_clan = 0,
    'ALTER TABLE `owner_activations`
        ADD COLUMN `clan_id` INT(10) UNSIGNED DEFAULT NULL AFTER `activation_id`,
        ADD INDEX `idx_owner_activations_clan_id` (`clan_id`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- owner_installations: add clan_id
-- ------------------------------------------------------------
SET @has_installation_clan := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'owner_installations'
      AND COLUMN_NAME = 'clan_id'
);

SET @sql := IF(
    @has_installation_clan = 0,
    'ALTER TABLE `owner_installations`
        ADD COLUMN `clan_id` INT(10) UNSIGNED DEFAULT NULL AFTER `installation_id`,
        ADD INDEX `idx_owner_installations_clan_id` (`clan_id`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
