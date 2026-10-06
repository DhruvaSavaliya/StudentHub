# Practical 7 — PHP form processing and JSON file storage

**Outcome:** accept a registration form using HTTP POST, validate values on the PHP server, show accessible success/error feedback, and store valid submissions as JSON.

## Project structure

- `pages/register.php` — the registration form linked from the regular StudentHub navigation.
- `lib/registration-handler.php` — shared POST handling, server-side validation, output escaping, and JSON persistence.
- `index.php` — redirects the Practical 7 entry point to the normal StudentHub registration page.
- `style.css` — responsive styling layered on the shared Practical 3 stylesheet.
- `data/registrations.json` — generated submissions from the live form. This local data file is excluded from Git so personal details are not committed.
- `data/registrations_example.json` — fictional example record showing the JSON output shape that is safe to submit.
- `router.php` and `data/.htaccess` — block direct browser downloads of the live submission file.

## Run the practical

From the StudentHub repository root, start PHP's local development server:

```sh
php -S 127.0.0.1:8000 -t . practical-07-php-file-storage/router.php
```

Open the main StudentHub home page at [http://127.0.0.1:8000/practical-02-semantic-html/pages/index.html](http://127.0.0.1:8000/practical-02-semantic-html/pages/index.html), click **Register**, fill in fictional details, and submit. The Home, Events, Students, FAQ, and other Register links all open `practical-07-php-file-storage/pages/register.php`. Valid submissions show a success message; invalid submissions show field-specific errors next to their controls. On success, the page redirects back to the same registration page to prevent accidental duplicate submissions on refresh.

Use PHP's web server on port `8000` for the combined site. A static server such as VS Code Live Server on port `5500` cannot execute PHP or save registrations. The main homepage keeps Practical 3's shared responsive stylesheet and Practical 4 interactions, and links to Practical 6's data pages. The registration page loads Practical 5's client-side checks before submitting valid details to this Practical 7 PHP handler. Practical 5's standalone `register.html` remains available as a frontend-only validation exercise.

Each successful submission is appended to `practical-07-php-file-storage/data/registrations.json`, so it is visible next to the other Practical 7 JSON files in VS Code. That file is intentionally ignored by Git; never push real personal data. `data/.htaccess` and the supplied PHP development-server router prevent browsers from downloading the live submission file. The `data/registrations_example.json` file is fictional example data for sharing or submission.

## Server-side validation and safety

- Requires POST for form submission and validates every value again on the server.
- Checks email addresses, names, phone-number digit counts, allowed course/year/gender choices, and terms acceptance.
- Includes a session-backed CSRF token and uses POST/Redirect/GET after a successful submission.
- Escapes submitted values when redisplaying them in HTML and stores normalized values rather than HTML.
- Uses an exclusive file lock while reading and updating the JSON array to avoid overlapping writes.
- Validates passwords in the browser and PHP request, then discards them without writing them to JSON. Use fictional classroom details only; this project is not intended for production or real personal data.

## Manual test cases

| Test | Input | Expected result |
| --- | --- | --- |
| Valid submission | Fictional name, valid email, 10–15 digit phone, allowed course/year/gender, accepted terms | Success message; one record appended to the JSON file. |
| Empty submission | Submit every field empty | Server-side errors appear beside required fields; nothing is written. |
| Invalid email | `not-an-email` | Email error appears; nothing is written. |
| Invalid phone | `12345` | Phone error appears; nothing is written. |
| Altered select value | Change the submitted course or year value in browser developer tools | Server rejects the value because it is not on the allowlist. |
| Terms omitted | Leave the checkbox unchecked | Terms error appears; nothing is written. |
| HTML in a name | Submit markup such as `<script>alert(1)</script>` | Name validation rejects it, and redisplayed values are HTML-escaped. |

## Evidence to capture

1. The form running from the PHP development server.
2. An empty/invalid submission showing field-specific PHP validation errors.
3. A successful submission and its success message.
4. `registrations.json` with the fictional record created by that submission.

For submission, include the PHP source, `data/registrations_example.json`, and the output screenshots. Keep the live `data/registrations.json` file out of GitHub if it contains your personal details.
