--
-- 005_create_meetings_table.sql
--
-- Online Meeting Rooms (Jitsi-based), mirroring the live `events` schema
-- conventions (clan-scoped with FK to clans).
--

CREATE TABLE IF NOT EXISTS `meetings` (
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
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `fk_meetings_clan` FOREIGN KEY (`clan_id`) REFERENCES `clans` (`clan_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_meetings_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;