<?php
/**
 * Global Header Template
 */
$isLoggedIn = Session::has('user_id');
$roleName = Session::get('role_name');
$fullName = Session::get('full_name');
$rankName = Session::get('rank');
$serviceNum = Session::get('service_number');
$profileName = '';
$profileCampName = '';

$notificationCount = 0;
if ($isLoggedIn && $serviceNum) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE service_number = ? AND is_read = 0");
        $stmt->execute([$serviceNum]);
        $notificationCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        // Fallback silently
    }

    try {
        $profile = User::getProfileInfo(Session::get('user_id'));
        if ($profile) {
            $initials = $profile['initials'];
            $fName = $profile['full_name'];
            $rankShort = $profile['rank_short_name'];
            
            // Format "Rank Initials Name" (e.g. CPL S. Perera)
            $nameParts = explode(' ', trim($fName));
            $lastName = end($nameParts);
            $profileName = ($rankShort ?: $rankName) . ' ' . $initials . ' ' . $lastName;
            $profileCampName = $profile['camp_name'];
        } else {
            $profileName = $rankName . ' ' . $fullName;
            $profileCampName = 'SLAF Base';
        }
    } catch (Exception $e) {
        $profileName = $rankName . ' ' . $fullName;
        $profileCampName = 'SLAF Base';
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
        $campName = $profileCampName ?: 'SLAF Base';
    ?>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar-custom offcanvas-lg offcanvas-start" id="sidebarMenu" tabindex="-1" aria-labelledby="sidebarMenuLabel">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="SLAF Logo" style="height: 38px; width: auto; object-fit: contain;">
                    <div class="sidebar-brand-text">
                        <div class="sidebar-brand-title">Sri Lanka Air Force</div>
                        <div class="sidebar-brand-subtitle">SMART ROSTER</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
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
                        <i class="fas fa-calendar-days"></i> All Rosters
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters/approve' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters/approve">
                        <i class="fas fa-stamp"></i> Duty Approvals
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
                    <a class="sidebar-link <?= ($route ?? '') === '/ranks' ? 'active' : '' ?>" href="<?= BASE_URL ?>/ranks">
                        <i class="fas fa-list-ol"></i> Ranks
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

                <?php elseif ($roleName === 'OCPROVST'): ?>
                    <!-- ROSTER OPERATIONS -->
                    <div class="sidebar-group-title">Roster Operations</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/personnel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/personnel">
                        <i class="fas fa-users"></i> Personnel
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/postings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/postings">
                        <i class="fas fa-arrows-spin"></i> Postings
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                        <i class="fas fa-calendar-days"></i> All Rosters
                    </a>
                    <a class="sidebar-link <?= ($route ?? '') === '/rosters/approve' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters/approve">
                        <i class="fas fa-stamp"></i> Duty Approvals
                    </a>
                    
                    <!-- REPORTING -->
                    <div class="sidebar-group-title">Reporting</div>
                    <a class="sidebar-link <?= ($route ?? '') === '/reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                        <i class="fas fa-print"></i> Reports
                    </a>

                <?php elseif ($roleName === 'SNCO'): ?>
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
                    <button class="topbar-action-btn mobile-nav-toggle d-lg-none me-3" id="sidebarToggle" aria-label="Toggle Sidebar" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <img src="<?= BASE_URL ?>/views/assets/images/slaf_logo.png" alt="SLAF Logo" class="me-3" style="height: 36px; width: auto; object-fit: contain;">
                    <div class="topbar-title-section">
                        <div class="topbar-title d-none d-md-block"><?= htmlspecialchars($campName) ?></div>
                        <div class="topbar-subtitle d-none d-md-block">Smart Duty Roster Management System</div>
                        <div class="topbar-title d-block d-md-none" style="font-size: 0.95rem;">SLAF Smart Roster</div>
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
                        <button class="btn btn-custom btn-custom-secondary dropdown-toggle py-1 px-2 px-md-3 d-inline-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fs-5"></i> 
                            <span class="d-none d-md-inline text-start" style="line-height: 1.1; font-size: 0.825rem;">
                                <span class="d-block fw-semibold" style="color: #0f172a;"><?= htmlspecialchars($profileName) ?></span>
                                <span class="d-block text-muted" style="font-size: 0.7rem; font-weight: normal;"><?= htmlspecialchars($profileCampName) ?></span>
                            </span>
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
    <?php elseif (isset($isLoginPage) && $isLoginPage): ?>
        <!-- Full-screen custom login layout -->
        <div class="login-wrapper">
    <?php else: ?>
        <!-- Guest View (Login screen, etc.) -->
        <div class="container my-4 content-wrapper">
    <?php endif; ?>
        <!-- Render alerts or notifications if set in session flash -->
        <?php if (Session::has('error_message') && !(isset($isLoginPage) && $isLoginPage)): ?>
            <div class="alert alert-danger alert-dismissible fade show glass-card border-danger text-danger" role="alert">
                <i class="fas fa-circle-exclamation me-2 text-danger"></i> <?= htmlspecialchars(Session::get('error_message')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php Session::remove('error_message'); ?>
        <?php endif; ?>
        
        <?php if (Session::has('success_message') && !(isset($isLoginPage) && $isLoginPage)): ?>
            <div class="alert alert-success alert-dismissible fade show glass-card border-success text-success" role="alert">
                <i class="fas fa-circle-check me-2 text-success"></i> <?= htmlspecialchars(Session::get('success_message')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php Session::remove('success_message'); ?>
        <?php endif; ?>
