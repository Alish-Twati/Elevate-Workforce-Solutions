<?php
require_once __DIR__ . '/../../config/config.php';  
require_once __DIR__ .  '/../../helpers/Session.php';
$page_title = "Register";
$page_description = "Create your free account on " . APP_NAME .  " to find jobs or hire talent";
$body_class = "auth-page register-page";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

// ✨ Redirect if already logged in
if (Session::isLoggedIn()) {
    require_once __DIR__ . '/../../controllers/DashboardController.php';
    DashboardController::redirectToDashboard();
    exit;
}
?>

<main id="main-content" class="auth-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <!-- Registration Card -->
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="auth-icon mb-3">
                                <i class="fas fa-user-plus fa-3x text-primary"></i>
                            </div>
                            <h2 class="card-title fw-bold mb-2">Create Account</h2>
                            <p class="text-muted">Join <?php echo APP_NAME; ?> today - It's free!</p>
                        </div>
                        
                        <!-- Flash Messages -->
                        <?php echo displayFlashMessage(); ?>
                        
                        <!-- Registration Form -->
                        <form action="<?php echo APP_URL; ?>controllers/AuthController.php? action=register" 
                              method="POST" 
                              id="registerForm" 
                              class="needs-validation" 
                              novalidate>
                            
                            <!-- CSRF Token -->
                            <?php echo Session::csrfField(); ?>
                            
                            <!-- ===================== USER TYPE SELECTION ===================== -->
                            <div class="mb-4">
                                <label class="form-label fw-bold fs-5">
                                    <i class="fas fa-user-tag me-2 text-primary"></i> I am a:
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="user-type-card">
                                            <input class="form-check-input d-none" 
                                                   type="radio" 
                                                   name="user_type" 
                                                   id="jobseeker" 
                                                   value="<?php echo USER_TYPE_JOBSEEKER; ?>" 
                                                   checked>
                                            <label class="card p-3 cursor-pointer user-type-label" for="jobseeker">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user fa-2x text-info me-3"></i>
                                                    <div>
                                                        <h6 class="mb-1">Job Seeker</h6>
                                                        <small class="text-muted">Looking for opportunities</small>
                                                    </div>
                                                    <i class="fas fa-check-circle text-success ms-auto d-none"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="user-type-card">
                                            <input class="form-check-input d-none" 
                                                   type="radio" 
                                                   name="user_type" 
                                                   id="company" 
                                                   value="<?php echo USER_TYPE_COMPANY; ?>">
                                            <label class="card p-3 cursor-pointer user-type-label" for="company">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-building fa-2x text-success me-3"></i>
                                                    <div>
                                                        <h6 class="mb-1">Company / Employer</h6>
                                                        <small class="text-muted">Looking to hire talent</small>
                                                    </div>
                                                    <i class="fas fa-check-circle text-success ms-auto d-none"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ===================== PERSONAL INFORMATION ===================== -->
                            <h5 class="mb-3">
                                <i class="fas fa-user-circle me-2 text-primary"></i> Personal Information
                            </h5>
                            
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-1"></i> First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           placeholder="John" 
                                           required
                                           minlength="2"
                                           maxlength="50"
                                           pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">
                                        First name must be 2-50 characters (letters only)
                                    </div>
                                </div>
                                
                                <!-- Last Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user me-1"></i> Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           placeholder="Doe" 
                                           required
                                           minlength="2"
                                           maxlength="50"
                                           pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">
                                        Last name must be 2-50 characters (letters only)
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i> Email Address <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="your.email@example.com" 
                                           required>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> We'll never share your email
                                </small>
                            </div>
                            
                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-1"></i> Phone Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+977</span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           placeholder="9800000000" 
                                           required
                                           pattern="9[0-9]{9}"
                                           maxlength="10">
                                    <div class="invalid-feedback">
                                        Enter valid Nepal phone (e.g., 9841234567)
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Format: 98XXXXXXXX
                                </small>
                            </div>
                            
                            <!-- ===================== COMPANY FIELDS (CONDITIONAL) ===================== -->
                            <div id="company_fields" class="d-none">
                                <h5 class="mb-3 mt-4">
                                    <i class="fas fa-building me-2 text-primary"></i> Company Information
                                </h5>
                                
                                <!-- Company Name -->
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">
                                        <i class="fas fa-building me-1"></i> Company Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="company_name" 
                                           name="company_name" 
                                           placeholder="Your Company Name"
                                           minlength="2"
                                           maxlength="100">
                                    <div class="invalid-feedback">
                                        Company name is required (2-100 characters)
                                    </div>
                                </div>
                                
                                <!-- Optional: Company Description -->
                                <div class="mb-3">
                                    <label for="company_description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i> Company Description (Optional)
                                    </label>
                                    <textarea class="form-control" 
                                              id="company_description" 
                                              name="company_description" 
                                              rows="3"
                                              placeholder="Brief description of your company..."
                                              maxlength="500"></textarea>
                                    <small class="form-text text-muted">
                                        <span id="desc_count">0</span>/500 characters
                                    </small>
                                </div>
                                
                                <!-- Company Location -->
                                <div class="mb-3">
                                    <label for="company_location" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i> Company Location
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="company_location" 
                                           name="company_location" 
                                           placeholder="e.g., Kathmandu, Nepal">
                                </div>
                            </div>
                            
                            <!-- ===================== PASSWORD FIELDS ===================== -->
                            <h5 class="mb-3 mt-4">
                                <i class="fas fa-lock me-2 text-primary"></i> Security
                            </h5>
                            
                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Min. <?php echo PASSWORD_MIN_LENGTH; ?> characters" 
                                               required 
                                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">
                                            Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters
                                        </div>
                                    </div>
                                    <!-- Password Strength Indicator -->
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar"></div>
                                        </div>
                                        <small class="form-text text-muted" id="strengthText">Password strength</small>
                                    </div>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               placeholder="Re-enter password" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback" id="confirmPasswordFeedback">
                                            Passwords must match
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ===================== TERMS & SUBMIT ===================== -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="terms" 
                                           required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the 
                                        <a href="<?php echo APP_URL; ?>views/pages/terms.php" target="_blank">Terms and Conditions</a> 
                                        and 
                                        <a href="<?php echo APP_URL; ?>views/pages/privacy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree to the terms and conditions
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        class="btn btn-primary btn-lg" 
                                        id="registerBtn">
                                    <i class="fas fa-user-plus me-2"></i> Create Account
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-2">Already have an account? </p>
                            <a href="<?php echo APP_URL; ?>views/auth/login.php" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i> Login Instead
                            </a>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Help Text -->
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your information is protected with industry-standard encryption
                    </p>
                </div>
                
            </div>
        </div>
    </div>
