<?php
/**
 * Smart Duty Roster Management System - Front Controller
 * Core PHP 8+ MVC Entrypoint
 */

// Error Reporting Config (For development, toggled in config.php)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload core files dynamically
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/helpers/',
        __DIR__ . '/middleware/'
    ];
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load global configuration
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} else {
    die("Global configuration file config/config.php is missing.");
}

// Load database connection helper
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

// Start secure session
Session::start();

// Request Routing Logic
$method = $_SERVER['REQUEST_METHOD'];
// Decode percent-encoded URLs (e.g. %20 for spaces in folder name)
$requestUri = urldecode($_SERVER['REQUEST_URI']);

// Clean base path (useful when running in a subdirectory under XAMPP htdocs)
$basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
$route = '/';
if (strpos($requestUri, $basePath) === 0) {
    $route = '/' . substr($requestUri, strlen($basePath));
} else {
    $route = $requestUri;
}

// Strip query strings
if (($pos = strpos($route, '?')) !== false) {
    $route = substr($route, 0, $pos);
}
$route = '/' . ltrim($route, '/');

// If routing directly to index.php or directory index, treat as root '/'
if ($route === '/index.php') {
    $route = '/';
}

// Routing table
$routes = [
    'GET' => [
        '/' => 'AuthController@loginView',
        '/login' => 'AuthController@loginView',
        '/logout' => 'AuthController@logout',
        '/dashboard' => 'DashboardController@index',
        '/personnel' => 'PersonnelController@index',
        '/personnel/view' => 'PersonnelController@view',
        '/personnel/search' => 'PersonnelController@search',
        '/personnel/history' => 'PersonnelController@history',
        '/postings' => 'PersonnelController@postingsIndex',
        '/rosters' => 'RosterController@index',
        '/rosters/create' => 'RosterController@createView',
        '/rosters/approve' => 'RosterController@approveView',
        '/rosters/view' => 'RosterController@viewRoster',
        '/rosters/calendar' => 'RosterController@calendarView',
        '/rosters/calendar-data' => 'RosterController@getCalendarData',
        '/rosters/timeline' => 'RosterController@timelineView',
        '/rosters/conflict-check' => 'RosterController@checkConflicts',
        '/camps' => 'AdminController@campsIndex',
        '/shifts' => 'AdminController@shiftsIndex',
        '/duty-types' => 'AdminController@dutyTypesIndex',
        '/ranks' => 'AdminController@ranksIndex',
        '/users' => 'AdminController@usersIndex',
        '/reports' => 'ReportController@index',
        '/reports/generate' => 'ReportController@generate',
        '/audit-logs' => 'AdminController@auditLogsIndex',
        '/notifications' => 'NotificationController@getNotifications',
        '/transfers' => 'TransferController@index',
        '/transfers/view' => 'TransferController@view',
        '/leaves' => 'LeaveController@index',
        '/leaves/calendar-data' => 'LeaveController@calendarData',
        '/dashboard/attendance-stats' => 'DashboardController@getAttendanceStats',
    ],
    'POST' => [
        '/login' => 'AuthController@loginProcess',
        '/personnel/add' => 'PersonnelController@addPersonnel',
        '/personnel/edit' => 'PersonnelController@editPersonnel',
        '/postings/add' => 'PersonnelController@addPosting',
        '/transfers/create' => 'TransferController@create',
        '/transfers/edit' => 'TransferController@edit',
        '/transfers/action' => 'TransferController@action',
        '/transfers/cancel' => 'TransferController@cancel',
        '/rosters/save' => 'RosterController@saveRoster',
        '/rosters/action' => 'RosterController@submitApproval',
        '/rosters/assignment-action' => 'RosterController@submitAssignmentApproval',
        '/camps/save' => 'AdminController@saveCamp',
        '/shifts/save' => 'AdminController@saveShift',
        '/duty-types/save' => 'AdminController@saveDutyType',
        '/ranks/save' => 'AdminController@saveRank',
        '/users/save' => 'AdminController@saveUser',
        '/users/status' => 'AdminController@toggleUserStatus',
        '/notifications/read' => 'NotificationController@markAsRead',
        '/leaves/save' => 'LeaveController@save',
        '/leaves/report-return' => 'LeaveController@reportReturn',
        '/leaves/grant-extension' => 'LeaveController@grantExtension',
    ]
];

// Check route existence
if (!isset($routes[$method][$route])) {
    http_response_code(404);
    // Display custom 404 page
    if (file_exists(__DIR__ . '/views/layout/header.php')) {
        include __DIR__ . '/views/layout/header.php';
        echo '<div class="container text-center my-5 py-5">';
        echo '<h1 class="display-1 text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> 404</h1>';
        echo '<p class="lead">The page or action requested is not found or unauthorized.</p>';
        echo '<a href="'.BASE_URL.'/dashboard" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Back to Dashboard</a>';
        echo '</div>';
        include __DIR__ . '/views/layout/footer.php';
    } else {
        echo "404 Not Found - Route: $route";
    }
    exit;
}

$handler = $routes[$method][$route];
list($controllerClass, $action) = explode('@', $handler);

try {
    // Authenticate and Authorize through Middleware
    AuthMiddleware::handle($route);

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            throw new Exception("Action '$action' not found in controller '$controllerClass'.");
        }
    } else {
        throw new Exception("Controller class '$controllerClass' not found.");
    }
} catch (Exception $e) {
    http_response_code(500);
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo "<h1>500 System Error</h1>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        // Safe user error
        if (file_exists(__DIR__ . '/views/layout/header.php') && Session::get('user_id')) {
            include __DIR__ . '/views/layout/header.php';
            echo '<div class="container text-center my-5 py-5">';
            echo '<h1 class="display-4 text-warning fw-bold"><i class="fas fa-shield-alt"></i> Access Denied / Error</h1>';
            echo '<p class="lead">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="'.BASE_URL.'/dashboard" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Go Back</a>';
            echo '</div>';
            include __DIR__ . '/views/layout/footer.php';
        } else {
            echo "Access Denied / Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
    exit;
}
