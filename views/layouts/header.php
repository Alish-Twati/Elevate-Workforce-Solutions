<?php
// ✨ Ensure config is loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../config/config.php';
}

// ✨ Set default page title if not set
if (!isset($page_title)) {
    $page_title = 'Welcome';
}

// ✨ Optional: Page-specific meta description
if (!isset($page_description)) {
    $page_description = 'Elevate Workforce Solutions - Find your dream job or hire talented professionals in Nepal';
}

// ✨ Optional: Page-specific keywords
if (!isset($page_keywords)) {
    $page_keywords = 'jobs in Nepal, job portal, career opportunities, recruitment, hiring';
}
?>
<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <meta name="author" content="Alish Twati">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags (for social sharing) -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title .  ' - ' . APP_NAME); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars(currentURL()); ?>">
    <meta property="og:image" content="<?php echo APP_URL; ?>public/images/og-image.jpg">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title . ' - ' . APP_NAME); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="<?php echo APP_URL; ?>public/images/og-image.jpg">
    
    <!-- Page Title -->
    <title><?php echo htmlspecialchars($page_title . ' - ' . APP_NAME); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL; ?>public/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL; ?>public/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>public/images/apple-touch-icon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" 
          crossorigin="anonymous">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare. com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    
    <!-- Google Fonts (Optional - uncomment if you want custom fonts) -->
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts. gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>public/css/style.css? v=<?php echo APP_VERSION; ?>">
    
    <!-- Page-specific CSS (if needed) -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo APP_URL .  $css_file; ?>? v=<?php echo APP_VERSION; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline styles (if needed) -->
    <?php if (isset($inline_styles)): ?>
        <style><?php echo $inline_styles; ?></style>
    <?php endif; ?>
    
    <!-- ✨ Preload critical resources -->
    <link rel="preload" href="<?php echo APP_URL; ?>public/css/style.css" as="style">
    
    <!-- ✨ DNS prefetch for external resources -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <?php
    // ✨ Add theme color based on user type
    $theme_color = '#0d6efd'; // Default blue
    if (Session::isLoggedIn()) {
        if (Session::isCompany()) {
            $theme_color = '#198754'; // Green for companies
        } elseif (Session::isJobSeeker()) {
            $theme_color = '#0dcaf0'; // Cyan for job seekers
        } elseif (Session::isAdmin()) {
            $theme_color = '#dc3545'; // Red for admin
        }
    }
    ?>
    
    <!-- Theme Color (for mobile browsers) -->
    <meta name="theme-color" content="<?php echo $theme_color; ?>">
    <meta name="msapplication-TileColor" content="<?php echo $theme_color; ?>">
</head>
<body class="<?php echo isset($body_class) ? htmlspecialchars($body_class) : ''; ?> <?php echo Session::isLoggedIn() ? 'logged-in user-' . Session::getUserType() : 'guest'; ?>">
    
    <!-- ✨ Loading Spinner (optional) -->
    <div id="page-loader" class="page-loader" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- ✨ Skip to main content (accessibility) -->
    <a href="#main-content" class="skip-to-content visually-hidden-focusable">
        Skip to main content
    </a>