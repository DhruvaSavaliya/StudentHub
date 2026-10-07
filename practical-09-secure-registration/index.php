<?php
declare(strict_types=1);

require __DIR__ . '/lib/registration.php';
require __DIR__ . '/config/db.php';

startRegistrationSession();
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

if (!isset($_SESSION['registration_csrf'])) {
    $_SESSION['registration_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['registration_csrf'];
$old = ['full_name' => '', 'username' => '', 'email' => ''];
$errors = [];
$notice = $_SESSION['registration_notice'] ?? null;
unset($_SESSION['registration_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        http_response_code(400);
        $notice = ['type' => 'error', 'text' => 'Your form session expired. Reload the page and try again.'];
    } else {
        [$values, $errors] = validateRegistration($_POST);
        $old = [
            'full_name' => $values['full_name'],
            'username' => $values['username'],
            'email' => $values['email'],
        ];

        if ($errors !== []) {
            http_response_code(422);
            $notice = ['type' => 'error', 'text' => 'Please correct the highlighted fields and submit again.'];
        } else {
            try {
                $result = saveRegistration(studentHubMysqli(), $values);
                $_SESSION['registration_csrf'] = bin2hex(random_bytes(32));
                $csrfToken = $_SESSION['registration_csrf'];
                if ($result === 'created') {
                    $_SESSION['registration_notice'] = [
                        'type' => 'success',
                        'text' => 'Registration complete. Your account was saved securely.',
                    ];
                    session_regenerate_id(true);
                    header('Location: index.php', true, 303);
                    exit;
                }
                $notice = ['type' => 'error', 'text' => 'That email or username is already registered. Try different details.'];
            } catch (Throwable $error) {
                error_log('[StudentHub Practical 9] ' . $error->getMessage());
                http_response_code(503);
                $notice = ['type' => 'error', 'text' => 'Registration could not be saved. Check that MySQL is running and try again.'];
            }
        }
    }
}

$noticeClass = ($notice['type'] ?? '') === 'success' ? 'notice success' : 'notice error';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Secure StudentHub account registration practical using PHP and MySQLi.">
    <title>Secure Registration | StudentHub</title>
    <link rel="stylesheet" href="../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="../practical-04-dom-interactivity/script.js" defer></script>
    <script src="assets/js/register.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to registration form</a>
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
        <a href="../practical-02-semantic-html/pages/login.html" class="btn btn-primary">Login</a>
        <a href="index.php" class="btn btn-primary" aria-current="page">Register</a>
        <button id="themeToggle" class="theme-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Toggle theme">🌙</button>
    </div>
</header>
<div class="notification" id="notification" role="status">
    <span>📢 StudentHub · Secure registration demo</span>
    <button id="closeNotification" type="button" aria-label="Dismiss notification">&times;</button>
</div>
<main id="main-content" class="layout" tabindex="-1">
    <section class="intro" aria-labelledby="page-title">
        <p class="eyebrow">StudentHub Registration</p>
        <h1 id="page-title">Create your account</h1>
        <p class="intro-copy">Register securely with server-side validation, duplicate checks, and password hashing.</p>
        <ul class="security-list">
            <li><span aria-hidden="true">✓</span> Passwords are stored as one-way hashes</li>
            <li><span aria-hidden="true">✓</span> Email and username duplicates are checked</li>
            <li><span aria-hidden="true">✓</span> Form requests include CSRF protection</li>
        </ul>
    </section>

    <section class="form-card" aria-labelledby="form-title">
        <div class="card-heading">
            <p class="eyebrow">Student account</p>
            <h2 id="form-title">Registration details</h2>
            <p>Use fictional details for this demo.</p>
        </div>

        <?php if (is_array($notice)): ?>
            <div class="<?= registrationEscape($noticeClass) ?>" role="<?= $notice['type'] === 'success' ? 'status' : 'alert' ?>">
                <?= registrationEscape((string) $notice['text']) ?>
            </div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <ul class="error-summary" aria-label="Validation errors">
                <?php foreach ($errors as $message): ?>
                    <li><?= registrationEscape($message) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form id="registrationForm" method="post" action="index.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= registrationEscape($csrfToken) ?>">

            <div class="field">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" type="text" autocomplete="name" required minlength="2" maxlength="80" value="<?= registrationEscape($old['full_name']) ?>" aria-invalid="<?= isset($errors['full_name']) ? 'true' : 'false' ?>" aria-describedby="fullNameHint fullNameError">
                <small id="fullNameHint" class="hint">2 to 80 characters.</small>
                <small id="fullNameError" class="field-error"><?= registrationEscape($errors['full_name'] ?? '') ?></small>
            </div>

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_]{3,30}" value="<?= registrationEscape($old['username']) ?>" aria-invalid="<?= isset($errors['username']) ? 'true' : 'false' ?>" aria-describedby="usernameHint usernameError">
                <small id="usernameHint" class="hint">3 to 30 letters, numbers, or underscores.</small>
                <small id="usernameError" class="field-error"><?= registrationEscape($errors['username'] ?? '') ?></small>
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= registrationEscape($old['email']) ?>" aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>" aria-describedby="emailHint emailError">
                <small id="emailHint" class="hint">Use an email address not already registered.</small>
                <small id="emailError" class="field-error"><?= registrationEscape($errors['email'] ?? '') ?></small>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required minlength="12" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{12,128}" aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>" aria-describedby="passwordHint passwordError">
                <small id="passwordHint" class="hint">12–128 characters; include lowercase, uppercase, a number, and a symbol.</small>
                <small id="passwordError" class="field-error"><?= registrationEscape($errors['password'] ?? '') ?></small>
            </div>

            <div class="field">
                <label for="password_confirm">Confirm password</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required aria-invalid="<?= isset($errors['password_confirm']) ? 'true' : 'false' ?>" aria-describedby="confirmHint confirmError">
                <small id="confirmHint" class="hint">Enter the same password again.</small>
                <small id="confirmError" class="field-error"><?= registrationEscape($errors['password_confirm'] ?? '') ?></small>
            </div>

            <button class="submit" type="submit">Create account <span aria-hidden="true">→</span></button>
        </form>
        <p class="privacy-note">Demo only. Do not enter real passwords or personal information.</p>
    </section>
</main>
<footer class="site-footer">StudentHub · Secure student registration</footer>
</body>
</html>
