<?php
$page_title = "Post a New Job";
$page_description = "Create a new job posting to attract top talent";
$body_class = "job-create-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Company.php';
require_once __DIR__ . '/../../controllers/CompanyController.php';

// ✨ Require company login
Session::requireCompany();

// ✨ Check if company profile is complete
$profile_check = CompanyController::checkProfileComplete();
if (!$profile_check['complete']) {
    Session::setWarning('Please complete your company profile before posting jobs');
    redirect(APP_URL . 'views/company/profile. php');
    exit;
}

// ✨ Get categories
try {
    $category = new Category();
    $categories = $category->getAll();
} catch (Exception $e) {
    error_log('Job create - Category fetch error: ' . $e->getMessage());
    $categories = [];
}
?>

<main id="main-content" class="job-create-page">
    <div class="container mt-4 mb-5">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-plus-circle me-2 text-success"></i> Post a New Job
                </h1>
                <p class="text-muted lead">Create a compelling job posting to attract qualified candidates</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== JOB FORM ==================== -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <form action="<?php echo APP_URL; ?>controllers/JobController.php? action=create" 
                              method="POST"
                              id="jobForm"
                              class="needs-validation"
                              novalidate>
                            
                            <!-- CSRF Token -->
                            <? php echo Session::csrfField(); ?>
                            
                            <!-- ==================== BASIC INFORMATION ==================== -->
                            <div class="form-section mb-5">
                                <h5 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-info-circle me-2 text-primary"></i> Basic Information
                                </h5>
                                
                                <!-- Job Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold">
                                        Job Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="title" 
                                           name="title" 
                                           placeholder="e.g., Senior PHP Developer"
                                           required
                                           minlength="5"
                                           maxlength="100">
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Be specific and clear about the position
                                    </div>
                                    <div class="invalid-feedback">
                                        Job title must be between 5 and 100 characters
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Category -->
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label fw-semibold">
                                            Category
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Helps candidates find your job</div>
                                    </div>
                                    
                                    <!-- Job Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="job_type" class="form-label fw-semibold">
                                            Job Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="job_type" name="job_type" required>
                                            <option value="">Select job type</option>
                                            <option value="full-time">Full Time</option>
                                            <option value="part-time">Part Time</option>
                                            <option value="contract">Contract</option>
                                            <option value="internship">Internship</option>
                                            <option value="remote">Remote</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a job type
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Location -->
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label fw-semibold">
                                            Location <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="location" 
                                               name="location" 
                                               placeholder="e.g., Kathmandu, Nepal"
                                               required
                                               list="locationSuggestions">
                                        <datalist id="locationSuggestions">
                                            <option value="Kathmandu, Nepal">
                                            <option value="Lalitpur, Nepal">
                                            <option value="Bhaktapur, Nepal">
                                            <option value="Pokhara, Nepal">
                                            <option value="Biratnagar, Nepal">
                                            <option value="Remote">
                                        </datalist>
                                        <div class="invalid-feedback">
                                            Please specify the job location
                                        </div>
                                    </div>
                                    
                                    <!-- Experience Level -->
                                    <div class="col-md-6 mb-3">
                                        <label for="experience_level" class="form-label fw-semibold">
                                            Experience Level
                                        </label>
                                        <select class="form-select" id="experience_level" name="experience_level">
                                            <option value="">Select level</option>
                                            <option value="entry">Entry Level (0-2 years)</option>
                                            <option value="mid">Mid Level (2-5 years)</option>
                                            <option value="senior">Senior Level (5+ years)</option>
                                            <option value="executive">Executive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== JOB DETAILS ==================== -->
                            <div class="form-section mb-5">
                                <h5 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-file-alt me-2 text-primary"></i> Job Details
                                </h5>
                                
                                <!-- Job Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">
                                        Job Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="8"
                                              required
                                              minlength="20"
                                              maxlength="5000"
                                              placeholder="Describe the role, responsibilities, what you're looking for, company culture, growth opportunities... "></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Include role overview, key responsibilities, and what makes this opportunity unique
                                        </small>
                                        <small class="text-muted">
                                            <span id="descCount">0</span>/5000
                                        </small>
                                    </div>
                                    <div class="invalid-feedback">
                                        Description must be between 20 and 5000 characters
                                    </div>
                                </div>
                                
                                <!-- Requirements -->
                                <div class="mb-3">
                                    <label for="requirements" class="form-label fw-semibold">
                                        Requirements & Qualifications
                                    </label>
                                    <textarea class="form-control" 
                                              id="requirements" 
                                              name="requirements" 
                                              rows="8"
                                              maxlength="5000"
                                              placeholder="• Bachelor's degree in Computer Science&#10;• 3+ years of PHP development experience&#10;• Strong knowledge of Laravel framework&#10;• Experience with MySQL databases&#10;• Excellent problem-solving skills"></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            List required education, skills, experience, and certifications
                                        </small>
                                        <small class="text-muted">
                                            <span id="reqCount">0</span>/5000
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== COMPENSATION & TIMELINE ==================== -->
                            <div class="form-section mb-5">
                                <h5 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-money-bill-wave me-2 text-primary"></i> Compensation & Timeline
                                </h5>
                                
                                <div class="row">
                                    <!-- Salary Min -->
                                    <div class="col-md-6 mb-3">
                                        <label for="salary_min" class="form-label fw-semibold">
                                            Minimum Salary (NPR/month)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">NPR</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salary_min" 
                                                   name="salary_min" 
                                                   placeholder="50,000"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                        <div class="form-text">Monthly salary (optional)</div>
                                    </div>
                                    
                                    <!-- Salary Max -->
                                    <div class="col-md-6 mb-3">
                                        <label for="salary_max" class="form-label fw-semibold">
                                            Maximum Salary (NPR/month)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">NPR</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salary_max" 
                                                   name="salary_max" 
                                                   placeholder="80,000"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                        <div class="form-text">Or leave blank for "Negotiable"</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Application Deadline -->
                                    <div class="col-md-6 mb-3">
                                        <label for="deadline" class="form-label fw-semibold">
                                            Application Deadline
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="deadline" 
                                               name="deadline"
                                               min="<? php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                        <div class="form-text">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            When should applications close?
                                        </div>
                                    </div>
                                    
                                    <!-- Job Status -->
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label fw-semibold">
                                            Publication Status
                                        </label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="<? php echo JOB_STATUS_ACTIVE; ? >" selected>
                                                Active (Publish Now)
                                            </option>
                                            <option value="<? php echo JOB_STATUS_DRAFT; ?>">
                                                Draft (Save for Later)
                                            </option>
                                        </select>
                                        <div class="form-text">
                                            <i class="fas fa-eye me-1"></i>
                                            Active jobs are visible to all users
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== SUBMIT BUTTONS ==================== -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                                <div class="btn-group">
                                    <button type="submit" 
                                            name="status" 
                                            value="<? php echo JOB_STATUS_DRAFT; ?>"
                                            class="btn btn-outline-primary">
                                        <i class="fas fa-save me-2"></i> Save as Draft
                                    </button>
                                    <button type="submit" 
                                            name="status" 
                                            value="<?php echo JOB_STATUS_ACTIVE; ?>"
                                            class="btn btn-success btn-lg"
                                            id="publishBtn">
                                        <i class="fas fa-check-circle me-2"></i> Publish Job
                                    </button>
                                </div>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Tips Card -->
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i> Job Posting Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Be specific in the title</strong>
                                <p class="text-muted mb-0">Use clear job titles that candidates search for</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Write detailed descriptions</strong>
                                <p class="text-muted mb-0">Include day-to-day tasks and growth opportunities</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>List clear requirements</strong>
                                <p class="text-muted mb-0">Be realistic about must-have vs nice-to-have skills</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Include salary range</strong>
                                <p class="text-muted mb-0">Transparent compensation attracts better candidates</p>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Set a reasonable deadline</strong>
                                <p class="text-muted mb-0">Give candidates enough time to prepare applications</p>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Preview Notice -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-eye me-2"></i> Before Publishing
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">Your job will be visible to thousands of job seekers. </p>
                        <div class="alert alert-light border mb-0">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                You can edit or close the job posting anytime from your dashboard.
                            </small>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</main>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('jobForm');
    const description = document.getElementById('description');
    const requirements = document.getElementById('requirements');
    const descCount = document.getElementById('descCount');
    const reqCount = document.getElementById('reqCount');
    const publishBtn = document.getElementById('publishBtn');
    const salaryMin = document.getElementById('salary_min');
    const salaryMax = document.getElementById('salary_max');
    
    // Character counters
    if (description && descCount) {
        description.addEventListener('input', function() {
            descCount.textContent = this. value.length;
        });
    }
    
    if (requirements && reqCount) {
        requirements.addEventListener('input', function() {
            reqCount.textContent = this.value.length;
        });
    }
    
    // Salary validation
    if (salaryMin && salaryMax) {
        salaryMax.addEventListener('change', function() {
            if (salaryMin.value && salaryMax.value) {
                if (parseFloat(salaryMax.value) < parseFloat(salaryMin.value)) {
                    salaryMax.setCustomValidity('Maximum salary must be greater than minimum salary');
                } else {
                    salaryMax.setCustomValidity('');
                }
            }
        });
        
        salaryMin.addEventListener('change', function() {
            salaryMax.dispatchEvent(new Event('change'));
        });
    }
    
    // Form submission
    if (form && publishBtn) {
        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                const submitBtn = e.submitter;
                submitBtn.disabled = true;
                
                if (submitBtn.value === '<? php echo JOB_STATUS_ACTIVE; ?>') {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing...';
                } else {
                    submitBtn. innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                }
            } else {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    }
});
</script>

<style>
@media (min-width: 992px) {
    .sticky-top {
        position: sticky;
        top: 80px;
    }
}

.form-section {
    scroll-margin-top: 100px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>