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

            $user = User::authenticate($serviceNumber, $password);

            if ($user) {
                // Secure Session Initialization
                Session::regenerate();
                Session::set('user_id', (int)$user['user_id']);
                Session::set('service_number', $user['service_number']);
                Session::set('role_name', $user['role_name']);
                Session::set('camp_id', $user['camp_id'] ? (int)$user['camp_id'] : null);
                Session::set('full_name', $user['full_name']);
                Session::set('rank', $user['rank']);
                
                // Log login success
                Logger::loginAttempt($serviceNumber, 'Success', 'Authentication successful.');
                
                // Audit log for login
                Logger::audit('Authentication', 'User Login: ' . $serviceNumber);

                Response::redirect('/dashboard');
            } else {
                // Log login failure
                Logger::loginAttempt($serviceNumber, 'Failed', 'Incorrect credentials or account/personnel inactive.');
                throw new Exception("Invalid Service Number or Password, or account is suspended.");
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
}
