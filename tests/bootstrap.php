<?php
/**
 * PHPUnit Test Bootstrap File
 * Configures the custom MVC autoloader for running unit tests.
 */

// Register the custom autoloader for controllers, models, helpers, and middleware
spl_autoload_register(function ($class) {
    $directories = [
        dirname(__DIR__) . '/controllers/',
        dirname(__DIR__) . '/models/',
        dirname(__DIR__) . '/helpers/',
        dirname(__DIR__) . '/middleware/'
    ];
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Set up minimal testing environment
define('DEVELOPMENT_MODE', true);
define('BASE_URL', 'http://localhost');
