<?php
/**
 * Company Model Class
 * 
 * Handles company profile management
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/Database.php';

    class Company {
        private $conn;
        private $table = 'companies';
        
        // Company properties
        public $id;
        public $user_id;
        public $company_name;
        public $description;
        public $location;
        public $website;
        public $logo;
        public $industry;
        public $company_size;
        public $founded_year;
        
        /**
         * Constructor - Initialize database connection
         */
        public function __construct() {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
        }
        
        /**
         * Create company profile
         * 
         * @return bool Returns true on success, false on failure
         */
        public function create() {
            if ($this->profileExists($this->user_id)) {
                return false;
            }
            
            $query = "INSERT INTO " . $this->table .  " 
                    (user_id, company_name, description, location, website, logo, industry, company_size, founded_year) 
                    VALUES (:user_id, :company_name, :description, :location, :website, : logo, :industry, :company_size, :founded_year)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':company_name', $this->company_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(':website', $this->website);
            $stmt->bindParam(':logo', $this->logo);
            $stmt->bindParam(':industry', $this->industry);
            $stmt->bindParam(':company_size', $this->company_size);
            $stmt->bindParam(':founded_year', $this->founded_year, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
        
        /**
         * Update company profile
         * 
         * @return bool Returns true on success, false on failure
         */
        public function update() {
            if (!empty($this->logo)) {
                $query = "UPDATE " . $this->table . " 
                        SET company_name = :company_name,
                            description = :description,
                            location = :location,
                            website = :website,
                            logo = :logo,
                            industry = :industry,
                            company_size = : company_size,
                            founded_year = :founded_year
                        WHERE id = :id AND user_id = :user_id";
            } else {
                $query = "UPDATE " . $this->table . " 
                        SET company_name = :company_name,
                            description = :description,
                            location = :location,
                            website = :website,
                            industry = :industry,
                            company_size = :company_size,
                            founded_year = :founded_year
                        WHERE id = :id AND user_id = : user_id";
            }
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':company_name', $this->company_name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(': website', $this->website);
            
            if (!empty($this->logo)) {
                $stmt->bindParam(':logo', $this->logo);
            }
            
            $stmt->bindParam(':industry', $this->industry);
            $stmt->bindParam(':company_size', $this->company_size);
            $stmt->bindParam(': founded_year', $this->founded_year, PDO::PARAM_INT);
            $stmt->bindParam(':id', $this->id, PDO:: PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Get company by ID
         * 
         * @param int $id Company ID
         * @return array|bool Company data or false
         */
        public function getById($id) {
            $query = "SELECT c. *, u.email, u.first_name, u.last_name 
                    FROM " . $this->table . " c
                    LEFT JOIN users u ON c. user_id = u.id
                    WHERE c.id = : id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Get company by user ID
         * 
         * @param int $user_id User ID
         * @return array|bool Company data or false
         */
        public function getByUserId($user_id) {
            $query = "SELECT * FROM " . $this->table .  " 
                    WHERE user_id = :user_id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO:: PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Check if company profile exists for user
         * 
         * @param int $user_id User ID
         * @return bool Returns true if exists, false otherwise
         */
        public function profileExists($user_id) {
            $query = "SELECT id FROM " . $this->table . " 
                    WHERE user_id = :user_id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(': user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        }
        
        /**
         * Get all companies
         * 
         * @param int $limit Number of records per page
         * @param int $offset Starting position
         * @return array Array of companies
         */
        public function getAll($limit = null, $offset = 0) {
            $query = "SELECT c.*, u.email 
                    FROM " . $this->table . " c
                    LEFT JOIN users u ON c.user_id = u.id
                    ORDER BY c.created_at DESC";
            
            if ($limit !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit !== null) {
                $stmt->bindParam(': limit', $limit, PDO:: PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get total count of companies
         * 
         * @return int Total number of companies
         */
        public function getTotalCount() {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)$result['total'];
        }
        
        /**
         * Search companies by name or location
         * 
         * @param string $keyword Search keyword
         * @return array Array of matching companies
         */
        public function search($keyword) {
            $query = "SELECT c.*, u.email 
                    FROM " . $this->table . " c
                    LEFT JOIN users u ON c.user_id = u. id
                    WHERE c.company_name LIKE :keyword 
                        OR c.location LIKE :keyword 
                        OR c.industry LIKE :keyword
                    ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $search_term = "%{$keyword}%";
            $stmt->bindParam(':keyword', $search_term);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get companies by industry
         * 
         * @param string $industry Industry name
         * @return array Array of companies
         */
        public function getByIndustry($industry) {
            $query = "SELECT c. *, u.email 
                    FROM " . $this->table .  " c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.industry = :industry
                    ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':industry', $industry);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get job count for a company
         * 
         * @param int $company_id Company ID
         * @return int Number of active jobs
         */
        public function getJobCount($company_id) {
            $query = "SELECT COUNT(*) as total 
                    FROM jobs 
                    WHERE company_id = :company_id 
                        AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return (int)$result['total'];
        }
        
        /**
         * Delete company profile
         * 
         * @return bool Returns true on success, false on failure
         */
        public function delete() {
            $company = $this->getById($this->id);
            
            $query = "DELETE FROM " . $this->table . " 
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(': id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($company && ! empty($company['logo'])) {
                    $logo_path = LOGO_PATH . $company['logo'];
                    if (file_exists($logo_path)) {
                        unlink($logo_path);
                    }
                }
                return true;
            }
            
            return false;
        }
        
        /**
         * Validate company data
         * 
         * @return array Array of validation errors (empty if valid)
         */
        public function validate() {
            $errors = [];
            
            if (empty($this->company_name) || strlen($this->company_name) < 2) {
                $errors[] = "Company name must be at least 2 characters long";
            }
            
            if (empty($this->description) || strlen($this->description) < 10) {
                $errors[] = "Description must be at least 10 characters long";
            }
            
            if (empty($this->location)) {
                $errors[] = "Location is required";
            }
            
            if (! empty($this->website) && !filter_var($this->website, FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid website URL";
            }
            
            if (!empty($this->founded_year)) {
                $current_year = date('Y');
                if ($this->founded_year < 1800 || $this->founded_year > $current_year) {
                    $errors[] = "Invalid founded year";
                }
            }
            
            return $errors;
        }
    }
?>