# Practical 10 — Secure login and logout

This practical adds PHP session authentication to StudentHub using the `users` table created in Practical 9.

## Folder structure

- `login.php` — accepts username or email, verifies the password, protects the form with CSRF, regenerates the session ID, and redirects by role.
- `dashboard.php` — sends unauthenticated requests to login and redirects signed-in users to the dashboard for their role.
- `student/dashboard.php` and `admin/dashboard.php` — role-protected pages with a CSRF-protected POST logout action.
- `logout.php` — revokes the current remember-me token, clears the login session, and returns to login.
- `lib/auth.php` — session policy, password lookup, role checks, idle timeout, and token-cookie handling.
- `database/schema.sql` — adds the intermediate `last_login_at` field and a hashed remember-me token table.
- `database/seed-demo-users.php` — creates one fictional student account and one fictional admin account with generated passwords and password hashes.

## Setup

1. Start MySQL in XAMPP. Use the existing `studenthub` database and the same `STUDENTHUB_DB_*` environment variables used in Practical 9.
2. Ensure Practical 9's `users` table exists. In phpMyAdmin, select `studenthub` and import `database/schema.sql` once. If `last_login_at` already exists, remove or skip only the `ALTER TABLE` statement before importing.
3. Seed fictional accounts from the repository root. The command prints random passwords once; save them for the test and do not include them in screenshots or Git:

   ```sh
   php practical-10-secure-auth/database/seed-demo-users.php
   ```

4. Run the PHP server from the repository root. The variables should be set in the same Terminal session:

   ```sh
   cd ~/Desktop/StudentHUB
   export STUDENTHUB_DB_HOST=127.0.0.1
   export STUDENTHUB_DB_PORT=3306
   export STUDENTHUB_DB_NAME=studenthub
   export STUDENTHUB_DB_USER=studenthub_app
   read -s STUDENTHUB_DB_PASSWORD
   export STUDENTHUB_DB_PASSWORD
   php -S 127.0.0.1:8003 -t .
   ```

5. Open <http://127.0.0.1:8003/practical-10-secure-auth/>. Use the generated `student_demo` or `admin_demo` credentials.

## Implemented security behavior

- Password checks use `password_verify()` and a prepared query; failure messages do not reveal whether the username or password was wrong.
- The session ID is regenerated after a valid login. Session cookies use `HttpOnly`, `SameSite=Lax`, and `Secure` when served over HTTPS.
- Student and admin dashboards require the matching role. Direct dashboard access without a login returns to the login form; access to the other role's dashboard redirects to the user's own dashboard.
- Sessions expire after 15 minutes without activity. A selected remember-me token can restore a login for up to 30 days, which is why timeout screenshots should use an account signed in without remember-me selected.
- Remember-me cookies contain a random selector and validator. Only the SHA-256 hash of the validator is stored in MySQL, and valid tokens rotate when used.
- The application updates `last_login_at` after successful authentication. Logout is POST-only, CSRF-protected, invalidates the session, and deletes the current remember token.

## Practical 10 test/report screenshots

Capture fictional test accounts only:

1. Successful student login and the student dashboard.
2. Successful admin login and the admin dashboard.
3. Failed login with the generic error message.
4. Unauthorized direct dashboard access redirecting to login (sign out first, then open the student or admin dashboard URL directly).
5. Logout confirmation on the login page and the protected dashboard no longer accessible.
6. Session timeout after 15 minutes idle. For this test, leave **Keep me signed in** unchecked.
7. In phpMyAdmin, show `users.last_login_at` updated after login and `auth_remember_tokens.validator_hash` containing only a hash after remember-me login. Hide emails if personal.

The remembered-login option is an extension and can keep a user signed in after the normal session expires. Use a fresh browser/private window or clear the `studenthub_remember` cookie when repeating authentication screenshots.

This is a local classroom exercise, not a production authentication service. Do not use real passwords or personal details.
