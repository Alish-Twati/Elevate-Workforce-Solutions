<?php
/**
 * Database Model Class
 * 
 * Base database class using Singleton pattern to ensure only one connection
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    class Database {
        private static $instance = null;
        private $connection;
        
        /**
         * Private constructor to prevent multiple instances
         */
        private function __construct() {
            try {
                $dsn = "mysql:host=" . DB_HOST .  ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false, // ✨ Added: Explicit setting
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                
                // ✨ Enhanced: Show details in debug mode
                if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                    throw new Exception("Database connection failed: " . $e->getMessage());
                }
                throw new Exception("Database connection failed");
            }
        }
        
        /**
         * Get singleton instance of Database
         * 
         * @return Database
         */
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Get PDO connection
         * 
         * @return PDO
         */
        public function getConnection() {
            return $this->connection;
        }
        
        /**
         * Execute a query and return results
         * ✨ Added: Helper method for SELECT queries
         * 
         * @param string $sql SQL query
         * @param array $params Parameters to bind
         * @return array Results
         */
        public function query($sql, $params = []) {
            try {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Query Error: " . $e->getMessage());
                throw new Exception("Database query failed");
            }
        }
        
        /**
         * Execute a statement (INSERT, UPDATE, DELETE)
         * ✨ Added: Helper method for modifications
         * 
         * @param string $sql SQL statement
         * @param array $params Parameters to bind
         * @return bool Success status
         */
        public function execute($sql, $params = []) {
            try {
                $stmt = $this->connection->prepare($sql);
                return $stmt->execute($params);
            } catch (PDOException $e) {
                error_log("Execute Error: " . $e->getMessage());
                throw new Exception("Database execution failed");
            }
        }
        
        /**
         * Get last inserted ID
         * ✨ Added: Useful after INSERT operations
         * 
         * @return string Last insert ID
         */
        public function lastInsertId() {
            return $this->connection->lastInsertId();
        }
        
        /**
         * Begin transaction
         * ✨ Added: For complex operations
         */
        public function beginTransaction() {
            return $this->connection->beginTransaction();
        }
        
        /**
         * Commit transaction
         * ✨ Added
         */
        public function commit() {
            return $this->connection->commit();
        }
        
        /**
         * Rollback transaction
         * ✨ Added
         */
        public function rollback() {
            return $this->connection->rollBack();
        }
        
        /**
         * Prevent cloning of instance
         */
        private function __clone() {}
        
        /**
         * Prevent unserialization of instance
         */
        public function __wakeup() {
            throw new Exception("Cannot unserialize singleton");
        }
        
        /**
         * Close connection on destruct (optional, PHP does this automatically)
         */
        public function __destruct() {
            $this->connection = null;
        }
    }
?>