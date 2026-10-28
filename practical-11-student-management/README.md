# Practical 11 — Student management CRUD

An admin-only PHP/MySQLi module for creating, viewing, editing, searching, filtering, exporting, and archiving StudentHub student records. It extends Practical 8's existing `students` and `courses` tables and uses Practical 10's session and role checks.

## Folder structure

- `index.php` — searchable, course-filtered, paginated active/archive student list.
- `create.php` and `edit.php` — validated student forms with prepared `INSERT` and `UPDATE` statements.
- `actions/status.php` — CSRF-protected archive/restore action. Delete confirmation runs before archiving.
- `export.php` — CSV export for the current search, course, and status filters.
- `lib/students.php` — shared validation, query, status, and CSV helpers.
- `database/schema.sql` — adds active/inactive status for soft delete.
- `database/seed_students.sql` — 20 fictional student rows across the existing courses.

## Database setup

1. Start MySQL in XAMPP and use the existing `studenthub` database.
2. In phpMyAdmin, select `studenthub`, choose **Import**, and import `database/schema.sql` once. It adds a `status` column to `students` and an index for status/course filters. If `status` already exists, do not rerun the `ALTER TABLE` statement.
3. Import `database/seed_students.sql` once. It adds 20 fictional records using course IDs 1–3 from Practical 8. With the original three seed records, the list will contain at least 23 students. Do not import this seed file again, because student codes and emails are unique.
4. Ensure `courses` contains the three Practical 8 course rows and the `studenthub_app` database user has `SELECT`, `INSERT`, `UPDATE`, and `DELETE` permissions.

## Run the module

1. Start MySQL in XAMPP. The StudentHub admin account must already be created by Practical 10.
2. Open a Terminal at the repository root and set the same database environment variables used in Practical 10:

   ```sh
   cd ~/Desktop/StudentHUB
   export STUDENTHUB_DB_HOST=127.0.0.1
   export STUDENTHUB_DB_PORT=3306
   export STUDENTHUB_DB_NAME=studenthub
   export STUDENTHUB_DB_USER=studenthub_app
   read -s STUDENTHUB_DB_PASSWORD
   export STUDENTHUB_DB_PASSWORD
   php -S 127.0.0.1:8005 -t .
   ```

3. Open <http://127.0.0.1:8005/practical-10-secure-auth/> and sign in with the Practical 10 admin account.
4. Select **Manage students** from the admin dashboard, or open <http://127.0.0.1:8005/practical-11-student-management/> directly. Keep the Terminal server running while using the page.

## Features and safety

- Every page requires the `admin` role from Practical 10. Students cannot access this module.
- Add and edit use backend validation and MySQLi prepared statements. Student code and email uniqueness is enforced by the Practical 8 database constraints.
- Search matches first/last name, combined name, email, course name, or course code. The course dropdown filters by course; status filters show active, archived, or all records.
- Results use prepared count/list queries and 10-row pagination. CSV export applies the same filters and neutralizes spreadsheet formula prefixes.
- Delete uses a browser confirmation, CSRF-protected POST, and changes the status to `inactive`. This soft delete preserves event registrations; archived records can be restored.
- All user-provided output is HTML-escaped. No destructive database `DELETE` is used for student records.

## Practical 11 test/report screenshots

Use fictional data and hide email/phone values if needed:

1. Student list showing the seeded records and pagination.
2. Search by name or email and filter by course.
3. Add a student and show the success message/list row.
4. Edit the student and show the updated values.
5. Click Delete, show the confirmation, confirm it, then show the record under Archived.
6. Restore the archived student and show it in Active.
7. Export CSV and show the downloaded file or its opened contents.
8. SQL dump of the `students` table and test cases for add, view, search/filter, edit, archive, restore, validation/duplicate failure, and CSV export.

For a database dump from Terminal, use `mysqldump -u studenthub_app -p studenthub students > practical-11-students.sql` and enter the database account password when prompted. Do not include real personal records in the report or repository.
