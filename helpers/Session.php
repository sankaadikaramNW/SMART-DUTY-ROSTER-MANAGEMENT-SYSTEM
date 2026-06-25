<?php
/**
 * Secure Session Manager Helper
 */

class Session {
    
    // Start session with hardening properties
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Hardening PHP Session configurations
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
        }

        // Validate Inactivity Session Expiry (default 15 minutes)
        if (self::has('user_id') && self::has('last_activity')) {
            $elapsedTime = time() - self::get('last_activity');
            if ($elapsedTime > SESSION_TIMEOUT) {
                self::destroy();
                header("Location: " . BASE_URL . "/login?timeout=1");
                exit;
            }
        }
        
        // Update activity timestamp
        self::set('last_activity', time());
    }

    // Set variable
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    // Get variable with default backup
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    // Check key
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    // Delete key
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    // Destroy all sessions
    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(), 
                    '', 
                    time() - 42000,
                    $params["path"], 
                    $params["domain"],
                    $params["secure"], 
                    $params["httponly"]
                );
            }
            @session_destroy();
        }
    }

    // Force regeneration to counter Session Fixation
    public static function regenerate() {
        if (session_status() !== PHP_SESSION_NONE) {
            session_regenerate_id(true);
        }
    }
}
