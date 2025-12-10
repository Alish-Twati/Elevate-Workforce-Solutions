<?php
$page_title = "Find Your Dream Job";
$page_description = "Discover thousands of job opportunities in Nepal.   Connect with top companies and find your perfect career match.";
$page_keywords = "jobs in Nepal, job portal, career opportunities, employment, job search";
$body_class = "home-page";

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/navbar.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Company.php';

// ✨ Get recent/featured jobs with error handling
try {
    $job = new Job();
    $recent_jobs = $job->getRecentJobs(6);
    $featured_jobs = $job->getFeaturedJobs(6);
    $total_jobs = $job->getTotalCount();
} catch (Exception $e) {
    error_log('Home page - Job fetch error: ' . $e->getMessage());
    $recent_jobs = [];
    $featured_jobs = [];
    $total_jobs = 0;
}

// ✨ Get categories with job count
try {
    $category = new Category();
    $categories = $category->getCategoriesWithJobCount(8); // Top 8
} catch (Exception $e) {
    error_log('Home page - Category fetch error: ' . $e->getMessage());
    $categories = [];
}

// ✨ Get statistics
try {
    $company = new Company();
    $total_companies = $company->getTotalCount();
} catch (Exception $e) {
    $total_companies = 0;
}

// ✨ Dynamic stats (you can make these real from database)
$stats = [
    'jobs' => $total_jobs,
    'companies' => $total_companies,
    'jobseekers' => 10000, // TODO: Get from User model
    'hired' => 5000 // TODO: Track successful hires
];
?>

<main id="main-content">

<!-- ==================== HERO SECTION ==================== -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInUp">
                    Find Your Dream Job Today
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    Discover thousands of job opportunities with all the information you need.   
                    It's your future.  Come find it.  Manage all your job applications from start to finish.
                </p>
                
                <!-- ✨ Quick Stats -->
                <div class="d-flex gap-4 mb-4 text-white-50">
                    <div>
                        <strong class="d-block text-white fs-4"><?php echo formatNumber($stats['jobs']); ?>+</strong>
                        <small>Active Jobs</small>
                    </div>
                    <div>
                        <strong class="d-block text-white fs-4"><?php echo formatNumber($stats['companies']); ?>+</strong>
                        <small>Companies</small>
                    </div>
                    <div>
                        <strong class="d-block text-white fs-4"><?php echo formatNumber($stats['hired']); ?>+</strong>
                        <small>Success Stories</small>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-3 animate__animated animate__fadeInUp animate__delay-2s">
                    <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-search me-2"></i> Browse Jobs
                    </a>
                    <?php if (! Session::isLoggedIn()): ?>
                        <a href="<?php echo APP_URL; ?>views/auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i> Get Started Free
                        </a>
                    <?php else: ?>
                        <a href="<?php echo APP_URL; ?>controllers/DashboardController.php? action=redirect" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <?php if (file_exists(__DIR__ . '/../public/images/hero-image.svg')): ?>
                    <img src="<?php echo APP_URL; ?>public/images/hero-image. svg" 
                         alt="Find Your Dream Job" 
                         class="img-fluid animate__animated animate__fadeIn">
                <?php else: ?>
                    <div class="bg-white bg-opacity-10 rounded-3 p-5 text-center">
                        <i class="fas fa-briefcase fa-5x mb-3"></i>
                        <h3>Your Career Starts Here</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==================== QUICK SEARCH ==================== -->