</main>

<!-- Page-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document. getElementById('confirm_password');
    const companyFields = document.getElementById('company_fields');
    const companyNameInput = document.getElementById('company_name');
    
    // ==================== USER TYPE TOGGLE ====================
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const userTypeLabels = document.querySelectorAll('.user-type-label');
    
    userTypeRadios.forEach((radio, index) => {
        radio.addEventListener('change', function() {
            // Update visual selection
            userTypeLabels. forEach(label => {
                label.classList.remove('border-primary', 'bg-light');
                label.querySelector('. fa-check-circle').classList.add('d-none');
            });
            
            const selectedLabel = this.closest('.user-type-card').querySelector('.user-type-label');
            selectedLabel.classList.add('border-primary', 'bg-light');
            selectedLabel.querySelector('.fa-check-circle').classList.remove('d-none');
            
            // Show/hide company fields
            if (this.value === '<?php echo USER_TYPE_COMPANY; ?>') {
                companyFields.classList.remove('d-none');
                companyNameInput.required = true;
            } else {
                companyFields.classList. add('d-none');
                companyNameInput.required = false;
            }
        });
        
        // Trigger change on page load for checked radio
        if (radio.checked) {
            radio.dispatchEvent(new Event('change'));
        }
    });
    
    // ==================== PASSWORD VISIBILITY TOGGLE ====================
    function setupPasswordToggle(toggleBtn, passwordInput) {
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                
                const icon = this.querySelector('i');
                icon.classList. toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }
    
    setupPasswordToggle(document.getElementById('togglePassword'), passwordField);
    setupPasswordToggle(document.getElementById('toggleConfirmPassword'), confirmPasswordField);
    
    // ==================== PASSWORD STRENGTH METER ====================
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;
            
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'progress-bar';
            
            if (strength < 40) {
                strengthBar.classList.add('bg-danger');
                strengthText.textContent = 'Weak password';
                strengthText.className = 'form-text text-danger';
            } else if (strength < 70) {
                strengthBar.classList.add('bg-warning');
                strengthText.textContent = 'Medium password';
                strengthText.className = 'form-text text-warning';
            } else {
                strengthBar.classList.add('bg-success');
                strengthText.textContent = 'Strong password';
                strengthText.className = 'form-text text-success';
            }
        });
    }
    
    // ==================== PASSWORD MATCH VALIDATION ====================
    function validatePasswordMatch() {
        if (confirmPasswordField.value) {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match');
                confirmPasswordField. classList.add('is-invalid');
            } else {
                confirmPasswordField.setCustomValidity('');
                confirmPasswordField.classList. remove('is-invalid');
                confirmPasswordField.classList.add('is-valid');
            }
        }
    }
    
    passwordField.addEventListener('input', validatePasswordMatch);
    confirmPasswordField. addEventListener('input', validatePasswordMatch);
    
    // ==================== CHARACTER COUNTER ====================
    const companyDesc = document.getElementById('company_description');
    const descCount = document.getElementById('desc_count');
    
    if (companyDesc && descCount) {
        companyDesc.addEventListener('input', function() {
            descCount.textContent = this.value.length;
        });
    }
    
    // ==================== FORM SUBMISSION ====================
    if (registerForm && registerBtn) {
        registerForm.addEventListener('submit', function(e) {
            // Validate password match
            validatePasswordMatch();
            
            if (registerForm.checkValidity() && passwordField.value === confirmPasswordField.value) {
                // Show loading state
                registerBtn.disabled = true;
                registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
            } else {
                e.preventDefault();
                e.stopPropagation();
            }
            
            registerForm.classList.add('was-validated');
        });
    }
    
    // ==================== REAL-TIME VALIDATION ====================
    const inputs = registerForm.querySelectorAll('input:not([type="radio"]):not([type="checkbox"])');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this. classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (this.value) {
                this.classList.add('is-invalid');
            }
        });
    });
});
</script>

<style>
/* User Type Card Styles */
.user-type-card {
    cursor: pointer;
}

.user-type-label {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.user-type-label:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.user-type-label.border-primary {
    border-color: #0d6efd ! important;
    background-color: #f0f8ff;
}

.cursor-pointer {
    cursor: pointer;
}

/* Password Strength Indicator */
.password-strength . progress {
    background-color: #e9ecef;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>