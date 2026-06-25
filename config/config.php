<?php
/**
 * Global Configuration Parameters
 */

// Dynamically discover base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$dir = dirname($script);
$base_url = $protocol . $domain . rtrim(str_replace('\\', '/', $dir), '/');

define('BASE_URL', $base_url);
define('APP_NAME', 'Smart Duty Roster Management System');

// Database settings (Standard XAMPP credentials)
define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_duty_roster');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session configs
define('SESSION_TIMEOUT', 900); // 15 minutes of idle time
define('DEVELOPMENT_MODE', true); // Toggle for database or routing error output

// Set standard timezone (Sri Lanka / Colombo matches +05:30)
date_default_timezone_set('Asia/Colombo');
