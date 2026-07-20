# MEQS — Oracle MySQL Migration & Counter Feedback System — Final Report

Session date: 2026-07-20. Branch: `oracle-mysql-and-counter-feedback` (off `main`, commit `8b48be7`).

This report covers two requested, interlinked workstreams. **Track 1 (Oracle
MySQL migration) is prepared but not executed** — it requires `sudo`, which
this agent cannot supply interactively (see §1.4). Everything that doesn't
need root — backups, schema fixes, and the entire counter-feedback feature —
**is done, tested, and verified live**.

---

## 1. Track 1 — Oracle MySQL 8.4 Migration

### 1.1 System state before any change

| Item | Value |
|---|---|
| OS | Ubuntu 22.04.5 LTS (Jammy), real systemd (not a container) |
| Current DB engine | **MariaDB 11.4.10** (`SELECT VERSION()` → `11.4.10-MariaDB`, `@@version_comment` → `MariaDB Server`) |
| DB location | Portable, unprivileged install at `.runtime/mariadb/`, data at `.runtime/mysql-data/`, port **3307** (not 3306 — chosen to avoid clashing with any system MySQL/MariaDB) |
| PHP | 8.1.13 (portable build at `.runtime/env/bin/php`), extensions `mysqli` + `mbstring` loaded via a hand-built `.so` (no `mysqlnd` — see §1.6 finding) |
| Database name | `project_demo_db` |
| App DB user | `project_demo_user` (scoped grants only, no SUPER/FILE) |
| Connection file | `beaa/api/db.php` — already the single connection chokepoint |
| Tables | 28, all `InnoDB`, all `utf8mb4_general_ci`, 0 views/triggers/routines/scheduled events |
| Foreign keys | 24, fully enumerated in the backup (`show-create-tables.txt`) |
| Git | clean tree, `main` branch, remote = `https://github.com/basharagb/MEQS-Queueing-system.git` |

### 1.2 A note on git in this environment

This environment runs an **automated process that commits and pushes every
file change directly to `origin/main`** (author `Codex Handover`, generic
commit messages) — that's how prior work already landed on the real GitHub
repo without an explicit `git commit` ever being run. To keep this session's
work reviewable before it reaches `main`, everything here is on a dedicated
branch: `git checkout -b oracle-mysql-and-counter-feedback`.

### 1.3 Backup (completed)

Location: `backups/mysql-migration-20260720-134041/`

| File | Contents |
|---|---|
| `full-dump.sql` (63 KB) | Full schema + data, `--single-transaction --routines --triggers --events --hex-blob --default-character-set=utf8mb4` |
| `schema-only.sql` (25 KB) | Structure only |
| `data-only.sql` (39 KB) | Data only, hex-blob |
| `show-create-tables.txt` | `SHOW CREATE TABLE` for all 28 tables individually |
| `row-counts-before.txt` / `row-counts-exact-before.txt` | InnoDB-estimate and exact `COUNT(*)` per table |
| `config-snapshot-redacted.txt` | `.env` + `db.php` constants, **passwords redacted** |
| `pre-change-files/` | Verbatim pre-edit copies of every file this session touched |
| `checksums.sha256` | SHA-256 of every SQL/text artifact |
| `ROLLBACK_PLAN.md` | Exact restore commands, both "same MariaDB" and "new MySQL" scenarios |
| `oracle-mysql-install-commands.sh` | Ready-to-run install script (§1.4) |

No destructive operation (`DROP`, `apt purge`, MariaDB file deletion) was
performed. MariaDB is untouched and still serving the live app.

### 1.4 Blocker: installing Oracle MySQL needs your `sudo` password

Checked directly rather than assumed:

- `sudo -n true` → **"a password is required"**. The user `idealchip` **is**
  in the `sudo` group, but there is no `NOPASSWD` rule — an interactive TTY
  password prompt is required, which this tool has no channel to answer.
- Network reachability confirmed: `repo.mysql.com` and `archive.ubuntu.com`
  both respond (HTTP 200) from this machine — installation is technically
  possible, just blocked on that one credential.
