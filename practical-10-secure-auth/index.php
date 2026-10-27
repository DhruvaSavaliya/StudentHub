<?php
declare(strict_types=1);

require __DIR__ . '/lib/auth.php';
authInitialize();
$user = authCurrentUser();
if ($user !== null) {
    authRedirectForRole($user);
}
header('Location: login.php', true, 303);
exit;
