-- ============================================================
-- MIGRATION 002: ADD clan_id TO ALL TENANT-OWNED TABLES
-- ============================================================
-- Adds clan_id to every table that contains tenant-specific data.
-- Existing data is assigned to clan_id = 1.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- CORE USER TABLES
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `user_id`,
    ADD INDEX `idx_users_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_users_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `user_roles`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `user_role_id`,
    ADD INDEX `idx_user_roles_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_user_roles_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `user_online_status`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `user_id`,
    ADD INDEX `idx_user_online_status_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_user_online_status_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- CLAN SETTINGS
-- ============================================================

ALTER TABLE `clan_settings`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `setting_id`,
    ADD INDEX `idx_clan_settings_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_clan_settings_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- LEADERSHIP
-- ============================================================

ALTER TABLE `leadership_terms`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `leadership_term_id`,
    ADD INDEX `idx_leadership_terms_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_leadership_terms_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- GENERATIONS
-- ============================================================

ALTER TABLE `generations`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `generation_id`,
    ADD INDEX `idx_generations_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_generations_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `member_generations`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `member_generation_id`,
    ADD INDEX `idx_member_generations_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_member_generations_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- FAMILY SYSTEM
-- ============================================================

ALTER TABLE `families`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `family_id`,
    ADD INDEX `idx_families_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_families_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_children`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `idx_family_children_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_children_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_relationships`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `relationship_id`,
    ADD INDEX `idx_family_relationships_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_relationships_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_documents`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `document_id`,
    ADD INDEX `idx_family_documents_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_documents_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `marriages`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `marriage_id`,
    ADD INDEX `idx_marriages_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_marriages_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- FAMILY MESSAGING (Group Chat)
-- ============================================================

ALTER TABLE `family_messages`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `message_id`,
    ADD INDEX `idx_family_messages_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_messages_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_message_reads`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `read_id`,
    ADD INDEX `idx_family_message_reads_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_message_reads_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_message_deletions`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `deletion_id`,
    ADD INDEX `idx_family_message_deletions_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_message_deletions_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- DIRECT MESSAGING
-- ============================================================

ALTER TABLE `messages`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `message_id`,
    ADD INDEX `idx_messages_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_messages_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `message_deletions`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `deletion_id`,
    ADD INDEX `idx_message_deletions_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_message_deletions_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- WALLET SYSTEM
-- ============================================================

ALTER TABLE `family_wallets`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `wallet_id`,
    ADD INDEX `idx_family_wallets_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_wallets_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_wallet_members`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `idx_family_wallet_members_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_wallet_members_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_wallet_balances`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `balance_id`,
    ADD INDEX `idx_family_wallet_balances_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_wallet_balances_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `family_wallet_payments`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `payment_id`,
    ADD INDEX `idx_family_wallet_payments_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_family_wallet_payments_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `wallet_monthly_dues`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `due_id`,
    ADD INDEX `idx_wallet_monthly_dues_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_wallet_monthly_dues_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- EVENTS
-- ============================================================

ALTER TABLE `events`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `event_id`,
    ADD INDEX `idx_events_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_events_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `event_contributions`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `contribution_id`,
    ADD INDEX `idx_event_contributions_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_event_contributions_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- CONTRIBUTIONS SYSTEM
-- ============================================================

ALTER TABLE `contribution_types`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `contribution_type_id`,
    ADD INDEX `idx_contribution_types_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_contribution_types_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `contributions`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `contribution_id`,
    ADD INDEX `idx_contributions_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_contributions_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `contribution_payment_methods`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `method_id`,
    ADD INDEX `idx_contribution_payment_methods_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_contribution_payment_methods_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `contribution_method_links`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `idx_contribution_method_links_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_contribution_method_links_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `contribution_payments`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `payment_id`,
    ADD INDEX `idx_contribution_payments_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_contribution_payments_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- FINANCE
-- ============================================================

ALTER TABLE `expenses`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `expense_id`,
    ADD INDEX `idx_expenses_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_expenses_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `income`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `income_id`,
    ADD INDEX `idx_income_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_income_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `payment_methods`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `payment_method_id`,
    ADD INDEX `idx_payment_methods_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_payment_methods_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- NOTIFICATIONS & ACTIVITY LOGS
-- ============================================================

ALTER TABLE `notifications`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `notification_id`,
    ADD INDEX `idx_notifications_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_notifications_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `activity_logs`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `log_id`,
    ADD INDEX `idx_activity_logs_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_activity_logs_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- GALLERY & MEDIA
-- ============================================================

ALTER TABLE `gallery`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `gallery_id`,
    ADD INDEX `idx_gallery_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_gallery_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

ALTER TABLE `media_gallery`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `media_id`,
    ADD INDEX `idx_media_gallery_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_media_gallery_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

-- ============================================================
-- INSTALLATION ACTIVATIONS (tie to clan)
-- ============================================================

ALTER TABLE `installation_activations`
    ADD COLUMN `clan_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `activation_id`,
    ADD INDEX `idx_installation_activations_clan_id` (`clan_id`),
    ADD CONSTRAINT `fk_installation_activations_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans`(`clan_id`) ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
