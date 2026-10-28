<?php
declare(strict_types=1);

const P12_MAX_POSTER_BYTES = 2097152;
const P12_UPLOAD_DIR = __DIR__ . '/../uploads';

function p12Escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function p12SetFlash(string $type, string $message): void
{
    $_SESSION['p12_flash'] = ['type' => $type, 'message' => $message];
}

function p12TakeFlash(): ?array
{
    $flash = $_SESSION['p12_flash'] ?? null;
    unset($_SESSION['p12_flash']);
    return is_array($flash) ? $flash : null;
}

function p12InputString(array $input, string $key): string
{
    $value = $input[$key] ?? '';
    return is_string($value) || is_numeric($value) ? (string) $value : '';
}

function p12Categories(): array
{
    return [
        'academic' => 'Academic',
        'workshop' => 'Workshop',
        'cultural' => 'Cultural',
        'sports' => 'Sports',
        'career' => 'Career',
        'general' => 'General',
    ];
}

function p12ValidateEvent(array $input): array
{
    $rawCapacity = filter_var($input['capacity'] ?? null, FILTER_VALIDATE_INT);
    $values = [
        'title' => trim(p12InputString($input, 'title')),
        'description' => trim(p12InputString($input, 'description')),
        'venue' => trim(p12InputString($input, 'venue')),
        'starts_at_input' => trim(p12InputString($input, 'starts_at')),
        'capacity' => $rawCapacity === false ? 0 : $rawCapacity,
        'category' => p12InputString($input, 'category'),
        'event_status' => p12InputString($input, 'event_status'),
    ];
    $errors = [];

    if ($values['title'] === '' || mb_strlen($values['title']) > 140) {
        $errors['title'] = 'Enter an event title of up to 140 characters.';
    }
    if (mb_strlen($values['description']) > 5000) {
        $errors['description'] = 'The description must be 5000 characters or fewer.';
    }
    if ($values['venue'] === '' || mb_strlen($values['venue']) > 120) {
        $errors['venue'] = 'Enter a venue of up to 120 characters.';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $values['starts_at_input']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
        $errors['starts_at'] = 'Choose a valid event date and time.';
        $values['starts_at'] = '';
    } else {
        $values['starts_at'] = $date->format('Y-m-d H:i:s');
    }
    if ($values['capacity'] < 1 || $values['capacity'] > 65535) {
        $errors['capacity'] = 'Capacity must be between 1 and 65,535.';
    }
    if (!array_key_exists($values['category'], p12Categories())) {
        $errors['category'] = 'Choose an event category.';
    }
    if (!in_array($values['event_status'], ['open', 'closed'], true)) {
        $errors['event_status'] = 'Choose Open or Closed.';
    }
    return [$values, $errors];
}

function p12ValidatePoster(array $file): ?string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The poster exceeds the 2 MB size limit.',
            UPLOAD_ERR_PARTIAL => 'The poster upload was incomplete. Try again.',
            default => 'The poster could not be uploaded. Try again.',
        };
    }
    if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file((string) $file['tmp_name'])) {
        return 'The uploaded poster could not be verified.';
    }
    if ((int) $file['size'] < 1 || (int) $file['size'] > P12_MAX_POSTER_BYTES) {
        return 'Choose an image smaller than or equal to 2 MB.';
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!is_string($mime) || !in_array($mime, $allowed, true)) {
        return 'Use a JPEG, PNG, or WebP image for the poster.';
    }
    $imageInfo = @getimagesize((string) $file['tmp_name']);
    if ($imageInfo === false || $imageInfo['mime'] !== $mime) {
        return 'The selected file is not a valid supported image.';
    }
    return null;
}

function p12StorePoster(array $file): string
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => throw new RuntimeException('Unsupported image type.'),
    };
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], P12_UPLOAD_DIR . '/' . $filename)) {
        throw new RuntimeException('The poster file could not be saved.');
    }
    return $filename;
}

function p12RemovePoster(?string $filename): void
{
    if ($filename !== null && preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $filename) === 1) {
        $path = P12_UPLOAD_DIR . '/' . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}

function p12Audit(mysqli $db, ?int $eventId, string $eventTitle, string $action, int $adminId): void
{
    $statement = $db->prepare(
        'INSERT INTO event_audit_log (admin_user_id, event_id, event_title, action) VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('iiss', $adminId, $eventId, $eventTitle, $action);
    $statement->execute();
    $statement->close();
}

function p12GetEvent(mysqli $db, int $id): ?array
{
    $statement = $db->prepare(
        'SELECT id, title, description, venue, starts_at, capacity, category, event_status, poster_filename '
        . 'FROM events WHERE id = ? LIMIT 1'
    );
    $statement->bind_param('i', $id);
    $statement->execute();
    $event = $statement->get_result()->fetch_assoc() ?: null;
    $statement->close();
    return $event;
}

function p12Filters(array $query): array
{
    $search = trim(p12InputString($query, 'q'));
    $category = p12InputString($query, 'category');
    if ($category !== '' && !array_key_exists($category, p12Categories())) {
        $category = '';
    }
    $status = p12InputString($query, 'status') ?: 'all';
    if (!in_array($status, ['all', 'open', 'closed'], true)) {
        $status = 'all';
    }
    return ['q' => $search, 'category' => $category, 'status' => $status];
}

function p12Where(array $filters): array
{
    $parts = [];
    $params = [];
    if ($filters['q'] !== '') {
        $parts[] = '(e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ? OR e.category LIKE ?)';
        $pattern = '%' . $filters['q'] . '%';
        array_push($params, $pattern, $pattern, $pattern, $pattern);
    }
    if ($filters['category'] !== '') {
        $parts[] = 'e.category = ?';
        $params[] = $filters['category'];
    }
    if ($filters['status'] !== 'all') {
        $parts[] = 'e.event_status = ?';
        $params[] = $filters['status'];
    }
    return [$parts === [] ? '' : ' WHERE ' . implode(' AND ', $parts), $params];
}

function p12CountEvents(mysqli $db, array $filters): int
{
    [$where, $params] = p12Where($filters);
    $statement = $db->prepare('SELECT COUNT(*) FROM events AS e' . $where);
    $statement->execute($params);
    $statement->bind_result($count);
    $statement->fetch();
    $statement->close();
    return (int) $count;
}

function p12ListEvents(mysqli $db, array $filters, int $limit, int $offset): array
{
    [$where, $params] = p12Where($filters);
    $params[] = $limit;
    $params[] = $offset;
    $statement = $db->prepare(
        'SELECT e.id, e.title, e.description, e.venue, e.starts_at, e.capacity, '
        . 'e.category, e.event_status, e.poster_filename, COUNT(r.id) AS registration_count '
        . 'FROM events AS e LEFT JOIN registrations AS r ON r.event_id = e.id'
        . $where . ' GROUP BY e.id ORDER BY e.starts_at DESC, e.id DESC LIMIT ? OFFSET ?'
    );
    $statement->execute($params);
    $events = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $events;
}