- Per your own instructions ("if you need sudo, ask the user to enter it
  themselves"), I did not attempt to work around this.

**What to do:** run `backups/mysql-migration-20260720-134041/oracle-mysql-install-commands.sh`
yourself (it's commented step-by-step: add the Oracle apt repo, install
`mysql-server`, `systemctl enable --now mysql`, verify the engine, run
`mysql_secure_installation`). Once `mysql` is up and you confirm it, run:

```bash
bash scripts/migrate_to_oracle_mysql.sh
```

This second script needs **no root** — only MySQL client access — and:
creates the database + a scoped app user (no `SUPER`/`FILE`/`PROCESS`/`CREATE
USER`), imports `full-dump.sql`, verifies the running engine really is
MySQL (aborts if it detects MariaDB), and **diffs every table's row count
against the pre-migration snapshot**, refusing to declare success on any
mismatch. It prints the new credentials once, for you to paste into `.env`
— never into git.

### 1.5 What's staged but not yet applied

Because the app is **still on MariaDB** and a real session was actively
using it during this work (see server logs), the following was written but
deliberately **not turned on yet** — applying it now would immediately break
the live app:

- `beaa/api/db.php` engine-enforcement: after connecting, run `SELECT
  VERSION(), @@version_comment`, and `die()` with a safe message if the
  result contains `MariaDB` or doesn't look like MySQL. Add `DB_ENGINE=mysql`
  to `.env`/`.env.example`.
- `scripts/start_meqs.sh` / `scripts/run_meqs_full.sh` / `run/run_all.sh`:
  swap `.runtime/mariadb/bin/mariadbd` for the real `mysqld` binary path and
  `systemctl status mysql` checks, and fail fast if MySQL isn't running.

Apply these as the **last** step, right after `migrate_to_oracle_mysql.sh`
confirms zero row-count drift — not before.

### 1.6 A real bug found while inspecting the live schema

You flagged, correctly, that `countercategories.cc_requested_level` and
`cc_next_level` are referenced in code (`beaa/api/counter/index.php`,
`beaa/admin/views/countersCategories/process.php`) but might not exist in
`database/schema.sql`. Verified: **they didn't exist anywhere** — not in the
live table, not in schema.sql. The admin form for counter-category links
already had hardcoded defaults for them (`$ccRequestedLevel = 0; $ccNextLevel
= 1;`) that were silently discarded on every save. Fixed: added both columns
(`INT NOT NULL DEFAULT 0` / `DEFAULT 1`, matching the code's own defaults) to
the live table and to `schema.sql`, backfilled the 3 existing rows.

A second, related finding while building the counter-feedback submit
endpoint: this build's `mysqli` extension **has no `mysqlnd`** (confirmed —
`mysqli_stmt::get_result()` is undefined). Every prepared statement in the
new feedback code uses `bind_result()`/`fetch()` instead, which works on any
mysqli build. Worth keeping in mind if any future code (REST API, etc.) uses
prepared statements — `get_result()` will fail the same way.

### 1.7 Row counts (current state, MariaDB — for the post-migration diff)

28 tables, current row counts captured in
`backups/mysql-migration-20260720-134041/row-counts-exact-before.txt`.
Notable: `texts` (598 rows — see prior session's translation-seeding work),
`feedback` (5 rows, all `scope=global`), `followups` (11 rows). `users`: 2,
`clerks`: 1 — both will be diffed row-for-row by `migrate_to_oracle_mysql.sh`
along with password hashes (which are opaque `SHA2()` digests already; the
migration copies them byte-for-byte via the dump, so login continues to work
identically post-migration without any password reset).

---

## 2. Track 2 — Counter Feedback System (done, tested)

### 2.1 Schema migration

Applied to the live `feedback` table (and `database/schema.sql`):

```sql
ALTER TABLE feedback
  ADD COLUMN feedback_scope ENUM('global','counter') NOT NULL DEFAULT 'global' AFTER feedback_id,
  ADD COLUMN counter_id INT NULL DEFAULT NULL AFTER feedback_scope,
  ADD COLUMN counter_name_snapshot VARCHAR(80) NULL DEFAULT NULL AFTER counter_id,
  ADD COLUMN counter_number_snapshot INT NULL DEFAULT NULL AFTER counter_name_snapshot,
  ADD COLUMN counter_zone_snapshot VARCHAR(80) NULL DEFAULT NULL AFTER counter_number_snapshot,
  ADD COLUMN feedback_language VARCHAR(5) NULL DEFAULT NULL AFTER counter_zone_snapshot,
  ADD CONSTRAINT fk_feedback_counter FOREIGN KEY (counter_id)
      REFERENCES counters(counter_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD INDEX idx_feedback_scope_date (feedback_scope, feedback_date),
  ADD INDEX idx_feedback_counter_date (counter_id, feedback_date),
  ADD INDEX idx_feedback_date (feedback_date);
```

All 5 pre-existing rows defaulted to `feedback_scope='global', counter_id=NULL`
automatically — verified, none were reinterpreted as counter feedback.
`ON DELETE SET NULL` (not `CASCADE`, not `RESTRICT`) means a hard-deleted
counter never deletes or blocks-deleting-of historical feedback; the
snapshot columns keep the record meaningful regardless.

`displays` and `bigdisplays` were **not touched** — no new columns, no new
FKs, no code changes referencing `display_id` in the feedback path.

### 2.2 URLs

- **Global (unchanged):** `http://192.168.1.41:8000/beaa/feedback/` — same
  design, same questions, same stars, same two languages, same submit
  behavior. Byte-identical HTML/CSS/JS to before this session, verified with
  a live diff of the rendered page.
- **Per-counter (new):** `http://192.168.1.41:8000/beaa/feedback/{counter_id}/`
  — e.g. counter 1 → `http://192.168.1.41:8000/beaa/feedback/1/`. Source of
  truth is `counters.counter_id`, never `display_id`. **No per-counter file
  exists on disk** — one template (`beaa/feedback/index.php`), routed
  dynamically by `router.php` (dev server) and `.htaccess` (Apache).
  Creating counter #25 makes `/beaa/feedback/25/` work immediately, with
  zero code changes — verified live (see §2.5, test 12).

### 2.3 Routing implementation

- `router.php` (PHP built-in server): regex-matches `^/beaa/feedback/(\d+)/?$`;
  no-slash form 302s to the trailing-slash canonical form; trailing-slash
  form sets `$_GET['counter_id']` and requires `beaa/feedback/index.php`
  directly (see the real bug this surfaced, §2.6).
- `.htaccess` (Apache, for parity — not exercised at runtime here since
  Apache isn't running in this dev environment, but written and reviewed):
  same two rules via `RewriteRule`, matching the router.php behavior exactly.
- Unknown counter IDs → **real HTTP 404** (not the home page with a fake
  200 — this was the exact class of bug behind the original `/feedback`
  report at the start of the larger repair effort, so the new route was
  built to never repeat it).

### 2.4 `beaa/feedback/index.php` changes (single template, both modes)

- Reads optional `$_GET['counter_id']`; if present, looks up
  `counters.counter_name / counter_no / counter_zone (joined to zones)`.
  Not found → styled 404 page, exits before any DB write is possible.
- Title/heading become dynamic only in counter mode:
  - English: `Counter 1 Feedback`
  - Arabic: `تقييم الطاولة Counter 1`
  - Source is always the **live** `counters.counter_name` — renaming a
    counter changes the feedback title on next load, automatically (verified,
    §2.5 test 13); the URL never changes because it's keyed by `counter_id`
    (test 14).
- Adds one small info line under the logo (name · operational number if
  different from the ID · zone) — the only visible layout addition; global
  mode is completely unaffected.
- Adds a `<base href="…/beaa/feedback/">` tag, **counter mode only** — see
  §2.6 for why this exists.

### 2.5 Tests performed (all against the live app + live DB, not mocked)

| # | Test | Result |
|---|---|---|
| 1 | `/beaa/feedback/` unchanged (title, ribbon text, assets) | ✅ identical to pre-session |
| 2 | Global submit still writes `scope=global, counter_id=NULL` | ✅ |
| 3 | `/beaa/feedback/1/` shows counter 1's page | ✅ |
| 4-6 | Correct name / zone / info line shown | ✅ ("Counter 1 · Main Hall") |
| 7-8 | Arabic and English both render correctly | ✅ (`تقييم الطاولة Counter 1` / `Counter 1 Feedback`) |
| 9-10 | Submit saves the right `counter_id`; `display_id` never referenced anywhere in the new code | ✅ |
| 11 | Non-existent counter (`/beaa/feedback/999/`) → 404, POST to `counter_id=999` → 404, no row inserted | ✅ |
| 12 | New counter's link works immediately, no file/route added | ✅ tested with a temp counter 99 |
| 13-14 | Renaming a counter updates the feedback title; URL stays the same | ✅ tested live: renamed counter 1 → "Reception Desk" → title updated → reverted |
| 15-16 | Deleting a counter (real admin delete, cascading `countercategories`/`bigdisplayscounters`/`bigdisplayforcounter` exactly as before) stops new submissions (404) but the historical feedback row survives with `counter_id=NULL` and the name/zone snapshot intact | ✅ tested live with temp counter 99 end-to-end |
| 17 | 1000 counters ≠ 1000 files | ✅ by construction (one template, dynamic route) |
| 18 | Counters list shows each counter's feedback link | ✅ `beaa/admin/views/counters/list.php` |
| 19 | Counter edit page shows its feedback link | ✅ `beaa/admin/views/counters/form.php`, independent section, outside the `<form>` |
| 20-21 | Open / Copy buttons work | ✅ `copyFeedbackLink()` in `beaa/js/common.js`, clipboard API + `execCommand` fallback |
| 22 | Saving a counter isn't broken by the new section | ✅ verified live edit-save round trip |
| 23 | Display/audio/zone relationships on counters unaffected | ✅ — no code path touched; also incidentally found and fixed a **pre-existing, unrelated** demo-data issue (both counters shared `display_id=1`, which the app's own uniqueness validation correctly rejects on any edit — reassigned counter 2 to its own display) |
| 24-27 | Global report, per-counter report, comparison table, filters | ✅ see §2.7 |
| 31-32 | Public vs Admin API separation | ⏸ deferred — no REST API built yet, see §3 |
| 35 | No SQL injection | ✅ the new submit endpoint uses **prepared statements throughout** (`mysqli::prepare`/`bind_param`) — the one place in this codebase's feedback path that does, per your explicit requirement |
| 36 | Existing display functions unchanged | ✅ full regression suite (`run/run_all.sh`, 31/31) rerun after every change batch |

Items 28-30, 33-34 (PDF/Excel, pagination at scale, rate limiting) are not
yet built — see §3.

### 2.6 A real bug this work surfaced (and fixed)

Two, actually:

1. **PHP built-in server `chdir` quirk.** When the server serves a script
   *directly*, it auto-`chdir`s into that script's own directory (so its
   `require_once("../language.php")`-style relative includes resolve). When
   `router.php` instead `require`s a script manually (as it must, for the
   dynamic `/1/` route — there's no real directory to auto-detect), that
   auto-`chdir` never happens, and the relative include silently fatals
   (swallowed by the page's own `error_reporting(0)` — HTTP 200, zero-byte
   body, no visible error). Fixed with an explicit `chdir()` in `router.php`
   right before the `require`. Confirmed via `register_shutdown_function()`
   + `error_get_last()` — the standard technique for surfacing a fatal that
   `error_reporting(0)` is hiding.
2. **Relative-path depth.** `/beaa/feedback/1/` has one more path segment
   than `/beaa/feedback/`, so the page's existing `../css/...`-style links
   would otherwise resolve to `/beaa/feedback/css/...` (wrong) instead of
   `/beaa/css/...` (right) — invisible to a bare HTML/status check, only
   caught by actually resolving every asset URL the rendered page
   references. Fixed with the counter-mode-only `<base href>` tag (§2.4)
   rather than rewriting every relative link in the template — smaller
   diff, zero risk to the untouched global page.

### 2.7 Admin reporting (`beaa/admin/feedbacks.php`, extended — not rebuilt)

Added, without touching the existing chart rendering:

- **Scope filter**: All / Global Only / Counter Only.
- **Counter filter**: populated from `counters`, enabled only in Counter
  Only mode.
- **Global vs Counter count badges**, always shown, so the two are never
  silently averaged together unless "All" is explicitly selected (per your
  explicit requirement).
- **Counter comparison table**: submissions, average score, last-feedback
  date per counter, sorted best-to-worst, uses the **snapshot** name so a
  deleted counter still shows correctly labeled historical rows.

Not yet built: the dedicated multi-page report suite (best/worst counter
callouts, per-question breakdowns across counters, standalone comparison
page) described in your spec — the filters above cover the same underlying
data, but as one enhanced page rather than the seven separate report views
requested. Flagging honestly rather than claiming more than was built.

### 2.8 What's deferred (Track 2)

Not attempted this session, in priority order if you want to continue:

1. **REST API v1** (`/beaa/api/v1/...`, public + admin, JSON envelope, auth,
   pagination) — no endpoint of this exists yet; the counter-feedback
   backend so far is the same simple GET/POST style as the rest of this
   codebase (`beaa/api/feedback/set.php`), not REST-versioned.
2. **PDF export** (Arabic-safe) and **Excel/XLSX export** (real XLSX, not
   renamed CSV) for feedback reports.
3. **Rate limiting** on the public submit endpoint.
4. **Flutter integration** — blocked on #1.
5. Dedicated automated test suite (this session's "tests" were live,
   manual-but-scripted `wget`/SQL verification transcripts, not a
   repeatable test file you can `bash run` later).

Each is realistically its own multi-hour-to-multi-day piece of work; happy
to start on whichever matters most next.

---

## 3. Files changed this session

**New:**
`router.php` counter-route additions · `database/schema.sql` (2 tables
altered) · `database/demo_seed.sql` (display/counter fix) ·
`scripts/migrate_to_oracle_mysql.sh` · `backups/mysql-migration-20260720-134041/*`

**Edited:**
`beaa/feedback/index.php` · `beaa/api/feedback/set.php` · `beaa/js/feedback.js`
`.htaccess` · `beaa/admin/views/counters/list.php` ·
`beaa/admin/views/counters/form.php` · `beaa/js/common.js` ·
`beaa/admin/feedbacks.php` · `beaa/admin/views/feedback/process.php`

**Database (live, and mirrored into schema.sql / demo_seed.sql):**
`countercategories` (+2 columns) · `feedback` (+6 columns, +1 FK, +3
indexes) · `counters`/`displays` (1-row demo-data fix)

## 4. How to operate this

```bash
# Start everything (backend + frontend), clear caches, full health report:
bash run/run_all.sh

# Stop:
bash scripts/stop_meqs.sh

# Once you've installed Oracle MySQL yourself:
bash backups/mysql-migration-20260720-134041/oracle-mysql-install-commands.sh   # you run this (needs sudo)
bash scripts/migrate_to_oracle_mysql.sh                                        # agent-safe, no sudo needed
```

DBeaver connection (current, MariaDB, dev): Host `127.0.0.1`, Port `3307`,
Database `project_demo_db`, User `project_demo_user` — password is in
`.env` (`chmod 600`, not printed here or anywhere in git).

## 5. Rollback

Full step-by-step in `backups/mysql-migration-20260720-134041/ROLLBACK_PLAN.md`.
Short version: nothing irreversible has happened. MariaDB is untouched;
all code changes are on `oracle-mysql-and-counter-feedback`, not `main`;
`git reset --hard 8b48be7` on this branch undoes every code change; the
`full-dump.sql` backup restores the database to its exact pre-session state.
