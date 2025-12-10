<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../models/Application.php';

// ✨ Get job ID with validation
if (! isset($_GET['id']) || empty($_GET['id'])) {
    Session::setError('Invalid job ID');
    redirect(APP_URL . 'views/jobs/index.php');
    exit;
}

$job_id = (int)$_GET['id'];

// ✨ Get job details with error handling
try {
    $job = JobController::show($job_id);
    
    if (!$job) {
        Session::setError('Job not found');
        redirect(APP_URL .  'views/jobs/index. php');
        exit;
    }
} catch (Exception $e) {
    error_log('Job detail error: ' . $e->getMessage());
    Session::setError('An error occurred while loading job details');
    redirect(APP_URL . 'views/jobs/index.php');
    exit;
}

// ✨ Check if user already applied
$already_applied = false;
$can_apply_check = ['can_apply' => true, 'reason' => ''];

if (Session::isLoggedIn() && Session::isJobSeeker()) {
    try {
        $application = new Application();
        $application->job_id = $job_id;
        $application->user_id = Session::getUserId();
        
        $already_applied = $application->checkDuplicate();
        
        // Use canApply() method for comprehensive check
        if (! $already_applied) {
            $can_apply_check = $application->canApply();
        }
    } catch (Exception $e) {
        error_log('Application check error: ' . $e->getMessage());
    }
}

// ✨ Check deadline status
$deadline_passed = isDeadlinePassed($job['deadline']);
$days_until_deadline = ! empty($job['deadline']) ? daysUntilDeadline($job['deadline']) : null;

