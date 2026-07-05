<?php
/**
 * Global Configuration Parameters
 */

// Load Env helper manually to ensure compatibility with all entry points
require_once dirname(__DIR__) . '/helpers/Env.php';

// Load .env configuration file
Env::load(dirname(__DIR__) . '/.env');

// Dynamically discover base URL as fallback
$protocol = 'http://';
if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
) {
    $protocol = 'https://';
}

$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$dir = dirname($script);
$base_url = $protocol . $domain . rtrim(str_replace('\\', '/', $dir), '/');

$env_app_url = Env::get('APP_URL');
$is_localhost_request = (strpos($domain, 'localhost') !== false || $domain === '127.0.0.1');

if ($is_localhost_request) {
    define('BASE_URL', $base_url);
} elseif ($env_app_url) {
    define('BASE_URL', rtrim($env_app_url, '/'));
} else {
    define('BASE_URL', $base_url);
}
define('APP_NAME', Env::get('APP_NAME', 'Smart Duty Roster Management System'));

// Database settings
define('DB_HOST', Env::get('DB_HOST', 'localhost'));
define('DB_PORT', Env::get('DB_PORT', '3306'));
define('DB_NAME', Env::get('DB_DATABASE', 'smart_duty_roster'));
define('DB_USER', Env::get('DB_USERNAME', 'root'));
define('DB_PASS', Env::get('DB_PASSWORD', ''));

// Session configs
define('SESSION_TIMEOUT', (int)Env::get('SESSION_TIMEOUT', 900));
define('DEVELOPMENT_MODE', (bool)Env::get('APP_DEBUG', true));

// Upload configuration
define('UPLOAD_PATH', Env::get('UPLOAD_PATH', 'uploads/'));

// Log configuration
define('LOG_LEVEL', Env::get('LOG_LEVEL', 'debug'));

// Set timezone from environment configuration
date_default_timezone_set(Env::get('TIMEZONE', 'Asia/Colombo'));

