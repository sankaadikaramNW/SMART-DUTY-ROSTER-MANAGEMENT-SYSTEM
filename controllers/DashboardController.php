<?php
/**
 * Dashboard Controller
 */

class DashboardController {

    // Render Main Dashboard Screen
    public function index() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $roleName    = Session::get('role_name');
            $serviceNum  = Session::get('service_number');
            $campId      = Session::get('camp_id');
            $restrictedCampId = LocationMiddleware::getCampConstraint();

            $camps = Camp::getAll(true);
            $shifts = Shift::getAll(true);
            $activeDutyTypes = DutyType::getAll(true);
            
            $activeCampId = $restrictedCampId ?? $campId ?? 1;

            // Fetch welcome info
            $rankName = Session::get('rank');
            $fullName = Session::get('full_name');
            $today = date('l, d F Y');

            if ($roleName === 'OCPROVST') {
                $ocStats = [];
                // Pending Duty Crews
                $stmt = $db->prepare("SELECT COUNT(DISTINCT da.roster_id, da.duty_date, da.shift_id, da.duty_type_id) 
                                      FROM duty_assignments da 
                                      JOIN duty_rosters dr ON da.roster_id = dr.roster_id 
                                      WHERE dr.status = 'Submitted' AND dr.camp_id = ?");
                $stmt->execute([$activeCampId]);
                $ocStats['pending_crews'] = (int)$stmt->fetchColumn();

                // Approved Duty Crews (Published)
                $stmt = $db->prepare("SELECT COUNT(DISTINCT da.roster_id, da.duty_date, da.shift_id, da.duty_type_id) 
                                      FROM duty_assignments da 
                                      JOIN duty_rosters dr ON da.roster_id = dr.roster_id 
                                      WHERE dr.status = 'Published' AND dr.camp_id = ?");
                $stmt->execute([$activeCampId]);
                $ocStats['approved_crews'] = (int)$stmt->fetchColumn();

                // Rejected Duty Crews
                $stmt = $db->prepare("SELECT COUNT(DISTINCT da.roster_id, da.duty_date, da.shift_id, da.duty_type_id) 
                                      FROM duty_assignments da 
                                      JOIN duty_rosters dr ON da.roster_id = dr.roster_id 
                                      WHERE dr.status = 'Rejected' AND dr.camp_id = ?");
                $stmt->execute([$activeCampId]);
                $ocStats['rejected_crews'] = (int)$stmt->fetchColumn();

                // Today's Approved Duties (personnel count)
                $stmt = $db->prepare("SELECT COUNT(*) 
                                      FROM duty_assignments da 
                                      JOIN duty_rosters dr ON da.roster_id = dr.roster_id 
                                      WHERE dr.status = 'Published' AND dr.camp_id = ? AND da.duty_date = CURDATE()");
                $stmt->execute([$activeCampId]);
                $ocStats['today_duties'] = (int)$stmt->fetchColumn();

                // Upcoming Duties
                $stmt = $db->prepare("SELECT COUNT(*) 
                                      FROM duty_assignments da 
                                      JOIN duty_rosters dr ON da.roster_id = dr.roster_id 
                                      WHERE dr.status = 'Published' AND dr.camp_id = ? AND da.duty_date > CURDATE()");
                $stmt->execute([$activeCampId]);
                $ocStats['upcoming_duties'] = (int)$stmt->fetchColumn();

                // Recent Approvals
                $stmt = $db->prepare("SELECT dca.*, p.full_name, rk.rank_short_name, dr.roster_name, dt.duty_type_name, s.shift_name
                                      FROM duty_crew_approvals dca
                                      JOIN duty_rosters dr ON dca.roster_id = dr.roster_id
                                      JOIN users u ON dca.action_by = u.user_id
                                      LEFT JOIN personnel p ON u.service_number = p.service_number
                                      LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                                      JOIN duty_types dt ON dca.duty_type_id = dt.duty_type_id
                                      JOIN shifts s ON dca.shift_id = s.shift_id
                                      WHERE dr.camp_id = ?
                                      ORDER BY dca.created_at DESC LIMIT 5");
                $stmt->execute([$activeCampId]);
                $ocStats['recent_approvals'] = $stmt->fetchAll();

                $pageTitle = 'OCPROVST Dashboard - Approving Authority';
                include __DIR__ . '/../views/dashboard/ocprovst_dashboard.php';
                return;
            }

            $pageTitle = 'Dashboard - Watch Calendar';
            include __DIR__ . '/../views/dashboard/index.php';
        } catch (Exception $e) {
            if (Session::has('user_id')) {
                throw $e;
            }
            Session::set('error_message', $e->getMessage());
            Response::redirect('/login');
        }
    }

    // JSON API for dashboard metrics and stays modal
    public function getAttendanceStats() {
        try {
            $systemTime = date('H:i:s');
            $overdueCount = LeaveRecord::getOverdueLeaveCount();
            
            // Get active crew members count (unique personnel assigned today on published rosters)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT COUNT(DISTINCT a.service_number) 
                FROM duty_assignments a
                JOIN duty_rosters r ON a.roster_id = r.roster_id
                WHERE a.duty_date = CURDATE()
                  AND r.status = 'Published'
            ");
            $activeCrewCount = (int)$stmt->fetchColumn();
            
            // List of stays
            $stays = LeaveRecord::getPersonnelStays();
            
            return Response::json([
                'system_time' => $systemTime,
                'overdue_count' => $overdueCount,
                'active_crew_count' => $activeCrewCount,
                'personnel_stays' => $stays
            ]);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
}
