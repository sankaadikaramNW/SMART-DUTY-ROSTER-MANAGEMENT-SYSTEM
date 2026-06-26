-- Smart Duty Roster Management System Schema & Seed Data
-- Database: smart_duty_roster

CREATE DATABASE IF NOT EXISTS `smart_duty_roster` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_duty_roster`;

-- --------------------------------------------------------
-- Table `camps`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `camps` (
  `camp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `camp_code` VARCHAR(50) UNIQUE NOT NULL,
  `camp_name` VARCHAR(150) NOT NULL,
  `address` TEXT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_camp_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `roles`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) UNIQUE NOT NULL,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `personnel`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personnel` (
  `service_number` VARCHAR(30) PRIMARY KEY,
  `rank` VARCHAR(50) NOT NULL,
  `initials` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `trade` VARCHAR(100) NOT NULL,
  `squadron` VARCHAR(100) NOT NULL,
  `camp_id` INT NOT NULL,
  `contact_number` VARCHAR(30) NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `status` ENUM('Active', 'Inactive', 'Temporary Duty', 'Leave') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`camp_id`) REFERENCES `camps` (`camp_id`) ON UPDATE CASCADE,
  INDEX idx_personnel_camp (`camp_id`),
  INDEX idx_personnel_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `status` ENUM('Active', 'Suspended') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE,
  INDEX idx_users_role (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `postings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `postings` (
  `posting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `from_camp_id` INT NOT NULL,
  `to_camp_id` INT NOT NULL,
  `effective_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `status` ENUM('Completed', 'Active', 'Pending') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`from_camp_id`) REFERENCES `camps` (`camp_id`),
  FOREIGN KEY (`to_camp_id`) REFERENCES `camps` (`camp_id`),
  INDEX idx_posting_service_date (`service_number`, `effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `shifts`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shifts` (
  `shift_id` INT AUTO_INCREMENT PRIMARY KEY,
  `shift_name` VARCHAR(100) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `duration_hours` DECIMAL(4,2) NOT NULL,
  `description` VARCHAR(255) NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `duty_types`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `duty_types` (
  `duty_type_id` INT AUTO_INCREMENT PRIMARY KEY,
  `duty_type_name` VARCHAR(100) UNIQUE NOT NULL,
  `description` VARCHAR(255) NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `duty_rosters`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `duty_rosters` (
  `roster_id` INT AUTO_INCREMENT PRIMARY KEY,
  `roster_name` VARCHAR(150) NOT NULL,
  `camp_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('Draft', 'Submitted', 'Approved', 'Rejected', 'Published') DEFAULT 'Draft',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`camp_id`) REFERENCES `camps` (`camp_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  INDEX idx_rosters_camp_dates (`camp_id`, `start_date`, `end_date`),
  INDEX idx_rosters_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `duty_assignments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `duty_assignments` (
  `assignment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `roster_id` INT NOT NULL,
  `duty_date` DATE NOT NULL,
  `duty_type_id` INT NOT NULL,
  `shift_id` INT NOT NULL,
  `service_number` VARCHAR(30) NOT NULL,
  `priority_level` ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
  `remarks` TEXT NULL,
  `conflict_level` ENUM('Normal', 'Warning', 'Critical') DEFAULT 'Normal',
  `justification` TEXT NULL,
  `supervisor_remarks` TEXT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`roster_id`) REFERENCES `duty_rosters` (`roster_id`) ON DELETE CASCADE,
  FOREIGN KEY (`duty_type_id`) REFERENCES `duty_types` (`duty_type_id`),
  FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`),
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON UPDATE CASCADE,
  INDEX idx_assignment_date_service (`duty_date`, `service_number`),
  INDEX idx_assignment_roster (`roster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `approvals`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `approvals` (
  `approval_id` INT AUTO_INCREMENT PRIMARY KEY,
  `roster_id` INT NOT NULL,
  `action_by` INT NOT NULL,
  `action` ENUM('Submit', 'Approve', 'Reject', 'Return') NOT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`roster_id`) REFERENCES `duty_rosters` (`roster_id`) ON DELETE CASCADE,
  FOREIGN KEY (`action_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `notifications`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE,
  INDEX idx_notifications_user_read (`service_number`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `audit_logs`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `service_number` VARCHAR(30) NULL,
  `module` VARCHAR(100) NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `previous_data` JSON NULL,
  `new_data` JSON NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `login_history`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_history` (
  `history_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NOT NULL,
  `status` ENUM('Success', 'Failed') NOT NULL,
  `remarks` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ========================================================
-- SEED DATA SETUP
-- ========================================================

-- Seed Camps
INSERT INTO `camps` (`camp_id`, `camp_code`, `camp_name`, `address`, `status`) VALUES
(1, 'SLAF-EKL', 'SLAF Ekala', 'Ekala, Ja-Ela, Sri Lanka', 'Active'),
(2, 'SLAF-RML', 'SLAF Ratmalana', 'Ratmalana, Colombo, Sri Lanka', 'Active'),
(3, 'SLAF-KTN', 'SLAF Katunayake', 'Katunayake, Sri Lanka', 'Active'),
(4, 'SLAF-CBY', 'SLAF China Bay', 'China Bay, Trincomalee, Sri Lanka', 'Active');

-- Seed Roles
INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Administrator', 'Full system configurations, security audits, and base settings management.'),
(2, 'OCPROVST', 'Officer Commanding Provost - Approves, rejects or returns rosters.'),
(3, 'SNCO', 'Senior Non-Commissioned Officer - Schedules and drafts duty rosters for assigned camp.'),
(4, 'Airman', 'Normal service personnel who view assigned duties and notifications.');

-- Seed Shifts
INSERT INTO `shifts` (`shift_id`, `shift_name`, `start_time`, `end_time`, `duration_hours`, `description`, `status`) VALUES
(1, 'Morning Shift', '06:00:00', '14:00:00', 8.00, 'Regular morning watch', 'Active'),
(2, 'Afternoon Shift', '14:00:00', '22:00:00', 8.00, 'Regular afternoon watch', 'Active'),
(3, 'Night Shift', '22:00:00', '06:00:00', 8.00, 'Overnight watch', 'Active'),
(4, '24 Hour Duty', '08:00:00', '08:00:00', 24.00, 'Full day watch duty rotation', 'Active');

-- Seed Duty Types
INSERT INTO `duty_types` (`duty_type_id`, `duty_type_name`, `description`, `status`) VALUES
(1, 'Guard Duty', 'Security sentry watch points', 'Active'),
(2, 'Main Gate Duty', 'Entry point checking and authorization', 'Active'),
(3, 'Patrol Duty', 'Base perimeter foot/vehicle patrols', 'Active'),
(4, 'Armoury Duty', 'Armour keeper and weapon logs guard', 'Active'),
(5, 'Operations Room Duty', 'Communication and radar watch', 'Active'),
(6, 'VIP Security Duty', 'Security escort and safety detail', 'Active');

-- Seed Personnel (Admin, OCPROVST, SNCO, and Airmen for different bases)
INSERT INTO `personnel` (`service_number`, `rank`, `initials`, `full_name`, `trade`, `squadron`, `camp_id`, `contact_number`, `email`, `status`) VALUES
-- Administrator
('admin', 'Warrant Officer', 'A.B.', 'John Smith', 'Admin General', 'Admin HQ', 1, '+94711111111', 'admin@slaf.lk', 'Active'),
-- OCPROVST
('51838', 'Squadron Leader', 'K.L.', 'Kamal Perera', 'Provost Officer', 'Provost Squadron', 1, '+94722222222', 'provost@slaf.lk', 'Active'),
-- SNCO for Ekala
('51839', 'Flight Sergeant', 'M.R.', 'Rohan Fernando', 'Operations', 'No 3 Air Defence', 1, '+94733333333', 'snco.ekala@slaf.lk', 'Active'),
-- SNCO for Ratmalana
('51840', 'Flight Sergeant', 'A.P.', 'Anura Priyantha', 'Operations', 'No 2 Squadron', 2, '+94733333334', 'snco.ratmalana@slaf.lk', 'Active'),
-- Airmen in Ekala (Camp 1)
('51841', 'Corporal', 'S.T.', 'Saman Thilakarathne', 'Provost Guard', 'Provost Squadron', 1, '+94744444441', 'saman@slaf.lk', 'Active'),
('51842', 'LAC', 'D.M.', 'Dinesh Madushanka', 'Operations', 'No 3 Air Defence', 1, '+94744444442', 'dinesh@slaf.lk', 'Active'),
('51843', 'LAC', 'W.S.', 'Wasanta Silva', 'Operations', 'No 3 Air Defence', 1, '+94744444443', 'wasanta@slaf.lk', 'Active'),
-- Airmen in Ratmalana (Camp 2)
('51844', 'LAC', 'G.H.', 'Gayan Harsha', 'Aviation Tech', 'No 2 Squadron', 2, '+94755555551', 'gayan@slaf.lk', 'Active'),
('51845', 'SAC', 'N.J.', 'Nipuna Jayasinghe', 'Provost Guard', 'No 2 Squadron', 2, '+94755555552', 'nipuna@slaf.lk', 'Active');

-- Seed Users (Passwords are all hashed using standard BCRYPT of 'Password@123')
INSERT INTO `users` (`user_id`, `service_number`, `password_hash`, `role_id`, `status`) VALUES
(1, 'admin', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 1, 'Active'),
(2, '51838', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 2, 'Active'),
(3, '51839', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 3, 'Active'),
(4, '51840', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 3, 'Active'),
(5, '51841', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 4, 'Active'),
(6, '51842', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 4, 'Active'),
(7, '51843', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 4, 'Active'),
(8, '51844', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 4, 'Active'),
(9, '51845', '$2y$12$XjMX//wiQfAohrhfp/MZBei3DNtmRyAifbplnAIj.NvNsK93prPPO', 4, 'Active');

-- Seed initial active postings
INSERT INTO `postings` (`service_number`, `from_camp_id`, `to_camp_id`, `effective_date`, `end_date`, `status`) VALUES
('admin', 1, 1, '2025-01-01', NULL, 'Active'),
('51838', 1, 1, '2025-01-01', NULL, 'Active'),
('51839', 1, 1, '2025-01-01', NULL, 'Active'),
('51840', 2, 2, '2025-01-01', NULL, 'Active'),
('51841', 1, 1, '2025-01-01', NULL, 'Active'),
('51842', 1, 1, '2025-01-01', NULL, 'Active'),
('51843', 1, 1, '2025-01-01', NULL, 'Active'),
('51844', 2, 2, '2025-01-01', NULL, 'Active'),
('51845', 2, 2, '2025-01-01', NULL, 'Active');
