<?php
$page_title = "Browse Jobs";
$page_description = "Browse and search thousands of job opportunities in Nepal.  Filter by category, location, and job type.";
$page_keywords = "job search, find jobs, employment opportunities, job listings";
$body_class = "jobs-listing-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../models/Category.php';

// ✨ Get jobs with error handling
try {
    $jobController = JobController::index();
    $jobs = $jobController['jobs'];
    $total_jobs = $jobController['total_jobs'];
    $current_page = $jobController['current_page'];
    $keyword = $jobController['keyword'];
    $filters = $jobController['filters'];
} catch (Exception $e) {
    error_log('Jobs index error: ' . $e->getMessage());
    Session::setError('An error occurred while loading jobs');
    $jobs = [];
    $total_jobs = 0;
    $current_page = 1;
    $keyword = '';
    $filters = [];
}

// ✨ Get categories for filter
try {
    $category = new Category();
    $categories = $category->getCategoriesWithJobCount();
} catch (Exception $e) {
    error_log('Categories fetch error: ' . $e->getMessage());
    $categories = [];
}

// ✨ Calculate result range
$results_start = ($current_page - 1) * JOBS_PER_PAGE + 1;
$results_end = min($current_page * JOBS_PER_PAGE, $total_jobs);
?>

<main id="main-content" class="jobs-page">
    <div class="container mt-4 mb-5">
        
        <!-- ==================== PAGE HEADER ==================== -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-search me-2 text-primary"></i> Browse Jobs
                </h1>
                <p class="text-muted mb-0">
                    <?php if ($total_jobs > 0): ?>
                        Showing <strong><?php echo $results_start; ?>-<?php echo $results_end; ?></strong> 
                        of <strong><?php echo number_format($total_jobs); ?></strong> jobs
                        <?php if (! empty($keyword)): ?>
                            for "<strong><?php echo htmlspecialchars($keyword); ?></strong>"
                        <?php endif; ?>
                    <?php else: ?>
                        <strong>0</strong> jobs found
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- ✨ Sort Options -->
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active">
                        <i class="fas fa-clock me-1"></i> Latest
                    </button>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="fas fa-fire me-1"></i> Popular
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ==================== SEARCH AND FILTER ==================== -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-light border-0">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Search & Filter Jobs
                    <?php if (!  empty($keyword) || ! empty($filters)): ?>
                        <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-sm btn-outline-danger float-end">
                            <i class="fas fa-times me-1"></i> Clear All
                        </a>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="jobSearchForm">
                    <div class="row g-3">
                        <!-- Keyword Search -->
                        <div class="col-lg-3 col-md-6">
                            <label for="keyword" class="form-label small text-muted">
                                <i class="fas fa-search me-1"></i> Keywords
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="keyword"
                                   name="keyword" 
                                   placeholder="Job title, skills..." 
                                   value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="col-lg-2 col-md-6">
                            <label for="category_id" class="form-label small text-muted">
                                <i class="fas fa-tags me-1"></i> Category
                            </label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($filters['category_id']) && $filters['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['job_count']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Job Type Filter -->
                        <div class="col-lg-2 col-md-6">
                            <label for="job_type" class="form-label small text-muted">
                                <i class="fas fa-briefcase me-1"></i> Job Type
                            </label>
                            <select class="form-select" id="job_type" name="job_type">
                                <option value="">All Types</option>
                                <option value="full-time" <?php echo (isset($filters['job_type']) && $filters['job_type'] == 'full-time') ? 'selected' : ''; ?>>Full Time</option>
                                <option value="part-time" <?php echo (isset($filters['job_type']) && $filters['job_type'] == 'part-time') ? 'selected' : ''; ?>>Part Time</option>
                                <option value="contract" <?php echo (isset($filters['job_type']) && $filters['job_type'] == 'contract') ? 'selected' : ''; ?>>Contract</option>
                                <option value="internship" <?php echo (isset($filters['job_type']) && $filters['job_type'] == 'internship') ? 'selected' : ''; ?>>Internship</option>
                                <option value="remote" <?php echo (isset($filters['job_type']) && $filters['job_type'] == 'remote') ? 'selected' : ''; ?>>Remote</option>
                            </select>
                        </div>
                        
                        <!-- Location Filter -->
                        <div class="col-lg-2 col-md-6">
                            <label for="location" class="form-label small text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i> Location
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="location"
                                   name="location" 
                                   placeholder="City or province" 
                                   value="<?php echo isset($filters['location']) ? htmlspecialchars($filters['location']) : ''; ?>">
                        </div>
                        
                        <!-- Experience Level (Optional) -->
                        <div class="col-lg-2 col-md-6">
                            <label for="experience_level" class="form-label small text-muted">
                                <i class="fas fa-chart-line me-1"></i> Experience
                            </label>
                            <select class="form-select" id="experience_level" name="experience_level">
                                <option value="">All Levels</option>
                                <option value="entry" <?php echo (isset($filters['experience_level']) && $filters['experience_level'] == 'entry') ? 'selected' : ''; ?>>Entry Level</option>
                                <option value="mid" <?php echo (isset($filters['experience_level']) && $filters['experience_level'] == 'mid') ? 'selected' : ''; ?>>Mid Level</option>
                                <option value="senior" <?php echo (isset($filters['experience_level']) && $filters['experience_level'] == 'senior') ? 'selected' : ''; ?>>Senior Level</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div class="col-lg-1 col-md-12 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> <span class="d-none d-lg-inline">Search</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- ✨ Active Filters Display -->
        <?php if (!empty($keyword) || !empty($filters)): ?>
            <div class="active-filters mb-3">
                <span class="text-muted me-2">Active Filters:</span>
                <?php if (!empty($keyword)): ?>
                    <span class="badge bg-primary me-2">
                        Keyword: <?php echo htmlspecialchars($keyword); ?>
                        <a href="? <?php echo http_build_query(array_merge($_GET, ['keyword' => ''])); ?>" class="text-white ms-1">×</a>
                    </span>
                <?php endif; ?>
                <?php foreach ($filters as $key => $value): ?>
                    <?php if (!  empty($value)): ?>
                        <span class="badge bg-secondary me-2">
                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?>
                            <?php 
                            $new_filters = $_GET;
                            unset($new_filters[$key]);
                            ?>
                            <a href="? <?php echo http_build_query($new_filters); ?>" class="text-white ms-1">×</a>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- ==================== JOB LISTINGS ==================== -->
        <div class="row">
            <?php if (empty($jobs)): ?>
                <!-- Empty State -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-search fa-5x text-muted mb-4"></i>
                            <h4 class="mb-3">No Jobs Found</h4>
                            <p class="text-muted mb-4">
                                We couldn't find any jobs matching your criteria.   <br>
                                Try adjusting your search filters or browse all available jobs.
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="<?php echo APP_URL; ?>views/jobs/index.  php" class="btn btn-primary">
                                    <i class="fas fa-redo me-2"></i> Clear Filters
                                </a>
                                <?php if (Session::isCompany()): ?>
                                    <a href="<?php echo APP_URL; ?>views/jobs/create. php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i> Post a Job
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $index => $job): ?>
                    <div class="col-12 mb-3" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                        <div class="card job-card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    
                                    <!-- Company Logo -->
                                    <div class="col-lg-1 col-md-2 text-center mb-3 mb-md-0">
                                        <?php if (! empty($job['logo'])): ?>
                                            <img src="<?php echo LOGO_URL .  $job['logo']; ?>" 
                                                 alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                                 class="company-logo img-fluid rounded shadow-sm"
                                                 onerror="this.src='<?php echo APP_URL; ?>public/images/default-company. png'">
                                        <?php else: ?>
                                            <div class="company-logo-placeholder bg-light rounded shadow-sm">
                                                <i class="fas fa-building text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Job Details -->
                                    <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                                        <!-- Job Title -->
                                        <h5 class="card-title mb-2">
                                            <a href="<?php echo APP_URL; ?>views/jobs/detail.php?  id=<?php echo $job['id']; ?>" 
                                               class="text-decoration-none text-dark job-title-hover">
                                                <?php echo htmlspecialchars($job['title']); ?>
                                            </a>
                                        </h5>
                                        
                                        <!-- Company Name -->
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-building me-1 text-primary"></i>
                                            <strong><?php echo htmlspecialchars($job['company_name']); ?></strong>
                                        </p>
                                        
                                        <!-- Job Description -->
                                        <p class="card-text text-muted small mb-3">
                                            <?php echo truncateHTML($job['description'], 150); ?>
                                        </p>
                                        
                                        <!-- Job Meta -->
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                <?php echo htmlspecialchars($job['location']); ?>
                                            </span>
                                            
                                            <?php echo getJobTypeBadge($job['job_type']); ?>
                                            
                                            <?php if (!empty($job['category_name'])): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($job['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (! empty($job['experience_level'])): ?>
                                                <span class="badge bg-info text-dark">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    <?php echo ucfirst($job['experience_level']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <!-- ✨ Application Count (if available) -->
                                            <?php if (isset($job['application_count']) && $job['application_count'] > 0): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?php echo $job['application_count']; ?> applicants
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Column -->
                                    <div class="col-lg-3 col-md-3 text-md-end">
                                        <!-- Posted Time -->
                                        <p class="text-muted mb-2 small">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo timeAgo($job['created_at']); ?>
                                        </p>
                                        
                                        <!-- Salary -->
                                        <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                            <p class="fw-bold text-success mb-3 fs-6">
                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                <?php echo formatSalary($job['salary_min'], $job['salary_max']); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-muted mb-3 small">Salary: Negotiable</p>
                                        <?php endif; ?>
                                        
                                        <!-- ✨ Deadline Warning -->
                                        <?php if (!empty($job['deadline'])): ?>
                                            <?php 
                                            $days_left = daysUntilDeadline($job['deadline']);
                                            if ($days_left >= 0 && $days_left <= 7): 
                                            ?>
                                                <p class="text-danger small mb-2">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <?php echo $days_left == 0 ? 'Ends today' : $days_left . ' days left'; ?>
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <!-- Actions -->
                                        <div class="d-grid gap-2">
                                            <a href="<?php echo APP_URL; ?>views/jobs/detail.php? id=<?php echo $job['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-arrow-right me-1"></i> View Details
                                            </a>
                                            
                                            <!-- ✨ Quick Apply (if logged in as job seeker) -->
                                            <?php if (Session::isJobSeeker()): ?>
                                                <a href="<?php echo APP_URL; ?>views/applications/create.php?job_id=<?php echo $job['id']; ?>" 
                                                   class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-paper-plane me-1"></i> Quick Apply
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- ✨ Save Job (future feature) -->
                                            <!-- <button class="btn btn-outline-secondary btn-sm">
                                                <i class="far fa-bookmark me-1"></i> Save
                                            </button> -->
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- ==================== PAGINATION ==================== -->
        <?php if ($total_jobs > JOBS_PER_PAGE): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <?php 
                    // Build pagination URL with filters preserved
                    $base_url = APP_URL . 'views/jobs/index.php';
                    echo pagination($current_page, $total_jobs, JOBS_PER_PAGE, $base_url); 
                    ?>
                </div>
            </div>
            
            <!-- ✨ Results Summary -->
            <div class="row mt-3">
                <div class="col-12 text-center text-muted">
                    <small>
                        Showing <?php echo $results_start; ?>-<?php echo $results_end; ?> 
                        of <?php echo number_format($total_jobs); ?> jobs
                    </small>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</main>

<!-- ✨ Additional Styles -->
<style>
/* Company Logo */
.company-logo {
    width: 70px;
    height: 70px;
    object-fit: cover;
}

. company-logo-placeholder {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Job Card Hover Effect */
.job-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.job-card:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !  important;
    border-left-color: #0d6efd;
}

. job-title-hover {
    transition: color 0.2s ease;
}

.job-title-hover:hover {
    color: #0d6efd ! important;
}

/* Active Filters */
.active-filters . badge a {
    text-decoration: none;
    font-weight: bold;
}

. active-filters .  badge a:hover {
    opacity: 0.8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .  company-logo,
    . company-logo-placeholder {
        width: 50px;
        height: 50px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>