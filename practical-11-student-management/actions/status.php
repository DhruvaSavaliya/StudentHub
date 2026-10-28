<?php
declare(strict_types=1);

require __DIR__ . '/../../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/../../practical-14-admin-dashboard/lib/dashboard.php';
require __DIR__ . '/../lib/students.php';
$admin = authRequireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the student list action to update a record.');
}
if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('The request expired. Return to the student list and try again.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$status = (string) ($_POST['status'] ?? '');
if ($id === false || $id === null || $id < 1 || !in_array($status, ['active', 'inactive'], true)) {
    p11SetFlash('error', 'The requested student action was invalid.');
    header('Location: ../index.php', true, 303);
    exit;
}

try {
    $db = studentHubMysqli();
    $changed = p11SetStudentStatus($db, $id, $status);
    if ($changed) {
        $action = $status === 'inactive' ? 'student.archived' : 'student.restored';
        p14LogAction($db, (int) $admin['id'], $action, 'student', $id, 'Changed student record status');
    }
    p11SetFlash($changed ? 'success' : 'error', $changed
        ? ($status === 'inactive' ? 'Student archived successfully.' : 'Student restored successfully.')
        : 'The student record was not changed.');
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 11 status] ' . $exception->getMessage());
    p11SetFlash('error', 'The student status could not be updated. Try again.');
}
header('Location: ../index.php', true, 303);
exit;
