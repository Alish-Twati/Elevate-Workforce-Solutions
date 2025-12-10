<?php
$page_title = "Job Seeker Dashboard";
$page_description = "Manage your job applications and track your progress";
$body_class = "dashboard-page jobseeker-dashboard";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

// ✨ Require job seeker login
Session::requireJobSeeker();

// ✨ Get dashboard data with error handling
try {
    $dashboardData = DashboardController::jobSeekerDashboard();
    $user = $dashboardData['user'] ?? [];
    $applications = $dashboardData['applications'] ?? [];
    $stats = $dashboardData['stats'] ?? [];
    $recent_jobs = $dashboardData['recent_jobs'] ?? [];
    $featured_jobs = $dashboardData['featured_jobs'] ?? [];
    $profile_completion = $dashboardData['profile_completion'] ?? 0;
} catch (Exception $e) {
    error_log('Job seeker dashboard error: ' . $e->getMessage());
    Session::setError('An error occurred while loading your dashboard');
    $applications = [];
    $stats = ['total_applications' => 0, 'pending' => 0, 'shortlisted' => 0, 'accepted' => 0, 'rejected' => 0];
    $recent_jobs = [];
    $featured_jobs = [];
    $profile_completion = 0;
}
?>

<main id="main-content" class="dashboard-container">
    <div class="container-fluid mt-4 mb-5">
        
        <!-- ==================== HEADER ==================== -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-tachometer-alt me-2 text-primary"></i> 
                    Welcome back, <?php echo htmlspecialchars(Session::get('first_name', 'User')); ?>!
                </h1>
                <p class="text-muted lead">Track your job applications and discover new opportunities</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Browse Jobs
                    </a>
                    <a href="<?php echo APP_URL; ?>views/profile/edit.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit me-2"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ✨ Profile Completion Alert -->
        <?php if ($profile_completion < 100): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Complete Your Profile</h6>
                        <p class="mb-2">Your profile is <?php echo $profile_completion; ?>% complete.  Complete it to improve your chances of getting hired!</p>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: <?php echo $profile_completion; ?>%"
                                 aria-valuenow="<?php echo $profile_completion; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo $profile_completion; ?>%
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo APP_URL; ?>views/profile/edit. php" class="btn btn-sm btn-warning ms-3">
                        Complete Now
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- ==================== STATISTICS CARDS ==================== -->
        <div class="row g-3 mb-4">
            <?php
            $stat_cards = [
                [
                    'title' => 'Total Applications',
                    'value' => $stats['total_applications'] ??  0,
                    'icon' => 'paper-plane',
                    'color' => 'primary',
                    'trend' => null
                ],
                [
                    'title' => 'Pending',
                    'value' => $stats['pending'] ?? 0,
                    'icon' => 'clock',
                    'color' => 'warning',
                    'trend' => 'Awaiting review'
                ],
                [
                    'title' => 'Shortlisted',
                    'value' => $stats['shortlisted'] ?? 0,
                    'icon' => 'star',
                    'color' => 'info',
                    'trend' => 'Great progress!'
                ],
                [
                    'title' => 'Accepted',
                    'value' => $stats['accepted'] ?? 0,
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'trend' => 'Success!'
                ],
                [
                    'title' => 'Rejected',
                    'value' => $stats['rejected'] ??  0,
                    'icon' => 'times-circle',
                    'color' => 'danger',
                    'trend' => "Don't give up"
                ]
            ];
            
            foreach ($stat_cards as $card):
            ?>
                <div class="col-xl col-md-6 col-sm-6">
                    <div class="card stat-card bg-<?php echo $card['color']; ?> text-white shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-uppercase mb-1 opacity-75"><?php echo $card['title']; ?></h6>
                                    <h2 class="mb-1 fw-bold"><?php echo $card['value']; ?></h2>
                                    <?php if ($card['trend']): ?>
                                        <small class="opacity-75"><i class="fas fa-info-circle me-1"></i><?php echo $card['trend']; ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-<?php echo $card['icon']; ?> fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            
            <!-- ==================== MY APPLICATIONS ==================== -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list me-2 text-primary"></i> My Applications
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-primary"><?php echo count($applications); ?> Total</span>
                            <!-- ✨ Filter Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="? status=all">All Applications</a></li>
                                    <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                                    <li><a class="dropdown-item" href="?status=shortlisted">Shortlisted</a></li>
                                    <li><a class="dropdown-item" href="?status=accepted">Accepted</a></li>
                                    <li><a class="dropdown-item" href="?status=rejected">Rejected</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        
                        <?php if (empty($applications)): ?>
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="fas fa-inbox fa-5x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Applications Yet</h5>
                                <p class="text-muted mb-4">Start applying for jobs to track them here</p>
                                <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search me-2"></i> Browse Available Jobs
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Applications Table -->
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job Details</th>
                                            <th>Company</th>
                                            <th>Applied</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                            <tr>
                                                <td>
                                                    <div class="job-info">
                                                        <a href="<?php echo APP_URL; ?>views/jobs/detail. php?id=<?php echo $app['job_id']; ?>" 
                                                           class="text-decoration-none">
                                                            <strong class="d-block text-dark"><?php echo htmlspecialchars($app['job_title']); ?></strong>
                                                        </a>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($app['location']); ?>
                                                            <span class="mx-2">|</span>
                                                            <?php echo getJobTypeBadge($app['job_type']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (! empty($app['logo'])): ?>
                                                            <img src="<?php echo LOGO_URL .  $app['logo']; ?>" 
                                                                 alt="<?php echo htmlspecialchars($app['company_name']); ?>"
                                                                 class="company-logo-sm me-2 rounded"
                                                                 onerror="this.style.display='none'">
                                                        <?php endif; ?>
                                                        <span><?php echo htmlspecialchars($app['company_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo formatDate($app['applied_at'], 'M d, Y'); ?>
                                                        <br>
                                                        <span class="badge bg-light text-dark">
                                                            <?php echo timeAgo($app['applied_at']); ?>
                                                        </span>
                                                    </small>
                                                </td>
                                                <td><?php echo getApplicationStatusBadge($app['status']); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo APP_URL; ?>views/applications/view.php?id=<?php echo $app['id']; ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View Application">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($app['status'] === APP_STATUS_PENDING): ?>
                                                            <button class="btn btn-outline-danger" 
                                                                    onclick="confirmWithdraw(<?php echo $app['id']; ?>)"
                                                                    title="Withdraw Application">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- View All Link -->
                            <?php if (count($applications) > 5): ?>
                                <div class="card-footer bg-light text-center">
                                    <a href="<?php echo APP_URL; ?>views/applications/view.php" class="text-decoration-none">
                                        View All <?php echo count($applications); ?> Applications <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Quick Stats -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-chart-pie me-2 text-primary"></i> Application Overview
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="applicationChart" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Recent/Featured Jobs -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-fire me-2 text-danger"></i> Recommended for You
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($featured_jobs)): ?>
                                <?php foreach (array_slice($featured_jobs, 0, 5) as $job): ?>
                                    <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<?php echo $job['id']; ?>" 
                                       class="list-group-item list-group-item-action border-0">
                                        <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                            <h6 class="mb-1 fw-semibold"><?php echo truncate($job['title'], 35); ?></h6>
                                            <?php echo getJobTypeBadge($job['job_type']); ?>
                                        </div>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($job['company_name']); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo timeAgo($job['created_at']); ?>
                                            </small>
                                            <?php if (! empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                                <small class="text-success fw-bold">
                                                    <?php echo formatSalary($job['salary_min'], $job['salary_max']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                
                                <div class="list-group-item text-center border-0 bg-light">
                                    <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="text-decoration-none">
                                        View All Jobs <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="list-group-item text-center border-0">
                                    <p class="text-muted mb-0">No jobs available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-bolt me-2 text-warning"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> Browse All Jobs
                            </a>
                            <a href="<?php echo APP_URL; ?>views/profile/edit.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i> Update Profile
                            </a>
                            <a href="<?php echo APP_URL; ?>views/profile/change-password.php" class="btn btn-outline-secondary">
                                <i class="fas fa-key me-2"></i> Change Password
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</main>

<!-- ✨ Chart.js for Application Stats -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Application Status Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('applicationChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Shortlisted', 'Accepted', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $stats['pending'] ??  0; ?>,
                        <?php echo $stats['shortlisted'] ?? 0; ?>,
                        <?php echo $stats['accepted'] ??  0; ?>,
                        <?php echo $stats['rejected'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#0dcaf0',
                        '#198754',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});

// Confirm Withdraw Application
function confirmWithdraw(appId) {
    if (confirm('Are you sure you want to withdraw this application?  This action cannot be undone.')) {
        // Create form and submit
        const form = document. createElement('form');
        form. method = 'POST';
        form.action = '<?php echo APP_URL; ?>controllers/ApplicationController.php?action=delete';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = '<?php echo Session::generateCSRFToken(); ?>';
        
        const id = document.createElement('input');
        id.type = 'hidden';
        id.name = 'id';
        id.value = appId;
        
        form.appendChild(csrf);
        form.appendChild(id);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
/* Stat Cards */
.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

. stat-icon {
    font-size: 2rem;
}

/* Company Logo */
.company-logo-sm {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

/* Empty State */
.empty-state-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>