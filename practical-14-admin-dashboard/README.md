# Practical 14 — Role-based admin dashboard and audit log

An admin-only StudentHub overview showing live account, student, event, and registration totals; the latest event registrations; and a recent audit trail. Menu options are rendered according to the signed-in account role. The page reuses Practical 10 authentication and the existing Practical 8–12 database tables.

## Folder structure

- `index.php` — role-protected dashboard, live statistics, recent registrations, and audit log.
- `lib/dashboard.php` — prepared queries and audit-log helper.
- `database/schema.sql` — creates the Practical 14 `audit_logs` table.
- `assets/css/dashboard.css` — responsive dashboard styling.
- Practical 11, 12, and 13 successful student/event create, update, archive/restore, and delete actions call the shared audit helper; Practical 10's admin dashboard links to this overview.

## Database setup

1. Start MySQL in XAMPP and select the existing `studenthub` database in phpMyAdmin.
2. Import `database/schema.sql` once. It creates the `audit_logs` table and does not erase existing records.
3. The dashboard needs the Practical 8 tables, Practical 9/10 user/auth tables, Practical 11 student status, and Practical 12 event columns. It reads registration data from the existing `registrations`, `students`, and `events` tables.
4. Ensure `studenthub_app` can `SELECT`, `INSERT`, and `UPDATE` the tables used by the dashboard and audit logger.

## Run it

1. Start MySQL and run PHP from the repository root with the same `STUDENTHUB_DB_*` environment variables used in Practicals 10–13.
2. Open `http://127.0.0.1:8006/practical-14-admin-dashboard/` and sign in with the Practical 10 **admin** account if prompted.
3. Confirm the four summary totals, recent registrations, audit entry for opening the dashboard, and role-specific navigation.
4. Use the Manage students and Manage events shortcuts to perform CRUD actions, then return to the dashboard audit trail. Successful record changes are logged with the acting admin, action, entity, and timestamp.

If port 8006 is already in use, keep the existing PHP server running and use its port. Live Server cannot execute PHP pages.

## Role and audit behavior

- `authRequireRole('admin')` protects the dashboard. A student account is redirected to its student dashboard; a signed-out visitor is sent to Practical 10 sign-in.
- Dashboard counts are queried from the database on each page load, not hard-coded.
- `audit_logs` stores the acting user ID, action name, entity type/ID, a short non-sensitive note, and timestamp. Student and event create/update/archive/restore/delete actions are logged. It does not store passwords, email addresses, or authentication tokens.
- The audit helper catches missing-table errors so earlier practicals continue to work before this migration is imported.

## Suggested report screenshots

1. Admin dashboard showing database-backed summary cards and admin-only menu options.
2. Recent registrations section with event and student names.
3. `audit_logs` in phpMyAdmin showing the dashboard-view entry and administrator.
4. Attempt to access the dashboard as a student, showing redirection to the student dashboard.
5. Test cases for live counts, role restriction, latest registrations, and audit logging.

Use fictional records in screenshots and hide email addresses or other personal details.
