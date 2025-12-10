<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// ✨ Require login
Session::requireLogin();

// ✨ Get and validate application ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Session::setError('Invalid application ID');
    redirect(APP_URL . 'views/dashboard/' . (Session::isCompany() ? 'company' : 'jobseeker') . '.php');
    exit;
}

$app_id = (int)$_GET['id'];

// ✨ Get application with permission check
try {
    $application = ApplicationController::view($app_id);
    
    if (!$application) {
        Session::setError('Application not found or you do not have permission to view it');
        redirect(APP_URL . 'views/dashboard/' .  (Session::isCompany() ?  'company' : 'jobseeker') . '.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Application view error: ' . $e->getMessage());
    Session::setError('An error occurred while loading the application');
    redirect(APP_URL . 'views/dashboard/' . (Session::isCompany() ? 'company' : 'jobseeker') . '.php');
    exit;
}

$page_title = "Application - " . $application['job_title'];
$page_description = "View application details";
$body_class = "application-view-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main id="main-content" class="application-view-page">
    <div class="container mt-4 mb-5">
        
        <!-- ==================== BREADCRUMB ==================== -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>">Home</a></li>
                <li class="breadcrumb-item">
                    <a href="<?php echo APP_URL; ?>views/dashboard/<?php echo Session::isCompany() ? 'company' : 'jobseeker'; ?>.php">
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Application Details</li>
            </ol>
        </nav>
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-file-alt me-2 text-primary"></i> Application Details
                </h1>
                <p class="text-muted lead">
                    <?php echo Session::isCompany() ? 'Review applicant information' : 'Your application status'; ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Go Back
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== APPLICATION STATUS ==================== -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i> Application Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Current Status</label>
                                <div><?php echo getApplicationStatusBadge($application['status']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Applied On</label>
                                <div><strong><?php echo formatDate($application['applied_at'], 'F j, Y \a\t g:i A'); ?></strong></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Job Title</label>
                                <div>
                                    <a href="<?php echo APP_URL; ?>views/jobs/detail. php?id=<?php echo $application['job_id']; ?>" 
                                       class="text-decoration-none">
                                        <strong><?php echo htmlspecialchars($application['job_title']); ?></strong>
                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Company</label>
                                <div><strong><?php echo htmlspecialchars($application['company_name']); ?></strong></div>
                            </div>
                            <?php if (! empty($application['location'])): ?>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Location</label>
                                    <div>
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?php echo htmlspecialchars($application['location']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($application['job_type'])): ?>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Job Type</label>
                                    <div><?php echo getJobTypeBadge($application['job_type']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- ==================== APPLICANT INFO (FOR COMPANIES) ==================== -->
                <?php if (Session::isCompany()): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i> Applicant Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Full Name</label>
                                    <div><strong><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></strong></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Email Address</label>
                                    <div>
                                        <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>" 
                                           class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($application['email']); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Phone Number</label>
                                    <div>
                                        <a href="tel:<?php echo htmlspecialchars($application['phone']); ?>" 
                                           class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i>
                                            <?php echo htmlspecialchars($application['phone']); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- ==================== COVER LETTER ==================== -->
                <?php if (! empty($application['cover_letter'])): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i> Cover Letter
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="cover-letter-content">
                                <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- ==================== RESUME ==================== -->
                <?php if (!empty($application['resume'])): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-file-pdf me-2"></i> Resume / CV
                            </h5>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                            <h6 class="mb-3"><strong><?php echo htmlspecialchars($application['resume']); ?></strong></h6>
                            <div class="btn-group" role="group">
                                <a href="<?php echo RESUME_URL .  $application['resume']; ?>" 
                                   class="btn btn-danger btn-lg" 
                                   download>
                                    <i class="fas fa-download me-2"></i> Download Resume
                                </a>
                                <a href="<?php echo RESUME_URL . $application['resume']; ?>" 
                                   class="btn btn-outline-danger btn-lg" 
                                   target="_blank">
                                    <i class="fas fa-external-link-alt me-2"></i> View in Browser
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- ✨ Update Status (FOR COMPANIES) -->
                <?php if (Session::isCompany()): ?>
                    <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-edit me-2"></i> Update Application Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo APP_URL; ?>controllers/ApplicationController.php?action=update_status" 
                                  method="POST"
                                  id="statusForm">
                                <?php echo Session::csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-semibold">Change Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="<?php echo APP_STATUS_PENDING; ?>" <?php echo ($application['status'] == APP_STATUS_PENDING) ? 'selected' : ''; ?>>
                                            Pending Review
                                        </option>
                                        <option value="<?php echo APP_STATUS_REVIEWED; ?>" <?php echo ($application['status'] == APP_STATUS_REVIEWED) ? 'selected' : ''; ?>>
                                            Reviewed
                                        </option>
                                        <option value="<?php echo APP_STATUS_SHORTLISTED; ?>" <?php echo ($application['status'] == APP_STATUS_SHORTLISTED) ? 'selected' : ''; ?>>
                                            Shortlisted
                                        </option>
                                        <option value="<?php echo APP_STATUS_ACCEPTED; ?>" <?php echo ($application['status'] == APP_STATUS_ACCEPTED) ?  'selected' : ''; ?>>
                                            Accepted
                                        </option>
                                        <option value="<?php echo APP_STATUS_REJECTED; ?>" <?php echo ($application['status'] == APP_STATUS_REJECTED) ? 'selected' : ''; ?>>
                                            Rejected
                                        </option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-warning w-100" id="updateBtn">
                                    <i class="fas fa-save me-2"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-bolt me-2"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>views/jobs/detail.php?id=<?php echo $application['job_id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-briefcase me-2"></i> View Job Posting
                            </a>
                            
                            <?php if (Session::isJobSeeker() && $application['status'] == APP_STATUS_PENDING): ?>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="confirmWithdraw()">
                                    <i class="fas fa-times me-2"></i> Withdraw Application
                                </button>
                            <?php endif; ?>
                            
                            <?php if (Session::isCompany()): ?>
                                <a href="<?php echo APP_URL; ?>views/applications/received.php" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-inbox me-2"></i> All Applications
                                </a>
                            <?php else: ?>
                                <a href="<?php echo APP_URL; ?>views/dashboard/jobseeker.php" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt me-2"></i> My Dashboard
                                </a>
                            <?php endif; ?>
                            
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fas fa-print me-2"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Application Timeline -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2"></i> Timeline
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <div>
                                    <strong>Applied</strong>
                                    <p class="small text-muted mb-0">
                                        <?php echo formatDate($application['applied_at'], 'M d, Y h:i A'); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if (!empty($application['updated_at'])): ?>
                                <div class="timeline-item">
                                    <i class="fas fa-edit text-warning"></i>
                                    <div>
                                        <strong>Last Updated</strong>
                                        <p class="small text-muted mb-0">
                                            <?php echo formatDate($application['updated_at'], 'M d, Y h:i A'); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
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
    const statusForm = document.getElementById('statusForm');
    const updateBtn = document.getElementById('updateBtn');
    
    if (statusForm && updateBtn) {
        statusForm.addEventListener('submit', function(e) {
            if (confirm('Are you sure you want to update this application status?')) {
                updateBtn.disabled = true;
                updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
            } else {
                e.preventDefault();
            }
        });
    }
});

<?php if (Session::isJobSeeker()): ?>
function confirmWithdraw() {
    if (confirm('Are you sure you want to withdraw this application?  This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo APP_URL; ?>controllers/ApplicationController.php?action=delete';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = '<?php echo Session::generateCSRFToken(); ?>';
        
        const id = document.createElement('input');
        id.type = 'hidden';
        id.name = 'id';
        id.value = '<?php echo $application['id']; ?>';
        
        form.appendChild(csrf);
        form.appendChild(id);
        document.body.appendChild(form);
        form.submit();
    }
}
<?php endif; ?>
</script>

<style>
. cover-letter-content {
    line-height: 1.8;
    font-size: 1.05rem;
    white-space: pre-wrap;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    display: flex;
    align-items-start;
    gap: 15px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item i {
    font-size: 1.2rem;
    margin-top: 3px;
}

@media print {
    .sidebar, .breadcrumb, .btn, nav, footer {
        display: none ! important;
    }
    
    .card {
        border: 1px solid #ddd ! important;
        box-shadow: none !important;
    }
}

@media (min-width: 992px) {
    .sticky-top {
        position: sticky;
        top: 80px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>