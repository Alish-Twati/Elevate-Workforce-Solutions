<footer class="bg-dark text-white mt-auto py-4">
    <div class="container">
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <h5 class="mb-3">
                    <i class="fas fa-briefcase me-2"></i>
                    <?php echo APP_NAME; ?>
                </h5>
                <p class="text-muted mb-3">
                    Connecting talent with opportunity.  Your career journey starts here.
                </p>
                
                <!-- Quick Stats (if logged in as admin) -->
                <?php if (Session::isAdmin()): ?>
                    <div class="footer-stats small text-muted mb-3">
                        <?php
                        require_once __DIR__ . '/../../controllers/DashboardController.php';
                        $quick_stats = DashboardController:: getQuickStats();
                        ?>
                        <div><i class="fas fa-users me-1"></i> <?php echo $quick_stats['total_users'] ??  0; ?> Users</div>
                        <div><i class="fas fa-briefcase me-1"></i> <?php echo $quick_stats['total_jobs'] ?? 0; ?> Jobs</div>
                    </div>
                <?php endif; ?>
                
                <!-- Social Links -->
                <div class="social-links mt-3">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
                       class="text-white me-3" title="Facebook">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" 
                       class="text-white me-3" title="Twitter">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" 
                       class="text-white me-3" title="LinkedIn">
                        <i class="fab fa-linkedin fa-lg"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" 
                       class="text-white" title="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="mb-3">For Job Seekers</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?php echo APP_URL; ?>views/jobs/index.php" class="text-muted text-decoration-none">
                        <i class="fas fa-chevron-right fa-xs me-1"></i> Browse Jobs
                    </a></li>
                    <?php if (Session::isJobSeeker()): ?>
                        <li><a href="<?php echo APP_URL; ?>views/dashboard/jobseeker.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> My Dashboard
                        </a></li>
                        <li><a href="<?php echo APP_URL; ?>views/applications/index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> My Applications
                        </a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>views/auth/register.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> Create Account
                        </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Company Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="mb-3">For Employers</h6>
                <ul class="list-unstyled footer-links">
                    <?php if (Session::isCompany()): ?>
                        <li><a href="<?php echo APP_URL; ?>views/dashboard/company.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> My Dashboard
                        </a></li>
                        <li><a href="<?php echo APP_URL; ?>views/jobs/create.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> Post a Job
                        </a></li>
                        <li><a href="<?php echo APP_URL; ?>views/company/profile.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> Company Profile
                        </a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>views/auth/register.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> Register Company
                        </a></li>
                        <li><a href="<?php echo APP_URL; ?>views/pages/employer-info.php" class="text-muted text-decoration-none">
                            <i class="fas fa-chevron-right fa-xs me-1"></i> Why Post Jobs? 
                        </a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo APP_URL; ?>views/pages/pricing.php" class="text-muted text-decoration-none">
                        <i class="fas fa-chevron-right fa-xs me-1"></i> Pricing
                    </a></li>
                </ul>
            </div>
            
            <!-- Contact & Legal -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <h6 class="mb-3">Contact & Support</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i> 
                        Kathmandu, Nepal
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-primary"></i> 
                        <a href="tel:+97714444444" class="text-muted text-decoration-none">+977-1-4444444</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2 text-primary"></i> 
                        <a href="mailto:info@elevateworkforce.com" class="text-muted text-decoration-none">
                            info@elevateworkforce.com
                        </a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock me-2 text-primary"></i> 
                        Sun - Fri: 9:00 AM - 5:00 PM
                    </li>
                </ul>
                
                <!-- Legal Links -->
                <div class="mt-3">
                    <h6 class="mb-2 small">Legal</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo APP_URL; ?>views/pages/privacy.php" class="text-muted text-decoration-none small">
                            Privacy Policy
                        </a>
                        <span class="text-muted">|</span>
                        <a href="<?php echo APP_URL; ?>views/pages/terms.php" class="text-muted text-decoration-none small">
                            Terms of Service
                        </a>
                        <span class="text-muted">|</span>
                        <a href="<?php echo APP_URL; ?>views/pages/about.php" class="text-muted text-decoration-none small">
                            About Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Newsletter Signup -->
        <?php if (! Session::isLoggedIn()): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="bg-primary bg-opacity-10 rounded p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <h6 class="mb-1"><i class="fas fa-envelope-open-text me-2"></i>Stay Updated</h6>
                                <small class="text-muted">Get the latest jobs delivered to your inbox</small>
                            </div>
                            <div class="col-md-6">
                                <form action="<?php echo APP_URL; ?>controllers/NewsletterController.php" method="POST" class="d-flex gap-2">
                                    <input type="email" name="email" class="form-control form-control-sm" 
                                           placeholder="Your email address" required>
                                    <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                                        Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary btn-floating" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Cookie Consent -->
<?php if (! isset($_COOKIE['cookie_consent'])): ?>
<div id="cookieConsent" class="cookie-consent">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 mb-2 mb-md-0">
                <p class="mb-0">
                    <i class="fas fa-cookie-bite me-2"></i>
                    We use cookies to enhance your experience.  By continuing to visit this site you agree to our use of cookies.
                    <a href="<?php echo APP_URL; ?>views/pages/privacy.php" class="text-primary">Learn more</a>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <button onclick="acceptCookies()" class="btn btn-primary btn-sm me-2">Accept</button>
                <button onclick="dismissCookies()" class="btn btn-outline-light btn-sm">Dismiss</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>



<!-- Custom JavaScript -->
<script src="<?php echo APP_URL; ?>public/js/script.js? v=<?php echo APP_VERSION; ?>"></script>

<!-- Page-specific JavaScript -->
<?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js_file): ?>
        <script src="<?php echo APP_URL .  $js_file; ?>? v=<?php echo APP_VERSION; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Inline scripts -->
<?php if (isset($inline_scripts)): ?>
    <script><?php echo $inline_scripts; ?></script>
<?php endif; ?>

<!-- Google Analytics -->
<?php if (defined('GA_TRACKING_ID') && !empty(GA_TRACKING_ID)): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA_TRACKING_ID; ?>"></script>
<script>
    window.dataLayer = window. dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo GA_TRACKING_ID; ?>');
</script>
<?php endif; ?>

<?php ob_end_flush(); ?>
</body>
</html>