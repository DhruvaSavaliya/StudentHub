<?php
declare(strict_types=1);

/** Connect to the existing StudentHub database using the Practical 8 env vars. */
function studentHubMysqli(): mysqli
{
    static $connection = null;
    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = getenv('STUDENTHUB_DB_HOST') ?: '127.0.0.1';
    $portValue = getenv('STUDENTHUB_DB_PORT') ?: '3306';
    $database = getenv('STUDENTHUB_DB_NAME') ?: 'studenthub';
    $username = getenv('STUDENTHUB_DB_USER') ?: '';
    $password = getenv('STUDENTHUB_DB_PASSWORD');

    if ($username === '' || $password === false || $password === '') {
        throw new RuntimeException('Set the StudentHub database user and password in the environment.');
    }
    if (!ctype_digit($portValue) || (int) $portValue < 1 || (int) $portValue > 65535) {
        throw new RuntimeException('The StudentHub database port is invalid.');
    }
    if (!preg_match('/\A[A-Za-z0-9_]+\z/', $database)) {
        throw new RuntimeException('The StudentHub database name is invalid.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($host, $username, $password, $database, (int) $portValue);
    $connection->set_charset('utf8mb4');

    return $connection;
}
