<?php
declare(strict_types=1);

require __DIR__ . '/../../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/../../practical-14-admin-dashboard/lib/dashboard.php';
require __DIR__ . '/../../practical-12-event-management/lib/events.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function p13Respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function p13Body(): array
{
    $body = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($body) ? $body : [];
}

authInitialize();
$admin = authCurrentUser();
if ($admin === null) {
    p13Respond(401, ['ok' => false, 'message' => 'Your session has expired. Sign in again.']);
}
if (($admin['role'] ?? '') !== 'admin') {
    p13Respond(403, ['ok' => false, 'message' => 'Administrator access is required.']);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    p13Respond(405, ['ok' => false, 'message' => 'This request method is not supported.']);
}
if ($method !== 'GET' && !authVerifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    p13Respond(403, ['ok' => false, 'message' => 'Security token expired. Refresh the page and try again.']);
}

try {
    $db = studentHubMysqli();
    if ($method === 'GET') {
        $filters = p12Filters(['q' => $_GET['q'] ?? '']);
        p13Respond(200, ['ok' => true, 'events' => p12ListEvents($db, $filters, 100, 0)]);
    }

    $input = p13Body();
    if ($method === 'POST' || $method === 'PUT') {
        [$values, $errors] = p12ValidateEvent($input);
        if ($errors !== []) {
            p13Respond(422, ['ok' => false, 'message' => 'Check the event fields and try again.', 'errors' => $errors]);
        }
        $description = $values['description'] === '' ? null : $values['description'];
        $category = $values['category'];
        $status = $values['event_status'];
        $capacity = (int) $values['capacity'];

        if ($method === 'POST') {
            $db->begin_transaction();
            try {
                $statement = $db->prepare('INSERT INTO events (title, description, venue, starts_at, capacity, category, event_status) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $statement->bind_param('ssssiss', $values['title'], $description, $values['venue'], $values['starts_at'], $capacity, $category, $status);
                $statement->execute();
                $id = (int) $db->insert_id;
                $statement->close();
                p12Audit($db, $id, $values['title'], 'created', (int) $admin['id']);
                $db->commit();
                p14LogAction($db, (int) $admin['id'], 'event.created', 'event', $id, 'Created an event through the JSON API');
                p13Respond(201, ['ok' => true, 'message' => 'Event added successfully.', 'id' => $id]);
            } catch (Throwable $exception) {
                $db->rollback();
                throw $exception;
            }
        }

        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            p13Respond(422, ['ok' => false, 'message' => 'Choose a valid event to update.']);
        }
        $oldEvent = p12GetEvent($db, (int) $id);
        if ($oldEvent === null) {
            p13Respond(404, ['ok' => false, 'message' => 'That event no longer exists.']);
        }
        $db->begin_transaction();
        try {
            $statement = $db->prepare('UPDATE events SET title = ?, description = ?, venue = ?, starts_at = ?, capacity = ?, category = ?, event_status = ? WHERE id = ?');
            $eventId = (int) $id;
            $statement->bind_param('ssssissi', $values['title'], $description, $values['venue'], $values['starts_at'], $capacity, $category, $status, $eventId);
            $statement->execute();
            $statement->close();
            p12Audit($db, $eventId, $values['title'], 'updated', (int) $admin['id']);
            $db->commit();
            p14LogAction($db, (int) $admin['id'], 'event.updated', 'event', $eventId, 'Updated an event through the JSON API');
            p13Respond(200, ['ok' => true, 'message' => 'Event updated successfully.']);
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }

    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id < 1) {
        p13Respond(422, ['ok' => false, 'message' => 'Choose a valid event to delete.']);
    }
    $event = p12GetEvent($db, (int) $id);
    if ($event === null) {
        p13Respond(404, ['ok' => false, 'message' => 'That event no longer exists.']);
    }
    $eventId = (int) $id;
    $check = $db->prepare('SELECT COUNT(*) FROM registrations WHERE event_id = ?');
    $check->bind_param('i', $eventId);
    $check->execute();
    $check->bind_result($registrationCount);
    $check->fetch();
    $check->close();
    if ((int) $registrationCount > 0) {
        p13Respond(409, ['ok' => false, 'message' => 'This event has registrations and cannot be deleted. Close it instead.']);
    }
    $db->begin_transaction();
    try {
        $statement = $db->prepare('DELETE FROM events WHERE id = ?');
        $statement->bind_param('i', $eventId);
        $statement->execute();
        $statement->close();
        p12Audit($db, null, (string) $event['title'], 'deleted', (int) $admin['id']);
        $db->commit();
        p14LogAction($db, (int) $admin['id'], 'event.deleted', 'event', $eventId, 'Deleted an event through the JSON API');
        p13Respond(200, ['ok' => true, 'message' => 'Event deleted successfully.']);
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 13 events API] ' . $exception->getMessage());
    p13Respond(500, ['ok' => false, 'message' => 'The event API could not complete the request. Check the database setup.']);
}
