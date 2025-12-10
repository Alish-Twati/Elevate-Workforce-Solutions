<?php
/**
 * Authentication Controller
 * 
 * Handles user registration, login, and logout
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../models/Company.php';

    class AuthController {
        
        /**
         * Handle user registration
         */
        public static function register() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    Session::setError('Invalid request.  Please try again.');
                    redirect(APP_URL . 'views/auth/register.php');
                    return;
                }
                
                // Sanitize input
                $email = sanitizeForDB($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $user_type = sanitizeForDB($_POST['user_type'] ?? '');
                $first_name = sanitizeForDB($_POST['first_name'] ?? '');
                $last_name = sanitizeForDB($_POST['last_name'] ?? '');
                $phone = sanitizeForDB($_POST['phone'] ?? '');
                
                // Validation
                $errors = [];
                
                if (empty($email)) {
                    $errors[] = 'Email is required';
                } elseif (!validateEmail($email)) {
                    $errors[] = 'Invalid email format';
                }
                
                if (empty($password)) {
                    $errors[] = 'Password is required';
                } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
                    $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
                }
                
                if ($password !== $confirm_password) {
                    $errors[] = 'Passwords do not match';
                }
                
                if (! in_array($user_type, [USER_TYPE_JOBSEEKER, USER_TYPE_COMPANY])) {
                    $errors[] = 'Invalid user type';
                }
                
                if (empty($first_name)) {
                    $errors[] = 'First name is required';
                }
                
                if (empty($last_name)) {
                    $errors[] = 'Last name is required';
                }
                
                // ✨ Additional validations
                if (!  empty($phone) && ! validatePhone($phone)) {
                    $errors[] = 'Invalid phone number format';
                }
                
                // If company registration, validate company name
                if ($user_type === USER_TYPE_COMPANY && empty($_POST['company_name'])) {
                    $errors[] = 'Company name is required for company registration';
                }
                
                if (! empty($errors)) {
                    Session::setError(implode('<br>', $errors));
                    redirect(APP_URL . 'views/auth/register.php');
                    return;
                }
                
                // Create user
                $user = new User();
                $user->email = $email;
                $user->password = $password;
                $user->user_type = $user_type;
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->phone = $phone;
                
                try {
                    if ($user->register()) {
                        // If company, create company profile
                        if ($user_type === USER_TYPE_COMPANY) {
                            $company = new Company();
                            $company->user_id = $user->id;
                            $company->company_name = sanitizeForDB($_POST['company_name'] ?? '');
                            $company->description = sanitizeForDB($_POST['company_description'] ?? '');
                            $company->location = sanitizeForDB($_POST['company_location'] ?? '');
                            
                            if (! $company->create()) {
                                // ✨ If company profile creation fails, still allow login
                                Session::setWarning('User registered but company profile needs completion');
                            }
                        }
                        
                        Session::setSuccess('Registration successful! Please login to continue.');
                        redirect(APP_URL . 'views/auth/login.php');
                        return;
                    } else {
                        Session::setError('Email already exists or registration failed.  Please try again.');
                        redirect(APP_URL . 'views/auth/register.php');
                        return;
                    }
                } catch (Exception $e) {
                    error_log('Registration error: ' . $e->getMessage());
                    Session::setError('An error occurred during registration. Please try again.');
                    redirect(APP_URL . 'views/auth/register.php');
                    return;
                }
            }
        }
        
        /**
         * Handle user login
         */
        public static function login() {
            // ✨ If already logged in, redirect to dashboard
            if (Session::isLoggedIn()) {
                self::redirectToDashboard();
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    Session::setError('Invalid request. Please try again.');
                    redirect(APP_URL . 'views/auth/login.php');
                    return;
                }
                
                // Sanitize input
                $email = sanitizeForDB($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $remember = isset($_POST['remember']);
                
                // Validation
                if (empty($email)) {
                    Session::setError('Email is required');
                    redirect(APP_URL .  'views/auth/login.php');
                    return;
                }
                
                if (!validateEmail($email)) {
                    Session::setError('Invalid email format');
                    redirect(APP_URL .  'views/auth/login.php');
                    return;
                }
                
                if (empty($password)) {
                    Session::setError('Password is required');
                    redirect(APP_URL . 'views/auth/login.php');
                    return;
                }
                
                try {
                    // Authenticate user
                    $user = new User();
                    $user->email = $email;
                    $user->password = $password;
                    $userData = $user->login();
                    
                    if (! $userData) {
                        // ✨ Enhanced: Generic error message for security
                        Session::setError('Invalid email or password');
                        redirect(APP_URL . 'views/auth/login.php');
                        return;
                    }
                    
                    // ✅ Login successful
                    Session::setUserLogin($userData);
                    
                    // ✨ Remember Me functionality
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        // TODO: Implement proper remember me with database token storage
                        // For now, just store user ID (NOT SECURE - implement token system)
                        setcookie('remember_me', $token, time() + (30*24*60*60), '/', '', false, true);
                    }
                    
                    Session::setSuccess('Welcome back, ' . htmlspecialchars($userData['first_name']) . '!');
                    
                    // ✨ Check for intended URL (from Session::requireLogin)
                    $intended_url = Session::getIntendedUrl();
                    if ($intended_url) {
                        redirect($intended_url);
                        return;
                    }
                    
                    // Redirect based on user type
                    self::redirectToDashboard($userData['user_type']);
                    
                } catch (Exception $e) {
                    error_log('Login error: ' . $e->getMessage());
                    Session::setError('An error occurred during login. Please try again.');
                    redirect(APP_URL . 'views/auth/login.php');
                    return;
                }
            }
        }
        
        /**
         * Handle user logout
         */
        public static function logout() {
            // ✨ Clear remember me cookie
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/', '', false, true);
                // TODO: Delete token from database
            }
            
            $user_name = Session::getUserName();
            Session::destroy();
            
            Session::setSuccess('You have been logged out successfully.  See you soon!');
            redirect(APP_URL . 'index.php');
        }
        
        /**
         * ✨ NEW: Redirect to appropriate dashboard based on user type
         * 
         * @param string|null $user_type User type (gets from session if not provided)
         */
        private static function redirectToDashboard($user_type = null) {
            if ($user_type === null) {
                $user_type = Session::getUserType();
            }
            
            switch ($user_type) {
                case USER_TYPE_COMPANY:
                    redirect(APP_URL . 'views/dashboard/company.php');
                    break;
                case USER_TYPE_ADMIN:
                    redirect(APP_URL . 'views/dashboard/admin.php');
                    break;
                case USER_TYPE_JOBSEEKER:
                default:
                    redirect(APP_URL . 'views/dashboard/jobseeker.php');
                    break;
            }
        }
        
        /**
         * ✨ NEW: Check if user is already logged in (for login/register pages)
         * 
         * @return bool
         */
        public static function checkAlreadyLoggedIn() {
            if (Session::isLoggedIn()) {
                self::redirectToDashboard();
                return true;
            }
            return false;
        }
        
        /**
         * ✨ NEW: Validate password strength
         * 
         * @param string $password Password
         * @return array Array with 'valid' boolean and 'errors' array
         */
        public static function validatePasswordStrength($password) {
            $errors = [];
            
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
            }
            
            if (! preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must contain at least one uppercase letter';
            }
            
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must contain at least one lowercase letter';
            }
            
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must contain at least one number';
            }
            
            // Optional: Require special character
            // if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            //     $errors[] = 'Password must contain at least one special character';
            // }
            
            return [
                'valid' => empty($errors),
                'errors' => $errors
            ];
        }
        
        /**
         * ✨ NEW: Send password reset email (placeholder)
         * 
         * @param string $email User email
         * @return bool
         */
        public static function forgotPassword($email) {
            if (! validateEmail($email)) {
                Session::setError('Invalid email format');
                return false;
            }
            
            $user = new User();
            if (! $user->emailExists(['email' => $email])) {
                // ✨ Security: Don't reveal if email exists
                Session::setSuccess('If that email exists, a password reset link has been sent.');
                return true;
            }
            
            // TODO: Implement password reset token and email sending
            // 1. Generate unique token
            // 2. Store token in database with expiration
            // 3.  Send email with reset link
            
            Session::setSuccess('If that email exists, a password reset link has been sent.');
            return true;
        }
    }

    // Handle actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'register':
                AuthController::register();
                break;
            case 'login':
                AuthController::login();
                break;
            case 'logout':
                AuthController::logout();
                break;
            case 'forgot-password':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    AuthController::forgotPassword($_POST['email'] ?? '');
                    redirect(APP_URL . 'views/auth/login.php');
                }
                break;
            default:
                redirect(APP_URL . 'index.php');
                break;
        }
    }
?>