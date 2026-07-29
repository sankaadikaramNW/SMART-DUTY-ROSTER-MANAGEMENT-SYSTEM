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

    // Verify token from POST or custom header (like X-CSRF-Token) or JSON body
    public static function verifyCsrf() {
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
        
        $token = null;
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } else {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $jsonData = json_decode($rawInput, true);
                if (is_array($jsonData) && isset($jsonData['csrf_token'])) {
                    $token = $jsonData['csrf_token'];
                }
            }
        }

        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("CSRF verification failed. Potential cross-site request forgery detected.");
        }
        return true;
    }

    // Verify Cloudflare Turnstile token
    public static function verifyTurnstile() {
        if (empty(TURNSTILE_SITE_KEY) || empty(TURNSTILE_SECRET_KEY)) {
            return true; // Bypassed if Turnstile is not configured
        }

        $token = $_POST['cf-turnstile-response'] ?? '';
        if (empty($token)) {
            throw new Exception("Please complete the Cloudflare Turnstile verification.");
        }

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => TURNSTILE_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $result = false;

        // Try cURL first (handles allow_url_fopen restrictions and SSL certificate mismatches)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypasses outdated server CA certificates issues
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            curl_close($ch);
        }

        // Fallback to file_get_contents with SSL verification disabled
        if ($result === false) {
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 5,
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]
            ];
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
        }

        if ($result === false) {
            throw new Exception("Unable to contact Cloudflare Turnstile verification service.");
        }

        $response = json_decode($result, true);
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception("Cloudflare Turnstile verification failed. Please try again.");
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

    // Validate SLAF Service Number format (e.g. 51837, AW/5188, 'admin'/'sadmin' for system administrators)
    public static function validateServiceNumber($serviceNumber) {
        $trimmed = trim($serviceNumber);
        $clean = strtolower($trimmed);
        if ($clean === 'admin' || $clean === 'sadmin') {
            return true;
        }
        return (bool)preg_match('/^[A-Za-z0-9\/]{1,20}$/', $trimmed);
    }
}
