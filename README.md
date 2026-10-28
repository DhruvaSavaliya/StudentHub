# StudentHub — Web Development Frameworks (ITUE203)

StudentHub is a 14-practical learning project covering semantic HTML, responsive CSS, JavaScript, PHP, MySQL, APIs, and administration. Each assignment has its own numbered folder; shared images live under `assets/images/`.

## Practical folders

1. [`practical-01-project-planning/`](practical-01-project-planning/): requirements, scope, roles, sitemap, project structure, and supplied wireframes.
2. [`practical-02-semantic-html/`](practical-02-semantic-html/): 15 semantic, linked student-portal page skeletons in `pages/`.
3. [`practical-03-responsive-css/`](practical-03-responsive-css/): shared mobile-first stylesheet with Grid, Flexbox, and breakpoints.
4. [`practical-04-dom-interactivity/`](practical-04-dom-interactivity/): hamburger navigation, saved theme, notification, image slider, modal, and expandable FAQ.
5. [`practical-05-registration-validation/`](practical-05-registration-validation/): accessible registration validation, inline messages, strength feedback, and validation cases.
6. [`practical-06-fetch-json/`](practical-06-fetch-json/): 15-record event, student, and FAQ JSON files; reusable Fetch API renderer; search, filter, sort, pagination, empty/loading/error states, and last-successful-data cache.
7. [`practical-07-php-file-storage/`](practical-07-php-file-storage/): accessible registration page and shared PHP POST handler, with server-side validation, CSRF protection, and JSON file storage.
8. [`practical-08-mysql-pdo/`](practical-08-mysql-pdo/): normalized MySQL schema for courses, students, events, and event registrations; ER diagram, fictional seed data, PDO connection, connection check, and prepared-query examples.
9. [`practical-09-secure-registration/`](practical-09-secure-registration/): server-side account registration, validation, duplicate checks, password hashing, and audit logging.
10. [`practical-10-secure-auth/`](practical-10-secure-auth/): role-aware sign-in, secure sessions, idle timeout, remember-me tokens, and student/admin dashboards.
11. [`practical-11-student-management/`](practical-11-student-management/): admin student CRUD, search, filters, archive/restore, pagination, and CSV export.
12. [`practical-12-event-management/`](practical-12-event-management/): admin event CRUD, category/status filters, validated poster uploads, and event audit log.
13. [`practical-13-php-json-api/`](practical-13-php-json-api/): event CRUD through PHP JSON endpoints and the Fetch API without full page reloads.
14. [`practical-14-admin-dashboard/`](practical-14-admin-dashboard/): role-protected admin overview with live database statistics, recent registrations, and audit history.

## Run the normal site locally

The main site registration page uses PHP to validate and save registrations. Start PHP's local server from the repository root:

```sh
php -S 127.0.0.1:8000 -t . practical-07-php-file-storage/router.php
```

Open [http://127.0.0.1:8000/practical-02-semantic-html/pages/index.html](http://127.0.0.1:8000/practical-02-semantic-html/pages/index.html). Click **Register** in the normal navigation to open the PHP form; the Home, Events, Students, and FAQ Register links all use this same route. Successful submissions are saved to `practical-07-php-file-storage/data/registrations.json` locally. That file is ignored by Git so personal details aren't committed. A static-only preview server such as Live Server on port `5500` cannot execute PHP, so registrations cannot be saved from that server.

Practical 6's Fetch API pages also work on this PHP server. If reviewing Practical 6 separately, run it from a local HTTP server rather than opening its pages with a `file://` URL.

## Frontend-only scope

The Practical 5 `register.html` page checks inputs in the browser but does not create accounts or store passwords. The normal site's `register.php` route uses Practical 7's local PHP handler to validate and save registrations to the ignored `practical-07-php-file-storage/data/registrations.json` file. Login, admin, dashboard, and profile screens remain static prototypes; do not use this project to collect real credentials or personal information.

## Git workflow

The ZIP includes the original Git repository and its existing remote. Preserve that history. Make one reviewable commit per practical (for example, `feat(practical-5): validate student registration`, `feat(practical-6): render searchable JSON data`, and `feat(practical-7): process registrations with PHP and JSON`). Review staged files before each commit so a commit contains only the intended practical. The repository owner handles all pushes manually; do not force-push or reset the remote.
