# Practical 6 — Fetch API, JSON, search, filter, sort, and pagination

**Outcome:** consume three external JSON resources, dynamically render reusable data cards, and expose search, filter, sorting, loading, failure, empty-result, and paginated result states.

- `data/events.json`, `data/students.json`, and `data/faq.json` each contain 15 records.
- `pages/` provides independent Events, Students, and FAQ views.
- `js/data-browser.js` is a shared modular renderer. It checks HTTP and JSON errors, updates controls from the loaded data, uses safe `textContent`, paginates six results at a time, announces status changes, and keeps the last successful response in `localStorage` for use if a later refresh fails.

## Manual review cases

1. Start the HTTP server from the repository root. Each page should announce that data loaded and display the first six entries.
2. Search for a known event title, student name/email, or FAQ keyword; confirm the list narrows and the result count updates.
3. Filter events by category, students by course, and FAQs by topic; confirm the filter works together with search.
4. Change sort direction/type; confirm the visible records reorder consistently.
5. Use Next/Previous and page-number controls; confirm each page contains at most six records and current page is announced.
6. Search for a term with no matches; confirm the accessible empty state appears.
7. Stop the local server and reload after a successful load; the last cached dataset should still render with a stale-data message. Clear local storage to observe the no-cache network error and retry button.

For evidence, capture the Events, Students, and FAQ pages with their results and filters, plus registration success/error cases and home/register/dashboard layouts at phone, tablet, and desktop widths.
