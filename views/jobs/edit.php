<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../models/Category.php';

// ✨ Require company login
Session::requireCompany();

// ✨ Get and validate job ID
if (! isset($_GET['id']) || empty($_GET['id'])) {
    Session::setError('Invalid job ID');
    redirect(APP_URL . 'views/dashboard/company.php');
    exit;
}

$job_id = (int)$_GET['id'];

// ✨ Get job with ownership check
try {
    $job = JobController::update($job_id);
    
    if (!$job) {
        Session::setError('Job not found or you do not have permission to edit it');
        redirect(APP_URL . 'views/dashboard/company.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Job edit error: ' . $e->getMessage());
    Session::setError('An error occurred while loading the job');
    redirect(APP_URL .  'views/dashboard/company. php');
    exit;
}

$page_title = "Edit Job - " . $job['title'];
$page_description = "Edit job posting: " . $job['title'];
$body_class = "job-edit-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

// ✨ Get categories
try {
    $category = new Category();
    $categories = $category->getAll();
} catch (Exception $e) {
    error_log('Categories fetch error: ' . $e->getMessage());
    $categories = [];
}

// ✨ Check if job has applications
$has_applications = isset($job['application_count']) && $job['application_count'] > 0;
?>

<main id="main-content" class="job-edit-page">
    <div class="container mt-4 mb-5">
        
        <!-- ==================== BREADCRUMB ==================== -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>views/dashboard/company.php">Dashboard</a></li>
                <li class="breadcrumb-item">
                    <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<?php echo $job['id']; ?>">
                        <?php echo truncate($job['title'], 40); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-edit me-2 text-warning"></i> Edit Job Posting
                </h1>
                <p class="text-muted lead">Update your job details and requirements</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<? php echo $job['id']; ?>" 
                   class="btn btn-outline-primary me-2">
                    <i class="fas fa-eye me-2"></i> Preview
                </a>
                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ✨ Applications Warning -->
        <?php if ($has_applications): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> This job has <strong><? php echo $job['application_count']; ?></strong> 
                application(s).   Major changes may confuse applicants.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== EDIT FORM ==================== -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-briefcase me-2 text-primary"></i> Job Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="<?php echo APP_URL; ?>controllers/JobController.php? action=update&id=<?php echo $job['id']; ?>" 
                              method="POST"
                              id="editJobForm"
                              class="needs-validation"
                              novalidate>
                            
                            <!-- CSRF Token -->
                            <? php echo Session::csrfField(); ?>
                            
                            <!-- ==================== BASIC INFO ==================== -->
                            <div class="form-section mb-5">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-info-circle me-2 text-primary"></i> Basic Information
                                </h6>
                                
                                <!-- Job Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold">
                                        Job Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($job['title']); ?>"
                                           required
                                           minlength="5"
                                           maxlength="100">
                                    <div class="invalid-feedback">
                                        Job title must be between 5 and 100 characters
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Category -->
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label fw-semibold">Category</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($job['category_id'] == $cat['id']) ? 'selected' : ''; ? >>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Job Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="job_type" class="form-label fw-semibold">
                                            Job Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="job_type" name="job_type" required>
                                            <option value="">Select job type</option>
                                            <option value="full-time" <?php echo ($job['job_type'] == 'full-time') ? 'selected' : ''; ?>>Full Time</option>
                                            <option value="part-time" <? php echo ($job['job_type'] == 'part-time') ? 'selected' : ''; ?>>Part Time</option>
                                            <option value="contract" <?php echo ($job['job_type'] == 'contract') ? 'selected' : ''; ?>>Contract</option>
                                            <option value="internship" <?php echo ($job['job_type'] == 'internship') ? 'selected' : ''; ?>>Internship</option>
                                            <option value="remote" <?php echo ($job['job_type'] == 'remote') ? 'selected' : ''; ?>>Remote</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a job type</div>
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
                                               value="<?php echo htmlspecialchars($job['location']); ?>"
                                               required>
                                        <div class="invalid-feedback">Please specify location</div>
                                    </div>
                                    
                                    <!-- Experience Level -->
                                    <div class="col-md-6 mb-3">
                                        <label for="experience_level" class="form-label fw-semibold">Experience Level</label>
                                        <select class="form-select" id="experience_level" name="experience_level">
                                            <option value="">Select level</option>
                                            <option value="entry" <?php echo ($job['experience_level'] == 'entry') ?  'selected' : ''; ? >>Entry Level</option>
                                            <option value="mid" <?php echo ($job['experience_level'] == 'mid') ? 'selected' : ''; ?>>Mid Level</option>
                                            <option value="senior" <?php echo ($job['experience_level'] == 'senior') ? 'selected' : ''; ?>>Senior</option>
                                            <option value="executive" <?php echo ($job['experience_level'] == 'executive') ? 'selected' : ''; ?>>Executive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== JOB DETAILS ==================== -->
                            <div class="form-section mb-5">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-file-alt me-2 text-primary"></i> Job Details
                                </h6>
                                
                                <!-- Description -->
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
                                              maxlength="5000"><? php echo htmlspecialchars($job['description']); ?></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">Describe the role and responsibilities</small>
                                        <small class="text-muted"><span id="descCount"><?php echo strlen($job['description']); ?></span>/5000</small>
                                    </div>
                                    <div class="invalid-feedback">Description required (20-5000 characters)</div>
                                </div>
                                
                                <!-- Requirements -->
                                <div class="mb-3">
                                    <label for="requirements" class="form-label fw-semibold">Requirements</label>
                                    <textarea class="form-control" 
                                              id="requirements" 
                                              name="requirements" 
                                              rows="8"
                                              maxlength="5000"><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">List required skills and qualifications</small>
                                        <small class="text-muted"><span id="reqCount"><?php echo strlen($job['requirements'] ?? ''); ?></span>/5000</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== COMPENSATION ==================== -->
                            <div class="form-section mb-5">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-money-bill-wave me-2 text-primary"></i> Compensation & Timeline
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="salary_min" class="form-label fw-semibold">Min Salary (NPR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">NPR</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salary_min" 
                                                   name="salary_min" 
                                                   value="<?php echo $job['salary_min'] ?? ''; ?>"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="salary_max" class="form-label fw-semibold">Max Salary (NPR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">NPR</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salary_max" 
                                                   name="salary_max" 
                                                   value="<?php echo $job['salary_max'] ?? ''; ?>"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="deadline" class="form-label fw-semibold">Application Deadline</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="deadline" 
                                               name="deadline" 
                                               value="<?php echo $job['deadline'] ?? ''; ? >"
                                               min="<? php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                        <small class="form-text text-muted">
                                            <? php if (! empty($job['deadline'])): ?>
                                                Current: <?php echo formatDate($job['deadline']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label fw-semibold">Job Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="<? php echo JOB_STATUS_ACTIVE; ?>" <?php echo ($job['status'] == JOB_STATUS_ACTIVE) ? 'selected' : ''; ?>>
                                                Active (Visible to all)
                                            </option>
                                            <option value="<? php echo JOB_STATUS_DRAFT; ?>" <?php echo ($job['status'] == JOB_STATUS_DRAFT) ? 'selected' : ''; ?>>
                                                Draft (Hidden)
                                            </option>
                                            <option value="<?php echo JOB_STATUS_CLOSED; ?>" <?php echo ($job['status'] == JOB_STATUS_CLOSED) ? 'selected' : ''; ?>>
                                                Closed (No new applications)
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== SUBMIT BUTTONS ==================== -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg" id="updateBtn">
                                    <i class="fas fa-save me-2"></i> Update Job
                                </button>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Job Stats -->
                <? php if ($has_applications): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i> Job Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Applications</span>
                            <strong class="text-primary"><?php echo $job['application_count']; ?></strong>
                        </div>
                        <a href="<?php echo APP_URL; ?>views/applications/received.php? job_id=<?php echo $job['id']; ?>" 
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-users me-2"></i> View Applicants
                        </a>
                    </div>
                </div>
                <? php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<? php echo $job['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i> Preview Job
                            </a>
                            <? php if ($has_applications): ?>
                                <a href="<?php echo APP_URL; ?>views/applications/received.php?job_id=<?php echo $job['id']; ?>" 
                                   class="btn btn-outline-success">
                                    <i class="fas fa-users me-2"></i> View Applications
                                </a>
                            <?php endif; ?>
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i> Delete Job
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Change Log -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i> Job History
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Created:</strong> <?php echo formatDate($job['created_at'], 'M d, Y h:i A'); ? ><br>
                            <? php if (! empty($job['updated_at'])): ?>
                                <strong>Last Updated:</strong> <?php echo formatDate($job['updated_at'], 'M d, Y h:i A'); ?>
                            <? php endif; ?>
                        </small>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</main>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editJobForm');
    const updateBtn = document.getElementById('updateBtn');
    const description = document.getElementById('description');
    const requirements = document.getElementById('requirements');
    const descCount = document.getElementById('descCount');
    const reqCount = document.getElementById('reqCount');
    
    // Character counters
    if (description && descCount) {
        description.addEventListener('input', function() {
            descCount.textContent = this.value.length;
        });
    }
    
    if (requirements && reqCount) {
        requirements.addEventListener('input', function() {
            reqCount.textContent = this.value.length;
        });
    }
    
    // Form submission
    if (form && updateBtn) {
        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                if (confirm('Are you sure you want to update this job posting?')) {
                    updateBtn.disabled = true;
                    updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
                } else {
                    e.preventDefault();
                }
            } else {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});

// Delete confirmation
function confirmDelete() {
    if (confirm('Are you sure you want to delete this job?  This will also delete all associated applications.  This action cannot be undone.')) {
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
        id.value = '<?php echo $job['id']; ?>';
        
        form.appendChild(csrf);
        form.appendChild(id);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<? php require_once __DIR__ .  '/../layouts/footer.php'; ?>