<?php
$page_title = "404 - Page Not Found";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
            <h1 class="display-1 fw-bold">404</h1>
            <h2 class="mb-4">Page Not Found</h2>
            <p class="lead text-muted mb-4">
                Sorry, the page you are looking for does not exist or has been moved.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="<? php echo APP_URL; ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-home me-2"></i> Go to Homepage
                </a>
                <a href="<?php echo APP_URL; ?>views/jobs/index.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-search me-2"></i> Browse Jobs
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>