<?php
/**
 * Roster Controller
 */

class RosterController {

    // List rosters
    public function index() {
        $rosters = Roster::getAll();
        $camps = Camp::getAll(true);

        $pageTitle = 'Duty Rosters';
        include __DIR__ . '/../views/rosters/index.php';
    }

    // Create / Edit Roster View
    public function createView() {
        try {
            $roleName = Session::get('role_name');
            if ($roleName !== 'SNCO' && $roleName !== 'Administrator') {
                throw new Exception("Unauthorized Access: Only SNCO or Administrator can build/edit rosters.");
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
        $activeCampId = LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1;

        $pageTitle = 'Calendar View';
        include __DIR__ . '/../views/rosters/calendar.php';
    }

    // AJAX Endpoint for calendar data
    public function getCalendarData() {
        try {
            $campId = isset($_GET['camp_id']) ? (int)$_GET['camp_id'] : (LocationMiddleware::getCampConstraint() ?? Session::get('camp_id') ?? 1);
            LocationMiddleware::validateCamp($campId);

            $startDate = $_GET['start'] ?? date('Y-m-01');
            $endDate = $_GET['end'] ?? date('Y-m-t');

            // For Airmen, only return published rosters. For others, return draft/submitted/approved/published.
            $roleName = Session::get('role_name');
            $statusList = [];
            if ($roleName === 'Airman') {
                $statusList = ['Published'];
            }

            $data = DutyAssignment::getCalendarData($campId, $startDate, $endDate, $statusList);
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
                
                $status = ($roster['status'] === 'Rejected') ? 'Draft' : $roster['status'];

                $stmt = $db->prepare("
                    UPDATE duty_rosters 
                    SET roster_name = :name, start_date = :start_date, end_date = :end_date, status = :status
                    WHERE roster_id = :roster_id
                ");
                $stmt->execute([
                    ':name' => $rosterName,
                    ':start_date' => $startDate,
                    ':end_date' => $endDate,
                    ':status' => $status,
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

            // Get all rosters in 'Submitted' status
            $rosters = Roster::getAll(null, 'Submitted');
            
            // For each roster, get assignments
            $pendingRosters = [];
            foreach ($rosters as $r) {
                $assignments = DutyAssignment::getByRosterId($r['roster_id']);
                $r['assignments'] = $assignments;
                $pendingRosters[] = $r;
            }

            $pageTitle = 'Duty Approvals';
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
                if ($roleName !== 'SNCO' && $roleName !== 'Administrator') {
                    throw new Exception("Unauthorized: Only SNCO or Administrator can submit rosters.");
                }
            } else {
                if ($roleName !== 'OCPROVST') {
                    throw new Exception("Unauthorized: Only OCPROVST can approve, reject, or return rosters.");
                }
                if ($roster['status'] !== 'Submitted') {
                    throw new Exception("Roster is not in pending approval status.");
                }
            }

            $status = 'Draft';
            if ($action === 'Submit') {
                $status = 'Submitted';
            } elseif ($action === 'Approve') {
                $status = 'Published'; // Automatically publish when approved
            } elseif ($action === 'Reject') {
                $status = 'Rejected';
            } elseif ($action === 'Return') {
                $status = 'Draft';
            } else {
                throw new Exception("Invalid workflow action: $action");
            }

            // Verify camp mapping
            LocationMiddleware::validateCamp($roster['camp_id']);

            Roster::updateStatus($rosterId, $status);
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
}
