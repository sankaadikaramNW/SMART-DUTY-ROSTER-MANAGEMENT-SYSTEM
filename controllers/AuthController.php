<?php
/**
 * Auth Controller
 */

class AuthController {

    // Render Login View
    public function loginView() {
        $pageTitle = 'Login';
        $isLoginPage = true;
        include __DIR__ . '/../views/auth/login.php';
    }

    // Process Login Request
    public function loginProcess() {
        try {
            // Verify CSRF
            Security::verifyCsrf();

            $serviceNumber = strtoupper(Security::sanitize($_POST['service_number'] ?? ''));
            $password = $_POST['password'] ?? '';

            if (empty($serviceNumber) || empty($password)) {
                throw new Exception("Please enter both Service Number and Password.");
            }

            if (!Security::validateServiceNumber($serviceNumber)) {
                throw new Exception("Login username must be a valid Service Number (e.g., SLAF/AIR/301).");
            }

            // Retrieve user details first (including inactive or locked status)
            $user = User::getByServiceNumber($serviceNumber);

            if (!$user) {
                // Fail generic
                Logger::loginAttempt($serviceNumber, 'Failed', 'User account does not exist.');
                throw new Exception("Invalid Service Number or Password.");
            }

            // 1. Check if archived (both user or personnel)
            if ((int)$user['is_archived'] === 1 || (int)$user['personnel_is_archived'] === 1) {
                Logger::loginAttempt($serviceNumber, 'Failed', 'Attempt to login to an archived account.');
                throw new Exception("This account has been archived. Access denied.");
            }

            // 2. Check if locked
            $isLocked = ($user['status'] === 'Locked');
            if ($isLocked) {
                Logger::loginAttempt($serviceNumber, 'Failed', 'Attempt to login to a locked account.');
                throw new Exception("Your account has been locked. Please contact the system administrator.");
            }

            // 3. Verify password
            if (Security::verifyPassword($password, $user['password_hash'])) {
                // 4. Verify Active status of user
                if ($user['status'] === 'Suspended') {
                    Logger::loginAttempt($serviceNumber, 'Failed', 'Attempt to login to a suspended account.');
                    throw new Exception("Your account is suspended. Please contact the system administrator.");
                }
                
                // 5. Verify personnel status
                if ($user['personnel_status'] !== 'Active') {
                    Logger::loginAttempt($serviceNumber, 'Failed', 'Personnel profile status is not Active: ' . $user['personnel_status']);
                    throw new Exception("Your personnel profile is inactive (" . $user['personnel_status'] . ").");
                }

                // Clean failed attempts and locks on success
                if ((int)$user['failed_attempts'] > 0) {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("UPDATE users SET failed_attempts = 0, lock_date = NULL, lock_reason = NULL, locked_until = NULL WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                }

                // Secure Session Initialization
                Session::regenerate();
                Session::set('user_id', (int)$user['user_id']);
                Session::set('service_number', $user['service_number']);
                Session::set('role_name', $user['role_name']);
                Session::set('camp_id', $user['camp_id'] ? (int)$user['camp_id'] : null);
                Session::set('full_name', $user['full_name']);
                Session::set('rank', $user['rank']);
                
                // Set force password change flag in session
                if ((int)$user['force_password_change'] === 1) {
                    Session::set('force_password_change', true);
                } else {
                    Session::remove('force_password_change');
                }

                // Log login success
                Logger::loginAttempt($serviceNumber, 'Success', 'Authentication successful.');
                
                // Audit log for login
                Logger::audit('Authentication', 'User Login: ' . $serviceNumber);

                if ((int)$user['force_password_change'] === 1) {
                    Response::redirect('/change-password');
                } else {
                    Response::redirect('/dashboard');
                }
            } else {
                // Password incorrect: Increment failed attempts
                User::incrementFailedAttempts($user['user_id']);
                $newFailedAttempts = (int)$user['failed_attempts'] + 1;
                
                if ($newFailedAttempts >= 5) {
                    // Lock the account
                    User::lock($user['user_id'], 'Locked due to 5 consecutive failed login attempts.');
                    Logger::loginAttempt($serviceNumber, 'Failed', 'Account locked after 5 failed attempts.');
                    throw new Exception("Your account has been locked due to 5 consecutive failed login attempts. Please contact the system administrator.");
                } else {
                    Logger::loginAttempt($serviceNumber, 'Failed', 'Incorrect password. Attempt ' . $newFailedAttempts . ' of 5.');
                    throw new Exception("Invalid Service Number or Password. Attempt " . $newFailedAttempts . " of 5.");
                }
            }
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/login');
        }
    }

    // Process Logout Request
    public function logout() {
        $serviceNumber = Session::get('service_number');
        if ($serviceNumber) {
            Logger::audit('Authentication', 'User Logout: ' . $serviceNumber);
        }
        Session::destroy();
        Response::redirect('/login');
    }

    // Render Force Password Change View
    public function changePasswordView() {
        $pageTitle = 'Force Password Change';
        $isLoginPage = true; // Hides headers/sidebar
        include __DIR__ . '/../views/auth/change_password.php';
    }

    // Process Force Password Change Request
    public function changePasswordProcess() {
        try {
            Security::verifyCsrf();

            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword) || empty($confirmPassword)) {
                throw new Exception("Please fill in both password fields.");
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception("New password and confirm password do not match.");
            }

            if (strlen($newPassword) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }

            $userId = Session::get('user_id');
            if (!$userId) {
                throw new Exception("Session expired or invalid user.");
            }

            // Update user password and clear flag
            User::updatePassword($userId, $newPassword);
            
            // Clear flag from session
            Session::remove('force_password_change');

            Session::set('success_message', "Password changed successfully. You now have full access.");
            Response::redirect('/dashboard');
        } catch (Exception $e) {
            Session::set('error_message', $e->getMessage());
            Response::redirect('/change-password');
        }
    }

    // Render Help View
    public function helpView() {
        $pageTitle = 'Help & Documentation';
        include __DIR__ . '/../views/help.php';
    }
}
