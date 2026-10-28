<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/../practical-14-admin-dashboard/lib/dashboard.php';
require __DIR__ . '/lib/events.php';
$admin = authRequireRole('admin');
$db = studentHubMysqli();
$values = ['title' => '', 'description' => '', 'venue' => '', 'starts_at_input' => '', 'capacity' => '', 'category' => 'general', 'event_status' => 'open'];
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
            try {
                if (($fileError = (int) ($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE)) === UPLOAD_ERR_OK) {
                    $newPoster = p12StorePoster($_FILES['poster']);
                }
                $description = $values['description'] === '' ? null : $values['description'];
                $db->begin_transaction();
                $statement = $db->prepare(
                    'INSERT INTO events (title, description, venue, starts_at, capacity, category, event_status, poster_filename) '
                    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->bind_param(
                    'ssssisss',
                    $values['title'],
                    $description,
                    $values['venue'],
                    $values['starts_at'],
                    $values['capacity'],
                    $values['category'],
                    $values['event_status'],
                    $newPoster
                );
                $statement->execute();
                $eventId = (int) $db->insert_id;
                $statement->close();
                p12Audit($db, $eventId, $values['title'], 'created', (int) $admin['id']);
                $db->commit();
                p14LogAction($db, (int) $admin['id'], 'event.created', 'event', $eventId, 'Created an event');
                p12SetFlash('success', 'Event created successfully.');
                header('Location: index.php', true, 303);
                exit;
            } catch (Throwable $exception) {
                try { $db->rollback(); } catch (Throwable) {}
                p12RemovePoster($newPoster);
                error_log('[StudentHub Practical 12 create] ' . $exception->getMessage());
                $formError = 'The event could not be saved. Check the database setup and try again.';
            }
        } else {
            http_response_code(422);
        }
    }
}

$currentPoster = null;
$pageTitle = 'Add event';
require __DIR__ . '/partials/header.php';
?>
<main class="p12-form-card" id="main-content">
    <p class="eyebrow">Event records</p>
    <h1>Add an event</h1>
    <p>Enter event details and optionally add a validated poster image.</p>
    <?php if ($formError !== null): ?><div class="p12-message error" role="alert"><?= p12Escape($formError) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><ul class="error-summary" aria-label="Form errors"><?php foreach ($errors as $message): ?><li><?= p12Escape($message) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form class="p12-form" method="post" action="create.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= p12Escape(authCsrfToken()) ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
        <?php require __DIR__ . '/partials/event-fields.php'; ?>
        <button class="submit" type="submit">Save event</button>
    </form>
    <div class="p12-form-footer"><a class="btn btn-primary" href="index.php">Back to events</a></div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
