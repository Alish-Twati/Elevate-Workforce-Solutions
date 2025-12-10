<?php
/**
 * Application Model Class
 * 
 * Handles job application management
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/Database.php';

    class Application {
        private $conn;
        private $table = 'applications';
        
        // Application properties
        public $id;
        public $job_id;
        public $user_id;
        public $cover_letter;
        public $resume;
        public $status;
        public $applied_at;
        
        /**
         * Constructor - Initialize database connection
         */
        public function __construct() {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
        }
        
        /**
         * Create new job application
         * 
         * @return bool Returns true on success, false on failure
         */
        public function create() {
            // Check for duplicate application
            if ($this->checkDuplicate()) {
                return false;
            }
            
            $query = "INSERT INTO " . $this->table . " 
                    (job_id, user_id, cover_letter, resume, status) 
                    VALUES (:job_id, :user_id, :cover_letter, :resume, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Set default status if not provided
            if (empty($this->status)) {
                $this->status = APP_STATUS_PENDING;
            }
            
            // Bind parameters
            $stmt->bindParam(':job_id', $this->job_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':cover_letter', $this->cover_letter);
            $stmt->bindParam(':resume', $this->resume);
            $stmt->bindParam(':status', $this->status);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
        
        /**
         * Update application status
         * ✨ Enhanced: Added company_id security check
         * 
         * @param int $company_id Optional company ID for authorization
         * @return bool Returns true on success, false on failure
         */
        public function updateStatus($company_id = null) {
            // If company_id provided, verify ownership
            if ($company_id !== null) {
                $query = "UPDATE " . $this->table . " a
                        INNER JOIN jobs j ON a.job_id = j.id
                        SET a.status = :status 
                        WHERE a.id = :id AND j.company_id = :company_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':status', $this->status);
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            } else {
                $query = "UPDATE " . $this->table . " 
                        SET status = :status 
                        WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':status', $this->status);
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        }
        
        /**
         * Get application by ID
         * 
         * @param int $id Application ID
         * @return array|bool Application data or false
         */
        public function getById($id) {
            $query = "SELECT a.*, j.title as job_title, j.company_id, j.location, j.job_type,
                            u.first_name, u.last_name, u.email, u.phone,
                            c.company_name, c.logo
                    FROM " . $this->table . " a
                    INNER JOIN jobs j ON a.job_id = j.id
                    INNER JOIN users u ON a.user_id = u.id
                    INNER JOIN companies c ON j.company_id = c.id
                    WHERE a.id = :id
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Get applications by job ID
         * ✨ Enhanced: Added status filter and pagination
         * 
         * @param int $job_id Job ID
         * @param string $status_filter Optional status filter
         * @param int $limit Optional limit
         * @param int $offset Optional offset
         * @return array Array of applications
         */
        public function getByJob($job_id, $status_filter = null, $limit = null, $offset = 0) {
            $query = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone
                    FROM " . $this->table . " a
                    INNER JOIN users u ON a.user_id = u.id
                    WHERE a.job_id = :job_id";
            
            if ($status_filter !== null) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.applied_at DESC";
            
            if ($limit !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
            
            if ($status_filter !== null) {
                $stmt->bindParam(':status', $status_filter);
            }
            
            if ($limit !== null) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get applications by user ID
         * ✨ Enhanced: Added status filter
         * 
         * @param int $user_id User ID
         * @param string $status_filter Optional status filter
         * @return array Array of applications
         */
        public function getByUser($user_id, $status_filter = null) {
            $query = "SELECT a.*, j.title as job_title, j.location, j.job_type, j.status as job_status,
                            c.company_name, c.logo
                    FROM " . $this->table . " a
                    INNER JOIN jobs j ON a. job_id = j.id
                    INNER JOIN companies c ON j.company_id = c.id
                    WHERE a.user_id = :user_id";
            
            if ($status_filter !== null) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.applied_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($status_filter !== null) {
                $stmt->bindParam(':status', $status_filter);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get applications for company's jobs
         * ✨ Enhanced: Added status filter and pagination
         * 
         * @param int $company_id Company ID
         * @param string $status_filter Optional status filter
         * @param int $limit Optional limit
         * @param int $offset Optional offset
         * @return array Array of applications
         */
        public function getByCompany($company_id, $status_filter = null, $limit = null, $offset = 0) {
            $query = "SELECT a.*, j. title as job_title, j.id as job_id,
                            u.first_name, u.last_name, u.email, u.phone
                    FROM " . $this->table . " a
                    INNER JOIN jobs j ON a.job_id = j.id
                    INNER JOIN users u ON a.user_id = u.id
                    WHERE j.company_id = :company_id";
            
            if ($status_filter !== null) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.applied_at DESC";
            
            if ($limit !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            
            if ($status_filter !== null) {
                $stmt->bindParam(':status', $status_filter);
            }
            
            if ($limit !== null) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Check if user already applied for this job
         * 
         * @return bool Returns true if duplicate, false otherwise
         */
        public function checkDuplicate() {
            $query = "SELECT id FROM " .  $this->table . " 
                    WHERE job_id = :job_id AND user_id = :user_id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':job_id', $this->job_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        }
        
        /**
         * ✨ NEW: Check if user can apply (job is active and deadline hasn't passed)
         * 
         * @return array Returns array with 'can_apply' boolean and 'reason' string
         */
        public function canApply() {
            $query = "SELECT status, deadline FROM jobs WHERE id = :job_id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':job_id', $this->job_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $job = $stmt->fetch();
            
            if (! $job) {
                return ['can_apply' => false, 'reason' => 'Job not found'];
            }
            
            if ($job['status'] !== 'active') {
                return ['can_apply' => false, 'reason' => 'Job is not active'];
            }
            
            if ($job['deadline'] && strtotime($job['deadline']) < time()) {
                return ['can_apply' => false, 'reason' => 'Application deadline has passed'];
            }
            
            if ($this->checkDuplicate()) {
                return ['can_apply' => false, 'reason' => 'You have already applied for this job'];
            }
            
            return ['can_apply' => true, 'reason' => ''];
        }
        
        /**
         * Delete application
         * ✨ Enhanced: Also delete resume file
         * 
         * @return bool Returns true on success, false on failure
         */
        public function delete() {
            // Get application data to delete resume file
            $app = $this->getById($this->id);
            
            $query = "DELETE FROM " . $this->table . " 
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // ✨ Delete resume file if exists
                if ($app && ! empty($app['resume'])) {
                    $resume_path = RESUME_PATH . $app['resume'];
                    if (file_exists($resume_path)) {
                        unlink($resume_path);
                    }
                }
                return true;
            }
            
            return false;
        }
        
        /**
         * Get application statistics for user
         * 
         * @param int $user_id User ID
         * @return array Statistics data
         */
        public function getUserStats($user_id) {
            $query = "SELECT 
                        COUNT(*) as total_applications,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM " . $this->table . "
                    WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * ✨ NEW: Get application statistics for company
         * 
         * @param int $company_id Company ID
         * @return array Statistics data
         */
        public function getCompanyStats($company_id) {
            $query = "SELECT 
                        COUNT(*) as total_applications,
                        SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN a.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                        SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                        SUM(CASE WHEN a. status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                        SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM " . $this->table .  " a
                    INNER JOIN jobs j ON a.job_id = j.id
                    WHERE j.company_id = :company_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * ✨ NEW: Get count of applications for a specific job
         * 
         * @param int $job_id Job ID
         * @return int Number of applications
         */
        public function getJobApplicationCount($job_id) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                    WHERE job_id = :job_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int)$result['total'];
        }
        
        /**
         * ✨ NEW: Bulk update application status
         * 
         * @param array $application_ids Array of application IDs
         * @param string $new_status New status
         * @param int $company_id Company ID for security
         * @return bool
         */
        public function bulkUpdateStatus($application_ids, $new_status, $company_id) {
            if (empty($application_ids)) {
                return false;
            }
            
            $placeholders = implode(',', array_fill(0, count($application_ids), '?'));
            
            $query = "UPDATE " .  $this->table . " a
                    INNER JOIN jobs j ON a.job_id = j.id
                    SET a.status = ? 
                    WHERE a.id IN ($placeholders) AND j.company_id = ? ";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind new status
            $params = [$new_status];
            // Bind application IDs
            $params = array_merge($params, $application_ids);
            // Bind company ID
            $params[] = $company_id;
            
            return $stmt->execute($params);
        }
        
        /**
         * ✨ NEW: Validate application data
         * 
         * @return array Array of validation errors
         */
        public function validate() {
            $errors = [];
            
            if (empty($this->job_id)) {
                $errors[] = "Job ID is required";
            }
            
            if (empty($this->user_id)) {
                $errors[] = "User ID is required";
            }
            
            if (empty($this->cover_letter) || strlen($this->cover_letter) < 50) {
                $errors[] = "Cover letter must be at least 50 characters long";
            }
            
            if (empty($this->resume)) {
                $errors[] = "Resume is required";
            }
            
            // Check if can apply
            $can_apply = $this->canApply();
            if (!$can_apply['can_apply']) {
                $errors[] = $can_apply['reason'];
            }
            
            return $errors;
        }
    }
?>