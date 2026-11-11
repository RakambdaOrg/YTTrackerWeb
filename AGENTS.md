# AGENTS.md

YTTrackerWeb is the website behind [YTTracker](https://github.com/RakambdaOrg/YTTracker), the browser extension that tracks time spent on YouTube. It has two jobs:

1. An **API** that receives the stats of users who opted in to online sharing in the extension.
2. A **public stats page** (https://yttracker.rakambda.fr/) that shows every sharing user's stats in a table and in charts.

It is plain PHP, served by a shared host that runs PHP pages. There is no build step and no Composer: the content of `app/` is synced as-is to the server by FTP.

If `JOURNAL.md` exists, read it first: it tracks the work in progress.

## Hosting and deployment

- `.github/workflows/ftp_deploy.yml`: on a push to the default branch (`master`), syncs `app/` to `/www/subdomains/yttracker` (shared workflow from `RakambdaOrg/rakambda-github-workflows`).
- Database credentials are **not** in the repo. They are read from `configs/database.config.php`, outside the web root (`/www/configs/`), which returns `['host', 'database', 'username', 'password']`. `app/src/Database.php` reaches it as `__DIR__ . '/../../../configs/database.config.php'`. The `YTT_DB_CONFIG` environment variable points elsewhere, for local checks.
- `app/.htaccess` forces HTTPS and routes `api/v2/*` to `api/v2/index.php?request=*`. `app/src/.htaccess` denies HTTP access to the PHP classes.
- Database: MySQL/MariaDB through PDO.
- Production runs PHP 8.5. The dev container has PHP 8.5 from mise and MariaDB binaries without a service (see Checks).
- The production `sql_mode` must not be strict: the extension's unpadded `Y-M-D H:m:s` date fails `STR_TO_DATE(..., '%Y-%m-%d')` in strict mode.

## Database

Two tables (schema inferred from the queries, there is no migration file):

| Table | Columns | Notes |
|-------|---------|-------|
| `YTT_Users` | `ID` (PK, auto), `UUID` (unique), `Username` (nullable), `LastActivity` | `UUID` is the extension's random `userId`. A null username is shown as `Anonymous`. |
| `YTT_Records` | `UserId` → `YTT_Users.ID`, `Type`, `Stat`, `StatDay` (DATE), `Amount` | One row per user, type and day (unique key, used by `ON DUPLICATE KEY UPDATE`). |

- `Type`: `1` = watched, `2` = opened.
- `Stat`: sum of the durations in **milliseconds**.
- `Amount`: number of POSTs added to that row. For type 2 it is the number of opened videos (the "count").

## API (must stay compatible)

The extension (`src/lib/api.ts` on `rewrite/wxt`) uses only v2. The old v1 API was unused and has been removed.

### v2 (`app/api/v2/index.php`)

Params are merged from the JSON body, the request headers, then `$_POST` or `$_GET`. Every response is JSON with an HTTP code copied from the `code` field.

| Method | Route | Params | Response |
|--------|-------|--------|----------|
| GET | `/api/v2/users` | `range` (days, optional, max 3650) | `{code, users: [{uuid, username}], message}`. With `range`, only users with records in the last `range` days. |
| GET | `/api/v2/<uuid>/stats` | | `{total, week, day}`, each `{opened, watched, count}`. Durations in **seconds** (float). `total` = last 3650 days. |
| GET | `/api/v2/<uuid>/stats/watched` | `range` (default 31) | `[{date, value, duration}]`, `value` in ms, one per day. |
| GET | `/api/v2/<uuid>/stats/opened` | `range` | same, for opened time. |
| GET | `/api/v2/<uuid>/stats/opened-count` | `range` | `[{date, value}]`, `value` = number of opened videos. |
| POST | `/api/v2/<uuid>/stats/add` | `type` (`watched`/`opened`/`1`/`2`), `stat` (ms), `date` (`Y-M-D H:m:s`, unpadded) | `{code, result}`. Creates the user if needed and adds to the day's row. |
| GET | `/api/v2/<uuid>/username` | | `{code: 200, username}` or `{code: 204, error}`. |
| POST | `/api/v2/<uuid>/username` | `username` (form-encoded) | `{code, result}`. Creates the user if needed. Empty or `Anonymous` stores null. |

## Code

```
app/
  index.php              the page (static HTML, only today's date comes from PHP)
  assets/style.css       the extension's tokens and components + the table, light and dark themes
  assets/main.js         ES module: users table and charts (vanilla JS, no build)
  api/v2/index.php       the route list: regex → handler, then Router::dispatch and Response::json
  src/                   namespace YTT, autoloaded by src/bootstrap.php, declare(strict_types=1) everywhere
    Database.php         one lazy PDO (exceptions; emulated prepares kept so JSON value types don't change)
    StatType.php         enum: Watched = 1, Opened = 2
    UserRepository.php   users, usernames, UUID → ID
    StatsRepository.php  per-day series, summaries, add
    Http/                Request (param merge), Router (first match wins), Response (JSON, CORS)
tests/                   not deployed
maintenance/             one-off SQL scripts for the production database, run by hand (not deployed)
```

- The page matches the extension's pages (`src/assets/common.css` and `src/entrypoints/chart/` on `YTTracker` `rewrite/wxt`): same CSS tokens, `nav`, `.card`s, durations formatted like `formatDuration()` (`1D 2H 3M 4S`). Keep them in sync when the extension's look changes.
- Users table: users active in the last 14 days, one `/stats` call each, sortable headers, sorted by today's watched time by default.
- Charts: Chart.js 4.6.1 from jsdelivr (pinned, with SRI), three charts (watched, opened, opened videos), one line per user, a period selector. A user's colour comes from their place in the full `/users` list, so it doesn't change with the period. The 8 colours are the dataviz reference categorical palette (`--user-1..8`); past 8 users they repeat with a dash pattern. One y-axis per chart.
- No jQuery, Bootstrap, DataTables, amCharts or FontAwesome.

## Checks

```
php tests/run.php        # unit checks of app/src (plain PHP, no framework)
tests/compat/run.sh      # old API (master) vs new API, side by side on a seeded local MariaDB
tests/db.sh start|stop   # the local MariaDB alone (run.sh starts and stops it itself)
```

MariaDB runs as the current user (data in `~/.local/share/yttrackerweb-mariadb`, port 3399), never as a service. Always stop it after use.

## Conventions

- Work in a git worktree under `.claude/worktrees/`, never in the main checkout.
- Tabs for indentation (see `.editorconfig`).
- All SQL goes through prepared statements.
- Never print user-provided values (usernames) without escaping: `htmlspecialchars()` in PHP, `textContent` in JS, never `innerHTML`.
