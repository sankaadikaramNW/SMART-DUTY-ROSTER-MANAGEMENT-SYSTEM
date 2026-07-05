-- Smart Duty Roster Management System Schema & Seed Data
-- Database: afp

CREATE DATABASE IF NOT EXISTS `afp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `afp`;

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
-- Table `ranks`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ranks` (
  `rank_id` INT AUTO_INCREMENT PRIMARY KEY,
  `rank_code` VARCHAR(50) UNIQUE NOT NULL,
  `rank_name` VARCHAR(100) NOT NULL,
  `rank_short_name` VARCHAR(20) NOT NULL,
  `display_order` INT DEFAULT 0,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `personnel`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personnel` (
  `service_number` VARCHAR(30) PRIMARY KEY,
  `rank_id` INT NOT NULL,
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
  FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`rank_id`) ON UPDATE CASCADE,
  INDEX idx_personnel_camp (`camp_id`),
  INDEX idx_personnel_status (`status`),
  INDEX idx_personnel_rank (`rank_id`)
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
(4, 'Airman', 'Normal service personnel who view assigned duties and notifications.'),
(5, 'Warrant Officer IC', 'Warrant Officer In-Charge - Schedules duty rosters, manages personnel, and administers user accounts.'),
(6, 'Super Admin', 'Super Admin with absolute privileges, including immutable log auditing.');

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

-- Seed Ranks
INSERT INTO `ranks` (`rank_id`, `rank_code`, `rank_name`, `rank_short_name`, `display_order`, `status`) VALUES
(1, 'GP_CAPT', 'Group Captain', 'Gp Capt', 1, 'Active'),
(2, 'WG_CDR', 'Wing Commander', 'Wg Cdr', 2, 'Active'),
(3, 'SQNLDR', 'Squadron Leader', 'Sqn Ldr', 3, 'Active'),
(4, 'FLT_LT', 'Flight Lieutenant', 'Flt Lt', 4, 'Active'),
(5, 'FG_OFF', 'Flying Officer', 'Fg Off', 5, 'Active'),
(6, 'PLT_OFF', 'Pilot Officer', 'Plt Off', 6, 'Active'),
(7, 'MWO', 'Master Warrant Officer', 'MWO', 7, 'Active'),
(8, 'WO', 'Warrant Officer', 'WO', 8, 'Active'),
(9, 'F_SGT', 'Flight Sergeant', 'F/Sgt', 9, 'Active'),
(10, 'SGT', 'Sergeant', 'Sgt', 10, 'Active'),
(11, 'CPL', 'Corporal', 'Cpl', 11, 'Active'),
(12, 'LAC', 'Leading Aircraftman', 'LAC', 12, 'Active'),
(13, 'AC', 'Aircraftman', 'AC', 13, 'Active');

-- Seed Personnel (Admin, OCPROVST, SNCO, and Airmen for different bases)
INSERT INTO `personnel` (`service_number`, `rank_id`, `initials`, `full_name`, `trade`, `squadron`, `camp_id`, `contact_number`, `email`, `status`) VALUES
-- Super Admin
('sadmin', 8, 'S.', 'Super Administrator', 'Cyber Security', 'Directorate of IT', 1, '+94777777777', 'sadmin@slaf.lk', 'Active'),
-- Administrator
('admin', 8, 'A.B.', 'John Smith', 'Admin General', 'Admin HQ', 1, '+94711111111', 'admin@slaf.lk', 'Active'),
-- OCPROVST Katunayake
('51838', 3, 'K.L.', 'Sathruwan', 'Provost Officer', 'Provost Squadron', 3, '+94722222222', 'provost@slaf.lk', 'Active'),
-- SNCO for Ekala
('51839', 9, 'M.R.', 'Rohan Fernando', 'Operations', 'No 3 Air Defence', 1, '+94733333333', 'snco.ekala@slaf.lk', 'Active'),
-- SNCO for Ratmalana
('51840', 9, 'A.P.', 'Anura Priyantha', 'Operations', 'No 2 Squadron', 2, '+94733333334', 'snco.ratmalana@slaf.lk', 'Active'),
-- Test Personnel (LAC Adikaram) in Ratmalana (Camp 2)
('51837', 12, 'S.', 'Adikaram', 'Provost Guard', 'No 2 Squadron', 2, '+94755555553', 'adikaram@slaf.lk', 'Active'),
-- OCPROVST for Ekala
('51846', 3, 'K.L.', 'Ekala OCPROVST', 'Provost Officer', 'Provost Squadron', 1, '+94722222223', 'ocprovost.ekala@slaf.lk', 'Active'),
-- OCPROVST for Ratmalana
('51847', 3, 'A.P.', 'Ratmalana OCPROVST', 'Provost Officer', 'No 2 Squadron', 2, '+94722222224', 'ocprovost.ratmalana@slaf.lk', 'Active');

-- Seed Users (Passwords are all hashed using standard BCRYPT of 'Password@123')
INSERT INTO `users` (`service_number`, `password_hash`, `role_id`, `status`) VALUES
('sadmin', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 6, 'Active'),
('admin', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 1, 'Active'),
('51838', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 2, 'Active'),
('51839', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 3, 'Active'),
('51840', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 3, 'Active'),
('51837', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 4, 'Active'),
('51846', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 2, 'Active'),
('51847', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 2, 'Active');

-- Seed initial active postings
INSERT INTO `postings` (`service_number`, `from_camp_id`, `to_camp_id`, `effective_date`, `end_date`, `status`) VALUES
('sadmin', 1, 1, '2025-01-01', NULL, 'Active'),
('admin', 1, 1, '2025-01-01', NULL, 'Active'),
('51838', 3, 3, '2025-01-01', NULL, 'Active'),
('51839', 1, 1, '2025-01-01', NULL, 'Active'),
('51840', 2, 2, '2025-01-01', NULL, 'Active'),
('51837', 2, 2, '2025-01-01', NULL, 'Active'),
('51846', 1, 1, '2025-01-01', NULL, 'Active'),
('51847', 2, 2, '2025-01-01', NULL, 'Active');

-- --------------------------------------------------------
-- Table `posting_transfers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posting_transfers` (
  `transfer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `from_camp_id` INT NOT NULL,
  `to_camp_id` INT NOT NULL,
  `effective_date` DATE NOT NULL,
  `reason` TEXT NOT NULL,
  `remarks` TEXT NULL,
  `supporting_documents` VARCHAR(255) NULL,
  `status` ENUM('Draft', 'Pending Origin Approval', 'Origin Approved', 'Pending Destination Review', 'Pending Destination Approval', 'Transfer Completed', 'Returned for Correction', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Draft',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`from_camp_id`) REFERENCES `camps` (`camp_id`),
  FOREIGN KEY (`to_camp_id`) REFERENCES `camps` (`camp_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  INDEX idx_transfer_status (`status`),
  INDEX idx_transfer_service (`service_number`),
  INDEX idx_transfer_from_camp (`from_camp_id`),
  INDEX idx_transfer_to_camp (`to_camp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `posting_approvals`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posting_approvals` (
  `approval_id` INT AUTO_INCREMENT PRIMARY KEY,
  `transfer_id` INT NOT NULL,
  `action_by` INT NOT NULL,
  `action_role` VARCHAR(50) NOT NULL,
  `action` ENUM('Submit', 'Approve', 'Reject', 'Return', 'Cancel', 'Override') NOT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`transfer_id`) REFERENCES `posting_transfers` (`transfer_id`) ON DELETE CASCADE,
  FOREIGN KEY (`action_by`) REFERENCES `users` (`user_id`),
  INDEX idx_posting_approval_transfer (`transfer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed LAC Adikaram (51837) and OCPROVST users for camps 1 and 2
INSERT INTO `personnel` (`service_number`, `rank_id`, `initials`, `full_name`, `trade`, `squadron`, `camp_id`, `contact_number`, `email`, `status`) VALUES
('51837', 5, 'S.', 'Adikaram', 'Provost Guard', 'No 2 Squadron', 2, '+94755555553', 'adikaram@slaf.lk', 'Active'),
('51846', 2, 'K.L.', 'Ekala OCPROVST', 'Provost Officer', 'Provost Squadron', 1, '+94722222223', 'ocprovost.ekala@slaf.lk', 'Active'),
('51847', 2, 'A.P.', 'Ratmalana OCPROVST', 'Provost Officer', 'No 2 Squadron', 2, '+94722222224', 'ocprovost.ratmalana@slaf.lk', 'Active');

INSERT INTO `users` (`service_number`, `password_hash`, `role_id`, `status`) VALUES
('51837', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 4, 'Active'),
('51846', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 2, 'Active'),
('51847', '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi', 2, 'Active');

INSERT INTO `postings` (`service_number`, `from_camp_id`, `to_camp_id`, `effective_date`, `end_date`, `status`) VALUES
('51837', 2, 2, '2025-01-01', NULL, 'Active'),
('51846', 1, 1, '2025-01-01', NULL, 'Active'),
('51847', 2, 2, '2025-01-01', NULL, 'Active');

-- --------------------------------------------------------
-- Table `camp_attendance`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `camp_attendance` (
  `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NULL,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table `leave_records`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leave_records` (
  `leave_id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_number` VARCHAR(30) NOT NULL,
  `leave_start_date` DATE NOT NULL,
  `leave_end_date` DATE NOT NULL,
  `leave_type` VARCHAR(50) NOT NULL,
  `approved_by` VARCHAR(30) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actual_reporting_date` DATE DEFAULT NULL,
  `granted_end_date` DATE DEFAULT NULL,
  `granted_by` VARCHAR(30) DEFAULT NULL,
  `granted_reason` TEXT DEFAULT NULL,
  `granted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`service_number`) REFERENCES `personnel` (`service_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed camp_attendance data
INSERT INTO `camp_attendance` (`service_number`, `check_in_date`, `check_out_date`) VALUES
('sadmin', '2026-07-01', NULL),
('admin', '2026-06-25', NULL),
('51837', '2026-06-01', NULL),
('51839', '2026-07-04', NULL),
('51838', '2026-06-01', '2026-06-19');

-- Seed leave_records data
INSERT INTO `leave_records` (`service_number`, `leave_start_date`, `leave_end_date`, `leave_type`, `approved_by`) VALUES
('51838', '2026-06-20', '2026-06-30', 'Annual Leave', 'admin'),
('51840', '2026-07-01', '2026-07-10', 'Casual Leave', 'admin');
