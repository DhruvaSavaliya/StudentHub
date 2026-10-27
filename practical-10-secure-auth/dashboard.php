<?php
declare(strict_types=1);

require __DIR__ . '/lib/auth.php';
authInitialize();
$user = authCurrentUser();
if ($user === null) {
    $_SESSION['auth_flash'] = 'Sign in to open your dashboard.';
    header('Location: login.php', true, 303);
    exit;
}
authRedirectForRole($user);
