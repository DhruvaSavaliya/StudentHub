<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/dashboard.php';
$user = authRequireRole('admin');
$csrfToken = authCsrfToken();
$dbError = null;
$stats = ['users' => 0, 'students' => 0, 'events' => 0, 'registrations' => 0];
$registrations = [];
$auditRows = [];

try {
    $db = studentHubMysqli();
    p14LogAction($db, (int) $user['id'], 'dashboard.viewed', 'dashboard', null, 'Opened administrator overview');
    $stats['users'] = p14Count($db, 'users');
    $stats['students'] = p14Count($db, 'students');
    $stats['events'] = p14Count($db, 'events');
    $stats['registrations'] = p14Count($db, 'registrations');
    $registrations = p14RecentRegistrations($db);
    $auditRows = p14RecentAudit($db);
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 14 dashboard] ' . $exception->getMessage());
    $dbError = 'Dashboard data could not be loaded. Check that Practicals 8–14 database setup is complete.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin overview · Practical 14 | StudentHub</title>
    <link rel="stylesheet" href="../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../practical-09-secure-registration/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="../practical-04-dom-interactivity/script.js" defer></script>
</head>
<body>
<header class="navbar">
    <a class="logo" href="../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home"><img src="../assets/images/logo.jpg" alt=""><h2>Student<span>Hub</span></h2></a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Administrator navigation">
        <a href="../practical-02-semantic-html/pages/index.html">Home</a>
        <a href="../practical-06-fetch-json/pages/events.html">Events</a>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="../practical-11-student-management/">Manage students</a>
            <a href="../practical-12-event-management/">Manage events</a>
            <a href="index.php" aria-current="page">Admin overview</a>
        <?php endif; ?>
    </nav>
    <div class="auth-buttons"><form method="post" action="../practical-10-secure-auth/logout.php"><input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>"><button class="btn btn-primary" type="submit">Logout</button></form><button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button></div>
</header>
<main class="p14-wrap" id="main-content">
    <section class="p14-intro">
        <div><p class="eyebrow">Administrator · Practical 14</p><h1>Admin dashboard</h1><p>Welcome, <?= authEscape($user['full_name']) ?>. This overview is available to administrator accounts only.</p></div>
        <span class="p14-role">Role · <?= authEscape(ucfirst($user['role'])) ?></span>
    </section>
    <?php if ($dbError !== null): ?><div class="p14-message error" role="alert"><?= authEscape($dbError) ?></div><?php else: ?>
        <section class="p14-stats" aria-label="StudentHub statistics">
            <article class="p14-stat"><span>Total accounts</span><strong><?= $stats['users'] ?></strong><small>Registered user accounts</small></article>
            <article class="p14-stat"><span>Students</span><strong><?= $stats['students'] ?></strong><small>Student records</small></article>
            <article class="p14-stat"><span>Events</span><strong><?= $stats['events'] ?></strong><small>Events in the database</small></article>
            <article class="p14-stat"><span>Registrations</span><strong><?= $stats['registrations'] ?></strong><small>Event registration records</small></article>
        </section>
        <div class="p14-grid">
            <section class="p14-card" aria-labelledby="recent-registration-heading">
                <div class="p14-section-heading"><div><p class="eyebrow">Latest activity</p><h2 id="recent-registration-heading">Recent registrations</h2></div><span><?= count($registrations) ?> shown</span></div>
                <?php if ($registrations === []): ?><p class="p14-empty">No event registrations are recorded yet.</p><?php else: ?>
                    <div class="p14-table-wrap"><table class="p14-table"><thead><tr><th>Student</th><th>Event</th><th>Status</th><th>Registered</th></tr></thead><tbody>
                    <?php foreach ($registrations as $row): ?><tr><td><?= authEscape($row['student_name']) ?></td><td><?= authEscape($row['event_title']) ?></td><td><span class="p14-status"><?= authEscape($row['status']) ?></span></td><td><?= authEscape($row['registered_at']) ?></td></tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
            <section class="p14-card" aria-labelledby="audit-heading">
                <div class="p14-section-heading"><div><p class="eyebrow">Traceability</p><h2 id="audit-heading">Admin audit log</h2></div><span>Recent actions</span></div>
                <?php if ($auditRows === []): ?><p class="p14-empty">No admin actions have been logged yet.</p><?php else: ?>
                    <ol class="p14-audit-list"><?php foreach ($auditRows as $row): ?><li><span class="p14-audit-action"><?= authEscape($row['action']) ?></span><span><?= authEscape($row['actor_name'] ?? 'Admin account') ?> · <?= authEscape($row['entity_type']) ?><?= $row['entity_id'] ? ' #' . (int) $row['entity_id'] : '' ?></span><time><?= authEscape($row['created_at']) ?></time><?php if ($row['details'] !== ''): ?><small><?= authEscape($row['details']) ?></small><?php endif; ?></li><?php endforeach; ?></ol>
                <?php endif; ?>
            </section>
        </div>
        <section class="p14-shortcuts" aria-label="Administrator shortcuts"><a class="btn btn-blue" href="../practical-11-student-management/">Manage students</a><a class="btn btn-blue" href="../practical-12-event-management/">Manage events</a><a class="btn btn-outline" href="../practical-13-php-json-api/">Open event API demo</a></section>
    <?php endif; ?>
</main>
<footer class="site-footer">StudentHub · Practical 14 role-based admin dashboard</footer>
</body>
</html>
