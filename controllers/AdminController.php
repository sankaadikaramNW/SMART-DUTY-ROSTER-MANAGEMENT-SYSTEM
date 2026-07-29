<?php
/**
 * Admin Settings Controller
 */

class AdminController {

    // List Camps
    public function campsIndex() {
        $camps = Camp::getAll();
        
        $pageTitle = 'Manage Camps & Bases';
        include __DIR__ . '/../views/admin/camps.php';
    }

    // List Shifts
    public function shiftsIndex() {
        $shifts = Shift::getAll();

        $pageTitle = 'Manage Duty Shifts';
        include __DIR__ . '/../views/admin/shifts.php';
    }

    // List Duty Types
    public function dutyTypesIndex() {
        $dutyTypes = DutyType::getAll();

        $pageTitle = 'Manage Duty Types';
        include __DIR__ . '/../views/admin/duty_types.php';
    }

    // List Ranks
    public function ranksIndex() {
        $ranks = Rank::getAll();

        $pageTitle = 'Manage Ranks';
        include __DIR__ . '/../views/admin/ranks.php';
    }

    // List User Accounts
    public function usersIndex() {
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        
        $users = User::getAll($restrictedCampId);
        $roles = Role::getAll();
        
        // Also fetch active personnel so admin can choose which personnel to link to a user account
        $personnel = Personnel::getAll();

        $pageTitle = 'Manage User Accounts';
        include __DIR__ . '/../views/admin/users.php';
    }

    // List Audit Logs with pagination
    public function auditLogsIndex() {
        $module = Security::sanitize($_GET['module'] ?? '');
        $user = Security::sanitize($_GET['user'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $logs = AuditLog::getAll($module, $user, $limit, $offset);
        $totalLogs = AuditLog::getCount($module, $user);
        $totalPages = ceil($totalLogs / $limit);

        // Fetch distinct modules for filter dropdown
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT DISTINCT module FROM audit_logs ORDER BY module ASC");
        $modules = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $pageTitle = 'System Audit Trail';
        include __DIR__ . '/../views/admin/audit_logs.php';
    }

    // Create or Update Camp
    public function saveCamp() {
        try {
            Security::verifyCsrf();

            $campId = isset($_POST['camp_id']) && $_POST['camp_id'] !== '' ? (int)$_POST['camp_id'] : null;
            $campCode = Security::sanitize($_POST['camp_code'] ?? '');
            $campName = Security::sanitize($_POST['camp_name'] ?? '');
            $address = Security::sanitize($_POST['address'] ?? null);
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($campCode) || empty($campName)) {
                throw new Exception("Camp code and name are required.");
            }

            Camp::save($campId, $campCode, $campName, $address, $status);

            Session::set('success_message', "Camp '$campName' saved successfully.");
            Response::redirect('/camps');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/camps');
        }
    }

    // Create or Update Shift
    public function saveShift() {
        try {
            Security::verifyCsrf();

            $shiftId = isset($_POST['shift_id']) && $_POST['shift_id'] !== '' ? (int)$_POST['shift_id'] : null;
            $shiftName = Security::sanitize($_POST['shift_name'] ?? '');
            $startTime = Security::sanitize($_POST['start_time'] ?? '');
            $endTime = Security::sanitize($_POST['end_time'] ?? '');
            $duration = (float)($_POST['duration_hours'] ?? 0.0);
            $description = Security::sanitize($_POST['description'] ?? null);
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($shiftName) || empty($startTime) || empty($endTime)) {
                throw new Exception("Shift name and timings are required.");
            }

            Shift::save($shiftId, $shiftName, $startTime, $endTime, $duration, $description, $status);

            Session::set('success_message', "Shift '$shiftName' saved successfully.");
            Response::redirect('/shifts');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/shifts');
        }
    }

