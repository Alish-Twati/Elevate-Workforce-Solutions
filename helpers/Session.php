<?php
/**
 * Session Management Class
 * 
 * Handles session operations and security
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    class Session {
        
        /**
         * Start session if not already started
         * ✨ Enhanced: Better security configuration
         */
        public static function start() {
            if (session_status() === PHP_SESSION_NONE) {
                // Enhanced security settings
                ini_set('session.cookie_httponly', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
                ini_set('session.use_strict_mode', 1); // ✨ Added: Reject uninitialized session IDs
                ini_set('session.cookie_samesite', 'Lax'); // ✨ Added: CSRF protection
                
                // ✨ Added: Session lifetime from config
                if (defined('SESSION_LIFETIME')) {
                    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
                    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
                }
                
                session_start();
                
                // ✨ Added: Regenerate session ID periodically
                if (! self::has('last_regeneration')) {
                    self::regenerateId();
                } elseif (time() - self::get('last_regeneration') > 300) { // Every 5 minutes
                    self::regenerateId();
                }
            }
        }
        
        /**
         * ✨ NEW: Regenerate session ID
         * 
         * @param bool $delete_old_session Delete old session data
         */
        public static function regenerateId($delete_old_session = false) {
            session_regenerate_id($delete_old_session);
            self::set('last_regeneration', time());
        }
        
        /**
         * Set session variable
         * 
         * @param string $key Session key
         * @param mixed $value Session value
         */
        public static function set($key, $value) {
            self::start();
            $_SESSION[$key] = $value;
        }
        
        /**
         * Get session variable
         * 
         * @param string $key Session key
         * @param mixed $default Default value if key doesn't exist
         * @return mixed Session value or default
         */
        public static function get($key, $default = null) {
            self::start();
            return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        }
        
        /**
         * Check if session variable exists
         * 
         * @param string $key Session key
         * @return bool Returns true if exists, false otherwise
         */
        public static function has($key) {
            self::start();
            return isset($_SESSION[$key]);
        }
        
        /**
         * Remove session variable
         * 
         * @param string $key Session key
         */
        public static function remove($key) {
            self::start();
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }
        
        /**
         * Destroy entire session
         * ✨ Enhanced: Complete cleanup
         */
        public static function destroy() {
            self::start();
            
            // ✨ Clear session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            session_unset();
            session_destroy();
        }
        
        /**
         * Set user login session
         * ✨ Enhanced: Added session regeneration for security
         * 
         * @param array $user User data
         */
        public static function setUserLogin($user) {
            // ✨ Regenerate session ID on login to prevent session fixation
            self::regenerateId(true);
            
            self::set('user_id', $user['id']);
            self::set('user_email', $user['email']);
            self::set('user_type', $user['user_type']);
            self::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
            self::set('first_name', $user['first_name']); // ✨ Added
            self::set('last_name', $user['last_name']); // ✨ Added
            self::set('logged_in', true);
            self::set('login_time', time()); // ✨ Added: Track login time
            
            // ✨ Added: Store IP address for security
            self::set('user_ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        }
        
        /**
         * ✨ NEW: Logout user
         */
        public static function logout() {
            self::destroy();
        }
        
        /**
         * Check if user is logged in
         * ✨ Enhanced: Added session timeout check
         * 
         * @return bool Returns true if logged in, false otherwise
         */
        public static function isLoggedIn() {
            if (self::get('logged_in', false) !== true) {
                return false;
            }
            
            // ✨ Check session timeout
            if (defined('SESSION_LIFETIME')) {
                $login_time = self::get('login_time', 0);
                if (time() - $login_time > SESSION_LIFETIME) {
                    self::destroy();
                    return false;
                }
            }
            
            // ✨ Optional: Check if IP changed (security feature)
            // Uncomment if you want strict IP checking
            /*
            $stored_ip = self::get('user_ip');
            $current_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if ($stored_ip !== $current_ip) {
                self::destroy();
                return false;
            }
            */
            
            return true;
        }
        
        /**
         * Get logged in user ID
         * 
         * @return int|null User ID or null
         */
        public static function getUserId() {
            return self::get('user_id');
        }
        
        /**
         * ✨ NEW: Get logged in user email
         * 
         * @return string|null User email or null
         */
        public static function getUserEmail() {
            return self::get('user_email');
        }
        
        /**
         * ✨ NEW: Get logged in user name
         * 
         * @return string|null User name or null
         */
        public static function getUserName() {
            return self::get('user_name');
        }
        
        /**
         * Get logged in user type
         * 
         * @return string|null User type or null
         */
        public static function getUserType() {
            return self::get('user_type');
        }
        
        /**
         * Check if user is job seeker
         * 
         * @return bool Returns true if job seeker, false otherwise
         */
        public static function isJobSeeker() {
            return self::getUserType() === USER_TYPE_JOBSEEKER;
        }
        
        /**
         * Check if user is company
         * 
         * @return bool Returns true if company, false otherwise
         */
        public static function isCompany() {
            return self::getUserType() === USER_TYPE_COMPANY;
        }
        
        /**
         * Check if user is admin
         * 
         * @return bool Returns true if admin, false otherwise
         */
        public static function isAdmin() {
            return self::getUserType() === USER_TYPE_ADMIN;
        }
        
        /**
         * Set flash message
         * 
         * @param string $type Message type (success, error, warning, info)
         * @param string $message Message content
         */
        public static function setFlash($type, $message) {
            self::set('flash_type', $type);
            self::set('flash_message', $message);
        }
        
        /**
         * ✨ NEW: Set success flash message
         * 
         * @param string $message Message content
         */
        public static function setSuccess($message) {
            self::setFlash('success', $message);
        }
        
        /**
         * ✨ NEW: Set error flash message
         * 
         * @param string $message Message content
         */
        public static function setError($message) {
            self::setFlash('error', $message);
        }
        
        /**
         * ✨ NEW: Set warning flash message
         * 
         * @param string $message Message content
         */
        public static function setWarning($message) {
            self::setFlash('warning', $message);
        }
        
        /**
         * ✨ NEW: Set info flash message
         * 
         * @param string $message Message content
         */
        public static function setInfo($message) {
            self::setFlash('info', $message);
        }
        
        /**
         * Get and remove flash message
         * 
         * @return array|null Flash message array or null
         */
        public static function getFlash() {
            if (self::has('flash_message')) {
                $flash = [
                    'type' => self::get('flash_type'),
                    'message' => self::get('flash_message')
                ];
                self::remove('flash_type');
                self::remove('flash_message');
                return $flash;
            }
            return null;
        }
        
        /**
         * ✨ NEW: Check if flash message exists
         * 
         * @return bool
         */
        public static function hasFlash() {
            return self::has('flash_message');
        }
        
        /**
         * Require login - redirect if not logged in
         * 
         * @param string $redirect_url URL to redirect if not logged in
         */
        public static function requireLogin($redirect_url = null) {
            if (!self::isLoggedIn()) {
                if ($redirect_url === null) {
                    $redirect_url = APP_URL . 'views/auth/login.php';
                }
                
                // ✨ Store intended URL to redirect after login
                self::set('intended_url', $_SERVER['REQUEST_URI'] ?? '');
                
                self::setFlash('error', 'Please login to access this page');
                header("Location: " .$redirect_url);
                exit();
            }
        }
        
        /**
         * ✨ NEW: Get and clear intended URL
         * 
         * @param string $default Default URL if no intended URL
         * @return string
         */
        public static function getIntendedUrl($default = null) {
            $url = self::get('intended_url', $default);
            self::remove('intended_url');
            return $url;
        }
        
        /**
         * Require specific user type
         * 
         * @param string|array $allowed_types Allowed user type(s)
         * @param string $redirect_url URL to redirect if not allowed
         */
        public static function requireUserType($allowed_types, $redirect_url = null) {
            self::requireLogin();
            
            if ($redirect_url === null) {
                $redirect_url = APP_URL .  'index.php';
            }
            
            $allowed_types = is_array($allowed_types) ?  $allowed_types : [$allowed_types];
            
            if (!in_array(self::getUserType(), $allowed_types)) {
                self::setFlash('error', 'Access denied.  You do not have permission to access this page.');
                header("Location: " .$redirect_url);
                exit();
            }
        }
        
        /**
         * ✨ NEW: Require job seeker
         */
        public static function requireJobSeeker() {
            self::requireUserType(USER_TYPE_JOBSEEKER);
        }
        
        /**
         * ✨ NEW: Require company
         */
        public static function requireCompany() {
            self::requireUserType(USER_TYPE_COMPANY);
        }
        
        /**
         * ✨ NEW: Require admin
         */
        public static function requireAdmin() {
            self::requireUserType(USER_TYPE_ADMIN);
        }
        
        /**
         * Generate CSRF token
         * 
         * @return string CSRF token
         */
        public static function generateCSRFToken() {
            if (! self::has('csrf_token')) {
                self::set('csrf_token', bin2hex(random_bytes(32)));
            }
            return self::get('csrf_token');
        }
        
        /**
         * Validate CSRF token
         * 
         * @param string $token Token to validate
         * @return bool Returns true if valid, false otherwise
         */
        public static function validateCSRFToken($token) {
            return self::has('csrf_token') && hash_equals(self::get('csrf_token'), $token);
        }
        
        /**
         * ✨ NEW: Get CSRF token HTML input field
         * 
         * @return string HTML input field
         */
        public static function csrfField() {
            $token = self::generateCSRFToken();
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) .  '">';
        }
        
        /**
         * ✨ NEW: Validate CSRF token from POST request
         * 
         * @return bool
         */
        public static function validatePostCSRF() {
            $token = $_POST['csrf_token'] ?? '';
            return self::validateCSRFToken($token);
        }
        
        /**
         * ✨ NEW: Get all session data (for debugging)
         * 
         * @return array
         */
        public static function all() {
            self::start();
            return $_SESSION;
        }
        
        /**
         * ✨ NEW: Clear all session data except user login
         * 
         * @return void
         */
        public static function flush() {
            $user_data = [
                'user_id' => self::get('user_id'),
                'user_email' => self::get('user_email'),
                'user_type' => self::get('user_type'),
                'user_name' => self::get('user_name'),
                'first_name' => self::get('first_name'),
                'last_name' => self::get('last_name'),
                'logged_in' => self::get('logged_in'),
                'login_time' => self::get('login_time'),
                'user_ip' => self::get('user_ip'),
                'csrf_token' => self::get('csrf_token'),
            ];
            
            session_unset();
            
            foreach ($user_data as $key => $value) {
                if ($value !== null) {
                    self::set($key, $value);
                }
            }
        }
    }
?>