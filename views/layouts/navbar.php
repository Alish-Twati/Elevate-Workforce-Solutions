<?php
// ✨ Ensure config and session are loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../config/config.php';
}

// ✨ Get current page for active class
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <!-- Brand/Logo -->
        <a class="navbar-brand fw-bold" href="<?php echo APP_URL; ?>index.php">
            <i class="fas fa-briefcase me-2"></i>
            <span class="d-none d-sm-inline"><?php echo APP_NAME; ?></span>
            <span class="d-inline d-sm-none">EWS</span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                
                <!-- Home Link -->
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('index.php'); ?>" href="<?php echo APP_URL; ?>index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                
                <!-- Browse Jobs -->
                <li class="nav-item">
                    <a class="nav-link <?php echo contains($current_page, 'jobs') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>views/jobs/index.php">
                        <i class="fas fa-search me-1"></i> Browse Jobs
                    </a>
                </li>
                
                <?php if (Session::isLoggedIn()): ?>
                    
                    <!-- ========================= JOB SEEKER MENU ========================= -->
                    <?php if (Session::isJobSeeker()): ?>
                        
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('jobseeker.php'); ?>" 
                               href="<?php echo APP_URL; ?>views/dashboard/jobseeker.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- My Applications -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>views/applications/view.php">
                                <i class="fas fa-file-alt me-1"></i> My Applications
                                <?php 
                                // ✨ Show pending count badge
                                require_once __DIR__ . '/../../models/Application.php';
                                $app = new Application();
                                $pending_count = count($app->getByUser(Session::getUserId(), APP_STATUS_PENDING));
                                if ($pending_count > 0): 
                                ?>
                                    <span class="badge bg-warning text-dark ms-1"><?php echo $pending_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        
                    <?php endif; ?>
                    
                    <!-- ========================= COMPANY MENU ========================= -->
                    <?php if (Session::isCompany()): ?>
                        
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('company.php'); ?>" 
                               href="<?php echo APP_URL; ?>views/dashboard/company.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- Post Job (Prominent Button) -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-success btn-sm text-white px-3 ms-lg-2" 
                               href="<?php echo APP_URL; ?>views/jobs/create.php">
                                <i class="fas fa-plus-circle me-1"></i> Post Job
                            </a>
                        </li>
                        
                        <!-- My Jobs Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="jobsDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-briefcase me-1"></i> My Jobs
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/jobs/my-jobs.php">
                                    <i class="fas fa-list me-2"></i> All Jobs
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/jobs/my-jobs.php? status=active">
                                    <i class="fas fa-check-circle me-2 text-success"></i> Active Jobs
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/jobs/my-jobs.php?status=draft">
                                    <i class="fas fa-edit me-2 text-secondary"></i> Draft Jobs
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/jobs/my-jobs.php?status=closed">
                                    <i class="fas fa-times-circle me-2 text-danger"></i> Closed Jobs
                                </a></li>
                            </ul>
                        </li>
                        
                        <!-- Applications -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>views/applications/received.php">
                                <i class="fas fa-inbox me-1"></i> Applications
                                <?php 
                                // ✨ Show pending applications count
                                require_once __DIR__ . '/../../models/Company.php';
                                require_once __DIR__ . '/../../models/Application.php';
                                try {
                                    $company = new Company();
                                    $companyData = $company->getByUserId(Session::getUserId());
                                    if ($companyData) {
                                        $app = new Application();
                                        $pending = count($app->getByCompany($companyData['id'], APP_STATUS_PENDING));
                                        if ($pending > 0): 
                                ?>
                                            <span class="badge bg-danger ms-1"><?php echo $pending; ?></span>
                                <?php 
                                        endif;
                                    }
                                } catch (Exception $e) {
                                    // Silently fail
                                }
                                ?>
                            </a>
                        </li>
                        
                        <!-- Company Profile -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>views/company/profile.php">
                                <i class="fas fa-building me-1"></i> Profile
                            </a>
                        </li>
                        
                    <?php endif; ?>
                    
                    <!-- ========================= ADMIN MENU ========================= -->
                    <?php if (Session::isAdmin()): ?>
                        
                        <!-- Admin Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('admin.php'); ?>" 
                               href="<?php echo APP_URL; ?>views/dashboard/admin.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Admin Dashboard
                            </a>
                        </li>
                        
                        <!-- Admin Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Manage
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/admin/users.php">
                                    <i class="fas fa-users me-2"></i> Users
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/admin/companies.php">
                                    <i class="fas fa-building me-2"></i> Companies
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/admin/jobs.php">
                                    <i class="fas fa-briefcase me-2"></i> Jobs
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/admin/categories.php">
                                    <i class="fas fa-tags me-2"></i> Categories
                                </a></li>
                            </ul>
                        </li>
                        
                    <?php endif; ?>
                    
                    <!-- ========================= USER DROPDOWN (ALL LOGGED IN USERS) ========================= -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <?php
                                // ✨ Show user initials or icon
                                $first_name = Session::get('first_name', '');
                                $last_name = Session::get('last_name', '');
                                if ($first_name && $last_name) {
                                    echo '<span class="avatar-initials">' . getInitials($first_name, $last_name) . '</span>';
                                } else {
                                    echo '<i class="fas fa-user-circle fa-lg"></i>';
                                }
                                ?>
                            </div>
                            <span class="d-none d-lg-inline">
                                <?php echo htmlspecialchars(Session::get('user_name', 'User')); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- User Info Header -->
                            <li class="dropdown-header">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold"><?php echo htmlspecialchars(Session::getUserName()); ?></span>
                                    <small class="text-muted"><?php echo htmlspecialchars(Session::getUserEmail()); ?></small>
                                    <small class="badge bg-<?php echo Session::isCompany() ? 'success' : (Session::isJobSeeker() ?  'info' : 'danger'); ?> mt-1">
                                        <?php echo ucfirst(Session::getUserType()); ?>
                                    </small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Profile Link -->
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/profile/edit.php">
                                <i class="fas fa-user-edit me-2 text-primary"></i> Edit Profile
                            </a></li>
                            
                            <!-- Change Password -->
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/profile/change-password.php">
                                <i class="fas fa-key me-2 text-warning"></i> Change Password
                            </a></li>
                            
                            <!-- Settings (Optional) -->
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>views/profile/settings.php">
                                <i class="fas fa-cog me-2 text-secondary"></i> Settings
                            </a></li>
                            
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Logout -->
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>controllers/AuthController.php?action=logout"
                                   onclick="return confirm('Are you sure you want to logout?');">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php else: ?>
                    
                    <!-- ========================= GUEST MENU ========================= -->
                    
                    <!-- Login -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>views/auth/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    
                    <!-- Register (Prominent Button) -->
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary px-3 ms-lg-2" 
                           href="<?php echo APP_URL; ?>views/auth/register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                    
                <?php endif; ?>
                
            </ul>
        </div>
    </div>
</nav>

<!-- ✨ Flash Messages (moved here for better UX) -->
<?php if (Session::hasFlash()): ?>
    <div class="container mt-3">
        <?php echo displayFlashMessage(); ?>
    </div>
<?php endif; ?>