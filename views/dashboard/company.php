<?php
$page_title = "Company Dashboard";
$page_description = "Manage your job postings and review applications";
$body_class = "dashboard-page company-dashboard";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';
require_once __DIR__ . '/../../controllers/CompanyController.php';

// ✨ Require company login
Session::requireCompany();

// ✨ Get dashboard data with error handling
try {
    $dashboardData = DashboardController::companyDashboard();
    
    if (!  $dashboardData) {
        throw new Exception('Failed to load dashboard data');
    }
    
    $user = $dashboardData['user'] ?? [];
    $company = $dashboardData['company'];
    $jobs = $dashboardData['jobs'] ?? [];
    $active_jobs = $dashboardData['active_jobs'] ?? [];
    $draft_jobs = $dashboardData['draft_jobs'] ?? [];
    $closed_jobs = $dashboardData['closed_jobs'] ?? [];
    $job_stats = $dashboardData['job_stats'] ?? [];
    $recent_applications = $dashboardData['recent_applications'] ?? [];
    $pending_applications = $dashboardData['pending_applications'] ?? [];
    $app_stats = $dashboardData['app_stats'] ?? [];
    $profile_completion = $dashboardData['profile_completion'] ?? 0;
} catch (Exception $e) {
    error_log('Company dashboard error: ' . $e->getMessage());
    Session::setError('An error occurred while loading your dashboard');
    redirect(APP_URL . 'views/company/profile.php');
    exit;
}
?>

