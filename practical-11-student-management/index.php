<?php
declare(strict_types=1);

require __DIR__ . '/../practical-10-secure-auth/lib/auth.php';
require __DIR__ . '/lib/students.php';
$admin = authRequireRole('admin');

try {
    $db = studentHubMysqli();
    $courses = p11Courses($db);
    $filters = p11Filters($_GET);
    $pageSize = 10;
    $totalRows = p11CountStudents($db, $filters);
    $totalPages = max(1, (int) ceil($totalRows / $pageSize));
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $page = $page !== false && $page > 0 ? min($page, $totalPages) : 1;
    $students = p11ListStudents($db, $filters, $pageSize, ($page - 1) * $pageSize);
    $dbError = null;
} catch (Throwable $exception) {
    error_log('[StudentHub Practical 11 list] ' . $exception->getMessage());
    $courses = [];
    $filters = p11Filters($_GET);
    $students = [];
    $page = 1;
    $totalPages = 1;
    $totalRows = 0;
    $dbError = 'Student records could not be loaded. Check the Practical 11 database setup.';
}

$csrfToken = authCsrfToken();
$pageTitle = 'Student management';
require __DIR__ . '/partials/header.php';
?>
<main class="p11-wrap" id="main-content">
    <div class="p11-heading">
        <div>
            <p class="eyebrow">Administrator · Practical 11</p>
            <h1>Student management</h1>
            <p>Search, filter, add, update, archive, and export student records.</p>
        </div>
        <div class="p11-actions">
            <a class="btn btn-blue" href="create.php">Add student</a>
            <a class="btn btn-outline" href="export.php?<?= p11Escape(http_build_query(['q' => $filters['q'], 'course_id' => $filters['course_id'], 'status' => $filters['status']])) ?>">Export CSV</a>
        </div>
    </div>

    <?php if ($dbError !== null): ?>
        <div class="p11-message error" role="alert"><?= p11Escape($dbError) ?></div>
    <?php else: ?>
        <form class="p11-filters" method="get" action="index.php">
            <div>
                <label for="q">Search name, email, or course</label>
                <input id="q" name="q" type="search" value="<?= p11Escape($filters['q']) ?>" placeholder="e.g. Aarav, email, IT">
            </div>
            <div>
                <label for="course_id">Filter by course</label>
                <select id="course_id" name="course_id">
                    <option value="0">All courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course['id'] ?>" <?= $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>><?= p11Escape($course['course_code'] . ' · ' . $course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Record status</label>
                <select id="status" name="status">
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Archived</option>
                    <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <p class="p11-summary" role="status">Showing <?= $totalRows === 0 ? 0 : (($page - 1) * $pageSize) + 1 ?>–<?= min($page * $pageSize, $totalRows) ?> of <?= $totalRows ?> students · Page <?= $page ?> of <?= $totalPages ?></p>
        <div class="p11-table-scroll">
            <?php if ($students === []): ?>
                <p class="p11-empty">No student records match these filters.</p>
            <?php else: ?>
                <table class="p11-table">
                    <thead><tr><th>Student</th><th>Email and mobile</th><th>Course</th><th>Year</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><span class="p11-student-name"><?= p11Escape($student['first_name'] . ' ' . $student['last_name']) ?></span><span class="p11-muted"><?= p11Escape($student['student_code']) ?></span></td>
                            <td><?= p11Escape($student['email']) ?><span class="p11-muted"><?= p11Escape((string) ($student['mobile'] ?? '—')) ?></span></td>
                            <td><?= p11Escape($student['course_code'] . ' · ' . $student['course_name']) ?></td>
                            <td><?= (int) $student['year_of_study'] ?></td>
                            <td><span class="p11-status <?= $student['status'] === 'inactive' ? 'inactive' : '' ?>"><?= p11Escape($student['status']) ?></span></td>
                            <td>
                                <div class="p11-row-actions">
                                    <a href="edit.php?id=<?= (int) $student['id'] ?>">Edit</a>
                                    <?php if ($student['status'] === 'active'): ?>
                                        <form method="post" action="actions/status.php" onsubmit="return confirm('Archive this student? The record will remain in the database.');">
                                            <input type="hidden" name="csrf_token" value="<?= p11Escape($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                                            <input type="hidden" name="status" value="inactive">
                                            <button class="p11-link-button" type="submit">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="actions/status.php">
                                            <input type="hidden" name="csrf_token" value="<?= p11Escape($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button class="p11-link-button restore" type="submit">Restore</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="p11-pagination">
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <nav aria-label="Student pages">
                    <?php if ($page > 1): ?>
                        <a class="p11-page-link" href="?<?= p11Escape(http_build_query(['q' => $filters['q'], 'course_id' => $filters['course_id'], 'status' => $filters['status'], 'page' => $page - 1])) ?>">Previous</a>
                    <?php endif; ?>
                    <?php for ($pageNumber = max(1, $page - 2); $pageNumber <= min($totalPages, $page + 2); $pageNumber++): ?>
                        <a class="p11-page-link" href="?<?= p11Escape(http_build_query(['q' => $filters['q'], 'course_id' => $filters['course_id'], 'status' => $filters['status'], 'page' => $pageNumber])) ?>" <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a class="p11-page-link" href="?<?= p11Escape(http_build_query(['q' => $filters['q'], 'course_id' => $filters['course_id'], 'status' => $filters['status'], 'page' => $page + 1])) ?>">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
