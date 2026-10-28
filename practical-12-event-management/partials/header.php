<?php
$pageTitle = $pageTitle ?? 'Event management';
$csrfForLogout = authCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= p12Escape($pageTitle) ?> | StudentHub</title>
    <link rel="stylesheet" href="../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../practical-09-secure-registration/assets/css/style.css">
    <link rel="stylesheet" href="../practical-10-secure-auth/assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/events.css">
    <script src="../practical-04-dom-interactivity/script.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to event management</a>
<header class="navbar">
    <a class="logo" href="../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home">
        <img src="../assets/images/logo.jpg" alt="">
        <h2>Student<span>Hub</span></h2>
    </a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
        <a href="index.php" aria-current="page">Events</a>
        <a href="../practical-11-student-management/">Students</a>
        <a href="../practical-10-secure-auth/admin/dashboard.php">Admin dashboard</a>
    </nav>
    <div class="auth-buttons">
        <form method="post" action="../practical-10-secure-auth/logout.php">
            <input type="hidden" name="csrf_token" value="<?= authEscape($csrfForLogout) ?>">
            <button class="btn btn-primary" type="submit">Logout</button>
        </form>
        <button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button>
    </div>
</header>
<?php if (is_array($flash = p12TakeFlash())): ?>
    <div class="p12-message <?= $flash['type'] === 'success' ? 'success' : 'error' ?>" role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
        <?= p12Escape((string) $flash['message']) ?>
    </div>
<?php endif; ?>
