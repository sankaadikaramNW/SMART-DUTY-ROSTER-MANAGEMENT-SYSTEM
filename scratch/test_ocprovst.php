<?php
/**
 * Validation Script for OCPROVST realignments & bulk approvals
 */

require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Validation Test Suite ===\n";

    // 1. Verify table presence
    $stmt = $db->query("SHOW TABLES LIKE 'duty_crew_approvals'");
    $tableExists = $stmt->fetchColumn();
    if ($tableExists) {
        echo "PASS: table 'duty_crew_approvals' is present in database.\n";
    } else {
        throw new Exception("FAIL: table 'duty_crew_approvals' does not exist.");
    }

    // 2. Validate columns structure
    $stmt = $db->query("DESCRIBE `duty_crew_approvals`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'roster_id', 'duty_date', 'shift_id', 'duty_type_id', 'action_by', 'action', 'remarks', 'previous_status', 'new_status', 'ip_address', 'user_agent', 'created_at'];
    $missing = array_diff($requiredColumns, $columns);
    if (empty($missing)) {
        echo "PASS: 'duty_crew_approvals' has all required metadata columns.\n";
    } else {
        throw new Exception("FAIL: Missing columns: " . implode(', ', $missing));
    }

    // 3. Test Roster/Crew grouping query structure
    // Fetch a sample roster in Submitted status
    $stmt = $db->query("SELECT roster_id FROM duty_rosters WHERE status = 'Submitted' LIMIT 1");
    $rosterId = $stmt->fetchColumn();
    if ($rosterId) {
        // Query grouping
        $stmtGroup = $db->prepare("
            SELECT da.duty_date, da.shift_id, da.duty_type_id, COUNT(*) as assigned_count
            FROM duty_assignments da
            WHERE da.roster_id = ?
            GROUP BY da.duty_date, da.shift_id, da.duty_type_id
        ");
        $stmtGroup->execute([$rosterId]);
        $groups = $stmtGroup->fetchAll(PDO::FETCH_ASSOC);
        echo "PASS: Successfully aggregated Duty Crews for Roster ID #{$rosterId}. Count: " . count($groups) . "\n";
    } else {
        echo "INFO: No submitted rosters available in DB currently to test dynamic groupings. (Ignored)\n";
    }

    // 4. Validate Transaction integrity (Rollback test)
    echo "Starting transaction rollback verification...\n";
    $db->beginTransaction();
    
    // Insert dummy record
    $stmt = $db->query("SELECT user_id FROM users LIMIT 1");
    $userId = $stmt->fetchColumn();
    $stmtRoster = $db->query("SELECT roster_id FROM duty_rosters LIMIT 1");
    $rId = $stmtRoster->fetchColumn();
    $stmtShift = $db->query("SELECT shift_id FROM shifts LIMIT 1");
    $sId = $stmtShift->fetchColumn();
    $stmtType = $db->query("SELECT duty_type_id FROM duty_types LIMIT 1");
    $tId = $stmtType->fetchColumn();

    if ($userId && $rId && $sId && $tId) {
        $stmtInsert = $db->prepare("INSERT INTO duty_crew_approvals 
            (roster_id, duty_date, shift_id, duty_type_id, action_by, action, remarks, previous_status, new_status, ip_address, user_agent)
            VALUES (?, '2026-12-31', ?, ?, ?, 'Approve', 'Rollback Test', 'Pending', 'Approved', '127.0.0.1', 'CLI Test')");
        $stmtInsert->execute([$rId, $sId, $tId, $userId]);
        $insertedId = $db->lastInsertId();
        echo "Inserted dummy log ID: {$insertedId}. Executing rollback...\n";
        
        $db->rollBack();
        
        // Verify absence after rollback
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM duty_crew_approvals WHERE id = ?");
        $stmtCheck->execute([$insertedId]);
        $exists = $stmtCheck->fetchColumn();
        if (!$exists) {
            echo "PASS: Transaction rolled back successfully. Database remains clean.\n";
        } else {
            throw new Exception("FAIL: Rollback did not erase the test insertion!");
        }
    } else {
        echo "INFO: Insufficient master data in database to run transaction test. (Ignored)\n";
        if ($db->inTransaction()) {
            $db->rollBack();
        }
    }

    echo "=== All Tests Completed Successfully ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
}
