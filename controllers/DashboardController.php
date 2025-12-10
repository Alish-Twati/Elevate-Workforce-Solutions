<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard data for different user types
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Job.php';
    require_once __DIR__ . '/../models/Application.php';
    require_once __DIR__ . '/../models/Company.php';
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../models/Category.php';

    class DashboardController {
        
        /**
         * Get job seeker dashboard data
         */
        public static function jobSeekerDashboard() {
            Session::requireJobSeeker();
            
            $user_id = Session::getUserId();
            
            try {
                // Get applications
                $application = new Application();
                $applications = $application->getByUser($user_id);
                $stats = $application->getUserStats($user_id);
                
                // ✨ Get recent jobs (not applied yet)
                $job = new Job();
                $recent_jobs = $job->getRecentJobs(6);
                
                // ✨ Get featured/popular jobs
                $featured_jobs = $job->getFeaturedJobs(5);
                
                // ✨ Filter out jobs already applied to
                $applied_job_ids = array_column($applications, 'job_id');
                $recent_jobs = array_filter($recent_jobs, function($job) use ($applied_job_ids) {
                    return ! in_array($job['id'], $applied_job_ids);
                });
                
                // ✨ Get user data
                $user = new User();
                $userData = $user->getUserById($user_id);
                
                // ✨ Calculate profile completion
                $profile_completion = self::calculateJobSeekerProfileCompletion($userData);
                
                return [
                    'user' => $userData,
                    'applications' => $applications,
                    'stats' => $stats,
                    'recent_jobs' => array_slice($recent_jobs, 0, 5), // Limit to 5
                    'featured_jobs' => $featured_jobs,
                    'profile_completion' => $profile_completion,
                    'total_applications' => count($applications)
                ];
                
            } catch (Exception $e) {
                error_log('Job seeker dashboard error: ' . $e->getMessage());
                Session::setError('An error occurred while loading your dashboard');
                return [
                    'applications' => [],
                    'stats' => [],
                    'recent_jobs' => [],
                    'featured_jobs' => [],
                    'profile_completion' => 0
                ];
            }
        }
        
        /**
         * Get company dashboard data
         */
        public static function companyDashboard() {
            Session::requireCompany();
            
            $user_id = Session::getUserId();
            
            try {
                // Get company
                $company = new Company();
                $companyData = $company->getByUserId($user_id);
                
                if (!$companyData) {
                    Session::setWarning('Please complete your company profile first');
                    redirect(APP_URL . 'views/dashboard/company.php');
                    return null;
                }
                
                // Get jobs
                $job = new Job();
                $jobs = $job->getByCompany($companyData['id']);
                $job_stats = $job->getCompanyStats($companyData['id']);
                
                // ✨ Separate jobs by status
                $active_jobs = array_filter($jobs, function($j) {
                    return $j['status'] === JOB_STATUS_ACTIVE;
                });
                
                $draft_jobs = array_filter($jobs, function($j) {
                    return $j['status'] === JOB_STATUS_DRAFT;
                });
                
                $closed_jobs = array_filter($jobs, function($j) {
                    return $j['status'] === JOB_STATUS_CLOSED;
                });
                
                // Get applications
                $application = new Application();
                $recent_applications = $application->getByCompany($companyData['id'], null, 10, 0); // ✨ Limit to 10
                $app_stats = $application->getCompanyStats($companyData['id']);
                
                // ✨ Get pending applications count
                $pending_applications = $application->getByCompany($companyData['id'], APP_STATUS_PENDING);
                
                // ✨ Calculate profile completion
                $profile_completion = self::calculateCompanyProfileCompletion($companyData);
                
                // ✨ Get user data
                $user = new User();
                $userData = $user->getUserById($user_id);
                
                return [
                    'user' => $userData,
                    'company' => $companyData,
                    'jobs' => $jobs,
                    'active_jobs' => $active_jobs,
                    'draft_jobs' => $draft_jobs,
                    'closed_jobs' => $closed_jobs,
                    'job_stats' => $job_stats,
                    'recent_applications' => $recent_applications,
                    'pending_applications' => $pending_applications,
                    'app_stats' => $app_stats,
                    'profile_completion' => $profile_completion,
                    'total_jobs' => count($jobs),
                    'total_applications' => $app_stats['total_applications'] ?? 0
                ];
                
            } catch (Exception $e) {
                error_log('Company dashboard error: ' . $e->getMessage());
                Session::setError('An error occurred while loading your dashboard');
                return null;
            }
        }
        
        /**
         * Get admin dashboard data
         * ✨ FULLY IMPLEMENTED
         */
        public static function adminDashboard() {
            Session::requireAdmin();
            
            try {
                $user = new User();
                $job = new Job();
                $application = new Application();
                $company = new Company();
                $category = new Category();
                
                // ✨ Get total counts
                $total_users = count($user->getAllUsers());
                $total_companies = $company->getTotalCount();
                $total_jobs = $job->getTotalCount();
                $total_categories = $category->getTotalCount();
                
                // ✨ Get users by type
                $jobseekers = $user->getUsersByType(USER_TYPE_JOBSEEKER);
                $companies_users = $user->getUsersByType(USER_TYPE_COMPANY);
                
                // ✨ Get recent data
                $recent_jobs = $job->getAll(1, 10);
                $recent_users = array_slice($user->getAllUsers(), 0, 10);
                $recent_companies = $company->getAll(10, 0);
                
                // ✨ Get job stats by status
                $active_jobs_count = 0;
                $closed_jobs_count = 0;
                $draft_jobs_count = 0;
                
                // Count jobs by status
                $all_jobs = $job->getAll(1, 1000); // Get many jobs
                foreach ($all_jobs as $j) {
                    switch ($j['status']) {
                        case JOB_STATUS_ACTIVE:
                            $active_jobs_count++;
                            break;
                        case JOB_STATUS_CLOSED:
                            $closed_jobs_count++;
                            break;
                        case JOB_STATUS_DRAFT:
                            $draft_jobs_count++;
                            break;
                    }
                }
                
                // ✨ Get categories with job counts
                $categories_with_jobs = $category->getCategoriesWithJobCount();
                
                // ✨ Get application statistics
                $total_applications = 0;
                $pending_apps = 0;
                $accepted_apps = 0;
                $rejected_apps = 0;
                
                // You would need to add a method to get all applications
                // For now, we'll estimate from company stats
                $all_companies = $company->getAll();
                foreach ($all_companies as $comp) {
                    $comp_apps = $application->getCompanyStats($comp['id']);
                    $total_applications += $comp_apps['total_applications'] ?? 0;
                    $pending_apps += $comp_apps['pending'] ?? 0;
                    $accepted_apps += $comp_apps['accepted'] ?? 0;
                    $rejected_apps += $comp_apps['rejected'] ?? 0;
                }
                
                return [
                    // User stats
                    'total_users' => $total_users,
                    'total_jobseekers' => count($jobseekers),
                    'total_companies_users' => count($companies_users),
                    
                    // Company stats
                    'total_companies' => $total_companies,
                    
                    // Job stats
                    'total_jobs' => $total_jobs,
                    'active_jobs' => $active_jobs_count,
                    'closed_jobs' => $closed_jobs_count,
                    'draft_jobs' => $draft_jobs_count,
                    
                    // Application stats
                    'total_applications' => $total_applications,
                    'pending_applications' => $pending_apps,
                    'accepted_applications' => $accepted_apps,
                    'rejected_applications' => $rejected_apps,
                    
                    // Category stats
                    'total_categories' => $total_categories,
                    'categories_with_jobs' => $categories_with_jobs,
                    
                    // Recent data
                    'recent_jobs' => $recent_jobs,
                    'recent_users' => $recent_users,
                    'recent_companies' => $recent_companies,
                    
                    // Charts data
                    'jobs_by_category' => $categories_with_jobs,
                    'users_by_type' => [
                        'jobseekers' => count($jobseekers),
                        'companies' => count($companies_users)
                    ]
                ];
                
            } catch (Exception $e) {
                error_log('Admin dashboard error: ' . $e->getMessage());
                Session::setError('An error occurred while loading admin dashboard');
                return [
                    'total_users' => 0,
                    'total_companies' => 0,
                    'total_jobs' => 0,
                    'total_applications' => 0
                ];
            }
        }
        
        /**
         * ✨ NEW: Calculate job seeker profile completion
         * 
         * @param array $user User data
         * @return int Percentage (0-100)
         */
        private static function calculateJobSeekerProfileCompletion($user) {
            if (!$user) {
                return 0;
            }
            
            $total_fields = 5;
            $completed_fields = 0;
            
            // Check required fields
            if (! empty($user['first_name'])) $completed_fields++;
            if (! empty($user['last_name'])) $completed_fields++;
            if (!empty($user['email'])) $completed_fields++;
            if (!empty($user['phone'])) $completed_fields++;
            
            // Check if user has applied to at least one job
            $application = new Application();
            $apps = $application->getByUser($user['id']);
            if (count($apps) > 0) $completed_fields++;
            
            return round(($completed_fields / $total_fields) * 100);
        }
        
        /**
         * ✨ NEW: Calculate company profile completion
         * 
         * @param array $company Company data
         * @return int Percentage (0-100)
         */
        private static function calculateCompanyProfileCompletion($company) {
            if (!$company) {
                return 0;
            }
            
            $total_fields = 7;
            $completed_fields = 0;
            
            if (!empty($company['company_name'])) $completed_fields++;
            if (!empty($company['description'])) $completed_fields++;
            if (!empty($company['location'])) $completed_fields++;
            if (!empty($company['industry'])) $completed_fields++;
            if (!empty($company['company_size'])) $completed_fields++;
            if (!empty($company['logo'])) $completed_fields++;
            if (!empty($company['website'])) $completed_fields++;
            
            return round(($completed_fields / $total_fields) * 100);
        }
        
        /**
         * ✨ NEW: Get quick stats for any user type
         * 
         * @return array Quick stats
         */
        public static function getQuickStats() {
            Session::requireLogin();
            
            try {
                if (Session::isJobSeeker()) {
                    $application = new Application();
                    $stats = $application->getUserStats(Session::getUserId());
                    
                    return [
                        'type' => 'jobseeker',
                        'total' => $stats['total_applications'] ?? 0,
                        'pending' => $stats['pending'] ?? 0,
                        'accepted' => $stats['accepted'] ?? 0,
                        'rejected' => $stats['rejected'] ?? 0
                    ];
                    
                } elseif (Session::isCompany()) {
                    $company = new Company();
                    $companyData = $company->getByUserId(Session::getUserId());
                    
                    if (!$companyData) {
                        return ['type' => 'company', 'total' => 0];
                    }
                    
                    $job = new Job();
                    $job_stats = $job->getCompanyStats($companyData['id']);
                    
                    return [
                        'type' => 'company',
                        'total_jobs' => $job_stats['total_jobs'] ?? 0,
                        'active_jobs' => $job_stats['active_jobs'] ?? 0,
                        'total_applications' => $job_stats['total_applications'] ?? 0
                    ];
                    
                } elseif (Session::isAdmin()) {
                    $user = new User();
                    $job = new Job();
                    
                    return [
                        'type' => 'admin',
                        'total_users' => count($user->getAllUsers()),
                        'total_jobs' => $job->getTotalCount()
                    ];
                }
                
            } catch (Exception $e) {
                error_log('Quick stats error: ' . $e->getMessage());
            }
            
            return ['type' => 'unknown', 'total' => 0];
        }
        
        /**
         * ✨ NEW: Route to appropriate dashboard based on user type
         */
        public static function redirectToDashboard() {
            Session::requireLogin();
            
            if (Session::isJobSeeker()) {
                redirect(APP_URL . 'views/dashboard/jobseeker.php');
            } elseif (Session::isCompany()) {
                redirect(APP_URL .  'views/dashboard/company.php');
            } elseif (Session::isAdmin()) {
                redirect(APP_URL . 'views/dashboard/admin.php');
            } else {
                redirect(APP_URL . 'index.php');
            }
        }
    }

    // ✨ Handle dashboard routing
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'redirect':
                DashboardController::redirectToDashboard();
                break;
            default:
                DashboardController::redirectToDashboard();
                break;
        }
    }
?>