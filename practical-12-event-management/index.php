<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/events.php';
authRequireRole('admin');

try {
    $db = studentHubMysqli();
    $filters = p12Filters($_GET);
    $pageSize = 10;
    $totalEvents = p12CountEvents($db, $filters);
    $totalPages = max(1, (int) ceil($totalEvents / $pageSize));
    $rawPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $page = $rawPage !== false && $rawPage > 0 ? min($rawPage, $totalPages) : 1;
    $events = p12ListEvents($db, $filters, $pageSize, ($page - 1) * $pageSize);
    $dbError = null;
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 12 list] ' . $exception->getMessage());
    $filters = p12Filters($_GET);
    $events = [];
    $page = 1;
    $totalPages = 1;
    $totalEvents = 0;
    $dbError = 'Events could not be loaded. Check the Practical 12 database setup.';
}

$pageTitle = 'Event management';
require __DIR__ . '/partials/header.php';
?>
<main class="p12-wrap" id="main-content">
    <div class="p12-heading">
        <div>
            <p class="eyebrow">Administrator · Practical 12</p>
            <h1>Event management</h1>
            <p>Add, edit, filter, and manage event posters in StudentHub.</p>
        </div>
        <div class="p12-actions"><a class="btn btn-blue" href="create.php">Add event</a></div>
    </div>
    <?php if ($dbError !== null): ?>
        <div class="p12-message error" role="alert"><?= p12Escape($dbError) ?></div>
    <?php else: ?>
        <form class="p12-filters" method="get" action="index.php">
            <div>
                <label for="q">Search title, venue, or category</label>
                <input id="q" name="q" type="search" value="<?= p12Escape($filters['q']) ?>" placeholder="e.g. Workshop, Auditorium">
            </div>
            <div>
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All categories</option>
                    <?php foreach (p12Categories() as $key => $label): ?>
                        <option value="<?= p12Escape($key) ?>" <?= $filters['category'] === $key ? 'selected' : '' ?>><?= p12Escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Event status</label>
                <select id="status" name="status">
                    <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filter</button>
        </form>
        <p class="p12-summary" role="status">Showing <?= $totalEvents === 0 ? 0 : (($page - 1) * $pageSize) + 1 ?>–<?= min($page * $pageSize, $totalEvents) ?> of <?= $totalEvents ?> events · Page <?= $page ?> of <?= $totalPages ?></p>
        <div class="p12-table-scroll">
            <?php if ($events === []): ?>
                <p class="p12-empty">No events match these filters.</p>
            <?php else: ?>
                <table class="p12-table">
                    <thead><tr><th>Poster</th><th>Event</th><th>Venue and date</th><th>Category</th><th>Capacity</th><th>Registration</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php if ($event['poster_filename'] !== null): ?><img class="p12-poster" src="uploads/<?= p12Escape($event['poster_filename']) ?>" alt="Poster for <?= p12Escape($event['title']) ?>"><?php else: ?><span class="p12-muted">No poster</span><?php endif; ?></td>
                            <td><span class="p12-event-title"><?= p12Escape($event['title']) ?></span><span class="p12-muted"><?= p12Escape((string) ($event['description'] ?? '')) ?></span></td>
                            <td><?= p12Escape($event['venue']) ?><span class="p12-muted"><?= p12Escape(date('M j, Y · g:i A', strtotime($event['starts_at']))) ?></span></td>
                            <td><?= p12Escape(p12Categories()[$event['category']] ?? $event['category']) ?></td>
                            <td><?= (int) $event['registration_count'] ?> / <?= (int) $event['capacity'] ?></td>
                            <td><span class="p12-badge <?= $event['event_status'] === 'closed' ? 'closed' : '' ?>"><?= p12Escape($event['event_status']) ?></span></td>
                            <td>
                                <div class="p12-row-actions">
                                    <a href="edit.php?id=<?= (int) $event['id'] ?>">Edit</a>
                                    <form method="post" action="actions/delete.php" onsubmit="return confirm('Delete this event permanently? Events with registrations cannot be deleted.');">
                                        <input type="hidden" name="csrf_token" value="<?= p12Escape(authCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                        <button class="p12-link-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="p12-pagination"><span>Page <?= $page ?> of <?= $totalPages ?></span><nav aria-label="Event pages">
                <?php if ($page > 1): ?><a class="p12-page-link" href="?<?= p12Escape(http_build_query(['q' => $filters['q'], 'category' => $filters['category'], 'status' => $filters['status'], 'page' => $page - 1])) ?>">Previous</a><?php endif; ?>
                <?php for ($pageNumber = max(1, $page - 2); $pageNumber <= min($totalPages, $page + 2); $pageNumber++): ?><a class="p12-page-link" href="?<?= p12Escape(http_build_query(['q' => $filters['q'], 'category' => $filters['category'], 'status' => $filters['status'], 'page' => $pageNumber])) ?>" <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a><?php endfor; ?>
                <?php if ($page < $totalPages): ?><a class="p12-page-link" href="?<?= p12Escape(http_build_query(['q' => $filters['q'], 'category' => $filters['category'], 'status' => $filters['status'], 'page' => $page + 1])) ?>">Next</a><?php endif; ?>
            </nav></div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
