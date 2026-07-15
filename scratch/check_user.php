<?php
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "USER '51847' STATE:\n";
    $stmt = $db->prepare("SELECT * FROM users WHERE service_number = ?");
    $stmt->execute(['51847']);
    print_r($stmt->fetch(PDO::FETCH_ASSOC));

    echo "\nUSER '51846' STATE:\n";
    $stmt = $db->prepare("SELECT * FROM users WHERE service_number = ?");
    $stmt->execute(['51846']);
    print_r($stmt->fetch(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
