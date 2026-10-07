<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/db.php';

/** Find one student by email with a native prepared statement. */
function findStudentByEmail(PDO $pdo, string $email): array|false
{
    $statement = $pdo->prepare(
        'SELECT id, student_code, first_name, last_name, email, year_of_study
         FROM students
         WHERE email = :email
         LIMIT 1'
    );
    $statement->execute(['email' => $email]);

    return $statement->fetch();
}

/** Insert a student/event relationship; the unique key prevents duplicates. */
function registerStudentForEvent(PDO $pdo, int $studentId, int $eventId): void
{
    $statement = $pdo->prepare(
        'INSERT INTO registrations (student_id, event_id, status)
         VALUES (:student_id, :event_id, :status)'
    );
    $statement->execute([
        'student_id' => $studentId,
        'event_id' => $eventId,
        'status' => 'registered',
    ]);
}

// Example only: supply an existing student email and valid IDs when running locally.
// $pdo = studentHubDatabase();
// $student = findStudentByEmail($pdo, 'aarav.patel@example.edu');
// if ($student !== false) {
//     registerStudentForEvent($pdo, (int) $student['id'], 201);
// }
