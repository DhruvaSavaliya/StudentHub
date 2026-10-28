<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
$admin = authRequireRole('admin');
$csrfToken = authCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= authEscape($csrfToken) ?>">
    <title>Practical 13 · Event API | StudentHub</title>
    <link rel="stylesheet" href="../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../practical-09-secure-registration/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/api.css">
    <script src="../practical-04-dom-interactivity/script.js" defer></script>
    <script src="assets/js/events.js" defer></script>
</head>
<body>
<header class="navbar">
    <a class="logo" href="../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home"><img src="../assets/images/logo.jpg" alt=""><h2>Student<span>Hub</span></h2></a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation"><a href="../practical-10-secure-auth/admin/dashboard.php">Admin dashboard</a><a href="../practical-12-event-management/">Event management</a></nav>
    <div class="auth-buttons"><form method="post" action="../practical-10-secure-auth/logout.php"><input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>"><button class="btn btn-primary" type="submit">Logout</button></form><button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button></div>
</header>
<main class="p13-wrap" id="main-content" data-admin="<?= authEscape($admin['username']) ?>">
    <div class="p13-heading"><div><p class="eyebrow">Administrator · Practical 13</p><h1>Event API playground</h1><p>Manage events through PHP JSON endpoints and Fetch, without reloading the page.</p></div><span class="p13-api-chip">JSON API · Fetch</span></div>
    <div id="notice" class="p13-notice" role="status" aria-live="polite" hidden></div>
    <section class="p13-panel" aria-labelledby="add-heading">
        <h2 id="add-heading">Add an event</h2>
        <form id="event-form" class="p13-form">
            <input type="hidden" name="id" value="">
            <label>Event title <input name="title" required maxlength="140" placeholder="Web Development Workshop"></label>
            <label>Category <select name="category" required><option value="academic">Academic</option><option value="workshop">Workshop</option><option value="cultural">Cultural</option><option value="sports">Sports</option><option value="career">Career</option><option value="general">General</option></select></label>
            <label>Venue <input name="venue" required maxlength="120" placeholder="CHARUSAT Computer Lab 2"></label>
            <label>Start date and time <input name="starts_at" type="datetime-local" required></label>
            <label>Capacity <input name="capacity" type="number" min="1" max="65535" required value="40"></label>
            <label>Status <select name="event_status"><option value="open">Open</option><option value="closed">Closed</option></select></label>
            <label class="p13-wide">Description <textarea name="description" rows="3" maxlength="5000" placeholder="Brief event details"></textarea></label>
            <div class="p13-form-actions p13-wide"><button id="save-button" class="btn btn-blue" type="submit">Add event</button><button id="cancel-edit" class="btn btn-outline" type="button" hidden>Cancel edit</button></div>
        </form>
    </section>
    <section class="p13-panel" aria-labelledby="events-heading">
        <div class="p13-list-heading"><div><h2 id="events-heading">Events</h2><p>Search updates asynchronously as you type.</p></div><label class="p13-search">Search events <input id="search" type="search" placeholder="Title, venue, category"></label></div>
        <div id="events-state" class="p13-state" aria-live="polite">Loading events…</div>
        <div class="p13-table-scroll"><table class="p13-table"><thead><tr><th>Event</th><th>Venue and date</th><th>Category</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody id="events-body"></tbody></table></div>
    </section>
</main>
<footer class="site-footer">StudentHub · Practical 13 PHP JSON API</footer>
</body>
</html>
