<?php
/**
 * User Model Class
 * 
 * Handles user authentication and profile management
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/Database.php';

    class User {
        private $conn;
        private $table = 'users';
        
        // User properties
        public $id;
        public $email;
        public $password;
        public $user_type;
        public $first_name;
        public $last_name;
        public $phone;
        public $is_active;
        public $created_at;
        
        /**
         * Constructor - Initialize database connection
         */
        public function __construct() {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
        }
        
        /**
         * Register a new user
         * 
         * @return bool Returns true on success, false on failure
         */
        public function register() {
            // Validate email doesn't already exist
            if ($this->emailExists()) {
                return false;
            }
            
            $query = "INSERT INTO " . $this->table . " 
                    (email, password, user_type, first_name, last_name, phone) 
                    VALUES (:email, :password, :user_type, :first_name, :last_name, :phone)";
            
            $stmt = $this->conn->prepare($query);
            
            // Hash password before storing
            $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
            
            // Bind parameters
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_type', $this->user_type);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':phone', $this->phone);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
        
        /**
         * Authenticate user login
         * 🔧 FIXED: Now properly fetches password field
         * 
         * @return array|bool Returns user data on success, false on failure
         */
        public function login() {
            // ✨ FIXED: Added password, user_type, first_name, last_name to SELECT
            $query = "SELECT id, email, password, user_type, first_name, last_name, phone, is_active, created_at 
                    FROM " . $this->table . " 
                    WHERE email = :email AND is_active = 1 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            // ✨ FIXED: Check if user exists first, then verify password
            if ($user && password_verify($this->password, $user['password'])) {
                // ✨ SECURITY: Remove password from returned data
                unset($user['password']);
                return $user;
            }
            
            return false;
        }
        
        /**
         * Check if email already exists in database
         * 
         * @return bool Returns true if email exists, false otherwise
         */
        public function emailExists() {
            $query = "SELECT id FROM " . $this->table . " 
                    WHERE email = :email 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        }
        
        /**
         * Get user by ID
         * 
         * @param int $id User ID
         * @return array|bool User data or false
         */
        public function getUserById($id) {
            $query = "SELECT id, email, user_type, first_name, last_name, phone, is_active, created_at 
                    FROM " . $this->table .  " 
                    WHERE id = :id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ✨ Added type hint
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Update user profile
         * 
         * @return bool Returns true on success, false on failure
         */
        public function updateProfile() {
            $query = "UPDATE " . $this->table . " 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        phone = :phone 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT); // ✨ Added type hint
            
            return $stmt->execute();
        }
        
        /**
         * Change user password
         * 
         * @param string $old_password Current password
         * @param string $new_password New password
         * @return bool Returns true on success, false on failure
         */
        public function changePassword($old_password, $new_password) {
            // ✨ FIXED: Need to select password to verify it
            $query = "SELECT password FROM " . $this->table .  " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($old_password, $user['password'])) {
                return false;
            }
            
            $query = "UPDATE " . $this->table . " 
                    SET password = :password 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Generate password reset token
         * 
         * @return string Reset token
         */
        public function generateResetToken() {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H: i:s', strtotime('+1 hour'));
            
            $query = "UPDATE " . $this->table . " 
                    SET reset_token = :token, 
                        reset_expires = : expires 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(': expires', $expires);
            $stmt->bindParam(': id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $token;
            }
            return false;
        }

        /**
         * Reset password using token
         * 
         * @param string $token Reset token
         * @param string $new_password New password
         * @return bool
         */
        public function resetPassword($token, $new_password) {
            $query = "SELECT id FROM " . $this->table . " 
                    WHERE reset_token = : token 
                    AND reset_expires > NOW() 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $user = $stmt->fetch();
            if (!$user) {
                return false;
            }
            
            $query = "UPDATE " .  $this->table . " 
                    SET password = :password, 
                        reset_token = NULL, 
                        reset_expires = NULL 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            
            $stmt->bindParam(':password', $hashed);
            $stmt->bindParam(':id', $user['id'], PDO:: PARAM_INT);
            
            return $stmt->execute();
        }
        /**
         * Get all users (admin function)
         * 
         * @return array Array of all users
         */
        public function getAllUsers() {
            $query = "SELECT id, email, user_type, first_name, last_name, phone, is_active, created_at 
                    FROM " . $this->table . " 
                    ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * ✨ NEW: Toggle user active status (admin function)
         * 
         * @param int $user_id User ID to toggle
         * @return bool
         */
        public function toggleActiveStatus($user_id) {
            $query = "UPDATE " .  $this->table . " 
                    SET is_active = NOT is_active 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * ✨ NEW: Get users by type (jobseeker/company)
         * 
         * @param string $type User type
         * @return array
         */
        public function getUsersByType($type) {
            $query = "SELECT id, email, user_type, first_name, last_name, phone, is_active, created_at 
                    FROM " . $this->table . " 
                    WHERE user_type = :type 
                    ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Validate email format
         * 
         * @param string $email Email address
         * @return bool Returns true if valid, false otherwise
         */
        public static function validateEmail($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        /**
         * Validate password strength
         * 
         * @param string $password Password
         * @return bool Returns true if valid, false otherwise
         */
        public static function validatePassword($password) {
            return strlen($password) >= PASSWORD_MIN_LENGTH;
        }
        
        /**
         * ✨ NEW: Sanitize user input
         * 
         * @param string $data Input data
         * @return string Sanitized data
         */
        public static function sanitize($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return $data;
        }
    }
?>