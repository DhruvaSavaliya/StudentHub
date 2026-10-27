<?php
declare(strict_types=1);

require_once __DIR__ . '/../../practical-09-secure-registration/config/db.php';

const AUTH_IDLE_TIMEOUT_SECONDS = 900;
const AUTH_REMEMBER_COOKIE = 'studenthub_remember';
const AUTH_REMEMBER_DAYS = 30;
const AUTH_BASE_PATH = '/practical-10-secure-auth/';

function authEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function authStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    session_name('STUDENTHUB_AUTH_P10');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function authInitialize(): void
{
    authStartSession();
    $now = time();

    if (isset($_SESSION['auth_user'])) {
        $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
        if ($lastActivity === 0 || ($now - $lastActivity) > AUTH_IDLE_TIMEOUT_SECONDS) {
            unset($_SESSION['auth_user'], $_SESSION['last_activity']);
            session_regenerate_id(true);
            $_SESSION['auth_flash'] = 'Your session expired after 15 minutes of inactivity. Please sign in again.';
        } else {
            $_SESSION['last_activity'] = $now;
            return;
        }
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    authRestoreRememberedUser();
}

function authCsrfToken(): string
{
    authStartSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function authVerifyCsrf(mixed $submitted): bool
{
    return is_string($submitted)
        && isset($_SESSION['csrf_token'])
        && hash_equals((string) $_SESSION['csrf_token'], $submitted);
}

function authCurrentUser(): ?array
{
    $user = $_SESSION['auth_user'] ?? null;
    return is_array($user) ? $user : null;
}

function authFindLoginUser(mysqli $db, string $identifier): ?array
{
    $statement = $db->prepare(
        'SELECT id, full_name, username, email, password_hash, role '
        . 'FROM users WHERE username = ? OR email = ? LIMIT 1'
    );
    $statement->bind_param('ss', $identifier, $identifier);
    $statement->execute();
    $statement->bind_result($id, $fullName, $username, $email, $passwordHash, $role);
    $found = $statement->fetch();
    $statement->close();

    if (!$found) {
        return null;
    }

    return [
        'id' => (int) $id,
        'full_name' => (string) $fullName,
        'username' => (string) $username,
        'email' => (string) $email,
        'password_hash' => (string) $passwordHash,
        'role' => (string) $role,
    ];
}

function authCompleteLogin(mysqli $db, array $user, bool $remember): void
{
    if (!in_array($user['role'], ['student', 'admin'], true)) {
        throw new RuntimeException('The account has an unsupported role.');
    }

    $userId = (int) $user['id'];
    $update = $db->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
    $update->bind_param('i', $userId);
    $update->execute();
    $update->close();

    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id' => $userId,
        'full_name' => (string) $user['full_name'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
    ];
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['auth_flash']);

    if ($remember) {
        authIssueRememberToken($db, $userId);
    } else {
        authRevokeCurrentRememberToken($db);
    }
}

function authIssueRememberToken(mysqli $db, int $userId): void
{
    authRevokeCurrentRememberToken($db);
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $validatorHash = hash('sha256', $validator);
    $expiresAt = (new DateTimeImmutable('+' . AUTH_REMEMBER_DAYS . ' days'))->format('Y-m-d H:i:s');

    $statement = $db->prepare(
        'INSERT INTO auth_remember_tokens (selector, user_id, validator_hash, expires_at) VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('siss', $selector, $userId, $validatorHash, $expiresAt);
    $statement->execute();
    $statement->close();

    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    setcookie(AUTH_REMEMBER_COOKIE, $selector . '.' . $validator, [
        'expires' => time() + (AUTH_REMEMBER_DAYS * 86400),
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function authExpireRememberCookie(): void
{
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    setcookie(AUTH_REMEMBER_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[AUTH_REMEMBER_COOKIE]);
}

function authCookieParts(): ?array
{
    $cookie = $_COOKIE[AUTH_REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || substr_count($cookie, '.') !== 1) {
        return null;
    }

    [$selector, $validator] = explode('.', $cookie, 2);
    if (!preg_match('/\A[a-f0-9]{24}\z/', $selector)
        || !preg_match('/\A[a-f0-9]{64}\z/', $validator)) {
        return null;
    }
    return [$selector, $validator];
}

function authRevokeCurrentRememberToken(mysqli $db): void
{
    $parts = authCookieParts();
    if ($parts !== null) {
        [$selector] = $parts;
        $statement = $db->prepare('DELETE FROM auth_remember_tokens WHERE selector = ?');
        $statement->bind_param('s', $selector);
        $statement->execute();
        $statement->close();
    }
    authExpireRememberCookie();
}

function authRestoreRememberedUser(): void
{
    $parts = authCookieParts();
    if ($parts === null) {
        if (isset($_COOKIE[AUTH_REMEMBER_COOKIE])) {
            authExpireRememberCookie();
        }
        return;
    }

    [$selector, $validator] = $parts;
    try {
        $db = studentHubMysqli();
        $statement = $db->prepare(
            'SELECT t.user_id, t.validator_hash, t.expires_at, u.full_name, u.username, u.email, u.role '
            . 'FROM auth_remember_tokens AS t JOIN users AS u ON u.id = t.user_id '
            . 'WHERE t.selector = ? LIMIT 1'
        );
        $statement->bind_param('s', $selector);
        $statement->execute();
        $statement->bind_result($userId, $storedHash, $expiresAt, $fullName, $username, $email, $role);
        $found = $statement->fetch();
        $statement->close();

        if (!$found || strtotime((string) $expiresAt) <= time()
            || !hash_equals((string) $storedHash, hash('sha256', $validator))) {
            $delete = $db->prepare('DELETE FROM auth_remember_tokens WHERE selector = ?');
            $delete->bind_param('s', $selector);
            $delete->execute();
            $delete->close();
            authExpireRememberCookie();
            return;
        }

        $user = [
            'id' => (int) $userId,
            'full_name' => (string) $fullName,
            'username' => (string) $username,
            'email' => (string) $email,
            'role' => (string) $role,
        ];
        $delete = $db->prepare('DELETE FROM auth_remember_tokens WHERE selector = ?');
        $delete->bind_param('s', $selector);
        $delete->execute();
        $delete->close();
        authCompleteLogin($db, $user, true);
    } catch (Throwable $error) {
        error_log('[StudentHub Practical 10 remember-me] ' . $error->getMessage());
        authExpireRememberCookie();
    }
}

function authRedirectForRole(array $user): never
{
    $destination = $user['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php';
    header('Location: ' . AUTH_BASE_PATH . $destination, true, 303);
    exit;
}

function authRequireRole(string $role): array
{
    authInitialize();
    $user = authCurrentUser();
    if ($user === null) {
        header('Location: ../login.php', true, 303);
        exit;
    }
    if ($user['role'] !== $role) {
        $_SESSION['auth_flash'] = 'That page is not available for your account role.';
        authRedirectForRole($user);
    }
    return $user;
}

function authConsumeFlash(): ?string
{
    $flash = $_SESSION['auth_flash'] ?? null;
    unset($_SESSION['auth_flash']);
    return is_string($flash) ? $flash : null;
}

function authLogout(mysqli $db): void
{
    authRevokeCurrentRememberToken($db);
    unset($_SESSION['auth_user'], $_SESSION['last_activity']);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['auth_flash'] = 'You have been signed out.';
}
