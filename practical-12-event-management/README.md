# Practical 12 — Event management with poster upload

This admin-only module adds, views, edits, and deletes StudentHub events using PHP and MySQLi. It extends Practical 8's existing `events` table and reuses Practical 10's session and role checks.

## Folder structure

- `index.php` — event list with search, category and open/closed filters, and pagination.
- `create.php` and `edit.php` — validated event forms with optional poster upload/replacement.
- `actions/delete.php` — CSRF-protected, confirmed delete action and registration safety check.
- `lib/events.php` — validation, image upload/rename, prepared query, and audit helpers.
- `database/schema.sql` — category, open/closed status, poster filename, and event audit log migration.
- `uploads/` — poster files; PHP files are denied by `.htaccess` and directory listing is disabled.

## Database setup

1. Start MySQL in XAMPP and use the existing `studenthub` database with the Practical 8 `events`, `registrations`, and Practical 9 `users` tables.
2. In phpMyAdmin, select `studenthub`, click **Import**, choose `database/schema.sql`, and run it once. Existing events become category `General` and status `open`; their posters remain blank.
3. If importing reports that the event columns already exist, do not rerun the `ALTER TABLE` statement. Check for `category`, `event_status`, `poster_filename`, and `event_audit_log` in the database structure.
4. Ensure `studenthub_app` has `SELECT`, `INSERT`, `UPDATE`, and `DELETE` permissions. The PHP account also needs write access to this practical's `uploads/` folder.

## Run the practical

1. Make sure XAMPP MySQL is running and an admin account from Practical 10 exists.
2. In Terminal, start PHP from the repository root with the same `STUDENTHUB_DB_*` variables used in Practicals 10 and 11:

   ```sh
   cd ~/Desktop/StudentHUB
   export STUDENTHUB_DB_HOST=127.0.0.1
   export STUDENTHUB_DB_PORT=3306
   export STUDENTHUB_DB_NAME=studenthub
   export STUDENTHUB_DB_USER=studenthub_app
   read -s STUDENTHUB_DB_PASSWORD
   export STUDENTHUB_DB_PASSWORD
   php -S 127.0.0.1:8006 -t .
   ```

3. Open <http://127.0.0.1:8006/practical-10-secure-auth/> and sign in as admin. Click **Manage events**, or open <http://127.0.0.1:8006/practical-12-event-management/> directly. Keep the Terminal window open.

## Validation and security

- Only the admin role can use these pages. All create, edit, and delete forms include a CSRF token; deletion is POST-only and has a browser confirmation.
- Event insert, update, listing, filtering, registration counts, and delete checks use MySQLi prepared statements.
- Poster files are optional, limited to 2 MB, and checked using server-side MIME detection and image parsing. Only JPEG, PNG, and WebP are accepted; SVG and executable formats are rejected.
- Accepted images receive random 128-bit hexadecimal names. Only the generated filename is stored in MySQL.
- Uploads cannot execute PHP under Apache and the directory has an index page to prevent listing. Built-in PHP server does not process `.htaccess`, so the application accepts only validated raster images and generated image extensions.
- Event changes are recorded in `event_audit_log`. Events that already have registrations cannot be permanently deleted, preserving registration records; close those events instead.
- Search, category filter, and open/closed filter work together. The status/category fields also support the extension requirement.

## Practical 12 test/report screenshots

Use fictional events and sample images:

1. Admin event list showing sample events and category/status filters.
2. Add event with a valid poster and show the success message and poster thumbnail.
3. Edit event details and replace its poster; show the updated list row.
4. Invalid poster type (for example, a `.txt` or `.svg` file) and its validation message.
5. Oversized poster and its validation message (maximum 2 MB).
6. Delete confirmation dialog and success message for an unregistered test event.
7. Delete blocked message for an event with existing registrations.
8. Search and category/open-closed filter results.
9. `event_audit_log` rows in phpMyAdmin after create, update, and delete.
10. SQL dump of the events and audit-log tables, plus a test-case table with expected and actual results.

For a database dump, run `mysqldump -u studenthub_app -p studenthub events event_audit_log > practical-12-events.sql` and enter the database account password when prompted. Do not include real personal information in screenshots or the dump.
