<?php
/**
 * Personnel & Postings Controller
 */

class PersonnelController {

    // List personnel (camp isolated for SNCO)
    public function index() {
        $personnelList = Personnel::getAll();
        $camps = Camp::getAll(true);
        $ranks = Rank::getAll(true); // Fetch active ranks
        
        $pageTitle = 'Personnel Management';
        include __DIR__ . '/../views/personnel/index.php';
    }

    // View specific personnel profile and history
    public function view() {
        try {
            $serviceNumber = Security::sanitize($_GET['service_number'] ?? '');
            if (empty($serviceNumber)) {
                throw new Exception("Service number is required.");
            }

            $person = Personnel::getByServiceNumber($serviceNumber);
            if (!$person) {
                throw new Exception("Personnel profile not found.");
            }

            $roleName = Session::get('role_name');
            if ($roleName === 'Warrant Officer IC') {
                Logger::audit('Personnel Management', 'View Personnel Profile: ' . $serviceNumber);
            }

            $postings = Posting::getHistory($serviceNumber);
            
            // Get camps for editing form
            $camps = Camp::getAll(true);
            $ranks = Rank::getAll(true); // Fetch active ranks

            $pageTitle = "Profile: " . htmlspecialchars($person['rank'] . ' ' . $person['full_name']);
            include __DIR__ . '/../views/personnel/view.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel');
        }
    }

    // Search personnel (JSON endpoint for AJAX auto-completion)
    public function search() {
        try {
            $query = Security::sanitize($_GET['q'] ?? $_GET['query'] ?? '');
            if (strlen($query) < 2) {
                return Response::json([]);
            }
            $results = Personnel::search($query);
            return Response::json($results);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    // View posting history of specific personnel
    public function history() {
        try {
            $serviceNumber = Security::sanitize($_GET['service_number'] ?? '');
            if (empty($serviceNumber)) {
                throw new Exception("Service number is required.");
            }

            $person = Personnel::getByServiceNumber($serviceNumber);
            if (!$person) {
                throw new Exception("Personnel profile not found.");
            }

            $postings = Posting::getHistory($serviceNumber);

            $pageTitle = "Posting History - " . htmlspecialchars($person['rank'] . ' ' . $person['full_name']);
            include __DIR__ . '/../views/personnel/history.php';
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel');
        }
    }

    // List postings records
    public function postingsIndex() {
        Response::redirect('/transfers');
    }

    // Add new personnel profile
    public function addPersonnel() {
        try {
            Security::verifyCsrf();

            $serviceNumber = trim(strtoupper(Security::sanitize($_POST['service_number'] ?? '')));
            $isAdmin = (strtolower($serviceNumber) === 'admin' || strtolower($serviceNumber) === 'sadmin');
            $rankId = !empty($_POST['rank_id']) ? (int)$_POST['rank_id'] : null;
            $initials = Security::sanitize($_POST['initials'] ?? '');
            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $trade = Security::sanitize($_POST['trade'] ?? '');
            $f1250 = Security::sanitize($_POST['f1250'] ?? '');
            $section = Security::sanitize($_POST['section'] ?? '');
            $appointment = Security::sanitize($_POST['appointment'] ?? '');
            $campId = !empty($_POST['camp_id']) ? (int)$_POST['camp_id'] : null;
            $contactNumber = Security::sanitize($_POST['contact_number'] ?? null);
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($serviceNumber) || (!$isAdmin && !$rankId) || empty($fullName) || (!$isAdmin && !$campId) || (!$isAdmin && empty($f1250)) || (!$isAdmin && empty($section)) || (!$isAdmin && empty($appointment))) {
                throw new Exception("Missing required fields.");
            }

            if (!Security::validateServiceNumber($serviceNumber)) {
                throw new Exception("Service Number must follow the format SLAF/BRANCH/NUMBER (e.g., SLAF/AIR/301) or letters, numbers, slash.");
            }

            Personnel::save($serviceNumber, $rankId, $initials, $fullName, $trade, $f1250, $section, $appointment, $campId, $contactNumber, $status, false);

            Session::set('success_message', "Personnel profile $serviceNumber created successfully.");
            Session::set('create_user_account_for', $serviceNumber);
            Response::redirect('/personnel');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel');
        }
    }

    // Edit personnel profile
    public function editPersonnel() {
        try {
            Security::verifyCsrf();

            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $isAdmin = (strtolower($serviceNumber) === 'admin' || strtolower($serviceNumber) === 'sadmin');
            $rankId = !empty($_POST['rank_id']) ? (int)$_POST['rank_id'] : null;
            $initials = Security::sanitize($_POST['initials'] ?? '');
            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $trade = Security::sanitize($_POST['trade'] ?? '');
            $f1250 = Security::sanitize($_POST['f1250'] ?? '');
            $section = Security::sanitize($_POST['section'] ?? '');
            $appointment = Security::sanitize($_POST['appointment'] ?? '');
            $campId = !empty($_POST['camp_id']) ? (int)$_POST['camp_id'] : null;
            $contactNumber = Security::sanitize($_POST['contact_number'] ?? null);
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($serviceNumber) || (!$isAdmin && !$rankId) || empty($fullName) || (!$isAdmin && !$campId) || (!$isAdmin && empty($f1250)) || (!$isAdmin && empty($section)) || (!$isAdmin && empty($appointment))) {
                throw new Exception("Missing required fields.");
            }

            Personnel::save($serviceNumber, $rankId, $initials, $fullName, $trade, $f1250, $section, $appointment, $campId, $contactNumber, $status, true);

            Session::set('success_message', "Personnel profile $serviceNumber updated successfully.");
            Response::redirect('/personnel/view?service_number=' . urlencode($serviceNumber));
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel');
        }
    }

    // Create a camp transfer posting record
    public function addPosting() {
        try {
            Security::verifyCsrf();

            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $fromCampId = (int)($_POST['from_camp_id'] ?? 0);
            $toCampId = (int)($_POST['to_camp_id'] ?? 0);
            $effectiveDate = Security::sanitize($_POST['effective_date'] ?? date('Y-m-d'));

            if (empty($serviceNumber) || empty($fromCampId) || empty($toCampId)) {
                throw new Exception("Missing required transfer credentials.");
            }

            if ($fromCampId === $toCampId) {
                throw new Exception("Transfer destination cannot be the same as the origin camp.");
            }

            Posting::addPostingRecord($serviceNumber, $fromCampId, $toCampId, $effectiveDate);

            Session::set('success_message', "Transfer posting for $serviceNumber completed successfully.");
            Response::redirect('/postings');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/postings');
        }
    }

    // View archived personnel list
    public function archivedIndex() {
        $personnelList = Personnel::getAll(null, null, 1);
        $pageTitle = 'Archived Personnel';
        include __DIR__ . '/../views/personnel/archived.php';
    }

    // Archive personnel profile
    public function archive() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                throw new Exception("Unauthorized: Only Administrator and Super Administrator can archive personnel.");
            }

            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $reason = Security::sanitize($_POST['archive_reason'] ?? 'Administrative Decision');
            if (empty($serviceNumber) || empty($reason)) {
                throw new Exception("Service number and reason are required.");
            }

            $adminServiceNum = Session::get('service_number');
            Personnel::archive($serviceNumber, $reason, $adminServiceNum);

            Session::set('success_message', "Personnel profile $serviceNumber archived successfully.");
            Response::redirect('/personnel');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel');
        }
    }

    // Restore personnel profile
    public function restore() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                throw new Exception("Unauthorized: Only Administrator and Super Administrator can restore personnel.");
            }

            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $reason = Security::sanitize($_POST['restore_reason'] ?? 'Administrative Decision');
            if (empty($serviceNumber) || empty($reason)) {
                throw new Exception("Service number and reason are required.");
            }

            $adminServiceNum = Session::get('service_number');
            Personnel::restore($serviceNumber, $reason, $adminServiceNum);

            Session::set('success_message', "Personnel profile $serviceNumber restored successfully.");
            Response::redirect('/personnel/archived');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/personnel/archived');
        }
    }
}
