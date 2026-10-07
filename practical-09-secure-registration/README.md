# Practical 9 — Secure user registration

This practical adds secure account registration with PHP and MySQLi. It uses the existing `studenthub` database created for Practical 8.

## Folder structure

- `index.php` — accessible registration form, CSRF verification, feedback, and POST/redirect/GET success flow.
- `config/db.php` — reusable MySQLi connection using the `STUDENTHUB_DB_*` environment variables from Practical 8.
- `lib/registration.php` — consistent server-side validation, duplicate check, password hashing, prepared inserts, and audit logging.
- `database/schema.sql` — `users` and `registration_audit_log` tables. It does not modify or delete the Practical 8 tables.
- `assets/css/style.css` and `assets/js/register.js` — Practical 9 page styling and client-side validation. The page reuses the portal's Practical 3 stylesheet and Practical 4 menu/theme controls.

## Database setup

1. Keep XAMPP's MySQL service running.
2. In phpMyAdmin, select the existing `studenthub` database and import `database/schema.sql` from this folder.
3. The Practical 8 application user needs `SELECT`, `INSERT`, `UPDATE`, and `DELETE` on `studenthub.*`. If you granted those permissions for Practical 8, they already cover these tables.

## Run the practical

Open a new Terminal window and set the same local connection values used in Practical 8. Enter the database password silently when prompted, then start the PHP server from the repository root on port 8002. Starting at the root lets this page load the shared portal CSS, logo, and menu script:

```sh
cd ~/Desktop/StudentHUB
export STUDENTHUB_DB_HOST=127.0.0.1
export STUDENTHUB_DB_PORT=3306
export STUDENTHUB_DB_NAME=studenthub
export STUDENTHUB_DB_USER=studenthub_app
read -s STUDENTHUB_DB_PASSWORD
export STUDENTHUB_DB_PASSWORD
php -S 127.0.0.1:8002 -t .
```

Open <http://127.0.0.1:8002/practical-09-secure-registration/>. Leave the Terminal window open while using the page. Use fictional details only.

## Security and assignment requirements

- Frontend constraints and JavaScript feedback mirror the PHP validation rules for name, username, email, password, and confirmation.
- PHP validates every submitted value again; browser validation is never trusted as the security boundary.
- Usernames and emails are normalized and checked for duplicates. Database unique keys also protect against simultaneous duplicate submissions.
- Passwords are stored only as `password_hash()` output. The plain password is never written to the database or audit table.
- All database lookups and writes use MySQLi prepared statements.
- A random session CSRF token is verified with `hash_equals()` on POST.
- The audit log records successful and duplicate-rejected registration outcomes without storing passwords, email addresses, or IP addresses.
- The success message uses POST/redirect/GET so refreshing the page does not submit the account a second time.

## Manual test/report cases

1. Submit valid fictional values: the page should show a success message, and `users` should contain a hash beginning with a password-hash prefix (never the plaintext password).
2. Submit a missing/invalid value or a password mismatch: client feedback should appear; the backend repeats the same checks if JavaScript is bypassed.
3. Submit the same email or username again: the form should show the duplicate message; `registration_audit_log` should record `duplicate_rejected`.
4. Inspect `registration_audit_log` after a successful submission: it should record `registration_success` linked to the new user.

Do not commit local passwords or real personal information. This practical is a classroom demonstration, not a production identity system.
