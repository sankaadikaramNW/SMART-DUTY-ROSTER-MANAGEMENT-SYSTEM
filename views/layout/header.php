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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?= BASE_URL ?>/views/assets/css/style.css" rel="stylesheet">
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const CSRF_TOKEN = '<?= Security::generateCSRFToken() ?>';
    </script>
</head>
<body>
    <?php if ($isLoggedIn): ?>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/dashboard">
                <i class="fas fa-shield-halved"></i> SLAF SMART ROSTER
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= ($route ?? '') === '/dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    
                    <?php if ($roleName === 'Administrator'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/personnel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/personnel">
                                <i class="fas fa-users-gear"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/postings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/postings">
                                <i class="fas fa-map-location-dot"></i> Postings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                                <i class="fas fa-calendar-days"></i> Rosters
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sliders"></i> Configuration
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/camps"><i class="fas fa-campground"></i> Camps</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/shifts"><i class="fas fa-clock"></i> Shifts</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/duty-types"><i class="fas fa-shield"></i> Duty Types</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/users"><i class="fas fa-user-shield"></i> User Accounts</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/audit-logs"><i class="fas fa-receipt"></i> Audit Logs</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                                <i class="fas fa-file-invoice"></i> Reports
                            </a>
                        </li>
                    <?php elseif ($roleName === 'OCPROVST' || $roleName === 'SNCO'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/personnel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/personnel">
                                <i class="fas fa-users"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/postings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/postings">
                                <i class="fas fa-arrows-spin"></i> Postings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/rosters' ? 'active' : '' ?>" href="<?= BASE_URL ?>/rosters">
                                <i class="fas fa-calendar-days"></i> Rosters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?= ($route ?? '') === '/reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                                <i class="fas fa-print"></i> Reports
                            </a>
                        </li>
                    <?php elseif ($roleName === 'Airman'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/rosters">
                                <i class="fas fa-calendar-days"></i> Duty Schedule
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="<?= BASE_URL ?>/notifications" class="btn btn-link position-relative text-light me-3 nav-link-custom">
                        <i class="fas fa-bell fs-5"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">
                                <?= $notificationCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
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
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container my-4 content-wrapper">
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
