-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: clan_management_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clan_settings`
--

DROP TABLE IF EXISTS `clan_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_name` varchar(255) NOT NULL,
  `clan_motto` varchar(255) DEFAULT NULL,
  `clan_logo` varchar(255) DEFAULT NULL,
  `clan_description` text DEFAULT NULL,
  `clan_email` varchar(150) DEFAULT NULL,
  `clan_phone` varchar(50) DEFAULT NULL,
  `clan_address` text DEFAULT NULL,
  `clan_website` varchar(255) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  KEY `updated_by_user_id` (`updated_by_user_id`),
  CONSTRAINT `clan_settings_ibfk_1` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contribution_method_links`
--

DROP TABLE IF EXISTS `contribution_method_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contribution_method_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_method_per_contribution` (`contribution_id`,`method_id`),
  KEY `fk_cml_method` (`method_id`),
  CONSTRAINT `fk_cml_contribution` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`contribution_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cml_method` FOREIGN KEY (`method_id`) REFERENCES `contribution_payment_methods` (`method_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contribution_payment_methods`
--

DROP TABLE IF EXISTS `contribution_payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contribution_payment_methods` (
  `method_id` int(11) NOT NULL AUTO_INCREMENT,
  `method_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(200) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `logo` varchar(255) NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contribution_payments`
--

DROP TABLE IF EXISTS `contribution_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contribution_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_id` int(11) NOT NULL,
  `member_user_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `receipt` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `secretary_status` enum('Pending','Forwarded') NOT NULL DEFAULT 'Pending',
  `treasurer_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `chairperson_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approved_by_treasurer` int(11) DEFAULT NULL,
  `approved_by_chairperson` int(11) DEFAULT NULL,
  `treasurer_date` datetime DEFAULT NULL,
  `chairperson_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `contribution_id` (`contribution_id`),
  KEY `member_user_id` (`member_user_id`),
  KEY `fk_contribution_payment_method` (`payment_method_id`),
  CONSTRAINT `contribution_payments_ibfk_2` FOREIGN KEY (`member_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_contribution_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `contribution_payment_methods` (`method_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contribution_types`
--

DROP TABLE IF EXISTS `contribution_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contribution_types` (
  `contribution_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contribution_type_id`),
  UNIQUE KEY `uq_contribution_type_name` (`type_name`),
  KEY `idx_created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `fk_contribution_type_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contributions`
--

DROP TABLE IF EXISTS `contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributions` (
  `contribution_id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_type_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `contribution_image` varchar(255) DEFAULT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `allow_partial_payment` tinyint(1) DEFAULT 1,
  `minimum_amount` decimal(15,2) DEFAULT 0.00,
  `current_amount` decimal(15,2) DEFAULT 0.00,
  `progress` decimal(5,2) DEFAULT 0.00,
  `total_paid_members` int(11) DEFAULT 0,
  `total_pending_members` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contribution_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  KEY `idx_contribution_type_id` (`contribution_type_id`),
  CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_contributions_type` FOREIGN KEY (`contribution_type_id`) REFERENCES `contribution_types` (`contribution_type_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_contributions`
--

DROP TABLE IF EXISTS `event_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_contributions` (
  `contribution_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `required_amount` decimal(15,2) NOT NULL,
  `allow_partial_payment` tinyint(1) NOT NULL DEFAULT 1,
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed') DEFAULT 'Open',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contribution_id`),
  KEY `event_id` (`event_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `event_contributions_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `event_contributions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `event_status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meetings`
--

DROP TABLE IF EXISTS `meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings` (
  `meeting_id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` int(10) unsigned NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_code` varchar(64) NOT NULL,
  `meeting_status` enum('upcoming','live','ended','cancelled') DEFAULT 'upcoming',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`meeting_id`),
  UNIQUE KEY `uq_meetings_room_code` (`room_code`),
  KEY `idx_meetings_clan_id` (`clan_id`),
  KEY `created_by_user_id` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_title` varchar(255) NOT NULL,
  `expense_category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `paid_to` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`expense_id`),
  KEY `idx_expense_date` (`expense_date`),
  KEY `idx_expense_category` (`expense_category`),
  KEY `idx_expense_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_expenses_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `families`
--

DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `husband_id` int(11) DEFAULT NULL,
  `wife_id` int(11) DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `marriage_place` varchar(255) DEFAULT NULL,
  `marriage_status` enum('Married','Divorced','Widowed') DEFAULT 'Married',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`family_id`),
  KEY `fk_family_husband` (`husband_id`),
  KEY `fk_family_wife` (`wife_id`),
  CONSTRAINT `fk_family_husband` FOREIGN KEY (`husband_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_family_wife` FOREIGN KEY (`wife_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_children`
--

DROP TABLE IF EXISTS `family_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_children` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fc_family` (`family_id`),
  KEY `fk_fc_child` (`child_id`),
  CONSTRAINT `fk_fc_child` FOREIGN KEY (`child_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fc_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_documents`
--

DROP TABLE IF EXISTS `family_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `uploaded_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`document_id`),
  KEY `user_id` (`user_id`),
  KEY `uploaded_by_user_id` (`uploaded_by_user_id`),
  CONSTRAINT `family_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `family_documents_ibfk_2` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_message_deletions`
--

DROP TABLE IF EXISTS `family_message_deletions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_message_deletions` (
  `deletion_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`deletion_id`),
  UNIQUE KEY `message_id` (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `family_message_deletions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `family_messages` (`message_id`) ON DELETE CASCADE,
  CONSTRAINT `family_message_deletions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_message_reads`
--

DROP TABLE IF EXISTS `family_message_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_message_reads` (
  `read_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`read_id`),
  UNIQUE KEY `unique_message_reader` (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `family_message_reads_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `family_messages` (`message_id`) ON DELETE CASCADE,
  CONSTRAINT `family_message_reads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_messages`
--

DROP TABLE IF EXISTS `family_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `reply_to_message_id` int(11) DEFAULT NULL,
  `mentioned_user_id` int(11) DEFAULT NULL,
  `message_type` enum('text','system') DEFAULT 'text',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `reply_to_message_id` (`reply_to_message_id`),
  KEY `mentioned_user_id` (`mentioned_user_id`),
  CONSTRAINT `family_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `family_messages_ibfk_2` FOREIGN KEY (`reply_to_message_id`) REFERENCES `family_messages` (`message_id`) ON DELETE SET NULL,
  CONSTRAINT `family_messages_ibfk_3` FOREIGN KEY (`mentioned_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_relationships`
--

DROP TABLE IF EXISTS `family_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_relationships` (
  `relationship_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_user_id` int(11) NOT NULL,
  `father_user_id` int(11) DEFAULT NULL,
  `mother_user_id` int(11) DEFAULT NULL,
  `child_type` enum('biological','adopted') DEFAULT 'biological',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`relationship_id`),
  KEY `member_user_id` (`member_user_id`),
  KEY `father_user_id` (`father_user_id`),
  KEY `mother_user_id` (`mother_user_id`),
  CONSTRAINT `family_relationships_ibfk_1` FOREIGN KEY (`member_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `family_relationships_ibfk_2` FOREIGN KEY (`father_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `family_relationships_ibfk_3` FOREIGN KEY (`mother_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_wallet_balances`
--

DROP TABLE IF EXISTS `family_wallet_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_wallet_balances` (
  `balance_id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_balance` decimal(12,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`balance_id`),
  UNIQUE KEY `wallet_id` (`wallet_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `family_wallet_balances_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `family_wallets` (`wallet_id`) ON DELETE CASCADE,
  CONSTRAINT `family_wallet_balances_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_wallet_members`
--

DROP TABLE IF EXISTS `family_wallet_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_wallet_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_balance` decimal(12,2) DEFAULT 0.00,
  `carry_forward` decimal(12,2) DEFAULT 0.00,
  `last_paid_month` date DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_id` (`wallet_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `family_wallet_members_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `family_wallets` (`wallet_id`) ON DELETE CASCADE,
  CONSTRAINT `family_wallet_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_wallet_payments`
--

DROP TABLE IF EXISTS `family_wallet_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_wallet_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `member_user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_month` date NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `secretary_status` enum('Pending','Forwarded') DEFAULT 'Pending',
  `treasurer_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `chairperson_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by_treasurer` int(11) DEFAULT NULL,
  `approved_by_chairperson` int(11) DEFAULT NULL,
  `treasurer_date` datetime DEFAULT NULL,
  `chairperson_date` datetime DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `member_user_id` (`member_user_id`),
  CONSTRAINT `family_wallet_payments_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `family_wallets` (`wallet_id`),
  CONSTRAINT `family_wallet_payments_ibfk_2` FOREIGN KEY (`member_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `family_wallets`
--

DROP TABLE IF EXISTS `family_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_wallets` (
  `wallet_id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `target_amount` decimal(12,2) NOT NULL,
  `monthly_amount` decimal(12,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `visibility` enum('all','selected') DEFAULT 'all',
  `expected_amount` decimal(12,2) DEFAULT 0.00,
  `current_balance` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','closed') DEFAULT 'active',
  `created_by_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`wallet_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `family_wallets_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`gallery_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `generations`
--

DROP TABLE IF EXISTS `generations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generations` (
  `generation_id` int(11) NOT NULL AUTO_INCREMENT,
  `generation_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`generation_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `generations_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `income`
--

DROP TABLE IF EXISTS `income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income` (
  `income_id` int(11) NOT NULL AUTO_INCREMENT,
  `income_title` varchar(255) NOT NULL,
  `income_category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `income_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `received_from` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`income_id`),
  KEY `idx_income_date` (`income_date`),
  KEY `idx_income_category` (`income_category`),
  KEY `idx_income_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_income_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leadership_terms`
--

DROP TABLE IF EXISTS `leadership_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leadership_terms` (
  `leadership_term_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`leadership_term_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `leadership_terms_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `leadership_terms_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marriages`
--

DROP TABLE IF EXISTS `marriages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marriages` (
  `marriage_id` int(11) NOT NULL AUTO_INCREMENT,
  `husband_user_id` int(11) NOT NULL,
  `wife_user_id` int(11) NOT NULL,
  `marriage_date` date DEFAULT NULL,
  `marriage_place` varchar(255) DEFAULT NULL,
  `marriage_certificate` varchar(255) DEFAULT NULL,
  `marriage_status` enum('active','divorced','widowed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`marriage_id`),
  KEY `husband_user_id` (`husband_user_id`),
  KEY `wife_user_id` (`wife_user_id`),
  CONSTRAINT `marriages_ibfk_1` FOREIGN KEY (`husband_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `marriages_ibfk_2` FOREIGN KEY (`wife_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_gallery`
--

DROP TABLE IF EXISTS `media_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_gallery` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`media_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `media_gallery_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_generations`
--

DROP TABLE IF EXISTS `member_generations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_generations` (
  `member_generation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `generation_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`member_generation_id`),
  KEY `user_id` (`user_id`),
  KEY `generation_id` (`generation_id`),
  CONSTRAINT `member_generations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `member_generations_ibfk_2` FOREIGN KEY (`generation_id`) REFERENCES `generations` (`generation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_deletions`
--

DROP TABLE IF EXISTS `message_deletions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_deletions` (
  `deletion_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`deletion_id`),
  UNIQUE KEY `message_id` (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `message_deletions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE,
  CONSTRAINT `message_deletions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_user_id` int(11) NOT NULL,
  `receiver_user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('read','unread') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `sender_user_id` (`sender_user_id`),
  KEY `receiver_user_id` (`receiver_user_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `notification_type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `method_name` varchar(100) NOT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `card_number` varchar(100) DEFAULT NULL,
  `account_owner` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_method_id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) NOT NULL,
  `permission_key` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_name` (`permission_name`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `relationship_types`
--

DROP TABLE IF EXISTS `relationship_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relationship_types` (
  `relationship_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `relationship_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`relationship_type_id`),
  UNIQUE KEY `relationship_name` (`relationship_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_online_status`
--

DROP TABLE IF EXISTS `user_online_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_online_status` (
  `user_id` int(11) NOT NULL,
  `last_seen` datetime NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_online_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by_user_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  KEY `fk_user_roles_role` (`role_id`),
  KEY `fk_user_roles_assigner` (`assigned_by_user_id`),
  CONSTRAINT `fk_user_roles_assigner` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_code` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `passport_photo_verified` tinyint(1) DEFAULT 0,
  `birth_place` varchar(255) DEFAULT NULL,
  `current_residence` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT 'Single',
  `biography` text DEFAULT NULL,
  `family_member_type` enum('blood','marriage') DEFAULT 'blood',
  `is_deceased` tinyint(1) DEFAULT 0,
  `death_date` date DEFAULT NULL,
  `death_place` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT 4,
  `created_by_user_id` int(11) DEFAULT NULL,
  `account_status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `father_user_id` int(11) DEFAULT NULL,
  `mother_user_id` int(11) DEFAULT NULL,
  `spouse_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `member_code` (`member_code`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  KEY `fk_users_created_by` (`created_by_user_id`),
  CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallet_monthly_dues`
--

DROP TABLE IF EXISTS `wallet_monthly_dues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_monthly_dues` (
  `due_id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `member_user_id` int(11) NOT NULL,
  `due_month` date NOT NULL,
  `required_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `balance` decimal(12,2) DEFAULT 0.00,
  `status` enum('Pending','Partial','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`due_id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `member_user_id` (`member_user_id`),
  CONSTRAINT `wallet_monthly_dues_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `family_wallets` (`wallet_id`),
  CONSTRAINT `wallet_monthly_dues_ibfk_2` FOREIGN KEY (`member_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-25 14:55:39
