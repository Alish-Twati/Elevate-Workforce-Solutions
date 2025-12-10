<?php
/**
 * Database Configuration File
 * 
 * This file contains the database connection settings for the 
 * Elevate Workforce Solutions application. 
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    // Database credentials
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'elevate_jobs');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');

    /**
     * Get database connection using PDO
     * 
     * @return PDO Returns PDO connection object
     * @throws PDOException on connection failure
     */
    function getDBConnection() {
        static $pdo = null; // ✨ Added: Singleton pattern to reuse connection
        
        if ($pdo !== null) {
            return $pdo;
        }
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" .  DB_NAME . ";charset=" .  DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false, // ✨ Added: Explicitly disable persistent connections
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // ✨ Improved: Better error message for development
            if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                die("Database connection failed: " . $e->getMessage());
            }
            die("Database connection failed.  Please contact administrator.");
        }
    }
?>