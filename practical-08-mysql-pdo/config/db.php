<?php
declare(strict_types=1);

/**
 * Build a PDO connection from environment variables. Keep credentials outside
 * source control; see this practical's README for local setup instructions.
 */
function studentHubDatabase(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('STUDENTHUB_DB_HOST') ?: '127.0.0.1';
    $port = getenv('STUDENTHUB_DB_PORT') ?: '3306';
    $database = getenv('STUDENTHUB_DB_NAME') ?: 'studenthub';
    $username = getenv('STUDENTHUB_DB_USER') ?: '';
    $password = getenv('STUDENTHUB_DB_PASSWORD');

    if ($username === '' || $password === false || $password === '') {
        throw new RuntimeException('Set STUDENTHUB_DB_USER and STUDENTHUB_DB_PASSWORD before connecting.');
    }
    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('STUDENTHUB_DB_PORT must be a valid TCP port.');
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
        throw new RuntimeException('STUDENTHUB_DB_NAME contains unsupported characters.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        (int) $port,
        $database
    );

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
