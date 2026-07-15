<?php
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "CONNECTED TO DB.\n\n";

    echo "--- PERSONNEL --- \n";
    $stmt = $db->query("SELECT service_number, initials, full_name, camp_id, status FROM personnel WHERE service_number IN ('51990', '51088')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n--- DUTY ASSIGNMENTS --- \n";
    $stmt = $db->query("SELECT a.*, r.camp_id AS roster_camp_id, r.status AS roster_status FROM duty_assignments a JOIN duty_rosters r ON a.roster_id = r.roster_id WHERE a.service_number IN ('51990', '51088')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
