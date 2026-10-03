# StudentHub — Web Development Frameworks (ITUE203)

A six-practical, static HTML/CSS/JavaScript student-portal project. Each assignment has its own numbered folder; shared images live under `assets/images/`. The assignment-specific pages, stylesheet, and scripts are linked to their practical folder so each milestone stays easy to review.

## Practical folders

1. [`practical-01-project-planning/`](practical-01-project-planning/): requirements, scope, roles, sitemap, project structure, and supplied wireframes.
2. [`practical-02-semantic-html/`](practical-02-semantic-html/): 15 semantic, linked student-portal page skeletons in `pages/`.
3. [`practical-03-responsive-css/`](practical-03-responsive-css/): shared mobile-first stylesheet with Grid, Flexbox, and breakpoints.
4. [`practical-04-dom-interactivity/`](practical-04-dom-interactivity/): hamburger navigation, saved theme, notification, image slider, modal, and expandable FAQ.
5. [`practical-05-registration-validation/`](practical-05-registration-validation/): accessible registration validation, inline messages, strength feedback, and validation cases.
6. [`practical-06-fetch-json/`](practical-06-fetch-json/): 15-record event, student, and FAQ JSON files; reusable Fetch API renderer; search, filter, sort, pagination, empty/loading/error states, and last-successful-data cache.

## Run locally

Fetch cannot reliably read a local JSON file from a `file://` URL. From the repository root run:

```sh
python3 -m http.server 8000
```

Open [http://localhost:8000/practical-02-semantic-html/pages/index.html](http://localhost:8000/practical-02-semantic-html/pages/index.html). The Events, Students, and FAQ pages are linked from the shared navigation.

## Frontend-only scope

Registration checks inputs in the browser and displays confirmation, but it does not create accounts or store passwords. Login, admin, dashboard, and profile screens are static prototypes. Practical 7 and later add server-side processing and database-backed access controls; do not use this project to collect real credentials.

## Git workflow

The ZIP includes the original Git repository and its existing remote. Preserve that history. Make one reviewable commit per practical (for example, `feat(practical-5): validate student registration` and `feat(practical-6): render searchable JSON data`) and push only to the correct authorized repository once the local commits look right. Do not force-push or reset the remote.