// Set page meta
$page_title = $job['title'] . ' at ' . $job['company_name'];
$page_description = truncateHTML($job['description'], 160);
$page_keywords = $job['title'] . ', ' . $job['company_name'] . ', jobs in ' . $job['location'];
$body_class = "job-detail-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main id="main-content" class="job-detail-page">
    <div class="container mt-4 mb-5">
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ==================== BREADCRUMB ==================== -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>views/jobs/index.php">Jobs</a></li>
                <?php if (! empty($job['category_name'])): ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo APP_URL; ?>views/jobs/index.php? category_id=<?php echo $job['category_id']; ?>">
                            <?php echo htmlspecialchars($job['category_name']); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo truncate($job['title'], 50); ?>
                </li>
            </ol>
        </nav>
        
        <!-- ==================== JOB HEADER ==================== -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    
                    <!-- Company Logo -->
                    <div class="col-lg-2 col-md-3 text-center mb-3 mb-md-0">
                        <?php if (!empty($job['logo'])): ?>
                            <img src="<?php echo LOGO_URL .  $job['logo']; ?>" 
                                 alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                 class="company-logo-large img-fluid rounded shadow"
                                 onerror="this.src='<?php echo APP_URL; ?>public/images/default-company. png'">
                        <?php else: ?>
                            <div class="company-logo-large-placeholder bg-light rounded shadow">
                                <i class="fas fa-building fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Job Info -->
                    <div class="col-lg-7 col-md-6 mb-3 mb-md-0">
                        <h1 class="h3 fw-bold mb-3"><?php echo htmlspecialchars($job['title']); ?></h1>
                        
                        <p class="text-muted mb-3">
                            <i class="fas fa-building me-2 text-primary"></i>
                            <strong><?php echo htmlspecialchars($job['company_name']); ?></strong>
                        </p>
                        
                        <!-- Job Badges -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                <?php echo htmlspecialchars($job['location']); ?>
                            </span>
                            
                            <?php echo getJobTypeBadge($job['job_type']); ?>
                            
                            <?php if (!empty($job['category_name'])): ?>
                                <span class="badge bg-secondary px-3 py-2">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($job['category_name']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($job['experience_level'])): ?>
                                <span class="badge bg-info text-dark px-3 py-2">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo ucfirst($job['experience_level']); ?> Level
                                </span>
                            <?php endif; ?>
                            
                            <?php echo getJobStatusBadge($job['status']); ?>
                        </div>
                        
                        <!-- Meta Info -->
                        <div class="text-muted small">
                            <i class="far fa-clock me-1"></i>
                            Posted <?php echo timeAgo($job['created_at']); ?>
                            
                            <?php if (!empty($job['deadline'])): ?>
                                <span class="ms-3 <?php echo $deadline_passed ? 'text-danger' : ($days_until_deadline <= 7 ? 'text-warning' : 'text-success'); ?>">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    <?php if ($deadline_passed): ?>
                                        Deadline passed
                                    <?php elseif ($days_until_deadline !== null): ?>
                                        <?php echo $days_until_deadline == 0 ? 'Last day to apply!' : $days_until_deadline .  ' days left'; ?>
                                    <?php else: ?>
                                        Deadline: <?php echo formatDate($job['deadline']); ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Column -->
                    <div class="col-lg-3 col-md-3 text-md-end">
                        <!-- Salary -->
                        <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                            <div class="salary-box bg-success bg-opacity-10 border border-success rounded p-3 mb-3">
                                <small class="text-muted d-block mb-1">Salary Range</small>
                                <div class="h5 text-success mb-0 fw-bold">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <?php echo formatSalary($job['salary_min'], $job['salary_max']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Apply Button -->
                        <div class="d-grid gap-2">
                            <?php if (Session::isJobSeeker()): ?>
                                <?php if ($already_applied): ?>
                                    <button class="btn btn-secondary btn-lg" disabled>
                                        <i class="fas fa-check-circle me-2"></i> Already Applied
                                    </button>
                                    <a href="<?php echo APP_URL; ?>views/dashboard/jobseeker.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-2"></i> View Application
                                    </a>
                                <?php elseif (! $can_apply_check['can_apply']): ?>
                                    <button class="btn btn-danger btn-lg" disabled title="<?php echo htmlspecialchars($can_apply_check['reason']); ?>">
                                        <i class="fas fa-times-circle me-2"></i> <?php echo $can_apply_check['reason']; ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo APP_URL; ?>views/applications/create.php? job_id=<?php echo $job['id']; ?>" 
                                       class="btn btn-primary btn-lg pulse-button">
                                        <i class="fas fa-paper-plane me-2"></i> Apply Now
                                    </a>
                                <?php endif; ?>
                                
                            <?php elseif (Session::isCompany()): ?>
                                <?php 
                                require_once __DIR__ . '/../../models/Company.php';
                                $company = new Company();
                                $userCompany = $company->getByUserId(Session::getUserId());
                                
                                if ($userCompany && $job['company_id'] == $userCompany['id']):
                                ?>
                                    <a href="<?php echo APP_URL; ?>views/jobs/edit.php?id=<?php echo $job['id']; ?>" 
                                       class="btn btn-warning btn-lg">
                                        <i class="fas fa-edit me-2"></i> Edit Job
                                    </a>
                                    <a href="<?php echo APP_URL; ?>controllers/ApplicationController.php?action=view&job_id=<?php echo $job['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-users me-2"></i> View Applications
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-lg" disabled>
                                        <i class="fas fa-info-circle me-2"></i> Company Account
                                    </button>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <a href="<?php echo APP_URL; ?>views/auth/login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login to Apply
                                </a>
                                <a href="<?php echo APP_URL; ?>views/auth/register.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-user-plus me-2"></i> Create Account
                                </a>
                            <?php endif; ?>
                            
                            <!-- Save Job (future feature) -->
                            <!-- <button class="btn btn-outline-secondary">
                                <i class="far fa-bookmark me-2"></i> Save Job
                            </button> -->
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <div class="row">
            
            <!-- ==================== MAIN CONTENT ==================== -->
            <div class="col-lg-8">
                
                <!-- Job Description -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i> Job Description
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="job-description">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Requirements -->
                <?php if (!empty($job['requirements'])): ?>
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list-check me-2"></i> Requirements & Qualifications
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="job-requirements">
                                <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- ✨ Additional Company Description -->
                <?php if (!empty($job['company_description'])): ?>
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2"></i> About <?php echo htmlspecialchars($job['company_name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($job['company_description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Job Overview -->
                <div class="card shadow-sm mb-4 border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i> Job Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded me-3">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Job Type</small>
                                        <strong><?php echo ucwords(str_replace('-', ' ', $job['job_type'])); ?></strong>
                                    </div>
                                </div>
                            </li>
                            
                            <?php if (!empty($job['experience_level'])): ?>
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-success bg-opacity-10 text-success rounded me-3">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Experience</small>
                                            <strong><?php echo ucfirst($job['experience_level']); ?> Level</strong>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                            
                            <li class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-danger bg-opacity-10 text-danger rounded me-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Location</small>
                                        <strong><?php echo htmlspecialchars($job['location']); ?></strong>
                                    </div>
                                </div>
                            </li>
                            
                            <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded me-3">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Salary Range</small>
                                            <strong><?php echo formatSalary($job['salary_min'], $job['salary_max']); ?></strong>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                            
                            <li class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-info bg-opacity-10 text-info rounded me-3">
                                        <i class="far fa-calendar"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Posted Date</small>
                                        <strong><?php echo formatDate($job['created_at'], 'M d, Y'); ?></strong>
                                    </div>
                                </div>
                            </li>
                            
                            <?php if (!empty($job['deadline'])): ?>
                                <li class="mb-0">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-<?php echo $deadline_passed ? 'danger' : 'success'; ?> bg-opacity-10 text-<?php echo $deadline_passed ?  'danger' : 'success'; ?> rounded me-3">
                                            <i class="fas fa-calendar-times"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Application Deadline</small>
                                            <strong class="<?php echo $deadline_passed ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo formatDate($job['deadline'], 'M d, Y'); ?>
                                            </strong>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Company Information -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i> Company Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                        
                        <?php if (!empty($job['company_location'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <?php echo htmlspecialchars($job['company_location']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['website'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-globe me-2 text-primary"></i>
                                <a href="<?php echo htmlspecialchars($job['website']); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="text-decoration-none">
                                    Visit Website <i class="fas fa-external-link-alt ms-1 small"></i>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['industry'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-industry me-2 text-primary"></i>
                                <?php echo htmlspecialchars($job['industry']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['company_size'])): ?>
                            <p class="mb-0">
                                <i class="fas fa-users me-2 text-primary"></i>
                                <?php echo htmlspecialchars($job['company_size']); ?> employees
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Share Job -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-share-alt me-2"></i> Share This Job
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php 
                            $job_url = APP_URL . 'views/jobs/detail.php?id=' .  $job['id'];
                            $job_title = htmlspecialchars($job['title']);
                            ?>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($job_url); ?>" 
                               target="_blank" 
                               class="btn btn-facebook">
                                <i class="fab fa-facebook me-2"></i> Share on Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($job_url); ?>&text=<?php echo urlencode($job_title); ?>" 
                               target="_blank" 
                               class="btn btn-twitter">
                                <i class="fab fa-twitter me-2"></i> Share on Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($job_url); ?>" 
                               target="_blank" 
                               class="btn btn-linkedin">
                                <i class="fab fa-linkedin me-2"></i> Share on LinkedIn
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode('Job: ' . $job_title); ?>&body=<?php echo urlencode('Check out this job opportunity: ' . $job_url); ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i> Share via Email
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Back to Listings -->
        <div class="row mt-5">
            <div class="col-12">
                <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i> Go Back
                </a>
                <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Browse More Jobs
                </a>
            </div>
        </div>
        
    </div>
</main>

<!-- ✨ Additional Styles -->
<style>
/* Company Logo */
.company-logo-large {
    max-width: 120px;
    max-height: 120px;
    object-fit: contain;
}

. company-logo-large-placeholder {
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Icon Box */
.icon-box {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Pulse Button Animation */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

.pulse-button {
    animation: pulse 2s infinite;
}

/* Social Share Buttons */
.btn-facebook {
    background: #3b5998;
    color: white;
    border: none;
}

.btn-facebook:hover {
    background: #2d4373;
    color: white;
}

. btn-twitter {
    background: #1da1f2;
    color: white;
    border: none;
}

.btn-twitter:hover {
    background: #0c85d0;
    color: white;
}

.btn-linkedin {
    background: #0077b5;
    color: white;
    border: none;
}

.btn-linkedin:hover {
    background: #005582;
    color: white;
}

/* Job Description Formatting */
.job-description,
.job-requirements {
    line-height: 1.8;
    font-size: 1.05rem;
}

/* Salary Box */
.salary-box {
    text-align: center;
}

/* Sticky Sidebar */
@media (min-width: 992px) {
    .sticky-top {
        position: sticky;
        top: 80px;
        z-index: 100;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>