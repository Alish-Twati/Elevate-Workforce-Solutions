-- ============================
-- Elevate Workforce Solutions
-- Database Schema
-- 
-- @author Alish Twati
-- @date June 2025
-- @version 1.0
-- ============================

-- Drop existing tables if they exist
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;

-- ============================
-- Users Table
-- ============================

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('jobseeker', 'company', 'admin') NOT NULL DEFAULT 'jobseeker',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Companies Table
-- ============================

CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    website VARCHAR(255),
    logo VARCHAR(255),
    industry VARCHAR(100),
    company_size VARCHAR(50),
    founded_year YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_company_name (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Categories Table
-- ============================

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Jobs Table
-- ============================

CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(255) NOT NULL,
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    job_type ENUM('full-time', 'part-time', 'contract', 'internship') NOT NULL,
    experience_level ENUM('entry', 'intermediate', 'senior', 'executive'),
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_company_id (company_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_location (location),
    INDEX idx_job_type (job_type),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Applications Table
-- ============================

CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume VARCHAR(255) NOT NULL,
    status ENUM('pending', 'reviewed', 'shortlisted', 'rejected', 'accepted') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, user_id),
    INDEX idx_job_id (job_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_applied_at (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================
-- Triggers for automatic updates
-- ============================

DELIMITER $$

-- Trigger to update updated_at on users table
CREATE TRIGGER before_user_update 
BEFORE UPDATE ON users
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger to update updated_at on companies table
CREATE TRIGGER before_company_update 
BEFORE UPDATE ON companies
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger to update updated_at on jobs table
CREATE TRIGGER before_job_update 
BEFORE UPDATE ON jobs
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger to update updated_at on applications table
CREATE TRIGGER before_application_update 
BEFORE UPDATE ON applications
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- ============================
-- Views for common queries
-- ============================

-- View for active jobs with company details
CREATE OR REPLACE VIEW v_active_jobs AS
SELECT 
    j.id,
    j.title,
    j.description,
    j.requirements,
    j.location,
    j.salary_min,
    j.salary_max,
    j.job_type,
    j.experience_level,
    j.deadline,
    j.created_at,
    c.id AS company_id,
    c.company_name,
    c.logo,
    c.location AS company_location,
    cat.name AS category_name,
    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) AS application_count
FROM jobs j
INNER JOIN companies c ON j.company_id = c.id
LEFT JOIN categories cat ON j.category_id = cat.id
WHERE j.status = 'active'
ORDER BY j.created_at DESC;

-- View for application statistics
CREATE OR REPLACE VIEW v_application_stats AS
SELECT 
    u.id AS user_id,
    COUNT(*) AS total_applications,
    SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN a.status = 'reviewed' THEN 1 ELSE 0 END) AS reviewed,
    SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted,
    SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
    SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) AS rejected
FROM users u
LEFT JOIN applications a ON u.id = a.user_id
WHERE u.user_type = 'jobseeker'
GROUP BY u.id;

-- ============================
-- Success Message
-- ============================

SELECT 'Database schema created successfully!' AS message;