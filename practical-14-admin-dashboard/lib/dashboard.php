<?php
declare(strict_types=1);

function p14Count(mysqli $db, string $table): int
{
    $allowed = ['users', 'students', 'events', 'registrations'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unsupported dashboard count.');
    }
    $result = $db->query('SELECT COUNT(*) AS total FROM `' . $table . '`');
    return (int) $result->fetch_assoc()['total'];
}

function p14LogAction(mysqli $db, int $actorId, string $action, string $entityType, ?int $entityId, string $details = ''): void
{
    try {
        $statement = $db->prepare('INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
        $statement->bind_param('issis', $actorId, $action, $entityType, $entityId, $details);
        $statement->execute();
        $statement->close();
    } catch (Throwable $exception) {
        // Keep earlier practicals usable until the Practical 14 migration is imported.
        error_log('[StudentHub audit log] ' . $exception->getMessage());
    }
}

function p14RecentRegistrations(mysqli $db): array
{
    $statement = $db->prepare(
        "SELECT CONCAT(s.first_name, ' ', s.last_name) AS student_name, e.title AS event_title, r.status, r.registered_at "
        . 'FROM registrations AS r JOIN students AS s ON s.id = r.student_id JOIN events AS e ON e.id = r.event_id '
        . 'ORDER BY r.registered_at DESC, r.id DESC LIMIT 8'
    );
    $statement->execute();
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}

function p14RecentAudit(mysqli $db): array
{
    $statement = $db->prepare(
        'SELECT a.action, a.entity_type, a.entity_id, a.details, a.created_at, u.full_name AS actor_name '
        . 'FROM audit_logs AS a LEFT JOIN users AS u ON u.id = a.actor_user_id '
        . 'ORDER BY a.created_at DESC, a.id DESC LIMIT 12'
    );
    $statement->execute();
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}
