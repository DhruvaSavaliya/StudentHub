<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/events.php';
$admin = authRequireRole('admin');
$db = studentHubMysqli();
$rawId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : 0;
$event = $id > 0 ? p12GetEvent($db, $id) : null;
if ($event === null) {
    http_response_code(404);
    p12SetFlash('error', 'Event record was not found.');
    header('Location: index.php', true, 303);
    exit;
}

$currentPoster = $event['poster_filename'];
$values = [
    'title' => $event['title'],
    'description' => (string) ($event['description'] ?? ''),
    'venue' => $event['venue'],
    'starts_at_input' => date('Y-m-d\TH:i', strtotime($event['starts_at'])),
    'capacity' => (string) $event['capacity'],
    'category' => $event['category'],
    'event_status' => $event['event_status'],
];
$errors = [];
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = array_merge($values, array_intersect_key($_POST, $values));
    if (!authVerifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $formError = 'The form expired. Return to the event list and try again.';
    } else {
        [$values, $errors] = p12ValidateEvent($_POST);
        $posterError = p12ValidatePoster($_FILES['poster'] ?? []);
        if ($posterError !== null) {
            $errors['poster'] = $posterError;
        }
        if ($errors === []) {
            $newPoster = null;
            $replacingPoster = (int) ($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
            try {
                if ($replacingPoster) {
                    $newPoster = p12StorePoster($_FILES['poster']);
                }
                $posterForDb = $replacingPoster ? $newPoster : $currentPoster;
                $description = $values['description'] === '' ? null : $values['description'];
                $db->begin_transaction();
                $statement = $db->prepare(
                    'UPDATE events SET title = ?, description = ?, venue = ?, starts_at = ?, capacity = ?, '
                    . 'category = ?, event_status = ?, poster_filename = ? WHERE id = ?'
                );
                $statement->bind_param(
                    'ssssisssi',
                    $values['title'],
                    $description,
                    $values['venue'],
                    $values['starts_at'],
                    $values['capacity'],
                    $values['category'],
                    $values['event_status'],
                    $posterForDb,
                    $id
                );
                $statement->execute();
                $statement->close();
                p12Audit($db, $id, $values['title'], 'updated', (int) $admin['id']);
                $db->commit();
                if ($replacingPoster) {
                    p12RemovePoster($currentPoster);
                }
                p12SetFlash('success', 'Event updated successfully.');
                header('Location: index.php', true, 303);
                exit;
            } catch (Throwable $exception) {
                try { $db->rollback(); } catch (Throwable) {}
                p12RemovePoster($newPoster);
                error_log('[StudentHub Practical 12 update] ' . $exception->getMessage());
                $formError = 'The event could not be updated. Check the database setup and try again.';
            }
        } else {
            http_response_code(422);
        }
    }
}

$pageTitle = 'Edit event';
require __DIR__ . '/partials/header.php';
?>
<main class="p12-form-card" id="main-content">
    <p class="eyebrow">Event records</p>
    <h1>Edit event</h1>
    <p>Update the event details. Existing poster stays unless you upload a replacement.</p>
    <?php if ($formError !== null): ?><div class="p12-message error" role="alert"><?= p12Escape($formError) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><ul class="error-summary" aria-label="Form errors"><?php foreach ($errors as $message): ?><li><?= p12Escape($message) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form class="p12-form" method="post" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= p12Escape(authCsrfToken()) ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
        <?php require __DIR__ . '/partials/event-fields.php'; ?>
        <button class="submit" type="submit">Save changes</button>
    </form>
    <div class="p12-form-footer"><a class="btn btn-primary" href="index.php">Back to events</a></div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
