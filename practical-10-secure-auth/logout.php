<?php
declare(strict_types=1);

require __DIR__ . '/lib/auth.php';
authInitialize();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the sign-out button to end your session.');
}

if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('The sign-out request expired. Return to your dashboard and try again.');
}

try {
    authLogout(studentHubMysqli());
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 10 logout] ' . $exception->getMessage());
    authExpireRememberCookie();
    unset($_SESSION['auth_user'], $_SESSION['last_activity']);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['auth_flash'] = 'You have been signed out. The database could not revoke a remember-me token.';
}

header('Location: login.php', true, 303);
exit;
