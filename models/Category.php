<?php
/**
 * Category Model Class
 * 
 * Handles job category operations
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1. 0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/Database.php';

    class Category {
        private $conn;
        private $table = 'categories';
        
        // Category properties
        public $id;
        public $name;
        public $description;
        public $created_at;
        
        /**
         * Constructor - Initialize database connection
         */
        public function __construct() {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
        }
        
        /**
         * Get all categories
         * ✨ Enhanced: Added active filter option
         * 
         * @param bool $only_with_jobs Only return categories that have active jobs
         * @return array Array of all categories
         */
        public function getAll($only_with_jobs = false) {
            if ($only_with_jobs) {
                $query = "SELECT DISTINCT c.* 
                        FROM " . $this->table . " c
                        INNER JOIN jobs j ON c.id = j.category_id 
                        WHERE j.status = 'active'
                        ORDER BY c.name ASC";
            } else {
                $query = "SELECT * FROM " . $this->table . " 
                        ORDER BY name ASC";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * Get category by ID
         * 
         * @param int $id Category ID
         * @return array|bool Category data or false
         */
        public function getById($id) {
            $query = "SELECT * FROM " . $this->table . " 
                    WHERE id = :id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * ✨ NEW: Get category by name
         * 
         * @param string $name Category name
         * @return array|bool Category data or false
         */
        public function getByName($name) {
            $query = "SELECT * FROM " . $this->table . " 
                    WHERE name = :name 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            return $stmt->fetch();
        }
        
        /**
         * Create new category
         * ✨ Enhanced: Check for duplicate names
         * 
         * @return bool Returns true on success, false on failure
         */
        public function create() {
            // Check if category name already exists
            if ($this->nameExists()) {
                return false;
            }
            
            $query = "INSERT INTO " . $this->table . " 
                    (name, description) 
                    VALUES (:name, :description)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':description', $this->description);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
        
        /**
         * Update category
         * ✨ Enhanced: Check for duplicate names (excluding current category)
         * 
         * @return bool Returns true on success, false on failure
         */
        public function update() {
            // Check if new name conflicts with existing category
            if ($this->nameExists($this->id)) {
                return false;
            }
            
            $query = "UPDATE " . $this->table . " 
                    SET name = :name, description = :description 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Delete category
         * ✨ Enhanced: Prevent deletion if category has jobs
         * 
         * @param bool $force Force delete even if jobs exist (will set jobs to NULL)
         * @return bool Returns true on success, false on failure
         */
        public function delete($force = false) {
            // Check if category has jobs
            if (! $force && $this->hasJobs()) {
                return false;
            }
            
            // If forcing, update jobs to remove category reference
            if ($force) {
                $update_query = "UPDATE jobs SET category_id = NULL WHERE category_id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                $update_stmt->execute();
            }
            
            $query = "DELETE FROM " .  $this->table . " 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        /**
         * Get category with job count
         * ✨ Enhanced: Added optional limit for popular categories
         * 
         * @param int $limit Optional limit for top categories
         * @return array Array of categories with job counts
         */
        public function getCategoriesWithJobCount($limit = null) {
            $query = "SELECT c. *, COUNT(j.id) as job_count
                    FROM " . $this->table . " c
                    LEFT JOIN jobs j ON c.id = j.category_id AND j.status = 'active'
                    GROUP BY c.id
                    ORDER BY job_count DESC, c.name ASC";
            
            if ($limit !== null) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit !== null) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * ✨ NEW: Get popular categories (most jobs)
         * 
         * @param int $limit Number of categories to return
         * @return array Array of popular categories
         */
        public function getPopularCategories($limit = 5) {
            return $this->getCategoriesWithJobCount($limit);
        }
        
        /**
         * ✨ NEW: Check if category name exists
         * 
         * @param int $exclude_id Optional ID to exclude (for updates)
         * @return bool
         */
        private function nameExists($exclude_id = null) {
            $query = "SELECT id FROM " . $this->table .  " 
                    WHERE name = :name";
            
            if ($exclude_id !== null) {
                $query .= " AND id != :exclude_id";
            }
            
            $query .= " LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            
            if ($exclude_id !== null) {
                $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        }
        
        /**
         * ✨ NEW: Check if category has jobs
         * 
         * @return bool
         */
        public function hasJobs() {
            $query = "SELECT COUNT(*) as total FROM jobs 
                    WHERE category_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['total'] > 0;
        }
        
        /**
         * ✨ NEW: Get job count for this category
         * 
         * @param bool $active_only Count only active jobs
         * @return int
         */
        public function getJobCount($active_only = true) {
            $query = "SELECT COUNT(*) as total FROM jobs 
                    WHERE category_id = :id";
            
            if ($active_only) {
                $query .= " AND status = 'active'";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int)$result['total'];
        }
        
        /**
         * ✨ NEW: Search categories by name
         * 
         * @param string $keyword Search keyword
         * @return array Array of matching categories
         */
        public function search($keyword) {
            $query = "SELECT c.*, COUNT(j.id) as job_count
                    FROM " . $this->table . " c
                    LEFT JOIN jobs j ON c.id = j.category_id AND j.status = 'active'
                    WHERE c.name LIKE :keyword OR c.description LIKE :keyword
                    GROUP BY c.id
                    ORDER BY c.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $search_term = "%{$keyword}%";
            $stmt->bindParam(':keyword', $search_term);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }
        
        /**
         * ✨ NEW: Get total count of categories
         * 
         * @return int
         */
        public function getTotalCount() {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int)$result['total'];
        }
        
        /**
         * ✨ NEW: Validate category data
         * 
         * @return array Array of validation errors
         */
        public function validate() {
            $errors = [];
            
            if (empty($this->name) || strlen($this->name) < 2) {
                $errors[] = "Category name must be at least 2 characters long";
            }
            
            if (strlen($this->name) > 50) {
                $errors[] = "Category name must not exceed 50 characters";
            }
            
            if (! empty($this->description) && strlen($this->description) > 255) {
                $errors[] = "Description must not exceed 255 characters";
            }
            
            // Check for duplicate name
            if ($this->nameExists($this->id)) {
                $errors[] = "A category with this name already exists";
            }
            
            return $errors;
        }
    }
?>