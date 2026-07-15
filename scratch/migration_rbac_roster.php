<?php
/**
 * Migration Script - Adds Roster Lifecycle Tracking Columns to `duty_rosters`
 */

require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Starting schema migration for duty_rosters lifecycle tracking...\n";

    // Check if columns already exist to make script idempotent
    $stmt = $db->query("SHOW COLUMNS FROM `duty_rosters`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $queries = [];

    if (!in_array('last_updated_by', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `last_updated_by` INT NULL";
        $queries[] = "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_last_updated_by` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL";
    }

    if (!in_array('submitted_by', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `submitted_by` INT NULL";
        $queries[] = "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL";
    }

    if (!in_array('submitted_date', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `submitted_date` DATETIME NULL";
    }

    if (!in_array('approved_by', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `approved_by` INT NULL";
        $queries[] = "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL";
    }

    if (!in_array('approved_date', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `approved_date` DATETIME NULL";
    }

    if (!in_array('rejected_by', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `rejected_by` INT NULL";
        $queries[] = "ALTER TABLE `duty_rosters` ADD CONSTRAINT `fk_roster_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL";
    }

    if (!in_array('rejected_date', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `rejected_date` DATETIME NULL";
    }

    if (!in_array('rejection_reason', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `rejection_reason` TEXT NULL";
    }

    if (!in_array('audit_log_ref', $columns)) {
        $queries[] = "ALTER TABLE `duty_rosters` ADD COLUMN `audit_log_ref` VARCHAR(255) NULL";
    }

    if (!empty($queries)) {
        foreach ($queries as $sql) {
            echo "Executing: $sql\n";
            $db->exec($sql);
        }
        echo "SUCCESS: Added lifecycle tracking columns to `duty_rosters`.\n";
    } else {
        echo "INFO: All lifecycle columns already exist in `duty_rosters`.\n";
    }

} catch (Exception $e) {
    echo "ERROR: Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
