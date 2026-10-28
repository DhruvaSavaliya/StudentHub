<?php
declare(strict_types=1);

require __DIR__ . '/../lib/auth.php';
$user = authRequireRole('admin');
$csrfToken = authCsrfToken();
$lastLogin = 'Not recorded';
try {
    $db = studentHubMysqli();
    $statement = $db->prepare('SELECT last_login_at FROM users WHERE id = ?');
    $statement->bind_param('i', $user['id']);
    $statement->execute();
    $statement->bind_result($lastLoginValue);
    if ($statement->fetch() && $lastLoginValue !== null) {
        $lastLogin = (string) $lastLoginValue;
    }
    $statement->close();
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 10 admin dashboard] ' . $exception->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard | StudentHub</title>
    <link rel="stylesheet" href="../../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../../practical-09-secure-registration/assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <script src="../../practical-04-dom-interactivity/script.js" defer></script>
</head>
<body>
<header class="navbar">
    <a class="logo" href="../../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home"><img src="../../assets/images/logo.jpg" alt=""><h2>Student<span>Hub</span></h2></a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation"><a href="../../practical-02-semantic-html/pages/index.html">Home</a><a href="../../practical-06-fetch-json/pages/events.html">Events</a></nav>
    <div class="auth-buttons"><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>"><button class="btn btn-primary" type="submit">Logout</button></form><button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button></div>
</header>
<main class="auth-dashboard" id="main-content">
    <p class="eyebrow">Administrator account</p>
    <h1>Welcome, <?= authEscape($user['full_name']) ?>.</h1>
    <p>You are signed in as <strong><?= authEscape($user['username']) ?></strong> with the admin role.</p>
    <div class="auth-detail"><span>Last successful login</span><strong><?= authEscape($lastLogin) ?></strong></div>
    <div class="notice success" role="status">Admin access is restricted to accounts with the admin role.</div>
    <a class="btn btn-blue" href="../../practical-11-student-management/">Manage students</a>
    <a class="btn btn-blue" href="../../practical-02-semantic-html/pages/index.html">Return to StudentHub</a>
</main>
<footer class="site-footer">StudentHub · Admin dashboard</footer>
</body>
</html>
