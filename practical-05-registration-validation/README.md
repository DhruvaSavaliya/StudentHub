# Practical 5 — Registration form and frontend validation

**Outcome:** validate full name, email, mobile number, password, password confirmation, course, year, gender, and terms acceptance. `register.js` validates on input/change and submit, places messages by their associated fields, focuses the first invalid control, and reports a password-strength hint. Passwords are never stored or transmitted.

## Manual validation cases

| Case | Input | Expected result |
|---|---|---|
| Valid form | `Aarav Patel`, `aarav@example.edu`, `9876543210`, `Campus2026`, matching confirmation, a course/year/gender, and checked terms | Clear field errors and show frontend-only success; do not create an account. |
| Required values missing | Submit with every field empty | Inline errors for name, email, mobile, password, confirmation, course, year, gender, and terms; focus the first invalid input. |
| Invalid email | `student.example.edu` | Email error appears beside Email. |
| Invalid mobile | `12345` | Mobile error explains the accepted digit range. |
| Weak password | `short` | Password error appears and strength hint updates. |
| Confirmation mismatch | Valid password plus a different confirmation | Confirmation error appears beside Confirm password. |
| Missing course/year | Leave either selection blank | A field-specific required-selection error appears. |
| Missing gender/consent | Leave radio group unanswered or terms unchecked | An accessible, adjacent error explains the missing choice. |
| Live correction | Correct an invalid value after submit | The adjacent error clears when that value becomes valid. |

To review the rendered form, use the local server and keyboard navigation; check both invalid and valid submissions at desktop and mobile widths.
