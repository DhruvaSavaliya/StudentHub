<?php
declare(strict_types=1);

function startRegistrationSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    session_name('STUDENTHUB_P9');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Strict',
        'path' => '/',
    ]);
    session_start();
}

function registrationEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Return normalized form values and field-specific errors. */
function validateRegistration(array $input): array
{
    $values = [
        'full_name' => trim((string) ($input['full_name'] ?? '')),
        'username' => strtolower(trim((string) ($input['username'] ?? ''))),
        'email' => strtolower(trim((string) ($input['email'] ?? ''))),
        'password' => (string) ($input['password'] ?? ''),
        'password_confirm' => (string) ($input['password_confirm'] ?? ''),
    ];
    $errors = [];

    if (preg_match('/\A.{2,80}\z/us', $values['full_name']) !== 1) {
        $errors['full_name'] = 'Enter a name between 2 and 80 characters.';
    }
    if (preg_match('/\A[A-Za-z0-9_]{3,30}\z/', $values['username']) !== 1) {
        $errors['username'] = 'Use 3–30 letters, numbers, or underscores.';
    }
    if (strlen($values['email']) > 254 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address (up to 254 characters).';
    }
    if (preg_match('/\A(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{12,128}\z/D', $values['password']) !== 1) {
        $errors['password'] = 'Use 12–128 characters with lowercase, uppercase, a number, and a symbol.';
    }
    if ($values['password_confirm'] === '' || !hash_equals($values['password'], $values['password_confirm'])) {
        $errors['password_confirm'] = 'The passwords do not match.';
    }

    return [$values, $errors];
}

function writeRegistrationAudit(mysqli $db, ?int $userId, string $outcome): void
{
    $statement = $db->prepare(
        'INSERT INTO registration_audit_log (user_id, outcome) VALUES (?, ?)'
    );
    $statement->bind_param('is', $userId, $outcome);
    $statement->execute();
    $statement->close();
}

/** Check duplicates, hash the password, and insert using prepared statements. */
function saveRegistration(mysqli $db, array $values): string
{
    $username = $values['username'];
    $email = $values['email'];
    $check = $db->prepare(
        'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1'
    );
    $check->bind_param('ss', $username, $email);
    $check->execute();
    $check->store_result();
    $duplicate = $check->num_rows > 0;
    $check->close();

    if ($duplicate) {
        writeRegistrationAudit($db, null, 'duplicate_rejected');
        return 'duplicate';
    }

    $passwordHash = password_hash($values['password'], PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        throw new RuntimeException('Password hashing failed.');
    }

    try {
        $db->begin_transaction();
        $insert = $db->prepare(
            'INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)'
        );
        $insert->bind_param('ssss', $values['full_name'], $username, $email, $passwordHash);
        $insert->execute();
        $userId = (int) $db->insert_id;
        $insert->close();

        writeRegistrationAudit($db, $userId, 'registration_success');
        $db->commit();
        return 'created';
    } catch (mysqli_sql_exception $error) {
        $db->rollback();
        if ((int) $error->getCode() === 1062) {
            // A simultaneous request may pass the first check; the UNIQUE keys still protect the insert.
            writeRegistrationAudit($db, null, 'duplicate_rejected');
            return 'duplicate';
        }
        throw $error;
    }
}
