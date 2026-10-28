<?php
declare(strict_types=1);

require __DIR__ . '/../../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/../lib/events.php';
$admin = authRequireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the event list action to delete an event.');
}
if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('The request expired. Return to the event list and try again.');
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    p12SetFlash('error', 'The event ID was invalid.');
    header('Location: ../index.php', true, 303);
    exit;
}

$db = studentHubMysqli();
$event = p12GetEvent($db, $id);
if ($event === null) {
    p12SetFlash('error', 'Event record was not found.');
    header('Location: ../index.php', true, 303);
    exit;
}

try {
    $count = $db->prepare('SELECT COUNT(*) FROM registrations WHERE event_id = ?');
    $count->bind_param('i', $id);
    $count->execute();
    $count->bind_result($registrationCount);
    $count->fetch();
    $count->close();
    if ((int) $registrationCount > 0) {
        p12SetFlash('error', 'This event has student registrations and cannot be deleted. Change its status to Closed instead.');
        header('Location: ../index.php', true, 303);
        exit;
    }

    $db->begin_transaction();
    p12Audit($db, null, $event['title'], 'deleted', (int) $admin['id']);
    $delete = $db->prepare('DELETE FROM events WHERE id = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
    $delete->close();
    $db->commit();
    p12RemovePoster($event['poster_filename']);
    p12SetFlash('success', 'Event deleted successfully.');
} catch (Throwable $exception) {
    try { $db->rollback(); } catch (Throwable) {}
    error_log('[StudentHub Practical 12 delete] ' . $exception->getMessage());
    p12SetFlash('error', 'The event could not be deleted. Try again.');
}
header('Location: ../index.php', true, 303);
exit;
