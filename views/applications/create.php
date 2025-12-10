<?php
$page_title = "Apply for Job";
$page_description = "Submit your job application";
$body_class = "application-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/Application.php';

// ✨ Require job seeker login
Session::requireJobSeeker();

// ✨ Get and validate job ID
if (! isset($_GET['job_id']) || empty($_GET['job_id'])) {
    Session::setError('Invalid job ID');
    redirect(APP_URL . 'views/jobs/index.php');
    exit;
}

$job_id = (int)$_GET['job_id'];

// ✨ Get job details with error handling
try {
    $job = new Job();
    $jobData = $job->getById($job_id);
    
    if (!$jobData) {
        Session::setError('Job not found');
        redirect(APP_URL . 'views/jobs/index.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Application form - Job fetch error: ' . $e->getMessage());
    Session::setError('An error occurred while loading job details');
    redirect(APP_URL . 'views/jobs/index.php');
    exit;
}

// ✨ Check job status
if ($jobData['status'] !== JOB_STATUS_ACTIVE) {
    Session::setError('This job is no longer accepting applications');
    redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
    exit;
}

// ✨ Check if already applied
try {
    $application = new Application();
    $application->job_id = $job_id;
    $application->user_id = Session::getUserId();
    
    if ($application->checkDuplicate()) {
        Session::setWarning('You have already applied for this job');
        redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
        exit;
    }
    
    // ✨ Use comprehensive canApply check
    $can_apply = $application->canApply();
    if (!$can_apply['can_apply']) {
        Session::setError($can_apply['reason']);
        redirect(APP_URL .  'views/jobs/detail. php?id=' . $job_id);
        exit;
    }
} catch (Exception $e) {
    error_log('Application check error: ' . $e->getMessage());
    Session::setError('An error occurred while checking application status');
    redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
    exit;
}

// ✨ Check deadline
$deadline_passed = isDeadlinePassed($jobData['deadline']);
$days_left = ! empty($jobData['deadline']) ? daysUntilDeadline($jobData['deadline']) : null;

if ($deadline_passed) {
    Session::setError('Application deadline has passed');
    redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
    exit;
}

// ✨ Get user data
require_once __DIR__ . '/../../models/User.php';
$user = new User();
$userData = $user->getUserById(Session::getUserId());
?>

<main id="main-content" class="application-form-page">
    <div class="container mt-4 mb-5">
        
        <!-- ==================== BREADCRUMB ==================== -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>views/jobs/index.php">Jobs</a></li>
                <li class="breadcrumb-item">
                    <a href="<?php echo APP_URL; ?>views/jobs/detail. php?id=<?php echo $job_id; ?>">
                        <?php echo truncate($jobData['title'], 40); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Apply</li>
            </ol>
        </nav>
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-paper-plane me-2 text-primary"></i> Apply for Position
                </h1>
                <p class="text-muted lead">Submit your application and showcase your skills</p>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ✨ Deadline Warning -->
        <?php if ($days_left !== null && $days_left <= 3): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Hurry!</strong> Application deadline is in 
                <strong><?php echo $days_left == 0 ? 'less than 24 hours' : $days_left . ' day' . ($days_left > 1 ? 's' : ''); ?></strong>! 
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== JOB SUMMARY ==================== -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-briefcase me-2"></i> Job Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <?php if (!empty($jobData['logo'])): ?>
                                    <img src="<?php echo LOGO_URL .  $jobData['logo']; ?>" 
                                         alt="<?php echo htmlspecialchars($jobData['company_name']); ?>"
                                         class="company-logo rounded shadow-sm"
                                         onerror="this.style.display='none'">
                                <?php else: ?>
                                    <div class="company-logo-placeholder bg-light rounded">
                                        <i class="fas fa-building fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-10">
                                <h5 class="mb-2 fw-bold"><?php echo htmlspecialchars($jobData['title']); ?></h5>
                                <p class="mb-2">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    <strong><?php echo htmlspecialchars($jobData['company_name']); ?></strong>
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?php echo htmlspecialchars($jobData['location']); ?>
                                    </span>
                                    <?php echo getJobTypeBadge($jobData['job_type']); ?>
                                    <?php if (!empty($jobData['salary_min']) || !empty($jobData['salary_max'])): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            <?php echo formatSalary($jobData['salary_min'], $jobData['salary_max']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ==================== APPLICATION FORM ==================== -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-edit me-2 text-primary"></i> Application Form
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info border-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Before You Apply</h6>
                                    <ul class="mb-0 small">
                                        <li>Ensure all information is accurate</li>
                                        <li>Prepare a well-written cover letter</li>
                                        <li>Upload an updated resume (PDF recommended)</li>
                                        <li>Double-check before submitting (no edits allowed after)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <form action="<?php echo APP_URL; ?>controllers/ApplicationController.php?action=apply&job_id=<?php echo $job_id; ?>" 
                              method="POST" 
                              enctype="multipart/form-data"
                              id="applicationForm"
                              class="needs-validation"
                              novalidate>
                            
                            <!-- CSRF Token -->
                            <?php echo Session::csrfField(); ?>
                            
                            <!-- ✨ Applicant Info (Read-only) -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-user me-2 text-primary"></i> Your Information
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Full Name</label>
                                        <input type="text" class="form-control-plaintext" readonly
                                               value="<?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Email</label>
                                        <input type="text" class="form-control-plaintext" readonly
                                               value="<?php echo htmlspecialchars($userData['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Phone</label>
                                        <input type="text" class="form-control-plaintext" readonly
                                               value="<?php echo htmlspecialchars($userData['phone']); ?>">
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Not correct?  
                                            <a href="<?php echo APP_URL; ?>views/profile/edit.php" target="_blank">
                                                Update Profile
                                            </a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Cover Letter -->
                            <div class="mb-4">
                                <label for="cover_letter" class="form-label fw-semibold">
                                    <i class="fas fa-file-alt me-1 text-primary"></i> 
                                    Cover Letter <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="cover_letter" 
                                          name="cover_letter" 
                                          rows="10" 
                                          required
                                          minlength="50"
                                          maxlength="2000"
                                          placeholder="Dear Hiring Manager,&#10;&#10;I am writing to express my strong interest in the <?php echo htmlspecialchars($jobData['title']); ?> position... "></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Explain why you're perfect for this role and highlight relevant experience
                                    </small>
                                    <small class="text-muted">
                                        <span id="charCount">0</span>/2000 characters (min: 50)
                                    </small>
                                </div>
                                <div class="invalid-feedback">
                                    Cover letter must be between 50 and 2000 characters
                                </div>
                            </div>
                            
                            <!-- Resume Upload -->
                            <div class="mb-4">
                                <label for="resume" class="form-label fw-semibold">
                                    <i class="fas fa-file-upload me-1 text-primary"></i> 
                                    Resume/CV <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="resume" 
                                       name="resume" 
                                       accept=".pdf,.doc,.docx"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    Accepted: PDF, DOC, DOCX
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    Max size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>
                                </div>
                                <div class="invalid-feedback">
                                    Please upload your resume
                                </div>
                                <!-- File Preview -->
                                <div id="filePreview" class="mt-2" style="display: none;">
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-file-pdf me-2"></i>
                                        <strong id="fileName"></strong>
                                        <span class="ms-2" id="fileSize"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Warning -->
                            <div class="alert alert-warning border-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Once submitted, you cannot edit or withdraw your application 
                                <?php if (! empty($jobData['deadline'])): ?>
                                    until after the deadline (<?php echo formatDate($jobData['deadline']); ?>)
                                <?php endif; ?>.
                                Please review all information carefully. 
                            </div>
                            
                            <!-- Terms -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I confirm that all information provided is accurate and I agree to the 
                                        <a href="<?php echo APP_URL; ?>views/pages/terms.php" target="_blank">Terms & Conditions</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree to continue
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<?php echo $job_id; ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Job
                                </a>
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Application
                                </button>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Application Tips -->
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i> Application Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Customize your cover letter</strong>
                                <p class="small text-muted mb-0">Tailor it specifically to this job and company</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Update your resume</strong>
                                <p class="small text-muted mb-0">Ensure it reflects your latest experience</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Highlight relevant skills</strong>
                                <p class="small text-muted mb-0">Match your skills to the job requirements</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Proofread everything</strong>
                                <p class="small text-muted mb-0">Check for typos and grammatical errors</p>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Be professional</strong>
                                <p class="small text-muted mb-0">Maintain a formal and respectful tone</p>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Need Help?  -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i> Need Help?
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">Having trouble with your application?</p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>views/pages/help.php" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-life-ring me-2"></i> Help Center
                            </a>
                            <a href="<?php echo APP_URL; ?>views/pages/contact.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i> Contact Support
                            </a>
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
    const form = document.getElementById('applicationForm');
    const submitBtn = document. getElementById('submitBtn');
    const coverLetter = document.getElementById('cover_letter');
    const charCount = document.getElementById('charCount');
    const resumeInput = document.getElementById('resume');
    const filePreview = document. getElementById('filePreview');
    
    // Character counter
    if (coverLetter && charCount) {
        coverLetter.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            
            if (this.value.length < 50) {
                charCount.classList.add('text-danger');
                charCount.classList.remove('text-success');
            } else {
                charCount.classList.remove('text-danger');
                charCount.classList. add('text-success');
            }
        });
    }
    
    // File preview
    if (resumeInput && filePreview) {
        resumeInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileName = document.getElementById('fileName');
                const fileSize = document. getElementById('fileSize');
                
                fileName.textContent = file. name;
                fileSize.textContent = '(' + formatBytes(file.size) + ')';
                filePreview.style.display = 'block';
            } else {
                filePreview.style.display = 'none';
            }
        });
    }
    
    // Form submission
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                if (confirm('Are you sure you want to submit this application?  You will not be able to edit it after submission.')) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
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
    
    // Helper function
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
});
</script>

<style>
.company-logo {
    max-width: 80px;
    max-height: 80px;
    object-fit: contain;
}

.company-logo-placeholder {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (min-width: 992px) {
    .sticky-top {
        position: sticky;
        top: 80px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>