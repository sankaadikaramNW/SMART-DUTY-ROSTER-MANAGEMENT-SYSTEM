<?php
/**
 * Database Singleton Connection class using PDO
 */

class Database {
    private static $instance = null;
    private $conn;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // True prepared statements
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->checkAndMigrate();
        } catch (PDOException $e) {
            throw new Exception("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Auto-migration schema self-healing check
    private function checkAndMigrate() {
        try {
            // Check if duty_crew_approvals exists
            $stmt = $this->conn->query("SHOW TABLES LIKE 'duty_crew_approvals'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                // Create duty_crew_approvals table
                $this->conn->exec("CREATE TABLE IF NOT EXISTS `duty_crew_approvals` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            }
            
            // Check if duty_rosters has approved_by
            $stmt = $this->conn->query("SHOW COLUMNS FROM `duty_rosters` LIKE 'approved_by'");
            $columnExists = $stmt->rowCount() > 0;
            
            if (!$columnExists) {
                // Add all lifecycle tracking columns to duty_rosters
                $columnsToAdd = [
                    "ALTER TABLE `duty_rosters` ADD COLUMN `last_updated_by` INT NULL",
                    "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_last_updated_by` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `submitted_by` INT NULL",
                    "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `submitted_date` DATETIME NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `approved_by` INT NULL",
                    "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `approved_date` DATETIME NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `rejected_by` INT NULL",
                    "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `rejected_date` DATETIME NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `rejection_reason` TEXT NULL",
                    "ALTER TABLE `duty_rosters` ADD COLUMN `audit_log_ref` VARCHAR(255) NULL"
                ];
                
                foreach ($columnsToAdd as $sql) {
                    try {
                        $this->conn->exec($sql);
                    } catch (PDOException $ex) {
                        // Ignore if column or constraint already exists
                    }
                }
            }
        } catch (Exception $e) {
            // Fail silently so as not to block DB connection for other requests
        }
    }

    // Get connection instance
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Return active connection
    public function getConnection() {
        return $this->conn;
    }
}
