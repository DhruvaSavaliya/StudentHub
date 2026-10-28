<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/students.php';
authRequireRole('admin');

try {
    p11ExportStudents(studentHubMysqli(), p11Filters($_GET));
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 11 CSV export] ' . $exception->getMessage());
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('The student list could not be exported. Check the database and try again.');
}
