<?php
$page_title = "500 - Server Error";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <i class="fas fa-exclamation-circle fa-5x text-danger mb-4"></i>
            <h1 class="display-1 fw-bold">500</h1>
            <h2 class="mb-4">Server Error</h2>
            <p class="lead text-muted mb-4">
                Oops! Something went wrong on our end. We're working to fix it. 
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-home me-2"></i> Go to Homepage
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i> Go Back
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>