<main id="main-content" class="dashboard-container">
    <div class="container-fluid mt-4 mb-5">
        
        <!-- ==================== HEADER ==================== -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <?php if (! empty($company['logo'])): ?>
                        <img src="<?php echo LOGO_URL .  $company['logo']; ?>" 
                             alt="<?php echo htmlspecialchars($company['company_name']); ?>"
                             class="company-logo-header me-3 rounded shadow-sm"
                             onerror="this.style.display='none'">
                    <?php endif; ?>
                    <div>
                        <h1 class="display-6 fw-bold mb-1">
                            <?php echo htmlspecialchars($company['company_name']); ?>
                        </h1>
                        <p class="text-muted mb-0 lead">Manage your job postings and review applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo APP_URL; ?>views/jobs/create.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> Post New Job
                    </a>
                    <a href="<?php echo APP_URL; ?>views/company/profile.php" class="btn btn-outline-primary">
                        <i class="fas fa-building me-2"></i> Edit Profile
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
                        <h6 class="alert-heading mb-1">Complete Your Company Profile</h6>
                        <p class="mb-2">Your profile is <?php echo $profile_completion; ?>% complete.  Complete it to attract more candidates!</p>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: <?php echo $profile_completion; ?>%">
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo APP_URL; ?>views/company/profile. php" class="btn btn-sm btn-warning ms-3">
                        Complete Now
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- ✨ Pending Applications Alert -->
        <?php if (! empty($pending_applications)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                You have <strong><?php echo count($pending_applications); ?></strong> pending application(s) awaiting review.
                <a href="#recent-applications" class="alert-link ms-2">Review now</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- ==================== STATISTICS CARDS ==================== -->
        <div class="row g-3 mb-4">
            <?php
            $stat_cards = [
                [
                    'title' => 'Total Jobs',
                    'value' => $job_stats['total_jobs'] ?? 0,
                    'icon' => 'briefcase',
                    'color' => 'primary',
                    'link' => APP_URL . 'views/jobs/my-jobs.php',
                    'trend' => null
                ],
                [
                    'title' => 'Active Jobs',
                    'value' => $job_stats['active_jobs'] ??  0,
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'link' => APP_URL .  'views/jobs/my-jobs.php? status=active',
                    'trend' => 'Currently hiring'
                ],
                [
                    'title' => 'Draft Jobs',
                    'value' => count($draft_jobs),
                    'icon' => 'edit',
                    'color' => 'secondary',
                    'link' => APP_URL . 'views/jobs/my-jobs.php? status=draft',
                    'trend' => 'Ready to publish'
                ],
                [
                    'title' => 'Closed Jobs',
                    'value' => count($closed_jobs),
                    'icon' => 'times-circle',
                    'color' => 'warning',
                    'link' => APP_URL . 'views/jobs/my-jobs.php?status=closed',
                    'trend' => null
                ],
                [
                    'title' => 'Total Applications',
                    'value' => $job_stats['total_applications'] ??  0,
                    'icon' => 'users',
                    'color' => 'info',
                    'link' => APP_URL . 'views/applications/received.php',
                    'trend' => 'All time'
                ],
                [
                    'title' => 'Pending Review',
                    'value' => count($pending_applications),
                    'icon' => 'hourglass-half',
                    'color' => 'danger',
                    'link' => APP_URL . 'views/applications/received.php?status=pending',
                    'trend' => 'Needs attention'
                ]
            ];
            
            foreach ($stat_cards as $card):
            ?>
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <a href="<?php echo $card['link']; ?>" class="text-decoration-none">
                        <div class="card stat-card bg-<?php echo $card['color']; ?> text-white shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-uppercase mb-1 opacity-75 small"><?php echo $card['title']; ?></h6>
                                        <h2 class="mb-1 fw-bold"><?php echo $card['value']; ?></h2>
                                        <?php if ($card['trend']): ?>
                                            <small class="opacity-75"><?php echo $card['trend']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <i class="fas fa-<?php echo $card['icon']; ?> fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            
            <!-- ==================== MY JOB POSTINGS ==================== -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-briefcase me-2 text-primary"></i> My Job Postings
                        </h5>
                        <div class="d-flex gap-2">
                            <!-- ✨ Filter Tabs -->
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="? filter=all" class="btn btn-outline-secondary <?php echo (! isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'active' : ''; ?>">
                                    All (<?php echo count($jobs); ?>)
                                </a>
                                <a href="?filter=active" class="btn btn-outline-success <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'active') ? 'active' : ''; ?>">
                                    Active (<?php echo count($active_jobs); ?>)
                                </a>
                                <a href="?filter=draft" class="btn btn-outline-secondary <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'draft') ? 'active' : ''; ?>">
                                    Draft (<?php echo count($draft_jobs); ?>)
                                </a>
                            </div>
                            <a href="<?php echo APP_URL; ?>views/jobs/create. php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> New Job
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        
                        <?php if (empty($jobs)): ?>
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="fas fa-briefcase fa-5x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Job Postings Yet</h5>
                                <p class="text-muted mb-4">Create your first job posting to start attracting talented candidates</p>
                                <a href="<?php echo APP_URL; ?>views/jobs/create.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus me-2"></i> Post Your First Job
                                </a>
                            </div>
                        <?php else: ?>
                            <?php
                            // Filter jobs based on GET parameter
                            $filtered_jobs = $jobs;
                            if (isset($_GET['filter'])) {
                                switch ($_GET['filter']) {
                                    case 'active':
                                        $filtered_jobs = $active_jobs;
                                        break;
                                    case 'draft':
                                        $filtered_jobs = $draft_jobs;
                                        break;
                                    case 'closed':
                                        $filtered_jobs = $closed_jobs;
                                        break;
                                }
                            }
                            ?>
                            
                            <!-- Jobs Table -->
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Status</th>
                                            <th class="text-center">Applications</th>
                                            <th>Posted</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filtered_jobs as $job): ?>
                                            <tr>
                                                <td>
                                                    <div class="job-info">
                                                        <strong class="d-block"><?php echo htmlspecialchars($job['title']); ?></strong>
                                                        <small class="text-muted">
                                                            <?php echo getJobTypeBadge($job['job_type']); ?>
                                                            <?php if (! empty($job['category_name'])): ?>
                                                                <span class="badge bg-secondary ms-1">
                                                                    <?php echo htmlspecialchars($job['category_name']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <span class="mx-1">|</span>
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($job['location']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td><?php echo getJobStatusBadge($job['status']); ?></td>
                                                <td class="text-center">
                                                    <?php if ($job['application_count'] > 0): ?>
                                                        <a href="<?php echo APP_URL; ?>views/applications/received.php?job_id=<?php echo $job['id']; ?>" 
                                                           class="badge bg-primary text-decoration-none">
                                                            <?php echo $job['application_count']; ?> 
                                                            <?php echo pluralize($job['application_count'], 'application'); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No applications</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo formatDate($job['created_at'], 'M d, Y'); ?>
                                                        <br>
                                                        <span class="badge bg-light text-dark">
                                                            <?php echo timeAgo($job['created_at']); ?>
                                                        </span>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<?php echo $job['id']; ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View Job">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>views/jobs/edit.php?id=<?php echo $job['id']; ?>" 
                                                           class="btn btn-outline-warning" 
                                                           title="Edit Job">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $job['id']; ?>, '<?php echo htmlspecialchars($job['title']); ?>')" 
                                                                title="Delete Job">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (count($jobs) > 10): ?>
                                <div class="card-footer bg-light text-center">
                                    <a href="<?php echo APP_URL; ?>views/jobs/my-jobs.php" class="text-decoration-none">
                                        View All <?php echo count($jobs); ?> Jobs <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Application Stats Chart -->
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
                
                <!-- Recent Applications -->
                <div class="card shadow-sm border-0 mb-4" id="recent-applications">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-users me-2 text-success"></i> Recent Applications
                        </h6>
                        <?php if (!empty($pending_applications)): ?>
                            <span class="badge bg-danger"><?php echo count($pending_applications); ?> Pending</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($recent_applications)): ?>
                                <?php foreach (array_slice($recent_applications, 0, 5) as $app): ?>
                                    <a href="<?php echo APP_URL; ?>views/applications/view.php?id=<?php echo $app['id']; ?>" 
                                       class="list-group-item list-group-item-action border-0">
                                        <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                            <h6 class="mb-1 fw-semibold">
                                                <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                                            </h6>
                                            <?php echo getApplicationStatusBadge($app['status']); ?>
                                        </div>
                                        <p class="mb-1 small text-muted">
                                            <strong><?php echo truncate($app['job_title'], 35); ?></strong>
                                        </p>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo timeAgo($app['applied_at']); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                                
                                <?php if (count($recent_applications) > 5): ?>
                                    <div class="list-group-item text-center border-0 bg-light">
                                        <a href="<?php echo APP_URL; ?>views/applications/received.php" class="text-decoration-none">
                                            View All Applications <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center border-0">
                                    <p class="text-muted mb-0 py-3">No applications yet</p>
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
                            <a href="<?php echo APP_URL; ?>views/jobs/create.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i> Post New Job
                            </a>
                            <a href="<?php echo APP_URL; ?>views/applications/received.php" class="btn btn-primary">
                                <i class="fas fa-inbox me-2"></i> View All Applications
                            </a>
                            <a href="<?php echo APP_URL; ?>views/company/profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-building me-2"></i> Edit Company Profile
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Application Status Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('applicationChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Reviewed', 'Shortlisted', 'Accepted', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $app_stats['pending'] ?? 0; ?>,
                        <?php echo $app_stats['reviewed'] ?? 0; ?>,
                        <?php echo $app_stats['shortlisted'] ?? 0; ?>,
                        <?php echo $app_stats['accepted'] ?? 0; ?>,
                        <?php echo $app_stats['rejected'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#0dcaf0',
                        '#0d6efd',
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

// Confirm Delete Job
function confirmDelete(jobId, jobTitle) {
    if (confirm(`Are you sure you want to delete "${jobTitle}"?  This will also delete all associated applications.  This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo APP_URL; ?>controllers/JobController.php?action=delete';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = '<?php echo Session::generateCSRFToken(); ?>';
        
        const id = document.createElement('input');
        id.type = 'hidden';
        id.name = 'id';
        id.value = jobId;
        
        form.appendChild(csrf);
        form.appendChild(id);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
/* Company Logo Header */
.company-logo-header {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

/* Stat Cards */
.stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

. stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2) ! important;
}

/* Empty State */
.empty-state-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>