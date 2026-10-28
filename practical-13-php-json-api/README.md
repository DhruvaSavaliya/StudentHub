# Practical 13 — PHP JSON API and Fetch-based event CRUD

An admin-only event management page that talks to a PHP JSON API with the browser Fetch API. Listing, adding, editing, deleting, searching, and status messages happen asynchronously without a full page reload. It reuses the Practical 10 authentication session and the Practical 12 `events` table and validation/audit helpers.

## Structure

- `index.php` — admin-only Fetch interface with an event form, live search, and event list.
- `api/events.php` — JSON endpoint for GET, POST, PUT, and DELETE requests.
- `assets/js/events.js` — asynchronous Fetch requests, DOM updates, search debounce, and status/error messages.
- `assets/css/api.css` — responsive page styling.

## Database prerequisites

Use the existing `studenthub` database. Practical 8's schema must be present, Practical 9/10's `users` and auth tables must be installed, and Practical 12's event migration must already have added `category`, `event_status`, `poster_filename`, and `event_audit_log`. No new SQL import is needed for Practical 13. The database account needs `SELECT`, `INSERT`, `UPDATE`, and `DELETE` access to the existing event-related tables.

## Run it

1. Start MySQL in XAMPP.
2. In Terminal, run from the StudentHUB project root with the same database environment values used for Practicals 10–12:

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

3. Open <http://127.0.0.1:8006/practical-13-php-json-api/>. Sign in with the Practical 10 admin account if prompted.
4. Add or edit a fictional event. Search filters asynchronously; use Edit or Delete in the list to demonstrate the remaining operations.

If port 8006 is already in use, keep using the existing server instead of starting another one. Open the Practical 13 URL on the port that server is using. Live Server cannot run PHP API endpoints.

## API behavior

- `GET api/events.php?q=...` returns JSON event records.
- `POST api/events.php` creates an event.
- `PUT api/events.php` updates an event by its JSON `id`.
- `DELETE api/events.php?id=...` deletes an event only when it has no registrations.
- Write requests require the session's CSRF token in the `X-CSRF-Token` header. The API validates fields server-side and returns JSON errors with appropriate HTTP status codes.
- All event changes are added to the Practical 12 event audit log. This practical does not upload posters; poster handling remains in Practical 12.

## Suggested report screenshots

1. Event list loaded through Fetch, with the Practical 13 heading visible.
2. Browser Developer Tools **Network** tab showing `GET api/events.php` with a JSON response.
3. Add event success message and the newly added event in the refreshed list, without a document reload.
4. Edit success message and updated event row.
5. Delete success message and event removed from the list.
6. Invalid field submission showing the JSON validation error rendered in the page.
7. Network tab showing a write request and its JSON response.
8. Practical 12 `event_audit_log` table showing the create, update, and delete actions.

Use fictional data in screenshots. Never include database passwords or personal registration details.
