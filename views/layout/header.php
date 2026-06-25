<?php
/**
 * Global Header Template
 */
$isLoggedIn = Session::has('user_id');
$roleName = Session::get('role_name');
$fullName = Session::get('full_name');
$rankName = Session::get('rank');
$serviceNum = Session::get('service_number');

$notificationCount = 0;
if ($isLoggedIn && $serviceNum) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE service_number = ? AND is_read = 0");
        $stmt->execute([$serviceNum]);
        $notificationCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        // Fallback silently if DB is not ready yet
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="<?= BASE_URL ?>/views/assets/vendor/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for Icons -->
    <link href="<?= BASE_URL ?>/views/assets/vendor/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?= BASE_URL ?>/views/assets/css/style.css" rel="stylesheet">
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const CSRF_TOKEN = '<?= Security::csrfToken() ?>';
    </script>
</head>
<body>
    <?php if ($isLoggedIn): 
        $campName = 'SLAF Base';
        if (Session::get('camp_id')) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT camp_name FROM camps WHERE camp_id = ?");
                $stmt->execute([Session::get('camp_id')]);
                $campName = $stmt->fetchColumn() ?: 'SLAF Base';
            } catch (Exception $e) {
                // Fallback silently
            }
        }
    ?>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar-custom">
            <div class="sidebar-header">
                <div class="sidebar-brand-img"><i class="fas fa-shield-halved"></i></div>
                <div class="sidebar-brand-text">
                    <div class="sidebar-brand-title">Sri Lanka Air Force</div>
                    <div class="sidebar-brand-subtitle">SMART ROSTER</div>
                </div>
            </div>
            <div class="sidebar-menu">
                <!-- MAIN MENU -->
                <div class="sidebar-group-title">Main Menu</div>
                <a class="sidebar-link <?= ($route ?? '') === '/dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                
                <?php if ($roleName === 'Administrator'): ?>
                    <!-- ROSTER OPERATIONS -->
                    <div class="sidebar-group-title">Roster Operations</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/personnel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/personnel">
                        <i class="fas fa-users-gear"></i> Personnel
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/postings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/postings">
                        <i class="fas fa-map-location-dot"></i> Postings
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                        <i class="fas fa-calendar-days"></i> Rosters
                    </a>
                    
                    <!-- SYSTEM CONFIGURATION -->
                    <div class="sidebar-group-title">System Settings</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/camps' ? 'active' : '' ?>" href="<?= BASE_URL ?>/camps">
                        <i class="fas fa-campground"></i> Camps
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/shifts' ? 'active' : '' ?>" href="<?= BASE_URL ?>/shifts">
                        <i class="fas fa-clock"></i> Shifts
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/duty-types' ? 'active' : '' ?>" href="<?= BASE_URL ?>/duty-types">
                        <i class="fas fa-shield"></i> Duty Types
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/users' ? 'active' : '' ?>" href="<?= BASE_URL ?>/users">
                        <i class="fas fa-user-shield"></i> User Accounts
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/audit-logs' ? 'active' : '' ?>" href="<?= BASE_URL ?>/audit-logs">
                        <i class="fas fa-receipt"></i> Audit Logs
                    </a>
                    
                    <!-- REPORTING -->
                    <div class="sidebar-group-title">Reporting</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                        <i class="fas fa-file-invoice"></i> Reports
                    </a>

                <?php elseif ($roleName === 'OCPROVST' || $roleName === 'SNCO'): ?>
                    <!-- ROSTER OPERATIONS -->
                    <div class="sidebar-group-title">Roster Operations</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/personnel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/personnel">
                        <i class="fas fa-users"></i> Personnel
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/postings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/postings">
                        <i class="fas fa-arrows-spin"></i> Postings
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                        <i class="fas fa-calendar-days"></i> Rosters
                    </a>
                    
                    <!-- REPORTING -->
                    <div class="sidebar-group-title">Reporting</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                        <i class="fas fa-print"></i> Reports
                    </a>

                <?php elseif ($roleName === 'Airman'): ?>
                    <!-- SERVICE MEMBER -->
                    <div class="sidebar-group-title">My Schedule</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                        <i class="fas fa-calendar-days"></i> Duty Schedule
                    </a>
                <?php endif; ?>
            </div>
            <div class="sidebar-footer">
                <i class="fas fa-shield"></i> SLAF SMART ROSTER - v1.0
            </div>
        </aside>

        <!-- Main Layout Wrapper -->
        <div class="main-layout">
            <!-- Topbar Header -->
            <header class="topbar-custom">
                <div class="d-flex align-items-center">
                    <!-- Mobile Nav Toggle button -->
                    <button class="topbar-action-btn mobile-nav-toggle d-none me-3" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-title-section">
                        <div class="topbar-title"><?= htmlspecialchars($campName) ?></div>
                        <div class="topbar-subtitle">Smart Duty Roster Management System</div>
                    </div>
                </div>
                
                <div class="topbar-actions">
                    <!-- Dark Mode Toggle button (Visual Only) -->
                    <button class="topbar-action-btn" id="themeToggle" title="Toggle theme">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <!-- Notifications Link -->
                    <a href="<?= BASE_URL ?>/notifications" class="topbar-action-btn position-relative text-decoration-none" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6rem; padding: 0.25em 0.5em;">
                                <?= $notificationCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Menu Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-custom btn-custom-secondary dropdown-toggle py-1 px-3" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($rankName . ' ' . $fullName) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" aria-labelledby="userMenu">
                            <li><span class="dropdown-item-text text-muted small"><i class="fas fa-id-card"></i> <?= htmlspecialchars($serviceNum) ?> (<?= htmlspecialchars($roleName) ?>)</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <div class="main-content-container">
    <?php else: ?>
        <!-- Guest View (Login screen, etc.) -->
        <div class="container my-4 content-wrapper">
    <?php endif; ?>
        <!-- Render alerts or notifications if set in session flash -->
        <?php if (Session::has('error_message')): ?>
            <div class="alert alert-danger alert-dismissible fade show glass-card border-danger text-light" role="alert">
                <i class="fas fa-circle-exclamation me-2 text-danger"></i> <?= htmlspecialchars(Session::get('error_message')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php Session::remove('error_message'); ?>
        <?php endif; ?>
        
        <?php if (Session::has('success_message')): ?>
            <div class="alert alert-success alert-dismissible fade show glass-card border-success text-light" role="alert">
                <i class="fas fa-circle-check me-2 text-success"></i> <?= htmlspecialchars(Session::get('success_message')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php Session::remove('success_message'); ?>
        <?php endif; ?>
