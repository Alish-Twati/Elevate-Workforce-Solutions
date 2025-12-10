<?php
    /**
     * Application Controller
     * 
     * Handles job application operations
     * 
     * @author Alish Twati
     * @date June 2025
     * @version 1.0
     */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Application.php';
    require_once __DIR__ . '/../models/Job.php';
    require_once __DIR__ . '/../models/Company.php';

    class ApplicationController {
        
        /**
         * Create new job application
         */
        public static function apply($job_id) {
            // Require job seeker login
            Session::requireJobSeeker();
            
            $job_id = (int)$job_id;
            
            if ($job_id <= 0) {
                Session::setError('Invalid job ID');
                redirect(APP_URL . 'views/jobs/index.php');
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request.  Please try again.');
                    redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
                    return;
                }
                
                try {
                    // Check if job exists
                    $job = new Job();
                    $jobData = $job->getById($job_id);
                    
                    if (!$jobData) {
                        Session::setError('Job not found');
                        redirect(APP_URL . 'views/jobs/index.php');
                        return;
                    }
                    
                    // ✨ Check if job is active
                    if ($jobData['status'] !== JOB_STATUS_ACTIVE) {
                        Session::setError('This job is no longer accepting applications');
                        redirect(APP_URL . 'views/jobs/detail.php?id=' .  $job_id);
                        return;
                    }
                    
                    // Check if deadline passed
                    if (isDeadlinePassed($jobData['deadline'])) {
                        Session::setError('Application deadline has passed');
                        redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
                        return;
                    }
                    
                    // ✨ Check for duplicate application FIRST
                    $application = new Application();
                    $application->job_id = $job_id;
                    $application->user_id = Session::getUserId();
                    
                    if ($application->checkDuplicate()) {
                        Session::setError('You have already applied for this job');
                        redirect(APP_URL .  'views/jobs/detail. php?id=' . $job_id);
                        return;
                    }
                    
                    // ✨ Use canApply() method for comprehensive check
                    $can_apply = $application->canApply();
                    if (!$can_apply['can_apply']) {
                        Session::setError($can_apply['reason']);
                        redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
                        return;
                    }
                    
                } catch (Exception $e) {
                    error_log('Application validation error: ' . $e->getMessage());
                    Session::setError('An error occurred.  Please try again.');
                    redirect(APP_URL . 'views/jobs/detail.php?id=' . $job_id);
                    return;
                }
                
                // Handle resume upload
                $resume_filename = null;
                if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                    $resume_filename = uploadFile(
                        $_FILES['resume'],
                        RESUME_PATH,
                        ALLOWED_RESUME_TYPES,
                        MAX_FILE_SIZE
                    );
                    
                    if (! $resume_filename) {
                        // Error message already set by uploadFile()
                        redirect(APP_URL . 'views/applications/create.php?job_id=' . $job_id);
                        return;
                    }
                } else {
                    Session::setError('Resume is required.  Please upload your resume (PDF, DOC, or DOCX).');
                    redirect(APP_URL . 'views/applications/create.php?job_id=' . $job_id);
                    return;
                }
                
                // Sanitize input
                $cover_letter = sanitizeForDB($_POST['cover_letter'] ?? '');
                
                // ✨ Validation
                $errors = [];
                
                if (empty($cover_letter) || strlen($cover_letter) < 50) {
                    $errors[] = 'Cover letter must be at least 50 characters';
                }
                
                if (strlen($cover_letter) > 2000) {
                    $errors[] = 'Cover letter must not exceed 2000 characters';
                }
                
                if (! empty($errors)) {
                    // Delete uploaded resume
                    if ($resume_filename) {
                        deleteFile(RESUME_PATH .  $resume_filename);
                    }
                    Session::setError(implode('<br>', $errors));
                    redirect(APP_URL . 'views/applications/create.php?job_id=' . $job_id);
                    return;
                }
                
                // Create application
                try {
                    $application->cover_letter = $cover_letter;
                    $application->resume = $resume_filename;
                    $application->status = APP_STATUS_PENDING;
                    
                    // ✨ Use model validation
                    $validation_errors = $application->validate();
                    if (!empty($validation_errors)) {
                        // Delete uploaded resume
                        if ($resume_filename) {
                            deleteFile(RESUME_PATH . $resume_filename);
                        }
                        Session::setError(implode('<br>', $validation_errors));
                        redirect(APP_URL . 'views/applications/create.php?job_id=' . $job_id);
                        return;
                    }
                    
                    if ($application->create()) {
                        Session::setSuccess('Application submitted successfully!  The company will review your application soon.');
                        redirect(APP_URL . 'views/dashboard/jobseeker.php');
                    } else {
                        // Delete uploaded resume if application creation failed
                        if ($resume_filename) {
                            deleteFile(RESUME_PATH . $resume_filename);
                        }
                        Session::setError('Failed to submit application. Please try again.');
                        redirect(APP_URL .  'views/jobs/detail.php?id=' .  $job_id);
                    }
                } catch (Exception $e) {
                    error_log('Application create error: ' . $e->getMessage());
                    // Delete uploaded resume
                    if ($resume_filename) {
                        deleteFile(RESUME_PATH . $resume_filename);
                    }
                    Session::setError('An error occurred while submitting your application');
                    redirect(APP_URL .  'views/applications/create. php?job_id=' . $job_id);
                }
            }
        }
        
        /**
         * View application details
         */
        public static function view($id) {
            Session::requireLogin();
            
            $id = (int)$id;
            
            if ($id <= 0) {
                Session::setError('Invalid application ID');
                redirect(APP_URL . 'views/dashboard/jobseeker.php');
                return null;
            }
            
            try {
                $application = new Application();
                $appData = $application->getById($id);
                
                if (! $appData) {
                    Session::setError('Application not found');
                    redirect(APP_URL .  'views/dashboard/jobseeker.php');
                    return null;
                }
                
                // Check access rights
                if (Session::isJobSeeker()) {
                    if ($appData['user_id'] != Session::getUserId()) {
                        Session::setError('Unauthorized access');
                        redirect(APP_URL .  'views/dashboard/jobseeker.php');
                        return null;
                    }
                } elseif (Session::isCompany()) {
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    if (! $companyData || $appData['company_id'] != $companyData['id']) {
                        Session::setError('Unauthorized access');
                        redirect(APP_URL . 'views/dashboard/company.php');
                        return null;
                    }
                } else {
                    Session::setError('Unauthorized access');
                    redirect(APP_URL . 'index.php');
                    return null;
                }
                
                return $appData;
                
            } catch (Exception $e) {
                error_log('Application view error: ' . $e->getMessage());
                Session::setError('An error occurred');
                redirect(APP_URL .  'index.php');
                return null;
            }
        }
        
        /**
         * Update application status (company only)
         */
        public static function updateStatus($id) {
            // Require company login
            Session::requireCompany();
            
            $id = (int)$id;
            
            if ($id <= 0) {
                Session::setError('Invalid application ID');
                redirect(APP_URL . 'views/dashboard/company.php');
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request. Please try again.');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return;
                }
                
                try {
                    // Get application
                    $application = new Application();
                    $appData = $application->getById($id);
                    
                    if (!$appData) {
                        Session::setError('Application not found');
                        redirect(APP_URL . 'views/dashboard/company.php');
                        return;
                    }
                    
                    // Check ownership
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    if (! $companyData || $appData['company_id'] != $companyData['id']) {
                        Session::setError('Unauthorized access');
                        redirect(APP_URL . 'views/dashboard/company.php');
                        return;
                    }
                    
                    // Update status
                    $new_status = sanitizeForDB($_POST['status'] ?? '');
                    
                    $valid_statuses = [
                        APP_STATUS_PENDING,
                        APP_STATUS_REVIEWED,
                        APP_STATUS_SHORTLISTED,
                        APP_STATUS_ACCEPTED,
                        APP_STATUS_REJECTED
                    ];
                    
                    if (!in_array($new_status, $valid_statuses)) {
                        Session::setError('Invalid status selected');
                        redirect(APP_URL . 'views/applications/view.php?id=' .  $id);
                        return;
                    }
                    
                    $application->id = $id;
                    $application->status = $new_status;
                    
                    // ✨ Use enhanced updateStatus with company verification
                    if ($application->updateStatus($companyData['id'])) {
                        $status_messages = [
                            APP_STATUS_REVIEWED => 'Application marked as reviewed',
                            APP_STATUS_SHORTLISTED => 'Applicant shortlisted successfully',
                            APP_STATUS_ACCEPTED => 'Application accepted! ',
                            APP_STATUS_REJECTED => 'Application rejected',
                            APP_STATUS_PENDING => 'Status updated to pending'
                        ];
                        Session::setSuccess($status_messages[$new_status] ?? 'Application status updated successfully! ');
                    } else {
                        Session::setError('Failed to update status.  Please try again.');
                    }
                    
                    redirect(APP_URL . 'views/applications/view.php? id=' . $id);
                    
                } catch (Exception $e) {
                    error_log('Update status error: ' . $e->getMessage());
                    Session::setError('An error occurred while updating the status');
                    redirect(APP_URL . 'views/dashboard/company.php');
                }
            }
        }
        
        /**
         * Delete application (job seeker only)
         */
        public static function delete($id) {
            // Require job seeker login
            Session::requireJobSeeker();
            
            $id = (int)$id;
            
            if ($id <= 0) {
                Session::setError('Invalid application ID');
                redirect(APP_URL . 'views/dashboard/jobseeker.php');
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request');
                    redirect(APP_URL .  'views/dashboard/jobseeker.php');
                    return;
                }
                
                try {
                    // Get application
                    $application = new Application();
                    $appData = $application->getById($id);
                    
                    if (!$appData) {
                        Session::setError('Application not found');
                        redirect(APP_URL . 'views/dashboard/jobseeker.php');
                        return;
                    }
                    
                    // Check ownership
                    if ($appData['user_id'] != Session::getUserId()) {
                        Session::setError('Unauthorized access');
                        redirect(APP_URL . 'views/dashboard/jobseeker.php');
                        return;
                    }
                    
                    // ✨ Prevent deletion if already accepted
                    if ($appData['status'] === APP_STATUS_ACCEPTED) {
                        Session::setError('Cannot withdraw an accepted application.  Please contact the company.');
                        redirect(APP_URL . 'views/dashboard/jobseeker.php');
                        return;
                    }
                    
                    // Delete application (model handles resume deletion)
                    $application->id = $id;
                    $application->user_id = Session::getUserId();
                    
                    if ($application->delete()) {
                        Session::setSuccess('Application withdrawn successfully!');
                    } else {
                        Session::setError('Failed to withdraw application. Please try again.');
                    }
                    
                } catch (Exception $e) {
                    error_log('Application delete error: ' . $e->getMessage());
                    Session::setError('An error occurred while withdrawing the application');
                }
                
                redirect(APP_URL .  'views/dashboard/jobseeker.php');
            }
        }
        
        /**
         * Get applications for job (company only)
         */
        public static function getJobApplications($job_id, $status_filter = null) {
            Session::requireCompany();
            
            $job_id = (int)$job_id;
            
            if ($job_id <= 0) {
                Session::setError('Invalid job ID');
                redirect(APP_URL . 'views/dashboard/company.php');
                return null;
            }
            
            try {
                // Verify job ownership
                $job = new Job();
                $jobData = $job->getById($job_id);
                
                if (!$jobData) {
                    Session::setError('Job not found');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return null;
                }
                
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                if (!$companyData || $jobData['company_id'] != $companyData['id']) {
                    Session::setError('Unauthorized access');
                    redirect(APP_URL .  'views/dashboard/company. php');
                    return null;
                }
                
                // Get applications
                $application = new Application();
                $applications = $application->getByJob($job_id, $status_filter);
                
                return [
                    'job' => $jobData,
                    'applications' => $applications
                ];
                
            } catch (Exception $e) {
                error_log('Get job applications error: ' . $e->getMessage());
                Session::setError('An error occurred');
                redirect(APP_URL . 'views/dashboard/company.php');
                return null;
            }
        }
        
        /**
         * ✨ NEW: Get user's applications
         */
        public static function getUserApplications($status_filter = null) {
            Session::requireJobSeeker();
            
            try {
                $application = new Application();
                return $application->getByUser(Session::getUserId(), $status_filter);
            } catch (Exception $e) {
                error_log('Get user applications error: ' .  $e->getMessage());
                return [];
            }
        }
        
        /**
         * ✨ NEW: Download resume
         */
        public static function downloadResume($id) {
            Session::requireCompany();
            
            $id = (int)$id;
            
            try {
                $application = new Application();
                $appData = $application->getById($id);
                
                if (!$appData) {
                    Session::setError('Application not found');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return;
                }
                
                // Verify ownership
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                if (!$companyData || $appData['company_id'] != $companyData['id']) {
                    Session::setError('Unauthorized access');
                    redirect(APP_URL .  'views/dashboard/company. php');
                    return;
                }
                
                $file_path = RESUME_PATH .  $appData['resume'];
                
                if (!file_exists($file_path)) {
                    Session::setError('Resume file not found');
                    redirect(APP_URL . 'views/applications/view.php?id=' . $id);
                    return;
                }
                
                // Download file
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($appData['resume']) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
                
            } catch (Exception $e) {
                error_log('Download resume error: ' . $e->getMessage());
                Session::setError('An error occurred');
                redirect(APP_URL .  'views/dashboard/company. php');
            }
        }
    }

    // Handle actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'apply':
                if (isset($_GET['job_id'])) {
                    ApplicationController::apply($_GET['job_id']);
                }
                break;
            case 'update_status':
                if (isset($_POST['id'])) {
                    ApplicationController::updateStatus($_POST['id']);
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    ApplicationController::delete($_POST['id']);
                }
                break;
            case 'download_resume':
                if (isset($_GET['id'])) {
                    ApplicationController::downloadResume($_GET['id']);
                }
                break;
            default:
                redirect(APP_URL . 'index.php');
                break;
        }
    }
?>