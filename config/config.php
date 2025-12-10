<?php
/**
 * Application Configuration File
 * 
 * Contains global application settings and constants
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    // ==================== APPLICATION SETTINGS ====================
    define('APP_NAME', 'Elevate Workforce Solutions');
    define('APP_VERSION', '1.0.0');
    define('APP_URL', 'http://localhost/elevate-workforce-solutions/');
    define('DEBUG_MODE', true); // Set to false in production
    define('MAINTENANCE_MODE', false);

    // ==================== TIMEZONE ====================
    define('APP_TIMEZONE', 'Asia/Kathmandu');
    date_default_timezone_set(APP_TIMEZONE);

    // ==================== PATH SETTINGS ====================
    define('BASE_PATH', dirname(__DIR__));
    define('UPLOAD_PATH', BASE_PATH .  '/public/uploads/');
    define('RESUME_PATH', UPLOAD_PATH . 'resumes/');
    define('LOGO_PATH', UPLOAD_PATH . 'logos/');
    define('LOG_PATH', BASE_PATH . '/logs/');

    // ==================== URL PATHS ====================
    define('UPLOAD_URL', APP_URL . 'public/uploads/');
    define('RESUME_URL', UPLOAD_URL . 'resumes/');
    define('LOGO_URL', UPLOAD_URL .  'logos/');

    // ==================== FILE UPLOAD SETTINGS ====================
    define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
    define('ALLOWED_RESUME_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml. document']);
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
    define('ALLOWED_RESUME_EXT', ['pdf', 'doc', 'docx']);
    define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png']);

    // ==================== PAGINATION ====================
    define('JOBS_PER_PAGE', 10);
    define('APPLICATIONS_PER_PAGE', 15);

    // ==================== SECURITY ====================
    define('PASSWORD_MIN_LENGTH', 8);
    define('SESSION_LIFETIME', 3600); // 1 hour

    // ==================== USER TYPES ====================
    define('USER_TYPE_JOBSEEKER', 'jobseeker');
    define('USER_TYPE_COMPANY', 'company');
    define('USER_TYPE_ADMIN', 'admin');

    // ==================== JOB STATUS ====================
    define('JOB_STATUS_ACTIVE', 'active');
    define('JOB_STATUS_CLOSED', 'closed');
    define('JOB_STATUS_DRAFT', 'draft');

    // ==================== APPLICATION STATUS ====================
    define('APP_STATUS_PENDING', 'pending');
    define('APP_STATUS_REVIEWED', 'reviewed');
    define('APP_STATUS_SHORTLISTED', 'shortlisted');
    define('APP_STATUS_REJECTED', 'rejected');
    define('APP_STATUS_ACCEPTED', 'accepted');

    // ==================== ERROR REPORTING ====================
    if (DEBUG_MODE) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_PATH . 'error.log');
    }

    // ==================== CREATE DIRECTORIES ====================
    $directories = [
        RESUME_PATH,
        LOGO_PATH,
        LOG_PATH
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // ==================== INCLUDE REQUIRED FILES ====================
    // Include database config
    if (file_exists(BASE_PATH .  '/config/database.php')) {
        require_once BASE_PATH . '/config/database.php';
    } else {
        die('ERROR: Database configuration file not found! ');
    }

    // Include helper functions
    if (file_exists(BASE_PATH . '/helpers/functions.php')) {
        require_once BASE_PATH . '/helpers/functions.php';
    } else {
        die('ERROR: Helper functions file not found!');
    }

    // Include session helper
    if (file_exists(BASE_PATH . '/helpers/Session.php')) {
        require_once BASE_PATH . '/helpers/Session.php';
    } else {
        die('ERROR: Session helper file not found!');
    }

    // ==================== START SESSION ====================
    // Start session AFTER all files are loaded
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => SESSION_LIFETIME,
            'cookie_httponly' => true,
            'cookie_secure' => false, // Set to true if using HTTPS
            'use_strict_mode' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }

    // ==================== MAINTENANCE MODE CHECK ====================
    if (MAINTENANCE_MODE && ! isset($_SESSION['is_admin'])) {
        if (file_exists(BASE_PATH . '/views/errors/maintenance.php')) {
            http_response_code(503);
            require_once BASE_PATH . '/views/errors/maintenance.php';
            exit;
        } else {
            die('Site is under maintenance. Please check back later.');
        }
    }
?>