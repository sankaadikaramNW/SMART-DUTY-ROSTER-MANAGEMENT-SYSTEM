<?php
/**
 * Database Migration Script for OCPROVST realignments
 */

require_once __DIR__ . '/../helpers/Env.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected successfully to " . DB_NAME . ".\n";

    $sql = "CREATE TABLE IF NOT EXISTS `duty_crew_approvals` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `roster_id` INT NOT NULL,
      `duty_date` DATE NOT NULL,
      `shift_id` INT NOT NULL,
      `duty_type_id` INT NOT NULL,
      `action_by` INT NOT NULL,
      `action` VARCHAR(50) NOT NULL,
      `remarks` TEXT NULL,
      `previous_status` VARCHAR(50) NOT NULL,
      `new_status` VARCHAR(50) NOT NULL,
      `ip_address` VARCHAR(45) NULL,
      `user_agent` VARCHAR(255) NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`roster_id`) REFERENCES `duty_rosters` (`roster_id`) ON DELETE CASCADE,
      FOREIGN KEY (`action_by`) REFERENCES `users` (`user_id`),
      FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`),
      FOREIGN KEY (`duty_type_id`) REFERENCES `duty_types` (`duty_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "SUCCESS: Created table duty_crew_approvals.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
