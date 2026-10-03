# StudentHub functional requirements

## Scope

StudentHub is a responsive, accessible student portal prototype for CHARUSAT. This submission covers a static frontend and client-side interactions. Authentication, administration, and data changes are demonstrative; there is no server or database in Practicals 1–6.

## User roles

- **Visitor:** browse public pages, search campus data, register, and send feedback.
- **Student:** view their dashboard, profile, attendance, timetable, assignments, notices, and campus information. Sign-in is a frontend demonstration only.
- **Administrator:** an identified future role for managing student, faculty, course, event, and feedback records. Server-side permissions are outside Practicals 1–6.

## Functional requirements

1. Provide a consistent, working navigation path between at least 10 student-portal pages.
2. Present portal purpose, campus information, news, and events on the home and about pages.
3. Provide accessible login, registration, and feedback forms with associated labels.
4. Provide separate dashboard, profile, attendance, timetable, assignment, notice, event, FAQ, contact, and admin page structures.
5. Adapt the interface for phone, tablet, and desktop screen widths using reusable CSS.
6. Provide keyboard-operable navigation, FAQ disclosure, carousel controls, notification dismissal, and a saved light/dark theme.
7. Validate registration fields in the browser and explain invalid values beside their fields.
8. Fetch event, student, and FAQ records from JSON files; support search, category/course filter, sort, pagination, loading, error, and empty states.
9. Preserve existing Git history and make a descriptive commit after each practical milestone.

## Non-functional requirements

- Use semantic HTML5, explicit form labels, useful image alternative text, visible keyboard focus, and readable color contrast.
- Use mobile-first responsive CSS, external stylesheets, and modular JavaScript.
- Provide clear file names, a README, data files, and diagrams/wireframes in the project repository.
- Use text content APIs for fetched values so data is not parsed as HTML.

## URL and browser notes

A URL identifies the location of a resource: a scheme (such as `https`), host (such as `example.edu`), optional port, path, query string, and fragment. A browser requests or reads an HTML resource, parses markup into the DOM, then loads referenced stylesheets, scripts, images, and data. This project must be served over HTTP for `fetch()` to load local JSON reliably.
