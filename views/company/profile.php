<?php
$page_title = "Company Profile";
$page_description = "Manage your company profile and information";
$body_class = "company-profile-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../controllers/CompanyController.php';

// ✨ Require company login
Session::requireCompany();

// ✨ Get company profile
try {
    $company = CompanyController::getProfile();
} catch (Exception $e) {
    error_log('Company profile fetch error: ' . $e->getMessage());
    $company = null;
}

// ✨ Calculate profile completion
$profile_check = CompanyController::checkProfileComplete();
$completion = $profile_check['complete'] ?  100 : 0;
if (! $profile_check['complete'] && $company) {
    $total_fields = 7;
    $filled_fields = $total_fields - count($profile_check['missing']);
    $completion = round(($filled_fields / $total_fields) * 100);
}
?>

<main id="main-content" class="company-profile-page">
    <div class="container mt-4 mb-5">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">
                    <i class="fas fa-building me-2 text-primary"></i> Company Profile
                </h1>
                <p class="text-muted lead">Manage your company information and branding</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php echo displayFlashMessage(); ?>
        
        <!-- ✨ Profile Completion Progress -->
        <?php if ($completion < 100): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Complete Your Profile</h6>
                        <p class="mb-2">Your profile is <strong><?php echo $completion; ?>%</strong> complete.  Complete it to attract more candidates! </p>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" 
                                role="progressbar" 
                                style="width:  <?php echo $completion; ?>%"
                                aria-valuenow="<?php echo $completion; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                        <?php if (! empty($profile_check['missing'])): ?>
                            <small class="text-muted mt-2 d-block">
                                Missing:  <?php echo implode(', ', $profile_check['missing']); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== PROFILE FORM ==================== -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <form action="<?php echo APP_URL; ?>controllers/CompanyController.php?action=update" 
                              method="POST" 
                              enctype="multipart/form-data"
                              id="companyProfileForm"
                              class="needs-validation"
                              novalidate>
                            
                            <!-- CSRF Token -->
                            <?php echo Session::csrfField(); ?>
                            
                            <!-- ==================== COMPANY LOGO ==================== -->
                            <div class="text-center mb-5">
                                <div class="logo-upload-wrapper">
                                    <?php if (!empty($company['logo'])): ?>
                                        <img src="<?php echo LOGO_URL .  $company['logo']; ?>" 
                                             alt="Company Logo" 
                                             class="company-logo-preview img-thumbnail mb-3"
                                             id="logoPreview">
                                    <?php else: ?>
                                        <div class="company-logo-placeholder mb-3" id="logoPlaceholder">
                                            <i class="fas fa-building fa-4x text-muted"></i>
                                        </div>
                                        <img src="#" 
                                             alt="Logo Preview" 
                                             class="company-logo-preview img-thumbnail mb-3 d-none"
                                             id="logoPreview">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="btn-group" role="group">
                                    <label for="logo" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> 
                                        <?php echo !empty($company['logo']) ? 'Change Logo' : 'Upload Logo'; ?>
                                    </label>
                                    <input type="file" 
                                           class="d-none" 
                                           id="logo" 
                                           name="logo" 
                                           accept="image/jpeg,image/png,image/jpg">
                                    
                                    <?php if (!empty($company['logo'])): ?>
                                        <a href="<?php echo APP_URL; ?>controllers/CompanyController.php?action=delete-logo" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to remove your logo?');">
                                            <i class="fas fa-trash me-2"></i> Remove
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-muted mt-3 mb-0 small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Recommended: Square image (500x500px), Max <?php echo formatFileSize(MAX_FILE_SIZE); ?> (JPG, PNG)
                                </p>
                            </div>
                            
                            <!-- ==================== BASIC INFORMATION ==================== -->
                            <div class="form-section mb-5">
                                <h5 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-info-circle me-2 text-primary"></i> Basic Information
                                </h5>
                                
                                <!-- Company Name -->
                                <div class="mb-3">
                                    <label for="company_name" class="form-label fw-semibold">
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="company_name" 
                                           name="company_name" 
                                           value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>"
                                           required
                                           minlength="2"
                                           maxlength="100"
                                           placeholder="Your Company Name">
                                    <div class="invalid-feedback">
                                        Company name is required (2-100 characters)
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">
                                        Company Description
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="5"
                                              maxlength="2000"
                                              placeholder="Tell job seekers about your company, mission, culture, and what makes you unique..."><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            A compelling description attracts better candidates
                                        </small>
                                        <small class="text-muted">
                                            <span id="descCount"><?php echo strlen($company['description'] ?? ''); ?></span>/2000
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Industry -->
                                    <div class="col-md-6 mb-3">
                                        <label for="industry" class="form-label fw-semibold">Industry</label>
                                        <select class="form-select" id="industry" name="industry">
                                            <option value="">Select industry</option>
                                            <?php
                                            $industries = [
                                                'IT & Software',
                                                'Finance & Banking',
                                                'Healthcare',
                                                'Education',
                                                'Manufacturing',
                                                'Retail',
                                                'Hospitality',
                                                'Construction',
                                                'Transportation',
                                                'Other'
                                            ];
                                            foreach ($industries as $ind):
                                            ?>
                                                <option value="<?php echo $ind; ?>" 
                                                    <?php echo (isset($company['industry']) && $company['industry'] == $ind) ?  'selected' : ''; ?>>
                                                    <?php echo $ind; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Company Size -->
                                    <div class="col-md-6 mb-3">
                                        <label for="company_size" class="form-label fw-semibold">Company Size</label>
                                        <select class="form-select" id="company_size" name="company_size">
                                            <option value="">Select company size</option>
                                            <option value="1-10" <?php echo (isset($company['company_size']) && $company['company_size'] == '1-10') ? 'selected' : ''; ?>>1-10 employees</option>
                                            <option value="11-50" <?php echo (isset($company['company_size']) && $company['company_size'] == '11-50') ? 'selected' : ''; ?>>11-50 employees</option>
                                            <option value="51-200" <?php echo (isset($company['company_size']) && $company['company_size'] == '51-200') ? 'selected' : ''; ?>>51-200 employees</option>
                                            <option value="201-500" <?php echo (isset($company['company_size']) && $company['company_size'] == '201-500') ? 'selected' : ''; ?>>201-500 employees</option>
                                            <option value="501-1000" <?php echo (isset($company['company_size']) && $company['company_size'] == '501-1000') ? 'selected' : ''; ?>>501-1000 employees</option>
                                            <option value="1000+" <?php echo (isset($company['company_size']) && $company['company_size'] == '1000+') ? 'selected' : ''; ?>>1000+ employees</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== CONTACT INFORMATION ==================== -->
                            <div class="form-section mb-5">
                                <h5 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i> Contact Information
                                </h5>
                                
                                <div class="row">
                                    <!-- Location -->
                                    <div class="col-md-8 mb-3">
                                        <label for="location" class="form-label fw-semibold">
                                            Location <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="location" 
                                               name="location" 
                                               value="<?php echo htmlspecialchars($company['location'] ?? ''); ?>"
                                               required
                                               placeholder="e.g., Kathmandu, Nepal">
                                        <div class="invalid-feedback">
                                            Location is required
                                        </div>
                                    </div>
                                    
                                    <!-- Founded Year -->
                                    <div class="col-md-4 mb-3">
                                        <label for="founded_year" class="form-label fw-semibold">Founded Year</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="founded_year" 
                                               name="founded_year" 
                                               value="<?php echo htmlspecialchars($company['founded_year'] ?? ''); ?>"
                                               min="1800"
                                               max="<?php echo date('Y'); ?>"
                                               placeholder="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                                
                                <!-- Website -->
                                <div class="mb-3">
                                    <label for="website" class="form-label fw-semibold">
                                        <i class="fas fa-globe me-1"></i> Website
                                    </label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="website" 
                                           name="website" 
                                           value="<?php echo htmlspecialchars($company['website'] ??  ''); ?>"
                                           placeholder="https://www.yourcompany.com">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Include https:// or http://
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ==================== SUBMIT BUTTONS ==================== -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="<?php echo APP_URL; ?>views/dashboard/company.php" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success btn-lg" id="saveBtn">
                                    <i class="fas fa-save me-2"></i> Save Profile
                                </button>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
                
            </div>
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="col-lg-4">
                
                <!-- Profile Tips -->
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i> Profile Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Upload a professional logo</strong>
                                <p class="text-muted mb-0">Your brand is the first thing candidates see</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Write a compelling description</strong>
                                <p class="text-muted mb-0">Highlight your mission, culture, and values</p>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Keep information current</strong>
                                <p class="text-muted mb-0">Update regularly to stay relevant</p>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Add your website</strong>
                                <p class="text-muted mb-0">Let candidates learn more about you</p>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Profile Preview -->
                <?php if ($company): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-eye me-2"></i> How Candidates See You
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if (!empty($company['logo'])): ?>
                                <img src="<?php echo LOGO_URL .  $company['logo']; ?>" 
                                     alt="Logo" 
                                     class="img-thumbnail"
                                     style="max-width: 100px;">
                            <?php endif; ?>
                        </div>
                        <h6 class="fw-bold"><?php echo htmlspecialchars($company['company_name'] ?? 'Your Company'); ?></h6>
                        <?php if (!empty($company['industry'])): ?>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-industry me-1"></i>
                                <?php echo htmlspecialchars($company['industry']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($company['location'])): ?>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($company['location']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>
</main>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('companyProfileForm');
    const saveBtn = document.getElementById('saveBtn');
    const logoInput = document.getElementById('logo');
    const logoPreview = document. getElementById('logoPreview');
    const logoPlaceholder = document.getElementById('logoPlaceholder');
    const description = document.getElementById('description');
    const descCount = document.getElementById('descCount');
    
    // Character counter
    if (description && descCount) {
        description.addEventListener('input', function() {
            descCount.textContent = this.value.length;
        });
    }
    
    // Logo preview
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > <?php echo MAX_FILE_SIZE; ?>) {
                    alert('File size must be less than <?php echo formatFileSize(MAX_FILE_SIZE); ?>');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG)');
                    this.value = '';
                    return;
                }
                
                // Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (logoPreview) {
                        logoPreview.src = e.target.result;
                        logoPreview.classList.remove('d-none');
                    }
                    if (logoPlaceholder) {
                        logoPlaceholder.classList.add('d-none');
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form submission
    if (form && saveBtn) {
        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
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
.company-logo-preview {
    max-height: 200px;
    max-width: 200px;
    object-fit: contain;
}

.company-logo-placeholder {
    width: 200px;
    height: 200px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}

@media (min-width: 992px) {
    .sticky-top {
        position: sticky;
        top: 80px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>