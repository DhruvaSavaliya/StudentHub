<?php
declare(strict_types=1);

require __DIR__ . '/lib/auth.php';
authInitialize();

$user = authCurrentUser();
if ($user !== null) {
    authRedirectForRole($user);
}

$error = authConsumeFlash();
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Your sign-in form expired. Reload the page and try again.';
    } else {
        $identifier = strtolower(trim((string) ($_POST['identifier'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if ($identifier === '' || $password === '') {
            $error = 'Enter your username or email and password.';
        } else {
            try {
                $db = studentHubMysqli();
                $account = authFindLoginUser($db, $identifier);
                if ($account === null || !password_verify($password, $account['password_hash'])) {
                    usleep(250000);
                    $error = 'Username/email or password is incorrect.';
                } else {
                    authCompleteLogin($db, $account, isset($_POST['remember_me']) && $_POST['remember_me'] === '1');
                    authRedirectForRole(authCurrentUser() ?? $account);
                }
            } catch (Throwable $exception) {
                error_log('[StudentHub Practical 10 login] ' . $exception->getMessage());
                http_response_code(503);
                $error = 'Sign-in is temporarily unavailable. Check the local MySQL service and try again.';
            }
        }
    }
}

$csrfToken = authCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Secure StudentHub sign-in demonstration using PHP sessions and role checks.">
    <title>Sign in | StudentHub</title>
    <link rel="stylesheet" href="../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../practical-09-secure-registration/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="../practical-04-dom-interactivity/script.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to sign-in form</a>
<header class="navbar">
    <a class="logo" href="../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home">
        <img src="../assets/images/logo.jpg" alt="">
        <h2>Student<span>Hub</span></h2>
    </a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
        <a href="../practical-02-semantic-html/pages/index.html">Home</a>
        <a href="../practical-06-fetch-json/pages/events.html">Events</a>
        <a href="../practical-06-fetch-json/pages/students.html">Students</a>
        <a href="../practical-06-fetch-json/pages/faq.html">FAQ</a>
    </nav>
    <div class="auth-buttons">
        <a href="login.php" class="btn btn-primary" aria-current="page">Login</a>
        <a href="../practical-09-secure-registration/" class="btn btn-primary">Register</a>
        <button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button>
    </div>
</header>
<main id="main-content" class="layout auth-layout" tabindex="-1">
    <section class="intro" aria-labelledby="page-title">
        <p class="eyebrow">StudentHub · Practical 10</p>
        <h1 id="page-title">Welcome back.</h1>
        <p class="intro-copy">Sign in to continue to your role-specific StudentHub dashboard.</p>
        <ul class="security-list">
            <li><span aria-hidden="true">✓</span> Passwords are checked with password_verify()</li>
            <li><span aria-hidden="true">✓</span> Admin and student areas are access-controlled</li>
            <li><span aria-hidden="true">✓</span> Sessions expire after 15 minutes idle</li>
        </ul>
    </section>
    <section class="form-card" aria-labelledby="form-title">
        <div class="card-heading">
            <p class="eyebrow">Secure account access</p>
            <h2 id="form-title">Sign in</h2>
            <p>Use a seeded demo account from this practical.</p>
        </div>
        <?php if ($error !== null): ?>
            <div class="notice error" role="alert"><?= authEscape($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>">
            <div class="field">
                <label for="identifier">Username or email</label>
                <input id="identifier" name="identifier" type="text" autocomplete="username" required maxlength="254" value="<?= authEscape($identifier) ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="remember-choice" for="remember_me">
                <input id="remember_me" name="remember_me" type="checkbox" value="1">
                <span>Keep me signed in on this device for 30 days</span>
            </label>
            <button class="submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <p class="privacy-note">Classroom demo only. Use fictional test accounts and passwords.</p>
    </section>
</main>
<footer class="site-footer">StudentHub · Secure session and role access</footer>
</body>
</html>
