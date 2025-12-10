<?php
/**
 * Job Controller
 * 
 * Handles job CRUD operations, search, and filtering
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Job.php';
    require_once __DIR__ . '/../models/Company.php';
    require_once __DIR__ . '/../models/Category.php';

    class JobController {
        
        /**
         * Display all jobs with pagination
         */
        public static function index() {
            $job = new Job();
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // ✨ Ensure page >= 1
            
            // Get search parameters
            $keyword = isset($_GET['keyword']) ?  sanitizeForDB($_GET['keyword']) : '';
            $filters = [
                'category_id' => isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null,
                'job_type' => isset($_GET['job_type']) && $_GET['job_type'] !== '' ? sanitizeForDB($_GET['job_type']) : null,
                'location' => isset($_GET['location']) && $_GET['location'] !== '' ? sanitizeForDB($_GET['location']) : null,
                'experience_level' => isset($_GET['experience_level']) && $_GET['experience_level'] !== '' ? sanitizeForDB($_GET['experience_level']) : null,
                // ✨ Added: Salary filter
                'min_salary' => isset($_GET['min_salary']) && $_GET['min_salary'] !== '' ? (int)$_GET['min_salary'] : null,
            ];
            
            // Remove null/empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Get jobs
            try {
                if (! empty($keyword) || !empty($filters)) {
                    $jobs = $job->search($keyword, $filters, $page);
                    $total_jobs = $job->getTotalCount($filters); // ✨ Pass filters to count
                } else {
                    $jobs = $job->getAll($page);
                    $total_jobs = $job->getTotalCount();
                }
            } catch (Exception $e) {
                error_log('Job index error: ' . $e->getMessage());
                Session::setError('An error occurred while fetching jobs');
                $jobs = [];
                $total_jobs = 0;
            }
            
            return [
                'jobs' => $jobs,
                'total_jobs' => $total_jobs,
                'current_page' => $page,
                'keyword' => $keyword,
                'filters' => $filters
            ];
        }
        
        /**
         * Display single job details
         */
        public static function show($id) {
            $id = (int)$id; // ✨ Type cast
            
            if ($id <= 0) {
                Session::setError('Invalid job ID');
                redirect(APP_URL . 'views/jobs/index.php');
                return null;
            }
            
            try {
                $job = new Job();
                $jobData = $job->getById($id);
                
                if (!$jobData) {
                    Session::setError('Job not found');
                    redirect(APP_URL . 'views/jobs/index.php');
                    return null;
                }
                
                // ✨ Check if job is active (for public viewing)
                if ($jobData['status'] !== JOB_STATUS_ACTIVE && ! Session::isCompany()) {
                    Session::setError('This job is no longer available');
                    redirect(APP_URL .  'views/jobs/index. php');
                    return null;
                }
                
                // ✨ Check deadline
                if (isDeadlinePassed($jobData['deadline'])) {
                    $jobData['deadline_passed'] = true;
                }
                
                return $jobData;
                
            } catch (Exception $e) {
                error_log('Job show error: ' . $e->getMessage());
                Session::setError('An error occurred while fetching job details');
                redirect(APP_URL . 'views/jobs/index.php');
                return null;
            }
        }
        
        /**
         * Create new job posting
         */
        public static function create() {
            // Require company login
            Session::requireCompany();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (! Session::validatePostCSRF()) {
                    Session::setError('Invalid request.  Please try again.');
                    redirect(APP_URL . 'views/jobs/create.php');
                    return;
                }
                
                // Get company ID
                try {
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    if (!$companyData) {
                        Session::setError('Please complete your company profile first');
                        redirect(APP_URL . 'views/company/profile.php');
                        return;
                    }
                } catch (Exception $e) {
                    error_log('Company fetch error: ' . $e->getMessage());
                    Session::setError('An error occurred.  Please try again.');
                    redirect(APP_URL . 'views/jobs/create.php');
                    return;
                }
                
                // Sanitize input
                $title = sanitizeForDB($_POST['title'] ?? '');
                $description = sanitizeForDB($_POST['description'] ?? '');
                $requirements = sanitizeForDB($_POST['requirements'] ?? '');
                $location = sanitizeForDB($_POST['location'] ?? '');
                $salary_min = ! empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
                $salary_max = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
                $job_type = sanitizeForDB($_POST['job_type'] ?? '');
                $experience_level = sanitizeForDB($_POST['experience_level'] ?? '');
                $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $deadline = ! empty($_POST['deadline']) ? sanitizeForDB($_POST['deadline']) : null;
                $status = sanitizeForDB($_POST['status'] ?? JOB_STATUS_ACTIVE);
                
                // Validation
                $errors = [];
                
                if (empty($title) || strlen($title) < 5) {
                    $errors[] = 'Job title must be at least 5 characters';
                }
                
                if (empty($description) || strlen($description) < 20) {
                    $errors[] = 'Job description must be at least 20 characters';
                }
                
                if (empty($location)) {
                    $errors[] = 'Location is required';
                }
                
                if (! in_array($job_type, ['full-time', 'part-time', 'contract', 'internship', 'remote'])) {
                    $errors[] = 'Invalid job type';
                }
                
                if (! in_array($status, [JOB_STATUS_ACTIVE, JOB_STATUS_DRAFT, JOB_STATUS_CLOSED])) {
                    $errors[] = 'Invalid job status';
                }
                
                // ✨ Salary validation
                if ($salary_min !== null && $salary_max !== null && $salary_min > $salary_max) {
                    $errors[] = 'Minimum salary cannot be greater than maximum salary';
                }
                
                // ✨ Deadline validation
                if ($deadline && strtotime($deadline) < time()) {
                    $errors[] = 'Deadline must be a future date';
                }
                
                // ✨ Category validation
                if ($category_id) {
                    $category = new Category();
                    if (! $category->getById($category_id)) {
                        $errors[] = 'Invalid category selected';
                    }
                }
                
                if (! empty($errors)) {
                    Session::setError(implode('<br>', $errors));
                    redirect(APP_URL . 'views/jobs/create.php');
                    return;
                }
                
                // Create job
                try {
                    $job = new Job();
                    $job->company_id = $companyData['id'];
                    $job->category_id = $category_id;
                    $job->title = $title;
                    $job->description = $description;
                    $job->requirements = $requirements;
                    $job->location = $location;
                    $job->salary_min = $salary_min;
                    $job->salary_max = $salary_max;
                    $job->job_type = $job_type;
                    $job->experience_level = $experience_level;
                    $job->status = $status;
                    $job->deadline = $deadline;
                    
                    // ✨ Use model validation
                    $validation_errors = $job->validate();
                    if (!empty($validation_errors)) {
                        Session::setError(implode('<br>', $validation_errors));
                        redirect(APP_URL . 'views/jobs/create.php');
                        return;
                    }
                    
                    if ($job->create()) {
                        Session::setSuccess('Job posted successfully!');
                        redirect(APP_URL . 'views/dashboard/company.php');
                    } else {
                        Session::setError('Failed to create job posting.  Please try again.');
                        redirect(APP_URL . 'views/jobs/create.php');
                    }
                } catch (Exception $e) {
                    error_log('Job create error: ' . $e->getMessage());
                    Session::setError('An error occurred while creating the job');
                    redirect(APP_URL .  'views/jobs/create. php');
                }
            }
        }
        
        /**
         * Update job posting
         */
        public static function update($id) {
            // Require company login
            Session::requireCompany();
            
            $id = (int)$id;
            
            if ($id <= 0) {
                Session::setError('Invalid job ID');
                redirect(APP_URL . 'views/dashboard/company.php');
                return null;
            }
            
            try {
                // Get job details
                $job = new Job();
                $jobData = $job->getById($id);
                
                if (!$jobData) {
                    Session::setError('Job not found');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return null;
                }
                
                // Get company
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                // Check ownership
                if ($jobData['company_id'] != $companyData['id']) {
                    Session::setError('Unauthorized access');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return null;
                }
                
            } catch (Exception $e) {
                error_log('Job update validation error: ' . $e->getMessage());
                Session::setError('An error occurred');
                redirect(APP_URL .  'views/dashboard/company.php');
                return null;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request. Please try again.');
                    redirect(APP_URL . 'views/jobs/edit.php?id=' . $id);
                    return null;
                }
                
                // Sanitize input
                $title = sanitizeForDB($_POST['title'] ?? '');
                $description = sanitizeForDB($_POST['description'] ?? '');
                $requirements = sanitizeForDB($_POST['requirements'] ?? '');
                $location = sanitizeForDB($_POST['location'] ?? '');
                $salary_min = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
                $salary_max = ! empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
                $job_type = sanitizeForDB($_POST['job_type'] ?? '');
                $experience_level = sanitizeForDB($_POST['experience_level'] ?? '');
                $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $deadline = !empty($_POST['deadline']) ? sanitizeForDB($_POST['deadline']) : null;
                $status = sanitizeForDB($_POST['status'] ??  JOB_STATUS_ACTIVE);
                
                // Validation
                $errors = [];
                
                if (empty($title) || strlen($title) < 5) {
                    $errors[] = 'Job title must be at least 5 characters';
                }
                
                if (empty($description) || strlen($description) < 20) {
                    $errors[] = 'Job description must be at least 20 characters';
                }
                
                if ($salary_min !== null && $salary_max !== null && $salary_min > $salary_max) {
                    $errors[] = 'Minimum salary cannot be greater than maximum salary';
                }
                
                if (! empty($errors)) {
                    Session::setError(implode('<br>', $errors));
                    redirect(APP_URL . 'views/jobs/edit.php?id=' .  $id);
                    return null;
                }
                
                // Update job
                try {
                    $job->id = $id;
                    $job->company_id = $companyData['id'];
                    $job->category_id = $category_id;
                    $job->title = $title;
                    $job->description = $description;
                    $job->requirements = $requirements;
                    $job->location = $location;
                    $job->salary_min = $salary_min;
                    $job->salary_max = $salary_max;
                    $job->job_type = $job_type;
                    $job->experience_level = $experience_level;
                    $job->status = $status;
                    $job->deadline = $deadline;
                    
                    if ($job->update()) {
                        Session::setSuccess('Job updated successfully!');
                        redirect(APP_URL . 'views/dashboard/company.php');
                    } else {
                        Session::setError('Failed to update job. Please try again.');
                        redirect(APP_URL . 'views/jobs/edit.php? id=' . $id);
                    }
                } catch (Exception $e) {
                    error_log('Job update error: ' . $e->getMessage());
                    Session::setError('An error occurred while updating the job');
                    redirect(APP_URL . 'views/jobs/edit.php?id=' .  $id);
                }
            }
            
            return $jobData;
        }
        
        /**
         * Delete job posting
         */
        public static function delete($id) {
            // Require company login
            Session::requireCompany();
            
            $id = (int)$id;
            
            // Validate CSRF token
            if (!Session::validatePostCSRF()) {
                Session::setError('Invalid request');
                redirect(APP_URL . 'views/dashboard/company.php');
                return;
            }
            
            if ($id <= 0) {
                Session::setError('Invalid job ID');
                redirect(APP_URL . 'views/dashboard/company.php');
                return;
            }
            
            try {
                // Get job details
                $job = new Job();
                $jobData = $job->getById($id);
                
                if (!$jobData) {
                    Session::setError('Job not found');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return;
                }
                
                // Get company
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                // Check ownership
                if ($jobData['company_id'] != $companyData['id']) {
                    Session::setError('Unauthorized access');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return;
                }
                
                // Delete job
                $job->id = $id;
                $job->company_id = $companyData['id'];
                
                if ($job->delete()) {
                    Session::setSuccess('Job deleted successfully!');
                } else {
                    Session::setError('Failed to delete job.  Please try again.');
                }
                
            } catch (Exception $e) {
                error_log('Job delete error: ' . $e->getMessage());
                Session::setError('An error occurred while deleting the job');
            }
            
            redirect(APP_URL . 'views/dashboard/company.php');
        }
        
        /**
         * ✨ NEW: Change job status (active/closed/draft)
         */
        public static function changeStatus($id, $new_status) {
            Session::requireCompany();
            
            $id = (int)$id;
            
            if (! Session::validatePostCSRF()) {
                Session::setError('Invalid request');
                redirect(APP_URL .  'views/dashboard/company. php');
                return;
            }
            
            try {
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                $job = new Job();
                if ($job->changeStatus($id, $new_status, $companyData['id'])) {
                    Session::setSuccess('Job status updated successfully!');
                } else {
                    Session::setError('Failed to update job status');
                }
            } catch (Exception $e) {
                error_log('Change status error: ' . $e->getMessage());
                Session::setError('An error occurred');
            }
            
            redirect(APP_URL . 'views/dashboard/company.php');
        }
        
        /**
         * ✨ NEW: Get company's jobs
         */
        public static function getCompanyJobs($status_filter = 'all') {
            Session::requireCompany();
            
            try {
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                if (! $companyData) {
                    return [];
                }
                
                $job = new Job();
                return $job->getByCompany($companyData['id'], $status_filter);
                
            } catch (Exception $e) {
                error_log('Get company jobs error: ' . $e->getMessage());
                return [];
            }
        }
    }

    // Handle actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'create':
                JobController::create();
                break;
            case 'update':
                if (isset($_GET['id'])) {
                    JobController::update($_GET['id']);
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    JobController::delete($_POST['id']);
                }
                break;
            case 'change-status':
                if (isset($_POST['id']) && isset($_POST['status'])) {
                    JobController::changeStatus($_POST['id'], $_POST['status']);
                }
                break;
            default:
                redirect(APP_URL . 'views/jobs/index.php');
                break;
        }
    }
    ?>