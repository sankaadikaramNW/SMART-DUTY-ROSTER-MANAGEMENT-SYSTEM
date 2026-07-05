<?php
/**
 * Security helper for hashing, sanitizing, escaping, and CSRF protection
 */

class Security {
    
    // Generate or fetch current CSRF token
    public static function csrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Output hidden input field for form templates
    public static function csrfField() {
        return '<input type="hidden" name="csrf_token" value="' . self::escape(self::csrfToken()) . '">';
    }

    // Verify token from POST or custom header (like X-CSRF-Token)
    public static function verifyCsrf() {
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
        
        $token = null;
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("CSRF verification failed. Potential cross-site request forgery detected.");
        }
        return true;
    }

    // Sanitize string data
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return trim(strip_tags($data));
    }

    // Escape output to prevent XSS
    public static function escape($data) {
        if (is_array($data)) {
            return array_map([self::class, 'escape'], $data);
        }
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }

    // Password Hashing (using modern bcrypt)
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Password Verification
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Validate SLAF Service Number format (e.g. 51837 or 'admin'/'sadmin' for system administrators)
    public static function validateServiceNumber($serviceNumber) {
        $clean = strtolower($serviceNumber);
        return $clean === 'admin' || $clean === 'sadmin' || (bool)preg_match('/^\d+$/', $serviceNumber);
    }
}
