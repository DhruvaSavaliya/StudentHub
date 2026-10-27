<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../../practical-09-secure-registration/config/db.php';

try {
    $db = studentHubMysqli();
    $check = $db->prepare(
        "SELECT id FROM users WHERE username IN ('student_demo', 'admin_demo') "
        . "OR email IN ('student.demo@example.test', 'admin.demo@example.test') LIMIT 1"
    );
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        fwrite(STDERR, "A demo seed account already exists. Keep using its existing credentials; no rows were changed.\n");
        exit(1);
    }
    $check->close();

    $studentPassword = bin2hex(random_bytes(12)) . 'Aa#7';
    $adminPassword = bin2hex(random_bytes(12)) . 'Bb#8';
    $studentHash = password_hash($studentPassword, PASSWORD_DEFAULT);
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    if ($studentHash === false || $adminHash === false) {
        throw new RuntimeException('Password hashing failed.');
    }

    $db->begin_transaction();
    $insert = $db->prepare(
        'INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)'
    );
    $name = 'Demo Student';
    $username = 'student_demo';
    $email = 'student.demo@example.test';
    $role = 'student';
    $insert->bind_param('sssss', $name, $username, $email, $studentHash, $role);
    $insert->execute();

    $name = 'Demo Administrator';
    $username = 'admin_demo';
    $email = 'admin.demo@example.test';
    $role = 'admin';
    $insert->bind_param('sssss', $name, $username, $email, $adminHash, $role);
    $insert->execute();
    $insert->close();
    $db->commit();

    fwrite(STDOUT, "Demo accounts created. Save these passwords now; they are not stored in plaintext.\n");
    fwrite(STDOUT, "Student username: student_demo\nStudent password: {$studentPassword}\n");
    fwrite(STDOUT, "Admin username: admin_demo\nAdmin password: {$adminPassword}\n");
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof mysqli) {
        try { $db->rollback(); } catch (Throwable) {}
    }
    fwrite(STDERR, 'Seed failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
