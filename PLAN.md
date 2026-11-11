# YTTrackerWeb rewrite: PHP 8.5 + the extension's look

## Context

The site is about 1,000 lines of PHP and JS spread over includes, PHP that generates JavaScript (`GraphSupplier::plot()`), two copies of the DB connection, a v1 API nobody uses, a public script that writes to the database (`temp.php`), and about 1,600 vendored FontAwesome files that are never used. It loads jQuery, Bootstrap, DataTables and amCharts 4 from CDNs.

Goal: the same features and the same v2 API, with a small, typed PHP core and a page that looks like the extension's pages (`YTTracker` `rewrite/wxt`).

Decisions made with the user are listed in `JOURNAL.md` (Decisions).

## Layout

```
app/                         synced as-is to the host by FTP
  .htaccess                  HTTPS + api/v2/* → api/v2/index.php?request=* (v1 rules removed)
  index.php                  the page: nav, table card, charts card (static HTML + a few PHP values)
  favicon.ico
  assets/
    style.css                the extension's common.css (same tokens, light/dark) + table styles
    main.js                  ES module: table, charts, duration format
    icon128.png              copied from YTTracker public/assets/ (no more hotlink to GitHub)
  api/
    index.html               unchanged
    v2/index.php             require bootstrap, declare the routes, dispatch
  src/                       PHP classes, namespace YTT, autoloaded; .htaccess "Require all denied"
    bootstrap.php            strict_types, PSR-4-style spl_autoload_register for YTT\ → src/
    Database.php             one lazy PDO; config read from /www/configs/database.config.php (__DIR__/../../../configs)
    StatType.php             enum StatType: int { Watched = 1; Opened = 2 } + fromParam()
    Http/Request.php         params merge, same order as today: JSON body, headers, then $_POST or $_GET
    Http/Router.php          [method, regex, handler] list, first match wins, 404 otherwise
    Http/Response.php        JSON + status from the result's `code`, same CORS header logic
    UserRepository.php       users list, username get/set, id lookup/create
    StatsRepository.php      per-day series, totals, add stat
tests/                       not deployed: plain-PHP assert scripts, run with `php tests/run.php`
```

PHP 8.5 features where they make the code shorter, not for show: enums, readonly classes and constructor promotion, `match`, first-class callables for the route handlers, typed properties, the pipe operator only if it reads better.

## Compatibility rules for the v2 API

The point of part one is that the extension and any other client see no difference. So, kept on purpose:

- Same routes and regexes (including the unanchored start), same param merge order, same SQL queries (results, row order, `GROUP BY` without `ORDER BY`).
- Same PDO attributes (`EMULATE_PREPARES` on, `STRINGIFY_FETCHES` off), so the JSON value types stay the same (for example `SUM()` decimals come out as strings).
- Same response bodies and HTTP codes, including `{code: 404, result: ''}` without `request`, `{code: 404, error: 'No route found'}`, `{code: 500, message: 'Failed to execute route'}`, and `stats/add` returning `{code: 400, result: 'err', error: 'E2.2'}` when the insert fails.
- The CORS origin list stays as it is (it never matches, see JOURNAL bugs; changing it is for part two).
- Only one internal change: PDO in exception mode, with exceptions caught where the old code checked `execute()` results, so the responses above are unchanged but errors are no longer silent in the logs.

The API bugs in `JOURNAL.md` are **not** fixed in part one, except where the rewrite of the page removes them (the `range` header sent by the old charts).

## Page

Same content as today, in the extension's style:

- `nav`: logo, "YouTube Tracker", spacer, "Chrome extension" and "Firefox add-on" buttons.
- Card "Users": users active in the last 14 days (`/users?range=14`, then `/<uuid>/stats` each), columns total / last 7 days / today × opened / watched / count. Sortable headers (click to sort, click again to reverse), default sort by today's watched time, descending, as today. Durations formatted like the extension's `formatDuration()` (`1D 2H 3M 4S`). Users with a total count of 0 are hidden, as today.
- Card "Charts": a period selector (7 / 30 / 90 / 365 days / everything = 3650), and three Chart.js line charts: watched (hours), opened (hours), opened count. One line per user, users from `/users?range=<period>`, data from the three series endpoints with `range` as a query param. Tooltip shows `formatDuration()`, plus the extension's vertical hover line. Line colours: a fixed palette that rotates, since there are more users than the three series colours.
- Light and dark theme from the CSS variables, as in the extension. Charts read their colours from the same tokens.
- Chart.js 4.6.1 (the version the extension uses) from jsdelivr, UMD build, pinned with an SRI hash.
- Usernames only ever go through `textContent` (they are user input).

## Steps (one commit each)

1. Commit `AGENTS.md`, `JOURNAL.md`, `PLAN.md`. Delete what nothing uses: `temp.php`, `.gitlab-ci.yml`, `api/v1/` and its two `.htaccess` rules, `css/bootstrap.min.css`, `css/libs/fontawesome-*` (and its `<link>` in `index.php`).
2. Backend core: `src/bootstrap.php`, `Database`, `StatType`, `Http/*`, `src/.htaccess`. Tests for `Request` (merge order), `Router` (match, 404) and `StatType::fromParam()`.
3. Repositories and the new `api/v2/index.php`. Delete `api/v2/model/`. Run the compatibility check (Verification).
4. Page and table: `index.php`, `assets/style.css`, `assets/main.js`, `assets/icon128.png`. Delete `header.php`, `table.php`, `js/`, `css/`.
5. Charts in `assets/main.js`. Delete `graphs.php`, `graphs/`, `model/`.
6. Update `README.md` (libraries: Chart.js only) and `AGENTS.md`. After the user merges and checks production, delete `PLAN.md` and `JOURNAL.md`.

## Verification

- `php -l` on every PHP file, and `php tests/run.php`.
- **API compatibility, side by side**: run the old code (`master`) and the new code with `php -S` (a small router script that does what `.htaccess` does), both against the same database, and diff the JSON and status of every route: `/users` with and without `range`, the four GET stats routes with and without `range`, `username` for a known and an unknown UUID, missing `request`, an unknown route, then `stats/add` and `username` POSTs (JSON and form bodies) followed by a dump of both tables. This needs a local MariaDB with the schema from `AGENTS.md` and some seed data: **a system package, so it needs the user's OK**. Without it, the check falls back to diffing the new GET responses against production after deployment.
- Page, by the user (no browser in the container): table numbers match the old page, sorting, period selector, tooltips, light and dark themes, phone width.
- After the merge: watch the deploy workflow, open the site, check that the extension still sends stats (a new row in today's stats).
