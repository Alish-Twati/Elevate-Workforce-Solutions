<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ .  '/../../helpers/Session.php';
$page_title = "Login";
$page_description = "Login to your " . APP_NAME . " account to access jobs and applications";
$body_class = "auth-page login-page";

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
            <div class="col-md-6 col-lg-5">
                
                <!-- Login Card -->
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="auth-icon mb-3">
                                <i class="fas fa-sign-in-alt fa-3x text-primary"></i>
                            </div>
                            <h2 class="card-title fw-bold mb-2">Welcome Back!</h2>
                            <p class="text-muted">Login to continue your job search</p>
                        </div>
                        
                        <!-- Flash Messages -->
                        <?php echo displayFlashMessage(); ?>
                        
                        <!-- Login Form -->
                        <form action="<?php echo APP_URL; ?>controllers/AuthController.php? action=login" 
                              method="POST" id="loginForm" class="needs-validation" novalidate>
                            
                            <!-- CSRF Token -->
                            <?php echo Session::csrfField(); ?>
                            
                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-1 text-primary"></i> Email Address
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="email" 
                                           name="email" 
                                           placeholder="your.email@example.com" 
                                           value="<?php echo isset($_SESSION['login_email']) ? htmlspecialchars($_SESSION['login_email']) : ''; ?>"
                                           required 
                                           autofocus>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address. 
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-1 text-primary"></i> Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password" 
                                           required
                                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword"
                                            title="Show/Hide Password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters.
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters required
                                </small>
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="remember" 
                                           name="remember"
                                           value="1">
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="<?php echo APP_URL; ?>views/auth/forgot-password.php" 
                                   class="text-decoration-none small">
                                    Forgot Password?
                                </a>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        class="btn btn-primary btn-lg" 
                                        id="loginBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <!-- ✨ Demo Credentials (only in debug mode) -->
                        <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <strong><i class="fas fa-info-circle me-2"></i>Demo Credentials:</strong>
                                <hr class="my-2">
                                <div class="row small">
                                    <div class="col-6">
                                        <strong>Job Seeker:</strong><br>
                                        jobseeker@test.com<br>
                                        password123
                                    </div>
                                    <div class="col-6">
                                        <strong>Company:</strong><br>
                                        company@test.com<br>
                                        password123
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-2">Don't have an account? </p>
                            <a href="<?php echo APP_URL; ?>views/auth/register.php" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </a>
                        </div>
                        
                        <!-- ✨ Alternative Login Options (Future) -->
                        <!-- <div class="text-center mt-4">
                            <p class="text-muted small mb-2">Or continue with</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="fab fa-google me-1"></i> Google
                                </button>
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="fab fa-facebook me-1"></i> Facebook
                                </button>
                            </div>
                        </div> -->
                        
                    </div>
                </div>
                
                <!-- ✨ Help Text -->
                <div class="text-center mt-4">
                    <p class="text-muted small mb-1">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your information is protected with industry-standard encryption
                    </p>
                    <p class="text-muted small">
                        Need help? <a href="<?php echo APP_URL; ?>views/pages/contact.php">Contact Support</a>
                    </p>
                </div>
                
            </div>
        </div>
    </div>
</main>

<!-- ✨ Page-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    // Toggle password visibility
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            
            const icon = this.querySelector('i');
            icon.classList. toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form submission with loading state
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function(e) {
            if (loginForm.checkValidity()) {
                // Show loading state
                loginBtn.disabled = true;
                loginBtn. innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
            } else {
                e.preventDefault();
                e.stopPropagation();
            }
            loginForm.classList.add('was-validated');
        });
    }
    
    // Auto-focus on email if empty
    const emailField = document.getElementById('email');
    if (emailField && !emailField.value) {
        emailField.focus();
    }
    
    // Remove validation on input
    const inputs = loginForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this. classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
});

// ✨ Quick fill demo credentials (debug mode only)
<?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
function fillJobSeeker() {
    document.getElementById('email').value = 'jobseeker@test.com';
    document.getElementById('password').value = 'password123';
}

function fillCompany() {
    document.getElementById('email').value = 'company@test.com';
    document.getElementById('password').value = 'password123';
}
<?php endif; ?>
</script>

<?php 
// ✨ Clear login email from session (used for form persistence)
unset($_SESSION['login_email']);

require_once __DIR__ . '/../layouts/footer.php'; 
?>