    // Create or Update Duty Type
    public function saveDutyType() {
        try {
            Security::verifyCsrf();

            $dutyTypeId = isset($_POST['duty_type_id']) && $_POST['duty_type_id'] !== '' ? (int)$_POST['duty_type_id'] : null;
            $dutyTypeName = Security::sanitize($_POST['duty_type_name'] ?? '');
            $dutyCode = Security::sanitize($_POST['duty_code'] ?? '');
            $description = Security::sanitize($_POST['description'] ?? null);
            $colorCode = Security::sanitize($_POST['color_code'] ?? '#0d6efd');
            $iconClass = Security::sanitize($_POST['icon_class'] ?? 'bi-shield');
            $displayOrder = isset($_POST['display_order']) && $_POST['display_order'] !== '' ? (int)$_POST['display_order'] : 0;
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($dutyTypeName)) {
                throw new Exception("Duty type name is required.");
            }
            if (empty($dutyCode)) {
                throw new Exception("Duty code is required.");
            }

            $userId = Session::get('user_id');

            DutyType::save($dutyTypeId, $dutyTypeName, $dutyCode, $description, $colorCode, $iconClass, $displayOrder, $status, $userId);

            Session::set('success_message', "Duty type '$dutyTypeName' saved successfully.");
            Response::redirect('/duty-types');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/duty-types');
        }
    }

    // Create or Update Rank
    public function saveRank() {
        try {
            Security::verifyCsrf();

            $rankId = isset($_POST['rank_id']) && $_POST['rank_id'] !== '' ? (int)$_POST['rank_id'] : null;
            $rankCode = strtoupper(Security::sanitize($_POST['rank_code'] ?? ''));
            $rankName = Security::sanitize($_POST['rank_name'] ?? '');
            $rankShortName = Security::sanitize($_POST['rank_short_name'] ?? '');
            $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($rankCode) || empty($rankName) || empty($rankShortName)) {
                throw new Exception("Rank code, name, and short name are required.");
            }

            Rank::save($rankId, $rankCode, $rankName, $rankShortName, $displayOrder, $status);

            Session::set('success_message', "Rank '$rankName' saved successfully.");
            Response::redirect('/ranks');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/ranks');
        }
    }

    // Create or Update User Account credentials & role
    public function saveUser() {
        try {
            Security::verifyCsrf();

            $userId = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null;
            $serviceNumber = Security::sanitize($_POST['service_number'] ?? '');
            $username = Security::sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $roleId = (int)($_POST['role_id'] ?? 0);
            $status = Security::sanitize($_POST['status'] ?? 'Active');

            if (empty($serviceNumber) || !$roleId) {
                throw new Exception("Service number and role are required.");
            }

            // Suggest/fallback username
            $username = trim($username);
            if (empty($username)) {
                $username = preg_replace('/[^A-Za-z0-9]/', '', $serviceNumber);
            }

            // Enforce location restrictions
            LocationMiddleware::validatePersonnel($serviceNumber);

            $roleName = Session::get('role_name');
            if ($roleName === 'Warrant Officer IC') {
                if ($roleId === 1 || $roleId === 6) {
                    throw new Exception("Unauthorized: Warrant Officer IC cannot assign Administrator or Super Admin roles.");
                }
                if ($userId) {
                    $existingUser = User::getById($userId);
                    if ($existingUser && ((int)$existingUser['role_id'] === 1 || (int)$existingUser['role_id'] === 6)) {
                        throw new Exception("Unauthorized: You cannot modify Administrator or Super Admin accounts.");
                    }
                    if (!empty($password)) {
                        throw new Exception("Unauthorized: Warrant Officer IC cannot reset user passwords.");
                    }
                }
            }

            if (!$userId && empty($password)) {
                throw new Exception("Password is required for new accounts.");
            }

            User::save($userId, $serviceNumber, $username, $password, $roleId, $status);

            Session::set('success_message', "User account for '$serviceNumber' saved successfully.");
            Response::redirect('/users');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users');
        }
    }

    // Suspend or Activate user status
    public function toggleUserStatus() {
        try {
            Security::verifyCsrf();

            $userId = (int)($_POST['user_id'] ?? 0);
            $status = Security::sanitize($_POST['status'] ?? '');

            if (!$userId || empty($status)) {
                throw new Exception("User ID and status are required.");
            }

            $user = User::getById($userId);
            if (!$user) {
                throw new Exception("User account not found.");
            }

            // Enforce location restrictions
            LocationMiddleware::validatePersonnel($user['service_number']);

            User::setStatus($userId, $status);

            Session::set('success_message', "User account status updated to $status.");
            Response::redirect('/users');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users');
        }
    }

    // Archived users list view
    public function archivedUsersIndex() {
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        $users = User::getAll($restrictedCampId, 1);
        $pageTitle = 'Archived User Accounts';
        include __DIR__ . '/../views/admin/archived_users.php';
    }

    // Locked users list view
    public function lockedUsersIndex() {
        $restrictedCampId = LocationMiddleware::getCampConstraint();
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT u.*, r.role_name, p.full_name, rk.rank_name AS `rank`, p.camp_id, c.camp_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            JOIN personnel p ON u.service_number = p.service_number
            LEFT JOIN ranks rk ON p.rank_id = rk.rank_id
            LEFT JOIN camps c ON p.camp_id = c.camp_id
            WHERE u.status = 'Locked'
        ";
        
        $params = [];
        if ($restrictedCampId !== null) {
            $sql .= " AND p.camp_id = :camp_id";
            $params[':camp_id'] = $restrictedCampId;
        }
        
        $sql .= " ORDER BY u.user_id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $pageTitle = 'Locked User Accounts';
        include __DIR__ . '/../views/admin/locked_users.php';
    }

    // Archive user account
    public function archiveUser() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin' && $roleName !== 'Warrant Officer IC') {
                throw new Exception("Unauthorized: You do not have permissions to archive user accounts.");
            }

            $userId = (int)($_POST['user_id'] ?? 0);
            $reason = Security::sanitize($_POST['archive_reason'] ?? 'Administrative Decision');
            if (!$userId || empty($reason)) {
                throw new Exception("User ID and reason are required.");
            }

            $user = User::getById($userId);
            if (!$user) {
                throw new Exception("User account not found.");
            }

            // Enforce location isolation
            LocationMiddleware::validatePersonnel($user['service_number']);

            $adminServiceNum = Session::get('service_number');
            User::archive($userId, $reason, $adminServiceNum);

            Session::set('success_message', "User account archived successfully.");
            Response::redirect('/users');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users');
        }
    }

    // Restore user account
    public function restoreUser() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin' && $roleName !== 'Warrant Officer IC') {
                throw new Exception("Unauthorized: You do not have permissions to restore user accounts.");
            }

            $userId = (int)($_POST['user_id'] ?? 0);
            $reason = Security::sanitize($_POST['restore_reason'] ?? 'Administrative Decision');
            if (!$userId || empty($reason)) {
                throw new Exception("User ID and reason are required.");
            }

            $user = User::getById($userId);
            if (!$user) {
                throw new Exception("User account not found.");
            }

            // Enforce location isolation
            LocationMiddleware::validatePersonnel($user['service_number']);

            $adminServiceNum = Session::get('service_number');
            User::restore($userId, $reason, $adminServiceNum);

            Session::set('success_message', "User account restored successfully.");
            Response::redirect('/users/archived');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users/archived');
        }
    }

    // Unlock locked account
    public function unlockUser() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                throw new Exception("Unauthorized: Only Administrator and Super Administrator can unlock user accounts.");
            }

            $userId = (int)($_POST['user_id'] ?? 0);
            $reason = Security::sanitize($_POST['unlock_reason'] ?? 'Administrative Decision');
            if (!$userId || empty($reason)) {
                throw new Exception("User ID and reason are required.");
            }

            $adminServiceNum = Session::get('service_number');
            User::unlock($userId, $reason, $adminServiceNum);

            Session::set('success_message', "User account unlocked successfully.");
            Response::redirect('/users/locked');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users/locked');
        }
    }

    // Reset password of user account
    public function resetPassword() {
        try {
            Security::verifyCsrf();
            $roleName = Session::get('role_name');
            if ($roleName !== 'Administrator' && $roleName !== 'Super Admin') {
                throw new Exception("Unauthorized: Only Administrator and Super Administrator can reset passwords.");
            }

            $userId = (int)($_POST['user_id'] ?? 0);
            $tempPassword = $_POST['temp_password'] ?? '';
            $reason = Security::sanitize($_POST['reset_reason'] ?? 'Requested by user');

            if (!$userId || empty($tempPassword) || empty($reason)) {
                throw new Exception("User ID, temporary password, and reason are required.");
            }

            if (strlen($tempPassword) < 8) {
                throw new Exception("Temporary password must be at least 8 characters long.");
            }

            $adminServiceNum = Session::get('service_number');
            User::resetPassword($userId, $tempPassword, $reason, $adminServiceNum);

            Session::set('success_message', "Password reset successfully. The user will be forced to change it on their next login.");
            Response::redirect('/users');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/users');
        }
    }
}
