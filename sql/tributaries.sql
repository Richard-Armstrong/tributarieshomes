SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `tributaries` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tributaries`;

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `db` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `guid` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `api_key` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `companies` (`id`, `name`, `db`, `guid`, `api_key`, `active`) VALUES
(1, 'BluWave', NULL, NULL, NULL, 1);

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `company` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

INSERT INTO `departments` (`id`, `name`, `description`, `company`) VALUES
(1, 'administration', 'Administrators', 1),
(2, 'unassigned', 'Unassigned', 1);

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(15) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `nvp_codes`;
CREATE TABLE `nvp_codes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `context` varchar(45) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  `display` varchar(45) DEFAULT NULL,
  `theValue` varchar(45) DEFAULT NULL,
  `altValue` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `nvp_codes` (`id`, `context`, `seq`, `display`, `theValue`, `altValue`) VALUES
(1, 'User_Levels', 1, 'Superuser', '1', ''),
(2, 'User_Levels', 2, 'Administration', '2', ''),
(3, 'User_Levels', 3, 'Employee', '3', ''),
(4, 'User_Levels', 4, 'External', '4', ''),
(5, 'Field_Types', 1, 'Text', 'VARCHAR', ''),
(6, 'Field_Types', 2, 'DateTime', 'DATETIME', ''),
(7, 'Field_Types', 3, 'Integer', 'INT', ''),
(8, 'Field_Types', 4, 'Decimal', 'DECIMAL', ''),
(9, 'Field_Types', 5, 'Currency', 'CURRENCY', ''),
(10, 'Field_Types', 6, 'Notes', 'TEXT', ''),
(11, 'Field_Types', 7, 'Dropdown', 'DROPDOWN', ''),
(12, 'Yes_No', 1, 'Yes', '1', ''),
(13, 'Yes_No', 2, 'No', '0', ''),
(14, 'Operators', 2, '>', '>', ''),
(15, 'Operators', 3, '>=', '>=', ''),
(16, 'Operators', 1, '=', '=', ''),
(17, 'Operators', 4, '<=', '<=', ''),
(18, 'Operators', 5, '<', '<', ''),
(19, 'Event_Types', 1, 'Company Created', '1', ''),
(20, 'Event_Types', 2, 'Company Edited', '2', ''),
(21, 'Event_Types', 3, 'User Created', '3', ''),
(22, 'Event_Types', 4, 'Admin Password Change', '4', ''),
(23, 'Event_Types', 5, 'Department Created', '5', ''),
(24, 'Event_Types', 6, 'Department Edited', '6', ''),
(25, 'Event_Types', 7, 'Department Deleted', '7', ''),
(26, 'Event_Types', 8, 'New Form Created', '8', ''),
(27, 'Event_Types', 9, 'New Form Entry', '9', ''),
(28, 'Event_Types', 10, 'Form Data Imported', '10', ''),
(29, 'Event_Types', 11, 'Form Deactivated', '11', ''),
(30, 'Event_Types', 12, 'Form Reactivated', '12', ''),
(31, 'Event_Types', 13, 'Form Alert Created', '13', ''),
(32, 'Event_Types', 14, 'Form Alert Edited', '14', ''),
(33, 'Event_Types', 15, 'Quality Check', '15', ''),
(34, 'Event_Types', 16, 'Form Edited', '16', ''),
(35, 'Event_Types', 17, 'Form Dropdown Edited', '17', ''),
(36, 'Event_Types', 18, 'Logic Column Created', '18', ''),
(37, 'Event_Types', 19, 'Logic Column Edited', '19', ''),
(38, 'Event_Types', 20, 'Logic Column Deleted', '20', ''),
(39, 'Event_Types', 21, 'Entry Edited', '21', ''),
(40, 'Event_Types', 22, 'Report Created', '22', ''),
(41, 'Event_Types', 23, 'Report Deactivated', '23', ''),
(42, 'Event_Types', 24, 'Report Reactivated', '24', ''),
(43, 'Event_Types', 25, 'Report Run', '25', ''),
(44, 'Event_Types', 26, 'Private Report Run', '26', ''),
(45, 'Event_Types', 27, 'Report Logic Added', '27', ''),
(46, 'Event_Types', 28, 'Report Logic Deleted', '28', ''),
(47, 'Event_Types', 29, 'Report Entry Edited', '29', ''),
(48, 'Report_Operations_Number', 1, 'Summation', '1', ''),
(49, 'Report_Operations_Number', 2, 'Average', '2', ''),
(50, 'Report_Operations_Number', 3, 'Minimum', '3', ''),
(51, 'Report_Operations_Number', 4, 'Maximum', '4', ''),
(52, 'Report_Operations_Date', 1, 'Earliest Date', '1', ''),
(53, 'Report_Operations_Date', 2, 'Latest Date', '2', ''),
(54, 'Report_Types', 1, 'Daily', '1', ''),
(55, 'Report_Types', 2, 'Weekly', '2', ''),
(56, 'Report_Types', 3, 'Monthly', '3', '');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` int(11) UNSIGNED DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT '4',
  `notify_sms` TINYINT(1) NOT NULL DEFAULT '0',
  `notify_email` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `ip_address`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `forgotten_password_time`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`, `level`, `notify_sms`, `notify_email`) VALUES
(1, '::1', '$2y$08$Epkbj.pe3Xl9lxKwT1YvWOW.ev3bHPNo3R91QMbUkrltq5XYUrU2i', NULL, 'richard.armstrong@gmail.com', NULL, NULL, NULL, NULL, 1464459128, 1564858599, 1, 'Richard', 'Armstrong', 1, '0', 1, 0, 0);

DROP TABLE IF EXISTS `user_departments`;
CREATE TABLE `user_departments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_users_departments` (`user_id`,`group_id`),
  KEY `fk_users_departments_users1_idx` (`user_id`),
  KEY `fk_users_departments_departments1_idx` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

INSERT INTO `user_departments` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

CREATE TABLE `script_calls` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `script_calls` (`id`, `run_date`) VALUES
(1, DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d 8:00:00'));

CREATE TABLE `access` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company` int(11) UNSIGNED NOT NULL,
  `access_to` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `user_departments`
  ADD CONSTRAINT `fk_users_departments_departments1` FOREIGN KEY (`group_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_users_departments1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

CREATE TABLE `tributaries`.`bios` (
  `bio_id` INT NOT NULL AUTO_INCREMENT,
  `bio_name` VARCHAR(100) NULL,
  `bio_title` VARCHAR(255) NULL,
  `bio_companies` VARCHAR(255) NULL,
  `bio_image` VARCHAR(255) NULL,
  `bio_seq` INT NULL,
  `bio_description` TEXT(1000) NULL,
  PRIMARY KEY (`bio_id`));

  CREATE TABLE `tributaries`.`inventory` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `inv_name` VARCHAR(45) NULL,
  `inv_directory` VARCHAR(45) NULL,
  `inv_description` TEXT(999) NULL,
  `inv_desc_short` VARCHAR(255) NULL,
  `active_flag` VARCHAR(45) NULL DEFAULT '1',
  `seq` INT NULL,
  `landing_image` VARCHAR(245) NULL,
  `flythru_link` VARCHAR(245) NULL,
  PRIMARY KEY (`id`));
