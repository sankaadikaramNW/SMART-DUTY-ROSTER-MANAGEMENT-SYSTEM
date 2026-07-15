<?php
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $hash = '$2y$12$uKibVn5mEo7hktNktAjsaOA3dNtWzK2NlFWlWeFM2bHd7re2Dl9Oi'; // Password@123
    $stmt = $db->prepare("UPDATE users SET password_hash = ?, failed_attempts = 0, lock_date = NULL, lock_reason = NULL WHERE service_number = ?");
    $stmt->execute([$hash, '51847']);
    echo "USER '51847' SUCCESSFULLY UNLOCKED AND PASSWORD RESET TO Password@123\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
