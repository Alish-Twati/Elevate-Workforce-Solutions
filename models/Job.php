<?php
/**
 * Job Model Class
 * 
 * Handles job posting CRUD operations
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/Database.php';

    class Job {
        private $conn;
        private $table = 'jobs';
        
        // Job properties
        public $id;
        public $company_id;
        public $category_id;
        public $title;
        public $description;
        public $requirements;
        public $location;
        public $salary_min;
        public $salary_max;
        public $job_type;
        public $experience_level;
        public $status;
        public $deadline;
        public $created_at;
        
        /**
         * Constructor - Initialize database connection
         */
        public function __construct() {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
        }
        
        /**
         * Create new job posting
         * 
         * @return bool Returns true on success, false on failure
         */
        public function create() {
            $query = "INSERT INTO " . $this->table . " 
                    (company_id, category_id, title, description, requirements, location, 
                    salary_min, salary_max, job_type, experience_level, status, deadline) 
                    VALUES (:company_id, :category_id, :title, :description, :requirements, :location, 
                            :salary_min, :salary_max, :job_type, :experience_level, :status, :deadline)";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':company_id', $this->company_id, PDO::PARAM_INT);
            $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':requirements', $this->requirements);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(':salary_min', $this->salary_min, PDO::PARAM_INT);
            $stmt->bindParam(':salary_max', $this->salary_max, PDO::PARAM_INT);
            $stmt->bindParam(':job_type', $this->job_type);
            $stmt->bindParam(':experience_level', $this->experience_level);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':deadline', $this->deadline);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
        
        /**
         * Update job posting
         * 
         * @return bool Returns true on success, false on failure
         */
        public function update() {
            $query = "UPDATE " . $this->table . " 
                    SET category_id = :category_id,
                        title = :title,
                        description = :description,
                        requirements = :requirements,
                        location = :location,
                        salary_min = :salary_min,
                        salary_max = :salary_max,
                        job_type = :job_type,
                        experience_level = :experience_level,
                        status = :status,
                        deadline = :deadline
                    WHERE id = :id AND company_id = :company_id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':requirements', $this->requirements);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(':salary_min', $this->salary_min, PDO::PARAM_INT);
            $stmt->bindParam(':salary_max', $this->salary_max, PDO::PARAM_INT);
            $stmt->bindParam(':job_type', $this->job_type);
            $stmt->bindParam(':experience_level', $this->experience_level);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':deadline', $this->deadline);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':company_id', $this->company_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Delete job posting
         * 
         * @return bool Returns true on success, false on failure
         */
        public function delete() {
            $query = "DELETE FROM " . $this->table . " 
                    WHERE id = :id AND company_id = :company_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':company_id', $this->company_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Get all active jobs with pagination
         * 
         * @param int $page Current page number
         * @param int $limit Items per page
         * @return array Array of jobs
         */
        public function getAll($page = 1, $limit = JOBS_PER_PAGE) {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT j.*, c.company_name, c.logo, cat.name as category_name,
                            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM " .  $this->table . " j
                    LEFT JOIN companies c ON j.company_id = c.id
                    LEFT JOIN categories cat ON j.category_id = cat. id
                    WHERE j.status = 'active'
                    ORDER BY j.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get total count of active jobs
         * ✨ Enhanced: Added optional filters
         * 
         * @param array $filters Optional filters
         * @return int Total number of active jobs
         */
        public function getTotalCount($filters = []) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                    WHERE status = 'active'";
            
            $params = [];
            
            // Add category filter
            if (!empty($filters['category_id'])) {
                $query .= " AND category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            
            // Add job type filter
            if (!empty($filters['job_type'])) {
                $query .= " AND job_type = :job_type";
                $params[':job_type'] = $filters['job_type'];
            }
            
            // Add location filter
            if (!empty($filters['location'])) {
                $query .= " AND location LIKE :location";
                $params[':location'] = "%{$filters['location']}%";
            }
            
            // Add keyword search
            if (!empty($filters['keyword'])) {
                $query .= " AND (title LIKE :keyword OR description LIKE :keyword)";
                $params[':keyword'] = "%{$filters['keyword']}%";
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return (int)$row['total'];
        }
        
        /**
         * Get job by ID
         * 
         * @param int $id Job ID
         * @return array|bool Job data or false
         */
        public function getById($id) {
            $query = "SELECT j.*, c. company_name, c.location as company_location, 
                            c.logo, c.website, c.description as company_description,
                            cat.name as category_name,
                            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM " . $this->table . " j
                    LEFT JOIN companies c ON j.company_id = c.id
                    LEFT JOIN categories cat ON j.category_id = cat.id
                    WHERE j.id = :id
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Get jobs by company ID
         * 
         * @param int $company_id Company ID
         * @param string $status_filter Optional status filter (active/closed/all)
         * @return array Array of jobs
         */
        public function getByCompany($company_id, $status_filter = 'all') {
            $query = "SELECT j.*, cat.name as category_name,
                            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM " . $this->table . " j
                    LEFT JOIN categories cat ON j.category_id = cat.id
                    WHERE j.company_id = :company_id";
            
            // ✨ Added: Status filter
            if ($status_filter !== 'all') {
                $query .= " AND j.status = :status";
            }
            
            $query .= " ORDER BY j.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            
            if ($status_filter !== 'all') {
                $stmt->bindParam(':status', $status_filter);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Search jobs
         * 🔧 FIXED: Syntax error on line with . =
         * 
         * @param string $keyword Search keyword
         * @param array $filters Additional filters
         * @param int $page Current page
         * @param int $limit Items per page
         * @return array Array of jobs
         */
        public function search($keyword = '', $filters = [], $page = 1, $limit = JOBS_PER_PAGE) {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT j.*, c.company_name, c.logo, cat.name as category_name,
                            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM " . $this->table . " j
                    LEFT JOIN companies c ON j.company_id = c.id
                    LEFT JOIN categories cat ON j.category_id = cat.id
                    WHERE j.status = 'active'";
            
            $params = [];
            
            // Add keyword search
            if (!empty($keyword)) {
                $query .= " AND (j.title LIKE :keyword OR j.description LIKE :keyword OR c.company_name LIKE :keyword)";
                $params[':keyword'] = "%$keyword%";
            }
            
            // Add category filter
            if (!empty($filters['category_id'])) {
                $query .= " AND j.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            
            // Add job type filter
            if (!empty($filters['job_type'])) {
                $query .= " AND j.job_type = :job_type";
                $params[':job_type'] = $filters['job_type'];
            }
            
            // Add location filter
            if (!empty($filters['location'])) {
                $query .= " AND j.location LIKE :location";
                $params[':location'] = "%{$filters['location']}%";
            }
            
            // Add experience level filter
            if (!empty($filters['experience_level'])) {
                $query .= " AND j.experience_level = :experience_level";
                $params[':experience_level'] = $filters['experience_level'];
            }
            
            // ✨ Added: Salary range filter
            if (!empty($filters['min_salary'])) {
                $query .= " AND j.salary_min >= :min_salary";
                $params[':min_salary'] = $filters['min_salary'];
            }
            
            // 🔧 FIXED: Changed from $query .  = to $query .=
            $query .= " ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get job statistics for company
         * 
         * @param int $company_id Company ID
         * @return array Statistics data
         */
        public function getCompanyStats($company_id) {
            $query = "SELECT 
                        COUNT(*) as total_jobs,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_jobs,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_jobs,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_jobs,
                        (SELECT COUNT(*) FROM applications a 
                        INNER JOIN jobs j ON a.job_id = j.id 
                        WHERE j.company_id = :company_id) as total_applications
                    FROM " . $this->table . "
                    WHERE company_id = :company_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * ✨ NEW: Get recent jobs (for homepage)
         * 
         * @param int $limit Number of jobs to fetch
         * @return array Array of recent jobs
         */
        public function getRecentJobs($limit = 6) {
            $query = "SELECT j.*, c.company_name, c.logo, cat. name as category_name
                    FROM " . $this->table . " j
                    LEFT JOIN companies c ON j.company_id = c.id
                    LEFT JOIN categories cat ON j.category_id = cat.id
                    WHERE j.status = 'active' 
                        AND j.deadline >= CURDATE()
                    ORDER BY j.created_at DESC
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * ✨ NEW: Get featured jobs (jobs with most applications or newest)
         * 
         * @param int $limit Number of jobs
         * @return array Array of featured jobs
         */
        public function getFeaturedJobs($limit = 5) {
            $query = "SELECT j.*, c.company_name, c.logo, cat. name as category_name,
                            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                    FROM " . $this->table . " j
                    LEFT JOIN companies c ON j.company_id = c.id
                    LEFT JOIN categories cat ON j.category_id = cat.id
                    WHERE j.status = 'active' 
                        AND j.deadline >= CURDATE()
                    ORDER BY application_count DESC, j.created_at DESC
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * ✨ NEW: Change job status
         * 
         * @param int $job_id Job ID
         * @param string $new_status New status (active/closed/draft)
         * @param int $company_id Company ID (for security)
         * @return bool
         */
        public function changeStatus($job_id, $new_status, $company_id) {
            $query = "UPDATE " . $this->table . " 
                    SET status = :status 
                    WHERE id = :id AND company_id = :company_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':id', $job_id, PDO::PARAM_INT);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * ✨ NEW: Check if job deadline has passed
         * 
         * @param int $job_id Job ID
         * @return bool
         */
        public function isDeadlinePassed($job_id) {
            $query = "SELECT deadline FROM " . $this->table . " 
                    WHERE id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $job_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $job = $stmt->fetch();
            
            if ($job && $job['deadline']) {
                return strtotime($job['deadline']) < time();
            }
            
            return false;
        }
        
        /**
         * ✨ NEW: Validate job data
         * 
         * @return array Array of validation errors
         */
        public function validate() {
            $errors = [];
            
            if (empty($this->title) || strlen($this->title) < 5) {
                $errors[] = "Job title must be at least 5 characters long";
            }
            
            if (empty($this->description) || strlen($this->description) < 20) {
                $errors[] = "Job description must be at least 20 characters long";
            }
            
            if (empty($this->location)) {
                $errors[] = "Location is required";
            }
            
            if (empty($this->job_type)) {
                $errors[] = "Job type is required";
            }
            
            if (empty($this->category_id)) {
                $errors[] = "Category is required";
            }
            
            if (! empty($this->salary_min) && !empty($this->salary_max)) {
                if ($this->salary_min > $this->salary_max) {
                    $errors[] = "Minimum salary cannot be greater than maximum salary";
                }
            }
            
            if (! empty($this->deadline)) {
                if (strtotime($this->deadline) < time()) {
                    $errors[] = "Deadline must be a future date";
                }
            }
            
            return $errors;
        }
    }
?>