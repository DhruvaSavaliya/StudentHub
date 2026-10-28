<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/students.php';
authRequireRole('admin');
$db = studentHubMysqli();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null && $id > 0 ? $id : 0;
$student = $id > 0 ? p11GetStudent($db, $id) : null;
if ($student === null) {
    http_response_code(404);
    p11SetFlash('error', 'Student record was not found.');
    header('Location: index.php', true, 303);
    exit;
}

$courses = p11Courses($db);
$values = $student;
$errors = [];
$formError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $formError = 'The form expired. Return to the student list and try again.';
    } else {
        [$values, $errors] = p11ValidateStudent($_POST, $db);
        if ($errors === []) {
            try {
                p11UpdateStudent($db, $id, $values);
                p11SetFlash('success', 'Student record updated successfully.');
                header('Location: index.php', true, 303);
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log('[StudentHub Practical 11 update] ' . $exception->getMessage());
                $formError = (int) $exception->getCode() === 1062
                    ? 'That student code or email is already in use.'
                    : 'The student record could not be updated. Try again.';
            } catch (Throwable $exception) {
                error_log('[StudentHub Practical 11 update] ' . $exception->getMessage());
                $formError = 'The student record could not be updated. Try again.';
            }
        } else {
            http_response_code(422);
        }
    }
}

$csrfToken = authCsrfToken();
$pageTitle = 'Edit student';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content" class="p11-form-card">
    <p class="eyebrow">Student records</p>
    <h1>Edit student</h1>
    <p>Update the selected student record. Existing event registrations remain linked.</p>
    <?php if ($formError !== null): ?><div class="p11-message error" role="alert"><?= p11Escape($formError) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><ul class="error-summary" aria-label="Form errors"><?php foreach ($errors as $message): ?><li><?= p11Escape($message) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form class="p11-form" method="post" action="edit.php?id=<?= $id ?>">
        <input type="hidden" name="csrf_token" value="<?= p11Escape($csrfToken) ?>">
        <?php require __DIR__ . '/partials/student-fields.php'; ?>
        <button class="submit" type="submit">Save changes</button>
    </form>
    <div class="p11-form-footer"><a class="btn btn-primary" href="index.php">Back to students</a></div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
