# iDEAL-Q — Fix Log

Chronological log of files touched during the 2026-07-20 repair session, and
the exact commands used to verify each batch. Cross-reference bug IDs against
[BUG_REGISTER_EN.md](BUG_REGISTER_EN.md) for root-cause detail.

## New files created
- `router.php` — dev-server router (BUG-0001)
- `run/run_all.sh` — one-shot clear-caches / start-everything / health-check / report script
- `beaa/css/feedback.css`, `beaa/css/bulkcall.css`, `beaa/css/bigdisplay.css`, `beaa/css/bd-bulk-slides.css`, `beaa/css/report.css`, `beaa/css/search.css`, `beaa/css/counter.css`
- `beaa/js/feedback.js`, `beaa/js/counter.js`, `beaa/js/followup.js`, `beaa/js/audios.js`, `beaa/js/morepapers.js`, `beaa/js/adminbulk.js`, `beaa/js/flow.js`, `beaa/js/search.js`, `beaa/js/subcategories.js`, `beaa/js/report.js`, `beaa/js/Chartjs_utils.js`, `beaa/js/report_followups.js`, `beaa/js/report_followups_charts.js`
- `beaa/api/feedback/set.php` — feedback submit endpoint (BUG-0003)
- Vendored third-party libs (unmodified upstream releases): `beaa/js/jquery.barrating.min.js`, `beaa/css/animate.css`, `beaa/css/themes/fontawesome-stars(-o).css`, `beaa/js/moment.min.js`, `beaa/js/Chartjs.min.js`, `beaa/js/jQuery.print.min.js`, `beaa/js/jquery-ui(-1.12.1).min.js` + `.css`, `beaa/js/FileSaver.min.js`, `beaa/js/chartist/*`, `beaa/js/echarts.common.min.js`, `beaa/js/minified/*` (sceditor + plugins)
- Generated images: `beaa/files/add.png`, `delete.png`, `check.png`, `uncheck.png`, `shortcut_icons/star.png`; reused `logo-idealchip.png`, `moenv-logo-ar/en.jpg`

## Files edited (code)
- `scripts/start_meqs.sh`, `scripts/run_meqs_full.sh` — wire in `router.php`
- `.htaccess` — added missing rewrite rules
- `beaa/feedback/index.php` — empty default `<option>` on rating selects (BUG-0004)
- 18 files under `beaa/admin/views/*/list.php`, `beaa/admin/languages.php`, `beaa/admin/morepapers.php` — `$filesPath` slash fix (BUG-0006)
- `beaa/admin/views/{countersCategories,clerks,displays,zones,bigDisplayBulk}/list.php`, `beaa/api/audio/index.php` — `mysqli_connect()` port fix (BUG-0007)
- `beaa/admin/views/followups/process.php` — `GetPercenatage()` default arg (BUG-0015)
- `beaa/css/common.css` — ~70 previously-undefined utility classes (`pad-*`, `s-*`, `round-*`, `sh-*`, `font-*`, `corner-*`, `ribbon-*`, `bg-*`, `.modal-center`, ...) used site-wide

## Database changes
- `database/schema.sql` — `followups`: added `day_order_no`, `event_id`, `extension_no`; `transfers`: added `transfer_zone`, relaxed `transfer_cat` to nullable (BUG-0010, BUG-0011)
- `database/demo_seed.sql` — 598 text translations (was ~14), 11 settings keys added (`bigdisplayMessageCount`, `maxCount`, `counterSwitchServices`, `counter_callDelaySeconds`, `counter_recallTimes`, `displayType`, `bulkDelay`, `maxTransactions`, `maxBulkNumber`, `minimumCategoriesCount`), 3 extension numbers, 10 additional `followups` rows spread across the last 10 days
- Same changes applied live via `mariadb` to the running `project_demo_db` (schema.sql/demo_seed.sql updates make them reproducible on a fresh `--reset-db`)

## Verification commands used

```bash
# Full stack restart + 31-point health check
bash run/run_all.sh

# PHP syntax check any file
.runtime/env/bin/php -c .runtime/php-ext/php.ini -l <file>

# Live login as admin (for authenticated crawls)
CJ=/tmp/cookies.txt
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" -O /dev/null \
  "http://127.0.0.1:8000/beaa/admin/account/login.php"
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
  --post-data="username=admin.demo@example.com&password=AdminDemo@123" \
  -O /dev/null "http://127.0.0.1:8000/beaa/admin/account/login.php"

# Live login as clerk (counter workflow)
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" -O /dev/null \
  "http://127.0.0.1:8000/beaa/counter/?id=1"
wget -q --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
  --post-data="username=operator.demo@example.com&password=OperatorDemo@123&autologin=false&counter=1" \
  -O - "http://127.0.0.1:8000/beaa/api/counter/?op=11"
```

The full-site asset/error crawl (39 pages, extracts every `href`/`src`,
strips HTML comments, checks each unique asset's HTTP status, greps every
page body for `Fatal error|Parse error|Uncaught|Warning|Notice|TypeError`)
was run as an ad-hoc Python script each iteration rather than committed as a
file — see the transcript in this conversation for the exact script if you
want to re-run it; it's a reasonable candidate to promote into
`run/crawl_check.py` if this repair work continues.

## Final verified state (end of this session)
- `run/run_all.sh`: 31/31 PASS, 0 FAIL
- Full crawl of all 39 discoverable pages (admin + counter + bigdisplay + bulkcall + feedback + audio + display): **0 broken assets, 0 visible PHP errors**
- Full clerk workflow (login → call → recall → pending → transfer cross-counter → close → issue follow-up card) verified end-to-end against the live DB
- Test/demo data cleaned up after each verification pass; final DB state: 14 events, 11 followups, 0 open transfers, 3 feedback rows, both counters closed/idle
