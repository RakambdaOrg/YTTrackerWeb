# JOURNAL.md

Working log for the website rewrite. Read it before starting, and update it after each step. Delete it once every task is done.

- Worktree: `.claude/worktrees/work`, branch `rewrite/php`
- Background: see `AGENTS.md`. Design reference: `YTTracker` repo, branch `rewrite/wxt` (`src/assets/common.css`, `src/entrypoints/chart/`, `AGENTS.md`).

## Goal

1. Realign the website design with the YTTracker extension's new pages, so both look the same.
2. Rewrite the PHP properly: less clutter, a clear architecture, PHP 8.5 features.
3. For this first part, the API and the database stay exactly the same (see the API tables in `AGENTS.md`).

## Current step

Done (2026-11-11): branch `rewrite/php` created, `AGENTS.md` and this journal written.

Done (2026-11-11): open questions answered (see Decisions).

Done (2026-11-11): `PLAN.md` written and approved by the user.

Done (2026-11-11): plan step 1 (unused files deleted).

Done (2026-11-11): plan step 2 (backend core in `app/src/`, tests in `tests/`, run with `php tests/run.php`).

Done (2026-11-11): plan step 3 (`UserRepository`, `StatsRepository`, new `app/api/v2/index.php`, old `api/v2/model/` deleted). `tests/compat/run.sh`: 39 identical, 7 known differences (listed below), 0 unexpected.

Next: plan step 4 (page and table in the extension's style).

## Tasks

- [x] Answer the open questions.
- [x] Write `PLAN.md` (layout, steps, verification) and get it approved.
- [x] Backend core: `app/src/` (namespace `YTT`, autoloader, `strict_types`), one PDO factory (exceptions mode, same config file), a small router and JSON response helper. Deny HTTP access to `app/src/`.
- [x] Repositories: `UserRepository`, `StatsRepository` with the exact same SQL results as today. Use an enum for the stat type (`Watched = 1`, `Opened = 2`).
- [x] API v2 on the new core, same routes, params, response shapes and codes.
- [x] Drop API v1: delete `app/api/v1/` and its two `.htaccess` rules.
- [ ] Page: one `index.php` with the extension's look (nav, cards, light/dark theme).
- [ ] Table: vanilla JS, sortable columns, users active in the last 14 days, durations as `1D 2H 3M 4S`.
- [ ] Charts: Chart.js, watched/opened/count per user over a period selector (like the extension: 7d/30d/90d/1y/all), instead of three amCharts charts.
- [x] Delete the unused files (step 1): `temp.php`, `.gitlab-ci.yml`, `css/bootstrap.min.css`, `css/libs/fontawesome-*`.
- [ ] Delete the rest as each part is replaced: `graphs/`, `model/GraphSupplier.php`, `header.php`, `graphs.php`, `table.php`, the old `api/v2/model/`, `js/`, `css/`.
- [ ] Update `README.md` (libraries list) and `AGENTS.md`.
- [ ] Verify against production: compare old and new JSON for every GET route on a few UUIDs, then a POST on a test UUID.
- [ ] Deploy (merge to `master` syncs by FTP).

## Open questions for the user

- None for now.

## Decisions

- Branch `rewrite/php`, same workflow as the extension: `AGENTS.md`, `JOURNAL.md`, then a `PLAN.md` approved before coding.
- API and database unchanged in this first part, except that v1 is dropped (below).
- (2026-11-11) PHP 8.5 is available on the host. PHP 8.5.12 is installed in the container with mise, with only the extensions this project needs (the full build fails on missing ICU and a GCC crash in curl): `CFLAGS="-O2 -g0" PHP_CONFIGURE_OPTIONS="--enable-mbstring --enable-mysqlnd --with-pdo-mysql=mysqlnd --with-mysqli=mysqlnd --with-zlib --with-openssl --without-pcre-jit" mise use -g php@8.5`.
- (2026-11-11) Chart.js from a reliable CDN (jsdelivr, pinned version + SRI hash).
- (2026-11-11) Charts keep one line per user.
- (2026-11-11) Only v2 is in use (the extension's `src/lib/api.ts` calls only `/api/v2/`). Drop `api/v1/` and its `.htaccess` rules (`api/v1/*` and the bare `api/*`).
- (2026-11-11) `.gitlab-ci.yml` is unused (project is on GitHub): delete it.
- (2026-11-11) `temp.php` can be deleted.
- (2026-11-11) `PLAN.md` approved.
- (2026-11-11) The local MariaDB runs with a non-strict `sql_mode`: in strict mode the extension's `Y-M-D H:m:s` date fails `STR_TO_DATE(..., '%Y-%m-%d')`, in the old and new code alike, so production must be non-strict. Assumed, not checked on the host.
- (2026-11-11) Local MariaDB for the API compatibility check, from `mariadb-server-core` + `mariadb-client-core` (binaries only, no systemd service). It must only run while needed: start it, run the check, stop it. Never leave it running.

## Intended API differences (part one)

- `range` on `/stats/watched` and `/stats/opened` is now read from the query string (or a header) too. The old code only took a JSON int, so `?range=7` returned 31 days. The new page's period selector needs it. Nothing else calls these routes (the extension doesn't).
- `stats/add` with an empty `date` stores today instead of a NULL day (old `date('%Y-%m-%d')` bug).
- `stats/add` for a UUID not in the database stores the stat under the new user. The old `DBConnection` checked `is_resource()` on a PDO object (never true), so it opened a new connection for every query, `lastInsertId()` returned 0, and that first stat went to `UserId 0`. Production probably has orphan `YTT_Records` rows with `UserId = 0` (part two: check, and decide whether to clean them up).
- A database error now returns `{code: 500, message: 'Failed to execute route'}` instead of a PHP fatal or a silently empty result. `stats/add` and `username` POST keep their `E2.2` and `E4` errors.

## Bugs found in the current code (keep or fix? ask before changing API behaviour)

- `js/script.js` `secondsToDurationStr`: `seconds %= seconds` always gives 0, so the table never shows seconds. (Front-end only, fixed by the rewrite.)
- CORS: the allowed origins are compared literally to `https://*.rakambda.fr` and `chrome-extension://…/*`, so they never match. Extensions don't need it (host permissions), but the header is effectively never sent.
- The charts send `range` as a request header, merged into params as a string: `is_int()` fails in `getUserDurationStats`, so the watched/opened charts always use 31 days and `?all=1` only works for the count chart.
- `getUserOpenedCount` uses `range` without `intval()`.
- `getDataType()` returns any unknown type unchanged, so `stats/add` can insert arbitrary `Type` values.
- `StatsHandler::getTimestamp()` uses `date('%Y-%m-%d')` (strftime syntax), which outputs literal `%Y-%m-%d`. Only reachable from v1 (v2 requires `date`), so it goes away with v1.
- "Today" and "last 7 days" are `StatDay >= NOW() - N DAY`, so "today" actually includes yesterday (the `DATE` is compared to a datetime).
- `DBConnection` sets `ATTR_EMULATE_PREPARES` twice (false, then true) and uses `ERRMODE_WARNING`, so SQL errors are silent.
- `apache_request_headers()` only exists under Apache/FPM with that function; any header can override a body param.
- `temp.php` is a public URL that runs a destructive dedupe on every GET.
- `header.php` loads the logo from `Rakambda/YTTracker/raw/master/includes/assets/icon128.png`, a path that disappears with the extension rewrite (`public/assets/` there).
