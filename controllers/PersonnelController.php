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

            $serviceNumber = strtoupper(Security::sanitize($_POST['service_number'] ?? ''));
            $isAdmin = (strtolower($serviceNumber) === 'admin');
            $rankId = !empty($_POST['rank_id']) ? (int)$_POST['rank_id'] : null;
            $initials = Security::sanitize($_POST['initials'] ?? '');
            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $trade = Security::sanitize($_POST['trade'] ?? '');
            $squadron = Security::sanitize($_POST['squadron'] ?? '');
            $campId = !empty($_POST['camp_id']) ? (int)$_POST['camp_id'] : null;
            $contactNumber = Security::sanitize($_POST['contact_number'] ?? null);
            $email = Security::sanitize($_POST['email'] ?? '');
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($serviceNumber) || (!$isAdmin && !$rankId) || empty($fullName) || (!$isAdmin && !$campId) || empty($email)) {
                throw new Exception("Missing required fields.");
            }

            if (!Security::validateServiceNumber($serviceNumber)) {
                throw new Exception("Service Number must follow the format SLAF/BRANCH/NUMBER (e.g., SLAF/AIR/301).");
            }

            Personnel::save($serviceNumber, $rankId, $initials, $fullName, $trade, $squadron, $campId, $contactNumber, $email, $status, false);

            Session::set('success_message', "Personnel profile $serviceNumber created successfully.");
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
            $isAdmin = (strtolower($serviceNumber) === 'admin');
            $rankId = !empty($_POST['rank_id']) ? (int)$_POST['rank_id'] : null;
            $initials = Security::sanitize($_POST['initials'] ?? '');
            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $trade = Security::sanitize($_POST['trade'] ?? '');
            $squadron = Security::sanitize($_POST['squadron'] ?? '');
            $campId = !empty($_POST['camp_id']) ? (int)$_POST['camp_id'] : null;
            $contactNumber = Security::sanitize($_POST['contact_number'] ?? null);
            $email = Security::sanitize($_POST['email'] ?? '');
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($serviceNumber) || (!$isAdmin && !$rankId) || empty($fullName) || (!$isAdmin && !$campId) || empty($email)) {
                throw new Exception("Missing required fields.");
            }

            Personnel::save($serviceNumber, $rankId, $initials, $fullName, $trade, $squadron, $campId, $contactNumber, $email, $status, true);

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
}