<section class="search-section bg-light py-4 shadow-sm">
    <div class="container">
        <form action="<?php echo APP_URL; ?>views/jobs/index.php" method="GET" class="job-search-form">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="searchKeyword" class="form-label small text-muted mb-1">
                        <i class="fas fa-search me-1"></i> What
                    </label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="searchKeyword"
                           name="keyword" 
                           placeholder="Job title, keywords, or company"
                           value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label for="searchLocation" class="form-label small text-muted mb-1">
                        <i class="fas fa-map-marker-alt me-1"></i> Where
                    </label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="searchLocation"
                           name="location" 
                           placeholder="City or province"
                           value="<?php echo isset($_GET['location']) ?  htmlspecialchars($_GET['location']) : ''; ?>">
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label for="searchCategory" class="form-label small text-muted mb-1">
                        <i class="fas fa-tags me-1"></i> Category
                    </label>
                    <select class="form-select form-select-lg" id="searchCategory" name="category_id">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                    <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['job_count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- ==================== STATISTICS SECTION ==================== -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row g-4">
            <?php
            $stat_items = [
                ['icon' => 'briefcase', 'color' => 'primary', 'count' => $stats['jobs'], 'label' => 'Active Jobs', 'suffix' => '+'],
                ['icon' => 'building', 'color' => 'success', 'count' => $stats['companies'], 'label' => 'Companies', 'suffix' => '+'],
                ['icon' => 'users', 'color' => 'warning', 'count' => $stats['jobseekers'], 'label' => 'Job Seekers', 'suffix' => '+'],
                ['icon' => 'check-circle', 'color' => 'info', 'count' => $stats['hired'], 'label' => 'Successfully Hired', 'suffix' => '+']
            ];
            
            foreach ($stat_items as $item):
            ?>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-hover h-100 text-center stat-card" data-aos="zoom-in">
                        <div class="card-body p-4">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-<?php echo $item['icon']; ?> fa-3x text-<?php echo $item['color']; ?>"></i>
                            </div>
                            <h3 class="fw-bold mb-2 counter" data-count="<?php echo $item['count']; ?>">
                                0<?php echo $item['suffix']; ?>
                            </h3>
                            <p class="text-muted mb-0"><?php echo $item['label']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== FEATURED JOBS ==================== -->
<section class="featured-jobs-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-2">
                    <i class="fas fa-star text-warning me-2"></i> Featured Jobs
                </h2>
                <p class="text-muted lead">Explore the latest and most popular job opportunities</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (! empty($recent_jobs)): ?>
                <?php foreach ($recent_jobs as $index => $job): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card h-100 job-card shadow-sm border-0">
                            <div class="card-body p-4">
                                <!-- Company Logo & Info -->
                                <div class="d-flex align-items-start mb-3">
                                    <?php if (! empty($job['logo'])): ?>
                                        <img src="<?php echo LOGO_URL .  $job['logo']; ?>" 
                                             alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                             class="company-logo me-3 rounded shadow-sm" 
                                             onerror="this.src='<?php echo APP_URL; ?>public/images/default-company. png'">
                                    <?php else: ?>
                                        <div class="company-logo-placeholder me-3 rounded shadow-sm">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </small>
                                    </div>
                                    <!-- ✨ Bookmark Button (if logged in) -->
                                    <?php if (Session::isJobSeeker()): ?>
                                        <button class="btn btn-sm btn-outline-secondary border-0" title="Save Job">
                                            <i class="far fa-bookmark"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Job Title -->
                                <h5 class="card-title mb-3">
                                    <a href="<?php echo APP_URL; ?>views/jobs/detail.php? id=<?php echo $job['id']; ?>" 
                                       class="text-decoration-none text-dark job-title-link">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </a>
                                </h5>
                                
                                <!-- Job Description -->
                                <p class="card-text text-muted small mb-3">
                                    <?php echo truncateHTML($job['description'], 100); ?>
                                </p>
                                
                                <!-- Job Meta -->
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php echo getJobTypeBadge($job['job_type']); ?>
                                    <?php if (! empty($job['category_name'])): ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($job['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Salary & Time -->
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        <?php echo formatSalary($job['salary_min'], $job['salary_max']); ?>
                                    </span>
                                    <span title="<?php echo formatDate($job['created_at']); ?>">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo timeAgo($job['created_at']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="card-footer bg-white border-top">
                                <a href="<?php echo APP_URL; ?>views/jobs/detail. php?id=<?php echo $job['id']; ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No jobs available at the moment</h5>
                        <p class="text-muted">Check back soon for new opportunities!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- View All Button -->
        <?php if (! empty($recent_jobs)): ?>
            <div class="text-center mt-5">
                <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-th-large me-2"></i> View All <?php echo formatNumber($stats['jobs']); ?>+ Jobs
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ==================== BROWSE BY CATEGORY ==================== -->
<section class="categories-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-2">Browse by Category</h2>
                <p class="text-muted lead">Find jobs in your preferred industry</p>
            </div>
        </div>
        
        <div class="row g-3">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $cat): ?>
                    <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="<?php echo $index * 50; ?>">
                        <a href="<?php echo APP_URL; ?>views/jobs/index. php?category_id=<?php echo $cat['id']; ?>" 
                           class="category-card card text-center h-100 text-decoration-none border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="category-icon mb-3">
                                    <i class="fas fa-folder fa-3x text-primary"></i>
                                </div>
                                <h6 class="card-title text-dark fw-semibold mb-2">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </h6>
                                <p class="text-primary mb-0">
                                    <strong><?php echo $cat['job_count']; ?></strong> 
                                    <small class="text-muted"><?php echo pluralize($cat['job_count'], 'Job'); ?></small>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== HOW IT WORKS ==================== -->
<section class="how-it-works-section py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-2">How It Works</h2>
                <p class="text-muted lead">Get hired in 3 simple steps</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            $steps = [
                ['icon' => 'user-plus', 'color' => 'primary', 'step' => '1', 'title' => 'Create Account', 'desc' => 'Sign up and create your professional profile in minutes'],
                ['icon' => 'search', 'color' => 'success', 'step' => '2', 'title' => 'Find Jobs', 'desc' => 'Browse thousands of jobs and find your perfect match'],
                ['icon' => 'paper-plane', 'color' => 'warning', 'step' => '3', 'title' => 'Apply & Get Hired', 'desc' => 'Submit your application and start your new career']
            ];
            
            foreach ($steps as $index => $step):
            ?>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 200; ?>">
                    <div class="how-step text-center p-4">
                        <div class="step-number mb-3">
                            <div class="step-circle bg-<?php echo $step['color']; ?> text-white mx-auto">
                                <i class="fas fa-<?php echo $step['icon']; ?> fa-2x"></i>
                            </div>
                            <span class="step-badge badge bg-<?php echo $step['color']; ?> mt-2">
                                Step <?php echo $step['step']; ?>
                            </span>
                        </div>
                        <h5 class="fw-bold mb-3"><?php echo $step['title']; ?></h5>
                        <p class="text-muted"><?php echo $step['desc']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== CALL TO ACTION ==================== -->
<?php if (!Session::isLoggedIn()): ?>
<section class="cta-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-3">Ready to Take the Next Step? </h2>
                <p class="lead mb-0">
                    Join thousands of job seekers and companies.  Find your perfect career opportunity or hire top talent today!
                </p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-grid gap-2">
                    <a href="<?php echo APP_URL; ?>views/auth/register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket me-2"></i> Get Started Now
                    </a>
                    <small class="text-white-50">Free forever.  No credit card required.</small>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

</main>

<!-- ✨ Additional Styles -->
<style>
/* Hero Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Company Logo */
.company-logo {
    width: 60px;
    height: 60px;
    object-fit: cover;
}

. company-logo-placeholder {
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

/* Job Card Hover */
.job-card {
    transition: all 0.3s ease;
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

. job-title-link:hover {
    color: #0d6efd ! important;
}

/* Category Card */
.category-card {
    transition: all 0. 3s ease;
}

. category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    border-color: #0d6efd ! important;
}

. category-card:hover .category-icon i {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

/* How It Works */
.step-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Counter Animation */
.counter {
    font-size: 2. 5rem;
}

/* Stats Card Hover */
.stat-card {
    transition: all 0. 3s ease;
}

. stat-card:hover {
    transform: translateY(-10px);
}

. shadow-hover:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

/* Min height for hero */
.min-vh-50 {
    min-height: 50vh;
}
</style>

<!-- ✨ Counter Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animated Counter
    const counters = document.querySelectorAll('.counter');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current) + (counter.textContent. includes('+') ? '+' : '');
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target + (counter.textContent.includes('+') ? '+' : '');
            }
        };
        
        updateCounter();
    };
    
    // Intersection Observer for counters
    const observer = new IntersectionObserver((entries) => {
        entries. forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer. observe(counter));
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>