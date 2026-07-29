<?php
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "CONNECTED TO DB.\n\n";

    echo "--- SHIFTS COLUMNS ---\n";
    $stmt = $db->query("DESCRIBE shifts");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n--- DUTY ASSIGNMENTS COLUMNS ---\n";
    $stmt = $db->query("DESCRIBE duty_assignments");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
