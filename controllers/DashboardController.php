<?php
/**
 * Dashboard Controller
 */

class DashboardController {

    // Render Main Dashboard Screen
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $roleName = Session::get('role_name');
        $serviceNum = Session::get('service_number');
        $restrictedCampId = LocationMiddleware::getCampConstraint();

        // 1. Get total personnel count
        $pCountSql = "SELECT COUNT(*) FROM personnel WHERE 1=1";
        $pParams = [];
        if ($restrictedCampId !== null) {
            $pCountSql .= " AND camp_id = ?";
            $pParams[] = $restrictedCampId;
        }
        $stmt = $db->prepare($pCountSql);
        $stmt->execute($pParams);
        $totalPersonnel = (int)$stmt->fetchColumn();

        // 2. Get total rosters count
        $rCountSql = "SELECT COUNT(*) FROM duty_rosters WHERE 1=1";
        $rParams = [];
        if ($restrictedCampId !== null) {
            $rCountSql .= " AND camp_id = ?";
            $rParams[] = $restrictedCampId;
        }
        $stmt = $db->prepare($rCountSql);
        $stmt->execute($rParams);
        $totalRosters = (int)$stmt->fetchColumn();

        // 3. Get pending approvals (Rosters in 'Submitted' status)
        $pendingApprovals = 0;
        if ($roleName === 'OCPROVST' || $roleName === 'Administrator') {
            $stmt = $db->query("SELECT COUNT(*) FROM duty_rosters WHERE status = 'Submitted'");
            $pendingApprovals = (int)$stmt->fetchColumn();
        }

        // 4. Get active shifts count
        $stmt = $db->query("SELECT COUNT(*) FROM shifts WHERE status = 'Active'");
        $totalShifts = (int)$stmt->fetchColumn();

        // 5. Get user's personal upcoming duties (For Airman/All)
        $upcomingDuties = [];
        if ($serviceNum) {
            $stmt = $db->prepare("
                SELECT a.*, r.roster_name, s.shift_name, s.start_time, s.end_time, t.duty_type_name, c.camp_name
                FROM duty_assignments a
                JOIN duty_rosters r ON a.roster_id = r.roster_id
                JOIN camps c ON r.camp_id = c.camp_id
                JOIN shifts s ON a.shift_id = s.shift_id
                JOIN duty_types t ON a.duty_type_id = t.duty_type_id
                WHERE a.service_number = ? AND a.duty_date >= CURDATE()
                  AND r.status = 'Published'
                ORDER BY a.duty_date ASC, s.start_time ASC
                LIMIT 5
            ");
            $stmt->execute([$serviceNum]);
            $upcomingDuties = $stmt->fetchAll();
        }

        // 6. Recent duty rosters for list view
        $recentRostersSql = "SELECT r.*, c.camp_name 
                             FROM duty_rosters r 
                             JOIN camps c ON r.camp_id = c.camp_id 
                             WHERE 1=1";
        $rrParams = [];
        if ($restrictedCampId !== null) {
            $recentRostersSql .= " AND r.camp_id = ?";
            $rrParams[] = $restrictedCampId;
        }
        $recentRostersSql .= " ORDER BY r.updated_at DESC LIMIT 5";
        $stmt = $db->prepare($recentRostersSql);
        $stmt->execute($rrParams);
        $recentRosters = $stmt->fetchAll();

        // Render dashboard view
        $pageTitle = 'Dashboard';
        include __DIR__ . '/../views/dashboard/index.php';
    }
}
