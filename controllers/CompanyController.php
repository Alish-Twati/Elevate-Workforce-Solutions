<?php
/**
 * Company Controller
 * 
 * Handles company profile management
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Company.php';

    class CompanyController {
        
        /**
         * Get company profile
         */
        public static function getProfile() {
            Session::requireCompany();
            
            try {
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                return $companyData;
                
            } catch (Exception $e) {
                error_log('Get company profile error: ' . $e->getMessage());
                return null;
            }
        }
        
        /**
         * ✨ NEW: Get public company profile (for job seekers viewing jobs)
         * 
         * @param int $company_id Company ID
         * @return array|null
         */
        public static function getPublicProfile($company_id) {
            $company_id = (int)$company_id;
            
            if ($company_id <= 0) {
                return null;
            }
            
            try {
                $company = new Company();
                return $company->getById($company_id);
            } catch (Exception $e) {
                error_log('Get public company profile error: ' . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Update company profile
         */
        public static function updateProfile() {
            Session::requireCompany();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request.  Please try again.');
                    redirect(APP_URL . 'views/company/profile.php');
                    return;
                }
                
                try {
                    // Get existing company
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    // Sanitize input
                    $company_name = sanitizeForDB($_POST['company_name'] ?? '');
                    $description = sanitizeForDB($_POST['description'] ?? '');
                    $location = sanitizeForDB($_POST['location'] ?? '');
                    $website = sanitizeForDB($_POST['website'] ?? '');
                    $industry = sanitizeForDB($_POST['industry'] ?? '');
                    $company_size = sanitizeForDB($_POST['company_size'] ?? '');
                    $founded_year = !empty($_POST['founded_year']) ? (int)$_POST['founded_year'] : null;
                    
                    // Validation
                    $errors = [];
                    
                    if (empty($company_name) || strlen($company_name) < 2) {
                        $errors[] = 'Company name must be at least 2 characters';
                    }
                    
                    if (strlen($company_name) > 100) {
                        $errors[] = 'Company name must not exceed 100 characters';
                    }
                    
                    if (empty($description) || strlen($description) < 10) {
                        $errors[] = 'Company description must be at least 10 characters';
                    }
                    
                    if (strlen($description) > 2000) {
                        $errors[] = 'Company description must not exceed 2000 characters';
                    }
                    
                    if (empty($location)) {
                        $errors[] = 'Location is required';
                    }
                    
                    // ✨ Website validation
                    if (!empty($website) && ! validateURL($website)) {
                        $errors[] = 'Invalid website URL format';
                    }
                    
                    // ✨ Founded year validation
                    if ($founded_year !== null) {
                        $current_year = (int)date('Y');
                        if ($founded_year < 1800 || $founded_year > $current_year) {
                            $errors[] = 'Invalid founded year (must be between 1800 and ' . $current_year . ')';
                        }
                    }
                    
                    // ✨ Industry validation
                    $valid_industries = [
                        'IT & Software',
                        'Finance & Banking',
                        'Healthcare',
                        'Education',
                        'Manufacturing',
                        'Retail',
                        'Hospitality',
                        'Construction',
                        'Transportation',
                        'Other'
                    ];
                    
                    if (!empty($industry) && !in_array($industry, $valid_industries)) {
                        $errors[] = 'Invalid industry selected';
                    }
                    
                    // ✨ Company size validation
                    $valid_sizes = [
                        '1-10',
                        '11-50',
                        '51-200',
                        '201-500',
                        '501-1000',
                        '1000+'
                    ];
                    
                    if (!empty($company_size) && !in_array($company_size, $valid_sizes)) {
                        $errors[] = 'Invalid company size selected';
                    }
                    
                    if (! empty($errors)) {
                        Session::setError(implode('<br>', $errors));
                        redirect(APP_URL . 'views/company/profile.php');
                        return;
                    }
                    
                    // Handle logo upload
                    $logo_filename = $companyData ? $companyData['logo'] : null;
                    
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                        $new_logo = uploadFile(
                            $_FILES['logo'],
                            LOGO_PATH,
                            ALLOWED_IMAGE_TYPES,
                            MAX_FILE_SIZE
                        );
                        
                        if ($new_logo) {
                            // Delete old logo
                            if ($logo_filename && file_exists(LOGO_PATH . $logo_filename)) {
                                deleteFile(LOGO_PATH . $logo_filename);
                            }
                            $logo_filename = $new_logo;
                        } else {
                            // Error message already set by uploadFile()
                            redirect(APP_URL . 'views/company/profile.php');
                            return;
                        }
                    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                        // File upload error (not just no file selected)
                        Session::setError('Logo upload failed. Please try again.');
                        redirect(APP_URL . 'views/company/profile.php');
                        return;
                    }
                    
                    // Update or create company profile
                    if ($companyData) {
                        // Update existing
                        $company->id = $companyData['id'];
                        $company->user_id = Session::getUserId();
                        $company->company_name = $company_name;
                        $company->description = $description;
                        $company->location = $location;
                        $company->website = $website;
                        $company->logo = $logo_filename;
                        $company->industry = $industry;
                        $company->company_size = $company_size;
                        $company->founded_year = $founded_year;
                        
                        // ✨ Use model validation
                        $validation_errors = $company->validate();
                        if (!empty($validation_errors)) {
                            Session::setError(implode('<br>', $validation_errors));
                            redirect(APP_URL . 'views/company/profile.php');
                            return;
                        }
                        
                        if ($company->update()) {
                            Session::setSuccess('Company profile updated successfully! ');
                        } else {
                            Session::setError('Failed to update profile.  Please try again.');
                        }
                    } else {
                        // Create new
                        $company->user_id = Session::getUserId();
                        $company->company_name = $company_name;
                        $company->description = $description;
                        $company->location = $location;
                        $company->website = $website;
                        $company->logo = $logo_filename;
                        $company->industry = $industry;
                        $company->company_size = $company_size;
                        $company->founded_year = $founded_year;
                        
                        // ✨ Use model validation
                        $validation_errors = $company->validate();
                        if (! empty($validation_errors)) {
                            // Delete uploaded logo if validation fails
                            if ($logo_filename) {
                                deleteFile(LOGO_PATH . $logo_filename);
                            }
                            Session::setError(implode('<br>', $validation_errors));
                            redirect(APP_URL .  'views/company/profile. php');
                            return;
                        }
                        
                        if ($company->create()) {
                            Session::setSuccess('Company profile created successfully!  You can now post jobs.');
                        } else {
                            // Delete uploaded logo if creation fails
                            if ($logo_filename) {
                                deleteFile(LOGO_PATH . $logo_filename);
                            }
                            Session::setError('Failed to create profile.  Company name may already exist.');
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log('Company profile update error: ' . $e->getMessage());
                    Session::setError('An error occurred while updating your profile');
                }
                
                redirect(APP_URL . 'views/company/profile.php');
            }
        }
        
        /**
         * ✨ NEW: Delete company logo
         */
        public static function deleteLogo() {
            Session::requireCompany();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!Session::validatePostCSRF()) {
                    Session::setError('Invalid request');
                    redirect(APP_URL .  'views/company/profile. php');
                    return;
                }
                
                try {
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    if (! $companyData) {
                        Session::setError('Company profile not found');
                        redirect(APP_URL . 'views/company/profile.php');
                        return;
                    }
                    
                    // Delete logo file
                    if (! empty($companyData['logo'])) {
                        deleteFile(LOGO_PATH . $companyData['logo']);
                    }
                    
                    // Update database
                    $company->id = $companyData['id'];
                    $company->user_id = Session::getUserId();
                    $company->company_name = $companyData['company_name'];
                    $company->description = $companyData['description'];
                    $company->location = $companyData['location'];
                    $company->website = $companyData['website'];
                    $company->logo = null; // ✨ Set to null
                    $company->industry = $companyData['industry'];
                    $company->company_size = $companyData['company_size'];
                    $company->founded_year = $companyData['founded_year'];
                    
                    if ($company->update()) {
                        Session::setSuccess('Logo deleted successfully');
                    } else {
                        Session::setError('Failed to delete logo');
                    }
                    
                } catch (Exception $e) {
                    error_log('Delete logo error: ' . $e->getMessage());
                    Session::setError('An error occurred');
                }
                
                redirect(APP_URL . 'views/company/profile.php');
            }
        }
        
        /**
         * ✨ NEW: Check if company profile is complete
         * 
         * @return array ['complete' => bool, 'missing' => array]
         */
        public static function checkProfileComplete() {
            Session::requireCompany();
            
            try {
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                if (!$companyData) {
                    return [
                        'complete' => false,
                        'missing' => ['Complete company profile to post jobs']
                    ];
                }
                
                $missing = [];
                
                if (empty($companyData['company_name'])) {
                    $missing[] = 'Company name';
                }
                
                if (empty($companyData['description'])) {
                    $missing[] = 'Company description';
                }
                
                if (empty($companyData['location'])) {
                    $missing[] = 'Location';
                }
                
                if (empty($companyData['industry'])) {
                    $missing[] = 'Industry';
                }
                
                return [
                    'complete' => empty($missing),
                    'missing' => $missing,
                    'data' => $companyData
                ];
                
            } catch (Exception $e) {
                error_log('Check profile complete error: ' . $e->getMessage());
                return [
                    'complete' => false,
                    'missing' => ['Error checking profile']
                ];
            }
        }
        
        /**
         * ✨ NEW: Get company statistics
         */
        public static function getStatistics() {
            Session::requireCompany();
            
            try {
                $company = new Company();
                $companyData = $company->getByUserId(Session::getUserId());
                
                if (!$companyData) {
                    return null;
                }
                
                // Get job statistics
                $job = new Job();
                $job_stats = $job->getCompanyStats($companyData['id']);
                
                // Get application statistics
                $application = new Application();
                $app_stats = $application->getCompanyStats($companyData['id']);
                
                return [
                    'company' => $companyData,
                    'jobs' => $job_stats,
                    'applications' => $app_stats
                ];
                
            } catch (Exception $e) {
                error_log('Get company statistics error: ' . $e->getMessage());
                return null;
            }
        }
    }

    // Handle actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'update':
                CompanyController::updateProfile();
                break;
            case 'delete-logo':
                CompanyController::deleteLogo();
                break;
            default:
                redirect(APP_URL . 'views/company/profile.php');
                break;
        }
    }
?>