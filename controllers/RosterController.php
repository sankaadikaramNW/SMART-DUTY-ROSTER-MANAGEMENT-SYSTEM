<?php
/**
 * Roster Controller
 */

class RosterController {

    // List rosters
    public function index() {
        $roleName = Session::get('role_name');
        if ($roleName === 'Airman') {
            $rosters = Roster::getAll(null, 'Published');
        } else {
            $rosters = Roster::getAll();
        }
        $camps = Camp::getAll(true);

        $pageTitle = 'Duty Rosters';
        include __DIR__ . '/../views/rosters/index.php';
    }

    // Create / Edit Roster View
    public function createView() {
        try {
            $roleName = Session::get('role_name');
            if ($roleName !== 'Warrant Officer IC' && $roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                throw new Exception("Unauthorized Access: Only Warrant Officer I/C or Administrator can build/edit rosters.");
            }

            $rosterId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $roster = null;
            $assignments = [];

            if ($rosterId) {
                $roster = Roster::getById($rosterId);
                if (!$roster) {
                    throw new Exception("Roster not found.");
                }
                if ($roster['status'] !== 'Draft' && $roster['status'] !== 'Rejected') {
                    throw new Exception("Roster is already submitted or approved and cannot be edited.");
                }
                $assignments = DutyAssignment::getByRosterId($rosterId);
                $campId = (int)$roster['camp_id'];
            } else {
                $campId = LocationMiddleware::getCampConstraint() ?? (int)($_GET['camp_id'] ?? Session::get('camp_id') ?? 1);
            }

            // Verify camp access
            LocationMiddleware::validateCamp($campId);

            $camp = Camp::getById($campId);
            $camps = Camp::getAll(true);
            $shifts = Shift::getAll(true);
            $dutyTypes = DutyType::getAll(true);
            $personnel = Personnel::getAll($campId, 'Active');

            $pageTitle = $rosterId ? "Edit Roster Draft" : "Create Smart Roster";
            include __DIR__ . '/../views/rosters/create.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/rosters');
        }
    }

    // View Roster details and approval actions
    public function viewRoster() {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception("Roster ID is required.");
            }

            $roster = Roster::getById($id);
            if (!$roster) {
                throw new Exception("Roster not found.");
            }

            // Access validation via camp isolation
            LocationMiddleware::validateCamp($roster['camp_id']);

            $roleName = Session::get('role_name');
            if ($roleName === 'Airman' && $roster['status'] !== 'Published') {
                throw new Exception("Unauthorized Access: Airman can only view published rosters.");
            }

            if ($roleName === 'Warrant Officer IC') {
                Logger::audit('Roster Management', 'View Roster: ' . $id);
            }

            $assignments = DutyAssignment::getByRosterId($id);
            $approvals = Approval::getByRosterId($id);

            $pageTitle = "Roster: " . htmlspecialchars($roster['roster_name']);
            include __DIR__ . '/../views/rosters/view.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/rosters');
        }
    }

    // Calendar View Page
    public function calendarView() {
        $camps = Camp::getAll(true);
        $shifts = Shift::getAll(true);
        $activeDutyTypes = DutyType::getAll(true);
        
        $activeCampId = LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1;
        $roleName = Session::get('role_name');

        $pageTitle = 'Calendar View';
        include __DIR__ . '/../views/rosters/calendar.php';
    }

    // AJAX Endpoint for calendar data
    public function getCalendarData() {
        try {
            $roleName = Session::get('role_name');
            $restrictedCampId = LocationMiddleware::getCampConstraint();
            
            $campId = $_GET['camp_id'] ?? '';
            if ($campId === 'All' || $campId === '') {
                if ($roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                    $campId = $restrictedCampId ?? Session::get('camp_id') ?? 1;
                    LocationMiddleware::validateCamp($campId);
                } else {
                    $campId = 'All';
                }
            } else {
                $campId = (int)$campId;
                LocationMiddleware::validateCamp($campId);
            }

            $startDate = isset($_GET['start']) ? substr(Security::sanitize($_GET['start']), 0, 10) : date('Y-m-01');
            $endDate = isset($_GET['end']) ? substr(Security::sanitize($_GET['end']), 0, 10) : date('Y-m-t');

            // For Airmen, only return published rosters. For others, return draft/submitted/approved/published.
            $statusList = [];
            $extraFilters = [];

            if ($roleName === 'Airman') {
                $statusList = ['Published'];
                $extraFilters['service_number'] = Session::get('service_number');
            } else {
                if (!empty($_GET['service_number'])) {
                    $extraFilters['service_number'] = Security::sanitize($_GET['service_number']);
                }
            }

            if (!empty($_GET['duty_type_id'])) {
                $extraFilters['duty_type_id'] = (int)$_GET['duty_type_id'];
            }
            if (!empty($_GET['shift_id'])) {
                $extraFilters['shift_id'] = (int)$_GET['shift_id'];
            }
            if (!empty($_GET['priority_level'])) {
                $extraFilters['priority_level'] = Security::sanitize($_GET['priority_level']);
            }
            if (!empty($_GET['status'])) {
                $extraFilters['status'] = Security::sanitize($_GET['status']);
            }
            if (!empty($_GET['search'])) {
                $extraFilters['search'] = Security::sanitize($_GET['search']);
            }

            $data = DutyAssignment::getCalendarData($campId, $startDate, $endDate, $statusList, $extraFilters);
            return Response::json($data);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    // Timeline View Page
    public function timelineView() {
        $camps = Camp::getAll(true);
        $activeCampId = LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1;

        $pageTitle = 'Duty Timeline';
        include __DIR__ . '/../views/rosters/timeline.php';
    }

    // AJAX Endpoint for Conflict Checks
    public function checkConflicts() {
        try {
            // Read JSON input
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (!$input) {
                // Fallback to POST/GET params
                $input = $_POST;
            }

            $campId = (int)($input['camp_id'] ?? Session::get('camp_id') ?? 1);
            LocationMiddleware::validateCamp($campId);

            $startDate = $input['start_date'] ?? '';
            $endDate = $input['end_date'] ?? '';
            $assignments = $input['assignments'] ?? [];
            $excludeRosterId = isset($input['exclude_roster_id']) ? (int)$input['exclude_roster_id'] : null;

            if (empty($startDate) || empty($endDate)) {
                throw new Exception("Start Date and End Date are required.");
            }

            $conflicts = DutyAssignment::checkConflicts($campId, $startDate, $endDate, $assignments, $excludeRosterId);
            return Response::json([
                'success' => true,
                'conflicts' => $conflicts
            ]);
        } catch (Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Save Roster (Draft)
    public function saveRoster() {
        try {
            Security::verifyCsrf();

            // Handle both JSON and normal POST
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (!$input) {
                $input = $_POST;
            }

            $rosterId = isset($input['roster_id']) && $input['roster_id'] !== '' ? (int)$input['roster_id'] : null;
            $rosterName = Security::sanitize($input['roster_name'] ?? '');
            $campId = (int)($input['camp_id'] ?? Session::get('camp_id') ?? 1);
            $startDate = Security::sanitize($input['start_date'] ?? '');
            $endDate = Security::sanitize($input['end_date'] ?? '');
            $assignments = $input['assignments'] ?? [];

            LocationMiddleware::validateCamp($campId);

            if (empty($rosterName) || empty($startDate) || empty($endDate)) {
                throw new Exception("Missing roster metadata fields.");
            }

            $db = Database::getInstance()->getConnection();

            if ($rosterId) {
                // Update roster name and dates, revert status to Draft if it was Rejected
                $roster = Roster::getById($rosterId);
                if (!$roster) {
                    throw new Exception("Roster draft not found.");
                }

                if (in_array($roster['status'], ['Submitted', 'Approved', 'Published'])) {
                    throw new Exception("Unauthorized: Roster is locked for modification in current status '{$roster['status']}'.");
                }
                
                $status = ($roster['status'] === 'Rejected') ? 'Draft' : $roster['status'];

                $stmt = $db->prepare("
                    UPDATE duty_rosters 
                    SET roster_name = :name, start_date = :start_date, end_date = :end_date, status = :status, last_updated_by = :last_updated_by
                    WHERE roster_id = :roster_id
                ");
                $stmt->execute([
                    ':name' => $rosterName,
                    ':start_date' => $startDate,
                    ':end_date' => $endDate,
                    ':status' => $status,
                    ':last_updated_by' => Session::get('user_id'),
                    ':roster_id' => $rosterId
                ]);
            } else {
                $rosterId = Roster::create($rosterName, $campId, $startDate, $endDate, Session::get('user_id'));
            }

            // Save the assignments
            DutyAssignment::saveMultiple($rosterId, $assignments);

            // Audit
            Logger::audit('Roster Management', 'Saved Roster assignments for Roster ID: ' . $rosterId);

            // Check if request is AJAX
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                return Response::json([
                    'success' => true,
                    'message' => 'Roster draft saved successfully.',
                    'roster_id' => $rosterId
                ]);
            } else {
                Session::set('success_message', "Roster draft saved successfully.");
                Response::redirect('/rosters/view?id=' . $rosterId);
            }
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
            } else {
                Session::set('error_message', $e->getMessage());
                Response::redirect('/rosters');
            }
        }
    }

    // Render Duty Approval Page
    public function approveView() {
        try {
            $roleName = Session::get('role_name');
            if ($roleName !== 'OCPROVST' && $roleName !== 'Administrator') {
                throw new Exception("Unauthorized Access: Only OCPROVST can approve rosters.");
            }

            $campId = Session::get('camp_id');
            $restrictedCampId = LocationMiddleware::getCampConstraint();
            $activeCampId = $restrictedCampId ?? $campId;

            $db = Database::getInstance()->getConnection();

            // Fetch rosters in 'Submitted' status for this camp
            $stmt = $db->prepare("SELECT dr.*, c.camp_name, p.full_name AS creator_name, rk.rank_short_name AS creator_rank
                                  FROM duty_rosters dr
                                  JOIN camps c ON dr.camp_id = c.camp_id
                                  JOIN users u ON dr.created_by = u.user_id
                                  LEFT JOIN personnel p ON u.service_number = p.service_number
                                  LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                                  WHERE dr.status = 'Submitted' AND dr.camp_id = ?
                                  ORDER BY dr.updated_at DESC");
            $stmt->execute([$activeCampId]);
            $rosters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group all assignments into Duty Crews
            $dutyCrews = [];

            foreach ($rosters as $r) {
                // Fetch all assignments for this roster
                $stmt = $db->prepare("
                    SELECT da.*, dt.duty_type_name, dt.color_code, dt.icon_class, s.shift_name,
                           TIME(da.duty_start_datetime) AS start_time, TIME(da.duty_end_datetime) AS end_time,
                           p.full_name AS personnel_name, rk.rank_short_name AS personnel_rank, p.trade AS personnel_trade, p.status AS personnel_status, p.section AS personnel_section
                    FROM duty_assignments da
                    JOIN duty_types dt ON da.duty_type_id = dt.duty_type_id
                    JOIN shifts s ON da.shift_id = s.shift_id
                    JOIN personnel p ON da.service_number = p.service_number
                    LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                    WHERE da.roster_id = ?
                    ORDER BY da.duty_date ASC, da.duty_start_datetime ASC
                ");
                $stmt->execute([$r['roster_id']]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group assignments of this roster by duty_date, shift_id, duty_type_id, and timings
                foreach ($assignments as $as) {
                    $key = "{$r['roster_id']}-{$as['duty_date']}-{$as['shift_id']}-{$as['duty_type_id']}-" . md5($as['duty_start_datetime'] . $as['duty_end_datetime']);
                    if (!isset($dutyCrews[$key])) {
                        $dutyCrews[$key] = [
                            'crew_key' => $key,
                            'roster_id' => $r['roster_id'],
                            'roster_name' => $r['roster_name'],
                            'camp_name' => $r['camp_name'],
                            'duty_date' => $as['duty_date'],
                            'shift_id' => $as['shift_id'],
                            'shift_name' => $as['shift_name'],
                            'duty_start_datetime' => $as['duty_start_datetime'],
                            'duty_end_datetime' => $as['duty_end_datetime'],
                            'start_time' => $as['start_time'],
                            'end_time' => $as['end_time'],
                            'duty_type_id' => $as['duty_type_id'],
                            'duty_type_name' => $as['duty_type_name'],
                            'color_code' => $as['color_code'],
                            'icon_class' => $as['icon_class'],
                            'creator_name' => $r['creator_name'],
                            'creator_rank' => $r['creator_rank'],
                            'submission_time' => $r['updated_at'],
                            'remarks' => $as['remarks'] ?? $as['supervisor_remarks'] ?? '',
                            'status' => $as['status'],
                            'personnel' => [],
                            'duplicate_warnings_count' => 0
                        ];
                    }

                    // Check for duplicate bookings (double assignment on same date)
                    $stmtCount = $db->prepare("SELECT COUNT(*) FROM duty_assignments WHERE service_number = ? AND duty_date = ?");
                    $stmtCount->execute([$as['service_number'], $as['duty_date']]);
                    $doubleAssignedCount = (int)$stmtCount->fetchColumn();
                    $isDoubleBooked = $doubleAssignedCount > 1;

                    // Check for leave or inactive status
                    $isConflict = $isDoubleBooked || $as['personnel_status'] === 'Leave' || $as['personnel_status'] === 'Inactive';
                    
                    if ($isConflict) {
                        $dutyCrews[$key]['duplicate_warnings_count']++;
                    }

                    // Get previous duty date
                    $stmtPrev = $db->prepare("SELECT MAX(duty_date) FROM duty_assignments 
                                              WHERE service_number = ? AND duty_date < ? AND status = 'Approved'");
                    $stmtPrev->execute([$as['service_number'], $as['duty_date']]);
                    $prevDutyDate = $stmtPrev->fetchColumn() ?: 'None';

                    $dutyCrews[$key]['personnel'][] = [
                        'assignment_id' => $as['assignment_id'],
                        'service_number' => $as['service_number'],
                        'rank' => $as['personnel_rank'],
                        'full_name' => $as['personnel_name'],
                        'trade' => $as['personnel_trade'],
                        'section' => $as['personnel_section'],
                        'status' => $as['status'],
                        'personnel_status' => $as['personnel_status'],
                        'is_double_booked' => $isDoubleBooked,
                        'is_conflict' => $isConflict,
                        'prev_duty_date' => $prevDutyDate
                    ];
                }
            }

            $pageTitle = 'Duty Approvals - Bulk Pipeline';
            include __DIR__ . '/../views/rosters/approve.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/dashboard');
        }
    }

    // Submit Approval Action (Workflow Pipeline)
    public function submitApproval() {
        try {
            Security::verifyCsrf();

            $rosterId = (int)($_POST['roster_id'] ?? 0);
            $action = Security::sanitize($_POST['action'] ?? '');
            $remarks = Security::sanitize($_POST['remarks'] ?? '');

            if (!$rosterId || empty($action)) {
                throw new Exception("Missing roster approval action parameters.");
            }

            $roster = Roster::getById($rosterId);
            if (!$roster) {
                throw new Exception("Roster not found.");
            }

            $roleName = Session::get('role_name');
            if ($action === 'Submit') {
                if ($roleName !== 'Warrant Officer IC' && $roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                    throw new Exception("Unauthorized: Only Warrant Officer I/C or Administrator can submit rosters.");
                }
            } else {
                if ($action === 'Return' && $roleName === 'Warrant Officer IC') {
                    // Allowed for WO IC to return/request corrections
                } elseif ($roleName !== 'OCPROVST' && $roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                    throw new Exception("Unauthorized: Only OCPROVST can approve, reject, or return rosters.");
                }
                if ($roster['status'] !== 'Submitted') {
                    throw new Exception("Roster is not in pending approval status.");
                }
            }

            $status = 'Draft';
            $db = Database::getInstance()->getConnection();

            if ($action === 'Submit') {
                $status = 'Submitted';
                $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Submitted', submitted_by = ?, submitted_date = NOW() WHERE roster_id = ?");
                $stmtLifecycle->execute([Session::get('user_id'), $rosterId]);
            } elseif ($action === 'Approve') {
                $status = 'Published';
                $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Published', approved_by = ?, approved_date = NOW() WHERE roster_id = ?");
                $stmtLifecycle->execute([Session::get('user_id'), $rosterId]);
            } elseif ($action === 'Reject') {
                $status = 'Rejected';
                $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Rejected', rejected_by = ?, rejected_date = NOW(), rejection_reason = ? WHERE roster_id = ?");
                $stmtLifecycle->execute([Session::get('user_id'), $remarks, $rosterId]);
            } elseif ($action === 'Return') {
                $status = 'Draft';
                $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Draft', rejected_by = ?, rejected_date = NOW(), rejection_reason = ? WHERE roster_id = ?");
                $stmtLifecycle->execute([Session::get('user_id'), $remarks, $rosterId]);
            } else {
                throw new Exception("Invalid workflow action: $action");
            }

            // Verify camp mapping
            LocationMiddleware::validateCamp($roster['camp_id']);

            Approval::add($rosterId, Session::get('user_id'), $action, $remarks);

            // Notify users about action
            $db = Database::getInstance()->getConnection();

            if ($action === 'Submit') {
                // Notify all OCPROVSTs
                $stmt = $db->query("
                    SELECT p.service_number 
                    FROM users u
                    JOIN roles r ON u.role_id = r.role_id
                    JOIN personnel p ON u.service_number = p.service_number
                    WHERE r.role_name = 'OCPROVST' AND u.status = 'Active'
                ");
                $provosts = $stmt->fetchAll();
                foreach ($provosts as $pr) {
                    Notification::add(
                        $pr['service_number'], 
                        "New Roster Submitted", 
                        "Roster '{$roster['roster_name']}' has been submitted by SNCO for review."
                    );
                }
            } elseif ($action === 'Approve') {
                // Notify SNCO who created it
                $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
                $stmt->execute([$roster['created_by']]);
                $sncoServiceNum = $stmt->fetchColumn();
                if ($sncoServiceNum) {
                    Notification::add(
                        $sncoServiceNum, 
                        "Roster Approved & Published", 
                        "Your roster draft '{$roster['roster_name']}' has been approved and published by OCPROVST."
                    );
                }

                // Notify all Airmen scheduled in this roster
                $stmt = $db->prepare("
                    SELECT DISTINCT service_number 
                    FROM duty_assignments 
                    WHERE roster_id = ?
                ");
                $stmt->execute([$rosterId]);
                $scheduledAirmen = $stmt->fetchAll();
                foreach ($scheduledAirmen as $sa) {
                    Notification::add(
                        $sa['service_number'], 
                        "New Duties Assigned", 
                        "You have been assigned to duties in roster: '{$roster['roster_name']}'. Please check your schedule."
                    );
                }
            } elseif ($action === 'Reject' || $action === 'Return') {
                // Notify SNCO who created it
                $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
                $stmt->execute([$roster['created_by']]);
                $sncoServiceNum = $stmt->fetchColumn();
                if ($sncoServiceNum) {
                    Notification::add(
                        $sncoServiceNum, 
                        "Roster Action: $action", 
                        "Your roster draft '{$roster['roster_name']}' has been $action" . "ed by OCPROVST. Remarks: $remarks"
                    );
                }
            }

            Session::set('success_message', "Workflow action '$action' submitted successfully.");
            Response::redirect('/rosters/view?id=' . $rosterId);
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/rosters');
        }
    }

    // Approve/Reject individual duty assignment
    public function submitAssignmentApproval() {
        // Determine redirect target for error handling
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $errorRedirect = (strpos($referer, 'rosters/approve') !== false)
            ? '/rosters/approve'
            : '/rosters/view?id=' . (int)($_POST['roster_id'] ?? 0);

        try {
            Security::verifyCsrf();

            $roleName = Session::get('role_name');
            if ($roleName !== 'OCPROVST' && $roleName !== 'Administrator') {
                throw new Exception("Unauthorized Access: Only OCPROVST can approve/reject duty assignments.");
            }

            $assignmentId      = (int)($_POST['assignment_id'] ?? 0);
            $rosterId          = (int)($_POST['roster_id'] ?? 0);
            $status            = Security::sanitize($_POST['status'] ?? '');
            $supervisorRemarks = trim(Security::sanitize($_POST['supervisor_remarks'] ?? ''));

            if (!$assignmentId || !$rosterId) {
                throw new Exception("Invalid assignment or roster ID.");
            }

            if (!in_array($status, ['Approved', 'Rejected'])) {
                throw new Exception("Invalid approval status: '{$status}'. Must be Approved or Rejected.");
            }

            if ($status === 'Rejected' && empty($supervisorRemarks)) {
                throw new Exception("A reason is required when rejecting an assignment.");
            }

            $roster = Roster::getById($rosterId);
            if (!$roster) {
                throw new Exception("Roster not found.");
            }
            if ($roster['status'] !== 'Submitted') {
                throw new Exception("Roster is not in Submitted status. Current status: '{$roster['status']}'.");
            }

            // Store null instead of empty string for approved remarks
            $remarksToSave = !empty($supervisorRemarks) ? $supervisorRemarks : null;

            // Update assignment status in the database
            DutyAssignment::updateStatus($assignmentId, $status, $remarksToSave);

            // Audit log
            Logger::audit('Roster Management', "Assignment ID {$assignmentId} set to '{$status}' in Roster ID {$rosterId}. Remarks: {$supervisorRemarks}");

            // Notify SNCO if assignment is rejected
            if ($status === 'Rejected') {
                $db   = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
                $stmt->execute([$roster['created_by']]);
                $sncoServiceNum = $stmt->fetchColumn();
                if ($sncoServiceNum) {
                    Notification::add(
                        $sncoServiceNum,
                        "Assignment Rejected in Roster",
                        "An assignment was rejected by OCPROVST in roster '{$roster['roster_name']}'. Reason: {$supervisorRemarks}"
                    );
                }
            }

            Session::set('success_message', "Assignment has been {$status} successfully.");

            // Redirect back to the page that submitted the form
            if (strpos($referer, 'rosters/approve') !== false) {
                Response::redirect('/rosters/approve');
            } else {
                Response::redirect('/rosters/view?id=' . $rosterId);
            }
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect($errorRedirect);
        }
    }

    // GET /rosters/crew-history — Fetch historical approval audit trail for a crew
    public function crewHistory() {
        try {
            $rosterId = (int)($_GET['roster_id'] ?? 0);
            $dutyDate = Security::sanitize($_GET['duty_date'] ?? '');
            $shiftId = (int)($_GET['shift_id'] ?? 0);
            $dutyTypeId = (int)($_GET['duty_type_id'] ?? 0);

            // If accessed directly without parameters, render the HTML view page
            if (!$rosterId && empty($dutyDate) && !$shiftId && !$dutyTypeId) {
                $db = Database::getInstance()->getConnection();
                $activeCampId = LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1;
                
                $stmt = $db->prepare("
                    SELECT dca.*, p.full_name, rk.rank_short_name, u.service_number AS username,
                           dr.roster_name, dt.duty_type_name, s.shift_name, dt.color_code, dt.icon_class,
                           (SELECT MIN(da.duty_start_datetime) FROM duty_assignments da 
                            WHERE da.roster_id = dca.roster_id AND da.duty_date = dca.duty_date 
                              AND da.shift_id = dca.shift_id AND da.duty_type_id = dca.duty_type_id) AS duty_start_datetime,
                           (SELECT MAX(da.duty_end_datetime) FROM duty_assignments da 
                            WHERE da.roster_id = dca.roster_id AND da.duty_date = dca.duty_date 
                              AND da.shift_id = dca.shift_id AND da.duty_type_id = dca.duty_type_id) AS duty_end_datetime
                    FROM duty_crew_approvals dca
                    JOIN users u ON dca.action_by = u.user_id
                    LEFT JOIN personnel p ON u.service_number = p.service_number
                    LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                    JOIN duty_rosters dr ON dca.roster_id = dr.roster_id
                    JOIN duty_types dt ON dca.duty_type_id = dt.duty_type_id
                    JOIN shifts s ON dca.shift_id = s.shift_id
                    WHERE dr.camp_id = ?
                    ORDER BY dca.created_at DESC
                ");
                $stmt->execute([$activeCampId]);
                $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $pageTitle = 'Approval Action History';
                include __DIR__ . '/../views/rosters/crew_history.php';
                exit;
            }

            header('Content-Type: application/json');
            if (!$rosterId || empty($dutyDate) || !$shiftId || !$dutyTypeId) {
                throw new Exception("Missing parameters.");
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT dca.*, p.full_name, rk.rank_short_name, u.service_number AS username
                                  FROM duty_crew_approvals dca
                                  JOIN users u ON dca.action_by = u.user_id
                                  LEFT JOIN personnel p ON u.service_number = p.service_number
                                  LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
                                  WHERE dca.roster_id = ? AND dca.duty_date = ? AND dca.shift_id = ? AND dca.duty_type_id = ?
                                  ORDER BY dca.created_at DESC");
            $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    // POST /rosters/bulk-approve — Approve selected crews in a single transaction
    public function bulkApprove() {
        header('Content-Type: application/json');
        try {
            Security::verifyCsrf();

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $crewKeys = $input['crews'] ?? [];
            $remarks = Security::sanitize($input['remarks'] ?? '');

            if (empty($crewKeys)) {
                throw new Exception("No duty crews selected for approval.");
            }

            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            $userId = Session::get('user_id');
            $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $notifiedCrews = [];

            foreach ($crewKeys as $key) {
                $parts = explode('-', $key);
                if (count($parts) < 6) {
                    throw new Exception("Invalid duty crew key format: $key");
                }
                $rosterId = (int)$parts[0];
                $dutyDate = $parts[1] . '-' . $parts[2] . '-' . $parts[3];
                $shiftId = (int)$parts[4];
                $dutyTypeId = (int)$parts[5];

                $roster = Roster::getById($rosterId);
                if (!$roster) {
                    throw new Exception("Roster not found.");
                }

                LocationMiddleware::validateCamp($roster['camp_id']);

                // Update assignments status
                $stmt = $db->prepare("UPDATE duty_assignments 
                                      SET status = 'Approved', updated_at = NOW() 
                                      WHERE roster_id = ? AND duty_date = ? AND shift_id = ? AND duty_type_id = ?");
                $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId]);

                // Log crew approval
                $stmt = $db->prepare("INSERT INTO duty_crew_approvals 
                                      (roster_id, duty_date, shift_id, duty_type_id, action_by, action, remarks, previous_status, new_status, ip_address, user_agent)
                                      VALUES (?, ?, ?, ?, ?, 'Approve', ?, 'Pending', 'Approved', ?, ?)");
                $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId, $userId, $remarks, $userIp, $userAgent]);

                // If all assignments are approved, publish roster
                $stmt = $db->prepare("SELECT COUNT(*) FROM duty_assignments WHERE roster_id = ? AND status != 'Approved'");
                $stmt->execute([$rosterId]);
                $pendingCount = (int)$stmt->fetchColumn();

                if ($pendingCount === 0) {
                    $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Published', approved_by = ?, approved_date = NOW() WHERE roster_id = ?");
                    $stmtLifecycle->execute([$userId, $rosterId]);
                    Approval::add($rosterId, $userId, 'Approve', "Bulk approved last crew: " . $remarks);
                }

                // Notify SNCO
                $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
                $stmt->execute([$roster['created_by']]);
                $sncoSvc = $stmt->fetchColumn();
                if ($sncoSvc && !in_array($sncoSvc, $notifiedCrews)) {
                    Notification::add(
                        $sncoSvc,
                        "Duty Crew Approved",
                        "Duty Crew for $dutyDate has been approved and published by OCPROVST."
                    );
                    $notifiedCrews[] = $sncoSvc;
                }

                // Notify Airmen
                $stmt = $db->prepare("SELECT DISTINCT service_number FROM duty_assignments WHERE roster_id = ? AND duty_date = ? AND shift_id = ? AND duty_type_id = ?");
                $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId]);
                $airmen = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($airmen as $sa) {
                    Notification::add(
                        $sa,
                        "Watch Assignment Approved",
                        "Your duty assignment on $dutyDate has been approved."
                    );
                }

                Logger::audit('OCPROVST Bulk Action', "Approved Duty Crew: $key. Remarks: $remarks", $rosterId);
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Selected duty crews approved successfully.']);
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // POST /rosters/bulk-reject — Reject selected crews and return to draft/rejected status
    public function bulkReject() {
        header('Content-Type: application/json');
        try {
            Security::verifyCsrf();

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $crewKeys = $input['crews'] ?? [];
            $remarks = Security::sanitize($input['remarks'] ?? '');

            if (empty($crewKeys)) {
                throw new Exception("No duty crews selected for rejection.");
            }
            if (empty($remarks)) {
                throw new Exception("Rejection remarks are mandatory.");
            }

            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            $userId = Session::get('user_id');
            $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $notifiedCrews = [];

            foreach ($crewKeys as $key) {
                $parts = explode('-', $key);
                if (count($parts) < 6) {
                    throw new Exception("Invalid duty crew key format: $key");
                }
                $rosterId = (int)$parts[0];
                $dutyDate = $parts[1] . '-' . $parts[2] . '-' . $parts[3];
                $shiftId = (int)$parts[4];
                $dutyTypeId = (int)$parts[5];

                $roster = Roster::getById($rosterId);
                if (!$roster) {
                    throw new Exception("Roster not found.");
                }

                LocationMiddleware::validateCamp($roster['camp_id']);

                // Update assignments status
                $stmt = $db->prepare("UPDATE duty_assignments 
                                      SET status = 'Rejected', updated_at = NOW() 
                                      WHERE roster_id = ? AND duty_date = ? AND shift_id = ? AND duty_type_id = ?");
                $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId]);

                // Log crew rejection
                $stmt = $db->prepare("INSERT INTO duty_crew_approvals 
                                      (roster_id, duty_date, shift_id, duty_type_id, action_by, action, remarks, previous_status, new_status, ip_address, user_agent)
                                      VALUES (?, ?, ?, ?, ?, 'Reject', ?, 'Pending', 'Rejected', ?, ?)");
                $stmt->execute([$rosterId, $dutyDate, $shiftId, $dutyTypeId, $userId, $remarks, $userIp, $userAgent]);

                // Change roster to Rejected
                $stmtLifecycle = $db->prepare("UPDATE duty_rosters SET status = 'Rejected', rejected_by = ?, rejected_date = NOW(), rejection_reason = ? WHERE roster_id = ?");
                $stmtLifecycle->execute([$userId, $remarks, $rosterId]);
                Approval::add($rosterId, $userId, 'Reject', "Rejected crew: " . $remarks);

                // Notify SNCO
                $stmt = $db->prepare("SELECT service_number FROM users WHERE user_id = ?");
                $stmt->execute([$roster['created_by']]);
                $sncoSvc = $stmt->fetchColumn();
                if ($sncoSvc && !in_array($sncoSvc, $notifiedCrews)) {
                    Notification::add(
                        $sncoSvc,
                        "Duty Crew Rejected",
                        "Duty Crew for $dutyDate in roster '{$roster['roster_name']}' was rejected. Reason: $remarks"
                    );
                    $notifiedCrews[] = $sncoSvc;
                }

                Logger::audit('OCPROVST Bulk Action', "Rejected Duty Crew: $key. Reason: $remarks", $rosterId);
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Selected duty crews rejected successfully.']);
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // POST /rosters/audit-print — Logs roster print actions to the immutable audit trail
    public function auditPrint() {
        header('Content-Type: application/json');
        try {
            Security::verifyCsrf();
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $rosterId = (int)($input['roster_id'] ?? 0);
            
            if (!$rosterId) {
                throw new Exception("Missing roster ID for audit log.");
            }

            $roster = Roster::getById($rosterId);
            if (!$roster) {
                throw new Exception("Roster not found.");
            }

            Logger::audit('Roster Management', "Printed Roster ID: $rosterId (Name: {$roster['roster_name']})");
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
