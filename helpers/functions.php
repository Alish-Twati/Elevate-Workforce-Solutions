<?php
/**
 * Helper Functions
 * 
 * Common utility functions used throughout the application
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

    /**
     * Sanitize input data
     * 
     * @param string $data Data to sanitize
     * @return string Sanitized data
     */
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * ✨ NEW: Sanitize for database (no HTML encoding)
     * 
     * @param string $data Data to sanitize
     * @return string Sanitized data
     */
    function sanitizeForDB($data) {
        return trim(stripslashes($data));
    }

    /**
     * Validate email format
     * 
     * @param string $email Email address
     * @return bool Returns true if valid, false otherwise
     */
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * ✨ NEW: Validate phone number
     * 
     * @param string $phone Phone number
     * @return bool
     */
    function validatePhone($phone) {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);
        // Check if it's 10 digits (Nepal format)
        return preg_match('/^9[0-9]{9}$/', $phone);
    }

    /**
     * ✨ NEW: Validate URL
     * 
     * @param string $url URL
     * @return bool
     */
    function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     */
    function redirect($url) {
        if (! headers_sent()) {
            header("Location: " . $url);
            exit();
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit();
        }
    }

    /**
     * Display flash message
     * 
     * @return string HTML for flash message
     */
    function displayFlashMessage() {
        $flash = Session::getFlash();
        if ($flash) {
            $alertClass = 'alert-' . ($flash['type'] === 'error' ? 'danger' : $flash['type']);
            return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                        ' . htmlspecialchars($flash['message']) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }
        return '';
    }

    /**
     * Format date
     * 
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    function formatDate($date, $format = 'F j, Y') {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'N/A';
        }
        return date($format, strtotime($date));
    }

    /**
     * Time ago format
     * ✨ Enhanced: Better grammar
     * 
     * @param string $datetime DateTime string
     * @return string Time ago string
     */
    function timeAgo($datetime) {
        if (empty($datetime)) {
            return 'Unknown';
        }
        
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 0) {
            return 'Just now';
        }
        
        if ($diff < 60) {
            return $diff == 1 ? '1 second ago' : $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins == 1 ? '1 minute ago' : $mins . ' minutes ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours == 1 ? '1 hour ago' : $hours . ' hours ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days == 1 ? '1 day ago' : $days . ' days ago';
        } elseif ($diff < 2592000) { // Less than 30 days
            $weeks = floor($diff / 604800);
            return $weeks == 1 ? '1 week ago' : $weeks . ' weeks ago';
        } else {
            return date('F j, Y', $time);
        }
    }

    /**
     * Truncate text
     * ✨ Enhanced: Don't break words
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix to add
     * @return string Truncated text
     */
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        // Don't break words
        $truncated = substr($text, 0, $length);
        $last_space = strrpos($truncated, ' ');
        
        if ($last_space !== false) {
            $truncated = substr($truncated, 0, $last_space);
        }
        
        return $truncated . $suffix;
    }

    /**
     * ✨ NEW: Strip HTML tags and truncate
     * 
     * @param string $text HTML text
     * @param int $length Maximum length
     * @param string $suffix Suffix
     * @return string
     */
    function truncateHTML($text, $length = 100, $suffix = '...') {
        $text = strip_tags($text);
        return truncate($text, $length, $suffix);
    }

    /**
     * Upload file
     * ✨ Enhanced: Better error handling
     * 
     * @param array $file File array from $_FILES
     * @param string $target_dir Target directory
     * @param array $allowed_types Allowed MIME types
     * @param int $max_size Maximum file size in bytes
     * @return string|bool Uploaded file name or false on failure
     */
    function uploadFile($file, $target_dir, $allowed_types, $max_size = MAX_FILE_SIZE) {
        // Check if file was uploaded
        if (! isset($file['tmp_name']) || empty($file['tmp_name'])) {
            Session::setError('No file uploaded');
            return false;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            ];
            Session::setError($errors[$file['error']] ?? 'Unknown upload error');
            return false;
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            Session::setError('File size exceeds maximum allowed size (' . formatFileSize($max_size) . ')');
            return false;
        }
        
        // Check if file is actually uploaded
        if (! is_uploaded_file($file['tmp_name'])) {
            Session::setError('Security error: Invalid file upload');
            return false;
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            Session::setError('Invalid file type.  Allowed types: ' . implode(', ', $allowed_types));
            return false;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                Session::setError('Failed to create upload directory');
                return false;
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $filename;
        }
        
        Session::setError('Failed to move uploaded file');
        return false;
    }

    /**
     * Delete file
     * 
     * @param string $filepath Full file path
     * @return bool Returns true on success, false on failure
     */
    function deleteFile($filepath) {
        if (file_exists($filepath) && is_file($filepath)) {
            return @unlink($filepath);
        }
        return false;
    }

    /**
     * ✨ NEW: Format file size
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted file size
     */
    function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Format salary
     * 
     * @param float $min Minimum salary
     * @param float $max Maximum salary
     * @return string Formatted salary range
     */
    function formatSalary($min, $max) {
        if (empty($min) && empty($max)) {
            return 'Negotiable';
        }
        
        if (empty($min)) {
            return 'Up to NPR ' . number_format($max);
        }
        
        if (empty($max)) {
            return 'NPR ' . number_format($min) . '+';
        }
        
        return 'NPR ' . number_format($min) . ' - NPR ' . number_format($max);
    }

    /**
     * ✨ NEW: Format number with suffix (1K, 1M, etc.)
     * 
     * @param int $number Number
     * @return string Formatted number
     */
    function formatNumber($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) .  'K';
        }
        return $number;
    }

    /**
     * Get job type badge
     * 
     * @param string $job_type Job type
     * @return string HTML badge
     */
    function getJobTypeBadge($job_type) {
        $badges = [
            'full-time' => '<span class="badge bg-success">Full Time</span>',
            'part-time' => '<span class="badge bg-info">Part Time</span>',
            'contract' => '<span class="badge bg-warning text-dark">Contract</span>',
            'internship' => '<span class="badge bg-secondary">Internship</span>',
            'remote' => '<span class="badge bg-primary">Remote</span>',
        ];
        
        return $badges[$job_type] ?? '<span class="badge bg-dark">' . htmlspecialchars(ucfirst($job_type)) .  '</span>';
    }

    /**
     * Get application status badge
     * 
     * @param string $status Application status
     * @return string HTML badge
     */
    function getApplicationStatusBadge($status) {
        $badges = [
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'reviewed' => '<span class="badge bg-info">Reviewed</span>',
            'shortlisted' => '<span class="badge bg-primary">Shortlisted</span>',
            'accepted' => '<span class="badge bg-success">Accepted</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($status)) .  '</span>';
    }

    /**
     * ✨ NEW: Get job status badge
     * 
     * @param string $status Job status
     * @return string HTML badge
     */
    function getJobStatusBadge($status) {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'closed' => '<span class="badge bg-danger">Closed</span>',
            'draft' => '<span class="badge bg-secondary">Draft</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge bg-dark">' . htmlspecialchars(ucfirst($status)) .  '</span>';
    }

    /**
     * Pagination helper
     * ✨ Enhanced: Smart page range display
     * 
     * @param int $current_page Current page number
     * @param int $total_items Total number of items
     * @param int $items_per_page Items per page
     * @param string $base_url Base URL for pagination links
     * @return string HTML pagination
     */
    function pagination($current_page, $total_items, $items_per_page, $base_url) {
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($total_pages <= 1) {
            return '';
        }
        
        // Preserve existing query parameters
        $separator = (strpos($base_url, '?') !== false) ? '&' : '?';
        
        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($current_page > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'page=' . ($current_page - 1) . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Smart page number display
        $range = 2; // Show 2 pages before and after current
        $start = max(1, $current_page - $range);
        $end = min($total_pages, $current_page + $range);
        
        // First page
        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'page=1">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">... </span></li>';
            }
        }
        
        // Page numbers in range
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $current_page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        // Last page
        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . $separator . 'page=' . ($current_page + 1) . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }

    /**
     * Check if deadline has passed
     * 
     * @param string $deadline Deadline date
     * @return bool Returns true if passed, false otherwise
     */
    function isDeadlinePassed($deadline) {
        if (empty($deadline) || $deadline === '0000-00-00') {
            return false;
        }
        return strtotime($deadline) < time();
    }

    /**
     * ✨ NEW: Get days until deadline
     * 
     * @param string $deadline Deadline date
     * @return int Days remaining (negative if passed)
     */
    function daysUntilDeadline($deadline) {
        if (empty($deadline)) {
            return 999;
        }
        $diff = strtotime($deadline) - time();
        return floor($diff / 86400);
    }

    /**
     * Get user initials
     * 
     * @param string $first_name First name
     * @param string $last_name Last name
     * @return string Initials
     */
    function getInitials($first_name, $last_name = '') {
        $first = ! empty($first_name) ? strtoupper(substr($first_name, 0, 1)) : '';
        $last = !empty($last_name) ? strtoupper(substr($last_name, 0, 1)) : '';
        return $first . $last;
    }

    /**
     * Generate random password
     * 
     * @param int $length Password length
     * @return string Random password
     */
    function generateRandomPassword($length = 12) {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*';
        
        // Ensure at least one of each type
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        // Fill the rest
        $all_chars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $all_chars[random_int(0, strlen($all_chars) - 1)];
        }
        
        // Shuffle
        return str_shuffle($password);
    }

    /**
     * ✨ NEW: Debug helper (only in debug mode)
     * 
     * @param mixed $data Data to dump
     * @param bool $die Die after dump
     */
    function dd($data, $die = true) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            if ($die) {
                die();
            }
        }
    }

    /**
     * ✨ NEW: Get current URL
     * 
     * @return string Current URL
     */
    function currentURL() {
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * ✨ NEW: Check if current page is active
     * 
     * @param string $page Page name
     * @return string 'active' if current page
     */
    function isActive($page) {
        $current = basename($_SERVER['PHP_SELF']);
        return ($current === $page) ? 'active' : '';
    }

    /**
     * ✨ NEW: Pluralize word
     * 
     * @param int $count Count
     * @param string $singular Singular form
     * @param string $plural Plural form (optional)
     * @return string Pluralized word
     */
    function pluralize($count, $singular, $plural = null) {
        if ($plural === null) {
            $plural = $singular .  's';
        }
        return $count . ' ' . (($count == 1) ? $singular : $plural);
    }

    /**
     * ✨ NEW: Generate slug from string
     * 
     * @param string $string String to slugify
     * @return string Slug
     */
    function slugify($string) {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * ✨ NEW: Check if string contains substring
     * 
     * @param string $haystack String to search in
     * @param string $needle String to search for
     * @return bool
     */
    function contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * ✨ NEW: Get file extension icon
     * 
     * @param string $filename Filename
     * @return string Font Awesome icon class
     */
    function getFileIcon($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $icons = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'jpg' => 'fa-file-image text-warning',
            'jpeg' => 'fa-file-image text-warning',
            'png' => 'fa-file-image text-warning',
            'zip' => 'fa-file-archive text-secondary',
            'rar' => 'fa-file-archive text-secondary',
        ];
        
        return $icons[$ext] ?? 'fa-file text-muted';
    }
?>