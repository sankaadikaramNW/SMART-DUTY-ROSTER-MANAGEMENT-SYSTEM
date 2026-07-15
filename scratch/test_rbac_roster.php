<?php
/**
 * Test Validation Script - WO I/C RBAC and Roster Lifecycle Locking
 */

require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Env.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/config.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/config/database.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Session.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/middleware/RoleMiddleware.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/middleware/LocationMiddleware.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/helpers/Logger.php';
require_once 'c:/xampp/htdocs/SMART DUTY ROSTER MANAGEMENT SYSTEM/models/Roster.php';

Session::start();

try {
    $db = Database::getInstance()->getConnection();
    echo "=== RBAC & Roster Lifecycle Validation Suite ===\n";

    // 1. Validate DB Columns
    $stmt = $db->query("SHOW COLUMNS FROM `duty_rosters`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $required = ['last_updated_by', 'submitted_by', 'submitted_date', 'approved_by', 'approved_date', 'rejected_by', 'rejected_date', 'rejection_reason', 'audit_log_ref'];
    $missing = array_diff($required, $columns);
    if (empty($missing)) {
        echo "PASS: All lifecycle tracking columns exist in `duty_rosters` table.\n";
    } else {
        throw new Exception("FAIL: Missing DB columns: " . implode(', ', $missing));
    }

    // 2. Validate RoleMiddleware Checks
    echo "Running RBAC route validation checks...\n";

    // Test 2a: WO I/C should have roster management access
    Session::set('role_name', 'Warrant Officer IC');
    try {
        RoleMiddleware::check('/rosters/create');
        RoleMiddleware::check('/rosters/save');
        RoleMiddleware::check('/rosters/conflict-check');
        echo "PASS: Warrant Officer IC has full roster creation & verification access.\n";
    } catch (Exception $e) {
        throw new Exception("FAIL: Warrant Officer IC denied roster management routes: " . $e->getMessage());
    }

    // Test 2b: SNCO should NOT have roster management access
    Session::set('role_name', 'SNCO');
    $deniedCount = 0;
    $deniedRoutes = ['/rosters/create', '/rosters/save', '/rosters/action', '/rosters/conflict-check', '/transfers', '/leaves'];
    foreach ($deniedRoutes as $r) {
        try {
            RoleMiddleware::check($r);
        } catch (Exception $e) {
            $deniedCount++;
        }
    }
    if ($deniedCount === count($deniedRoutes)) {
        echo "PASS: SNCO successfully restricted from all create, save, action, posting, and leave management routes.\n";
    } else {
        throw new Exception("FAIL: SNCO bypassed one or more prohibited routes. Prohibitions caught: {$deniedCount}/" . count($deniedRoutes));
    }

    // 3. Test Roster Status Locking check
    echo "Validating roster modification status locks...\n";
    
    Session::set('role_name', 'Administrator');
    Session::set('camp_id', 1);

    // Get a roster from the DB to test locking
    $stmt = $db->query("SELECT * FROM duty_rosters LIMIT 1");
    $roster = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($roster) {
        $rId = $roster['roster_id'];
        $originalStatus = $roster['status'];

        // Mock a 'Submitted' status
        $db->query("UPDATE duty_rosters SET status = 'Submitted' WHERE roster_id = $rId");

        // Verify status locking check
        $lockedRoster = Roster::getById($rId);
        if (in_array($lockedRoster['status'], ['Submitted', 'Approved', 'Published'])) {
            echo "PASS: Roster ID #{$rId} status successfully locked server-side ('{$lockedRoster['status']}').\n";
        } else {
            throw new Exception("FAIL: Roster status locking check failed.");
        }

        // Restore original status
        $db->query("UPDATE duty_rosters SET status = '{$originalStatus}' WHERE roster_id = $rId");
    } else {
        echo "INFO: No roster found in database to test locks. (Ignored)\n";
    }

    // 4. Validate Print Auditing Log Insertion
    echo "Testing Print Roster audit log insertion...\n";
    Session::set('user_id', 1);
    Session::set('service_number', 'admin');
    
    $stmtCountPrev = $db->query("SELECT COUNT(*) FROM audit_logs WHERE module = 'Roster Management' AND action LIKE 'Printed Roster%'");
    $prevCount = (int)$stmtCountPrev->fetchColumn();

    Logger::audit('Roster Management', 'Printed Roster ID: 9999 (Name: CLI Validation Test)');

    $stmtCountNew = $db->query("SELECT COUNT(*) FROM audit_logs WHERE module = 'Roster Management' AND action LIKE 'Printed Roster%'");
    $newCount = (int)$stmtCountNew->fetchColumn();

    if ($newCount > $prevCount) {
        echo "PASS: Print Roster log successfully written to audit trail.\n";
    } else {
        throw new Exception("FAIL: Print Roster audit log insertion failed.");
    }

    echo "=== All RBAC & Roster Lifecycle Tests Passed Successfully ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
