<?php
declare(strict_types=1);

function p11Escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function p11SetFlash(string $type, string $message): void
{
    $_SESSION['p11_flash'] = ['type' => $type, 'message' => $message];
}

function p11TakeFlash(): ?array
{
    $flash = $_SESSION['p11_flash'] ?? null;
    unset($_SESSION['p11_flash']);
    return is_array($flash) ? $flash : null;
}

function p11InputString(array $input, string $key): string
{
    $value = $input[$key] ?? '';
    return is_string($value) || is_numeric($value) ? (string) $value : '';
}

function p11Courses(mysqli $db): array
{
    $statement = $db->prepare('SELECT id, course_code, course_name FROM courses ORDER BY course_name');
    $statement->execute();
    $courses = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $courses;
}

function p11ValidateStudent(array $input, mysqli $db): array
{
    $values = [
        'student_code' => strtoupper(trim(p11InputString($input, 'student_code'))),
        'first_name' => trim(p11InputString($input, 'first_name')),
        'last_name' => trim(p11InputString($input, 'last_name')),
        'email' => strtolower(trim(p11InputString($input, 'email'))),
        'mobile' => trim(p11InputString($input, 'mobile')),
        'course_id' => filter_var($input['course_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'year_of_study' => filter_var($input['year_of_study'] ?? null, FILTER_VALIDATE_INT) ?: 0,
    ];
    $errors = [];

    if (!preg_match('/\A[A-Z0-9-]{3,24}\z/', $values['student_code'])) {
        $errors['student_code'] = 'Use 3 to 24 letters, numbers, or hyphens.';
    }
    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($values[$field] === '' || mb_strlen($values[$field]) > 60) {
            $errors[$field] = $label . ' is required and must be at most 60 characters.';
        }
    }
    if (strlen($values['email']) > 254 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address (up to 254 characters).';
    }
    if ($values['mobile'] !== '' && !preg_match('/\A\+?[0-9 ()-]{7,24}\z/', $values['mobile'])) {
        $errors['mobile'] = 'Enter a valid phone number or leave this field blank.';
    }
    if ($values['year_of_study'] < 1 || $values['year_of_study'] > 4) {
        $errors['year_of_study'] = 'Choose a year from 1 to 4.';
    }

    if ($values['course_id'] < 1) {
        $errors['course_id'] = 'Choose a course.';
    } else {
        $courseCheck = $db->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
        $courseCheck->bind_param('i', $values['course_id']);
        $courseCheck->execute();
        $courseCheck->store_result();
        if ($courseCheck->num_rows !== 1) {
            $errors['course_id'] = 'Choose a course from the list.';
        }
        $courseCheck->close();
    }

    return [$values, $errors];
}

function p11InsertStudent(mysqli $db, array $values): void
{
    $mobile = $values['mobile'] === '' ? null : $values['mobile'];
    $statement = $db->prepare(
        'INSERT INTO students (student_code, first_name, last_name, email, mobile, course_id, year_of_study) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param(
        'sssssii',
        $values['student_code'],
        $values['first_name'],
        $values['last_name'],
        $values['email'],
        $mobile,
        $values['course_id'],
        $values['year_of_study']
    );
    $statement->execute();
    $statement->close();
}

function p11UpdateStudent(mysqli $db, int $id, array $values): void
{
    $mobile = $values['mobile'] === '' ? null : $values['mobile'];
    $statement = $db->prepare(
        'UPDATE students SET student_code = ?, first_name = ?, last_name = ?, email = ?, mobile = ?, '
        . 'course_id = ?, year_of_study = ? WHERE id = ?'
    );
    $statement->bind_param(
        'sssssiii',
        $values['student_code'],
        $values['first_name'],
        $values['last_name'],
        $values['email'],
        $mobile,
        $values['course_id'],
        $values['year_of_study'],
        $id
    );
    $statement->execute();
    $statement->close();
}

function p11GetStudent(mysqli $db, int $id): ?array
{
    $statement = $db->prepare(
        'SELECT id, student_code, first_name, last_name, email, mobile, course_id, year_of_study, status '
        . 'FROM students WHERE id = ? LIMIT 1'
    );
    $statement->bind_param('i', $id);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc() ?: null;
    $statement->close();
    return $row;
}

function p11SetStudentStatus(mysqli $db, int $id, string $status): bool
{
    if (!in_array($status, ['active', 'inactive'], true)) {
        return false;
    }
    $statement = $db->prepare('UPDATE students SET status = ? WHERE id = ?');
    $statement->bind_param('si', $status, $id);
    $statement->execute();
    $changed = $statement->affected_rows > 0;
    $statement->close();
    return $changed;
}

function p11Filters(array $query): array
{
    $search = trim(p11InputString($query, 'q'));
    $courseId = filter_var($query['course_id'] ?? 0, FILTER_VALIDATE_INT);
    $courseId = $courseId !== false && $courseId > 0 ? $courseId : 0;
    $status = p11InputString($query, 'status') ?: 'active';
    if (!in_array($status, ['active', 'inactive', 'all'], true)) {
        $status = 'active';
    }
    return ['q' => $search, 'course_id' => $courseId, 'status' => $status];
}

function p11Where(array $filters): array
{
    $parts = [];
    $params = [];
    if ($filters['q'] !== '') {
        $parts[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ? "
            . 'OR s.email LIKE ? OR c.course_name LIKE ? OR c.course_code LIKE ?)';
        $pattern = '%' . $filters['q'] . '%';
        for ($index = 0; $index < 6; $index++) {
            $params[] = $pattern;
        }
    }
    if ($filters['course_id'] > 0) {
        $parts[] = 's.course_id = ?';
        $params[] = $filters['course_id'];
    }
    if ($filters['status'] !== 'all') {
        $parts[] = 's.status = ?';
        $params[] = $filters['status'];
    }
    return [$parts === [] ? '' : ' WHERE ' . implode(' AND ', $parts), $params];
}

function p11CountStudents(mysqli $db, array $filters): int
{
    [$where, $params] = p11Where($filters);
    $statement = $db->prepare(
        'SELECT COUNT(*) FROM students AS s JOIN courses AS c ON c.id = s.course_id' . $where
    );
    $statement->execute($params);
    $statement->bind_result($count);
    $statement->fetch();
    $statement->close();
    return (int) $count;
}

function p11ListStudents(mysqli $db, array $filters, int $limit, int $offset): array
{
    [$where, $params] = p11Where($filters);
    $params[] = $limit;
    $params[] = $offset;
    $statement = $db->prepare(
        'SELECT s.id, s.student_code, s.first_name, s.last_name, s.email, s.mobile, '
        . 's.year_of_study, s.status, c.course_code, c.course_name '
        . 'FROM students AS s JOIN courses AS c ON c.id = s.course_id' . $where
        . ' ORDER BY s.id DESC LIMIT ? OFFSET ?'
    );
    $statement->execute($params);
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}

function p11CsvSafe(mixed $value): string
{
    $value = (string) $value;
    return preg_match('/\A[=+@-]/', $value) ? "'" . $value : $value;
}

function p11ExportStudents(mysqli $db, array $filters): never
{
    [$where, $params] = p11Where($filters);
    $statement = $db->prepare(
        'SELECT s.student_code, s.first_name, s.last_name, s.email, s.mobile, c.course_code, '
        . 'c.course_name, s.year_of_study, s.status '
        . 'FROM students AS s JOIN courses AS c ON c.id = s.course_id' . $where
        . ' ORDER BY s.last_name, s.first_name, s.id'
    );
    $statement->execute($params);
    $rows = $statement->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="studenthub-students.csv"');
    header('X-Content-Type-Options: nosniff');
    $output = fopen('php://output', 'wb');
    fputcsv($output, ['Student code', 'First name', 'Last name', 'Email', 'Mobile', 'Course code', 'Course', 'Year', 'Status']);
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, array_map('p11CsvSafe', array_values($row)));
    }
    fclose($output);
    $statement->close();
    exit;
}
