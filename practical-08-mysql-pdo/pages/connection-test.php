<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/db.php';

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$connected = false;
$serverVersion = '';
$checkedAt = '';
$message = '';

try {
    $pdo = studentHubDatabase();
    $result = $pdo->query('SELECT VERSION() AS server_version, CURRENT_TIMESTAMP AS checked_at')->fetch();
    if (!is_array($result)) {
        throw new RuntimeException('The server did not return connection details.');
    }
    $serverVersion = (string) $result['server_version'];
    $checkedAt = (string) $result['checked_at'];
    $connected = true;
    $message = 'PDO connected to the StudentHub database successfully.';
} catch (Throwable $error) {
    error_log('[StudentHub Practical 8] ' . $error->getMessage());
    http_response_code(503);
    $message = 'Database connection failed. Start MySQL and check the local connection settings in your terminal.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 8 Database Connection</title>
    <style>
        :root { color-scheme: light; font: 16px/1.5 system-ui, sans-serif; color: #1f2937; background: #f4f7fb; }
        body { margin: 0; padding: 2rem 1rem; }
        main { max-width: 42rem; margin: 4rem auto; padding: 2rem; background: #fff; border: 1px solid #d7e0eb; border-radius: 1rem; box-shadow: 0 1rem 2.5rem #16324f12; }
        h1 { margin-top: 0; color: #17324d; }
        .status { padding: 1rem; border-radius: .65rem; font-weight: 650; }
        .success { color: #14532d; background: #dcfce7; border: 1px solid #86efac; }
        .failure { color: #7f1d1d; background: #fee2e2; border: 1px solid #fca5a5; }
        dl { display: grid; grid-template-columns: 10rem 1fr; gap: .5rem 1rem; }
        dt { font-weight: 650; }
        dd { margin: 0; overflow-wrap: anywhere; }
        code { background: #eef2f7; padding: .15rem .35rem; border-radius: .25rem; }
    </style>
</head>
<body>
<main>
    <p>StudentHub · ITUE203 · Practical 8</p>
    <h1>PDO connection check</h1>
    <p class="status <?= $connected ? 'success' : 'failure' ?>" role="status"><?= escape($message) ?></p>
    <?php if ($connected): ?>
        <dl>
            <dt>Database</dt><dd><code>studenthub</code></dd>
            <dt>Server version</dt><dd><?= escape($serverVersion) ?></dd>
            <dt>Checked at</dt><dd><?= escape($checkedAt) ?></dd>
        </dl>
    <?php endif; ?>
    <p>Connection details and passwords are never displayed on this page.</p>
</main>
</body>
</html>
