<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/../practical-14-admin-dashboard/lib/dashboard.php';
require __DIR__ . '/lib/students.php';
$admin = authRequireRole('admin');
$db = studentHubMysqli();
$courses = p11Courses($db);
$values = ['student_code' => '', 'first_name' => '', 'last_name' => '', 'email' => '', 'mobile' => '', 'course_id' => 0, 'year_of_study' => 0];
$errors = [];
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $formError = 'The form expired. Return to the student list and try again.';
        $values = array_merge($values, $_POST);
    } else {
        [$values, $errors] = p11ValidateStudent($_POST, $db);
        if ($errors === []) {
            try {
                p11InsertStudent($db, $values);
                p14LogAction($db, (int) $admin['id'], 'student.created', 'student', (int) $db->insert_id, 'Created a student record');
                p11SetFlash('success', 'Student record added successfully.');
                header('Location: index.php', true, 303);
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log('[StudentHub Practical 11 create] ' . $exception->getMessage());
                $formError = (int) $exception->getCode() === 1062
                    ? 'That student code or email is already in use.'
                    : 'The student record could not be saved. Try again.';
            } catch (Throwable $exception) {
                error_log('[StudentHub Practical 11 create] ' . $exception->getMessage());
                $formError = 'The student record could not be saved. Try again.';
            }
        } else {
            http_response_code(422);
        }
    }
}

$csrfToken = authCsrfToken();
$pageTitle = 'Add student';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content" class="p11-form-card">
    <p class="eyebrow">Student records</p>
    <h1>Add a student</h1>
    <p>Enter fictional student data for this Practical 11 demonstration.</p>
    <?php if ($formError !== null): ?><div class="p11-message error" role="alert"><?= p11Escape($formError) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><ul class="error-summary" aria-label="Form errors"><?php foreach ($errors as $message): ?><li><?= p11Escape($message) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form class="p11-form" method="post" action="create.php">
        <input type="hidden" name="csrf_token" value="<?= p11Escape(authCsrfToken()) ?>">
        <?php require __DIR__ . '/partials/student-fields.php'; ?>
        <button class="submit" type="submit">Save student</button>
    </form>
    <div class="p11-form-footer"><a class="btn btn-primary" href="index.php">Back to students</a></div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
