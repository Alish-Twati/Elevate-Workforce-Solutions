<?php
/**
 * Application Entry Point
 * 
 * Main index file that serves as the entry point for the application
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

// ==================== ERROR REPORTING ====================
// ✨ Only show errors in debug mode
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
    
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/logs/error.log');
    }
} else {
    die('Configuration file not found.  Please check your installation.');
}

// ==================== SECURITY HEADERS ====================
// ✨ Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');

// ✨ Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// ✨ Enable XSS protection
header('X-XSS-Protection: 1; mode=block');

// ✨ Referrer policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// ✨ Content Security Policy (adjust as needed)
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' data: https://cdnjs.cloudflare.com;");

// ==================== SESSION MANAGEMENT ====================
// ✨ Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings (already in Session class, but good to have here too)
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// ==================== AUTOLOADER (Optional) ====================
// ✨ Simple autoloader for classes (if not using Composer)
spl_autoload_register(function ($class_name) {
    $directories = [
        __DIR__ .  '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/helpers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ==================== MAINTENANCE MODE CHECK ====================
// ✨ Check if site is in maintenance mode
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    // Allow access for admin users
    require_once __DIR__ . '/helpers/Session.php';
    
    if (! Session::isAdmin()) {
        http_response_code(503);
        include __DIR__ .  '/views/errors/maintenance.php';
        exit;
    }
}

// ==================== TIMEZONE SETTING ====================
// ✨ Set default timezone
if (defined('APP_TIMEZONE')) {
    date_default_timezone_set(APP_TIMEZONE);
} else {
    date_default_timezone_set('Asia/Kathmandu');
}

// ==================== HELPER FUNCTIONS ====================
// ✨ Load helper functions
if (file_exists(__DIR__ .  '/helpers/functions.php')) {
    require_once __DIR__ . '/helpers/functions.php';
}

// ✨ Load Session helper
if (file_exists(__DIR__ . '/helpers/Session.php')) {
    require_once __DIR__ . '/helpers/Session.php';
}

// ==================== ROUTING ====================
// ✨ Simple routing logic
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';

// Remove query string
$request_uri = strtok($request_uri, '?');

// Get the path relative to the app
$base_path = dirname($script_name);
if ($base_path !== '/') {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Ensure leading slash
$request_uri = '/' . ltrim($request_uri, '/');

// ✨ Route to appropriate page
switch ($request_uri) {
    case '/':
    case '/index.php':
    case '/home':
        // Home page
        require_once __DIR__ . '/views/home.php';
        break;
        
    case '/jobs':
        // Job listings
        require_once __DIR__ . '/views/jobs/index.php';
        break;
        
    case '/login':
        // Login page
        require_once __DIR__ . '/views/auth/login.php';
        break;
        
    case '/register':
        // Registration page
        require_once __DIR__ . '/views/auth/register.php';
        break;
        
    case '/dashboard':
        // Dashboard (redirect based on user type)
        require_once __DIR__ . '/helpers/Session.php';
        require_once __DIR__ . '/controllers/DashboardController.php';
        
        if (Session::isLoggedIn()) {
            DashboardController::redirectToDashboard();
        } else {
            header('Location: ' . APP_URL . 'views/auth/login.php');
        }
        exit;
        break;
        
    case '/logout':
        // Logout
        require_once __DIR__ .  '/controllers/AuthController.php';
        AuthController::logout();
        break;
        
    default:
        // ✨ Check if it's a static file request (let web server handle it)
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $request_uri)) {
            // Let the web server handle static files
            return false;
        }
        
        // ✨ 404 - Page not found
        http_response_code(404);
        if (file_exists(__DIR__ .  '/views/errors/404.php')) {
            require_once __DIR__ . '/views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you are looking for does not exist.</p>';
            echo '<a href="' . APP_URL . '">Go to Homepage</a>';
        }
        break;
}
// Define routes array
$routes = [
    '/' => 'views/home.php',
    '/jobs' => 'views/jobs/index.php',
    '/login' => 'views/auth/login.php',
    '/register' => 'views/auth/register.php',
];

// Match route
if (isset($routes[$request_uri])) {
    require_once __DIR__ . '/' . $routes[$request_uri];
} else {
    // 404 handler
}

// ==================== SHUTDOWN FUNCTION ====================
// ✨ Log fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log the error
        error_log(sprintf(
            "Fatal Error: %s in %s on line %d",
            $error['message'],
            $error['file'],
            $error['line']
        ));
        
        // Show user-friendly error page (only if not in debug mode)
        if (! defined('DEBUG_MODE') || DEBUG_MODE === false) {
            if (file_exists(__DIR__ .  '/views/errors/500.php')) {
                require_once __DIR__ . '/views/errors/500.php';
            } else {
                echo '<h1>500 - Server Error</h1>';
                echo '<p>An unexpected error occurred.  Please try again later.</p>';
            }
            exit;
        }
    }
});

?>