#!/usr/bin/env bash
# ============================================================================
#  MEQS / iDEAL-Q  —  run_all.sh
#  One-shot launcher: clears caches, opens the database if it is closed,
#  (re)starts the backend (Oracle MySQL 8.4, systemd service "mysql") and
#  the frontend (PHP web server), runs end-to-end health checks (including
#  a regression test for the "/feedback shows the home page instead of the
#  feedback kiosk" bug and a live check that the DB engine really is MySQL,
#  not MariaDB), and prints/saves a pass/warn/fail report.
#
#  Usage:   bash run/run_all.sh
#  Options:
#     --reset-db     drop and rebuild the database from schema + demo seed
#     --host=IP      web bind/report host (default 192.168.1.41)
#     --port=N       web port (default 8000)
#
#  DB credentials come from .env (see .env.example) -- there is no
#  built-in fallback password. The portable MariaDB under .runtime/mariadb
#  is kept only as an offline rollback reference; nothing here starts it.
# ============================================================================
set -u

# ---------------------------------------------------------------- config ----
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="$PROJECT_DIR/.runtime/env/bin/php"
PHP_INI="$PROJECT_DIR/.runtime/php-ext/php.ini"
ROUTER="$PROJECT_DIR/router.php"
MYSQL_BIN="mysql"
RUN_DIR="$PROJECT_DIR/.runtime/run"
LOG_DIR="$PROJECT_DIR/.runtime/logs"

HTTP_HOST="192.168.1.41"
HTTP_PORT="8000"

# ---- load DB_* from .env (never hardcode credentials here) ----
if [ -f "$PROJECT_DIR/.env" ]; then
  while IFS='=' read -r k v; do
    case "$k" in
      ''|'#'*) continue ;;
    esac
    v="${v%\"}"; v="${v#\"}"; v="${v%\'}"; v="${v#\'}"
    export "ENV_$k=$v" 2>/dev/null
  done < "$PROJECT_DIR/.env"
fi
DB_HOST="${ENV_DB_HOST:-127.0.0.1}"
DB_PORT="${ENV_DB_PORT:-3306}"
DB_NAME="${ENV_DB_NAME:-project_demo_db}"
DB_USER="${ENV_DB_USER:-project_demo_user}"
DB_PASS="${ENV_DB_PASSWORD:-}"

RESET_DB=0
for arg in "$@"; do
  case "$arg" in
    --reset-db)   RESET_DB=1 ;;
    --host=*)     HTTP_HOST="${arg#*=}" ;;
    --port=*)     HTTP_PORT="${arg#*=}" ;;
    *) echo "Unknown option: $arg" ;;
  esac
done

mkdir -p "$RUN_DIR" "$LOG_DIR" "$RUN_DIR/sessions"
REPORT="$LOG_DIR/run-all-report-$(date +%Y%m%d-%H%M%S).txt"

if [ -t 1 ]; then C_OK=$'\e[32m'; C_WARN=$'\e[33m'; C_ERR=$'\e[31m'; C_HD=$'\e[1;36m'; C_0=$'\e[0m'
else C_OK=; C_WARN=; C_ERR=; C_HD=; C_0=; fi

PASS=0; WARN=0; FAIL=0
declare -a FAILURES=()

log()   { echo "$*" | tee -a "$REPORT" ; }
head_() { log ""; log "${C_HD}== $* ==${C_0}"; }
ok()    { PASS=$((PASS+1)); log "  ${C_OK}[OK]${C_0}   $*"; }
warn()  { WARN=$((WARN+1)); log "  ${C_WARN}[WARN]${C_0} $*"; }
fail()  { FAIL=$((FAIL+1)); FAILURES+=("$*"); log "  ${C_ERR}[FAIL]${C_0} $*"; }

log "================================================================"
log "  MEQS / iDEAL-Q — run_all.sh — clear caches, run, verify"
log "  Time    : $(date '+%Y-%m-%d %H:%M:%S')"
log "  Project : $PROJECT_DIR"
log "  Target  : http://$HTTP_HOST:$HTTP_PORT"
log "  Report  : $REPORT"
log "================================================================"

# ============================================================================
#  Phase 0: Clear caches
# ============================================================================
head_ "Phase 0: Clear caches"

# Stop any running PHP dev server so it is guaranteed to reload the latest
# code (router.php fix included) instead of serving from an old process.
OLD_PHP="$(pgrep -f "env/bin/php -c .* -S .*:$HTTP_PORT" 2>/dev/null || true)"
if [ -n "$OLD_PHP" ]; then
  kill $OLD_PHP 2>/dev/null
  sleep 1
  ok "Stopped previous PHP server (PID: $OLD_PHP) to force a clean reload"
else
  ok "No stale PHP server process to stop"
fi

# Clear PHP session files (stale/logged-in sessions).
SESS_COUNT=$(find "$RUN_DIR/sessions" -type f -name 'sess_*' 2>/dev/null | wc -l)
find "$RUN_DIR/sessions" -type f -name 'sess_*' -delete 2>/dev/null
ok "Cleared $SESS_COUNT PHP session file(s)"

# Opcache: not compiled into this local PHP build, nothing to reset there;
# a fresh process (above) already guarantees no stale bytecode either way.

# Rotate the PHP server log so old runs don't obscure this run's errors.
if [ -f "$LOG_DIR/php-server.log" ]; then
  mv "$LOG_DIR/php-server.log" "$LOG_DIR/php-server.log.previous"
  ok "Rotated php-server.log -> php-server.log.previous"
fi

# Keep only the last 5 old reports so logs/ doesn't grow unbounded.
find "$LOG_DIR" -maxdepth 1 -name 'run-all-report-*.txt' -o -name 'health-report-*.txt' 2>/dev/null \
  | sort | head -n -5 | xargs -r rm -f
ok "Cleared old report files (kept last 5)"

# ============================================================================
#  Phase 1: Preflight checks
# ============================================================================
head_ "Phase 1: Preflight checks"

if [ -x "$PHP_BIN" ] && "$PHP_BIN" -v >/dev/null 2>&1; then
  ok "PHP interpreter works ($("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null))"
else
  fail "PHP interpreter missing or broken: $PHP_BIN"
  log ""; log "Cannot continue without PHP. Stopping."
  log ""; log "  Summary: ${C_OK}PASS $PASS${C_0} ${C_WARN}WARN $WARN${C_0} ${C_ERR}FAIL $FAIL${C_0}"
  exit 1
fi

[ -f "$PHP_INI" ] && ok "php.ini found" || warn "php.ini missing; using defaults"

for ext in mysqli mbstring; do
  if "$PHP_BIN" -c "$PHP_INI" -r "exit(extension_loaded('$ext')?0:1);" 2>/dev/null; then
    ok "PHP extension loaded: $ext"
  else
    fail "PHP extension NOT loaded: $ext (required by the app)"
  fi
done

if command -v mysql >/dev/null 2>&1; then
  ok "mysql client present ($(mysql --version 2>/dev/null | head -c 60))"
else
  fail "mysql client not found on PATH -- is Oracle MySQL installed?"
fi

if [ -z "$DB_PASS" ]; then
  fail "DB_PASSWORD is not set in .env -- cannot connect. See .env.example."
  log ""; log "  Summary: ${C_OK}PASS $PASS${C_0} ${C_WARN}WARN $WARN${C_0} ${C_ERR}FAIL $FAIL${C_0}"
  exit 1
fi

if [ -f "$ROUTER" ]; then
  ok "router.php present (fixes /feedback and other short URLs)"
else
  fail "router.php missing at $ROUTER — /feedback will fall back to the home page"
fi

# ============================================================================
#  Phase 2: Backend — Oracle MySQL 8.4 (systemd service "mysql")
# ============================================================================
head_ "Phase 2: Backend — Oracle MySQL database"

mysql_app() { mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$@" 2>/dev/null; }

if systemctl is-active --quiet mysql 2>/dev/null; then
  ok "mysql.service is already running"
else
  log "  ... database service is closed, attempting to open it (systemctl start mysql)"
  # Only works if this shell already holds a cached sudo ticket (NOPASSWD or a
  # recent successful `sudo` in this session); this script never carries or
  # prompts for a password itself.
  if sudo -n systemctl start mysql >/dev/null 2>&1; then
    sleep 2
  fi
  if systemctl is-active --quiet mysql 2>/dev/null; then
    ok "mysql.service started"
  else
    fail "mysql.service is not running and could not be started without a password."
    log "        Run this yourself, then re-run this script:  sudo systemctl start mysql"
  fi
fi

DB_UP=0
for i in $(seq 1 20); do
  if mysql_app -e "SELECT 1" >/dev/null 2>&1; then DB_UP=1; break; fi
  sleep 1
done
if [ "$DB_UP" = 1 ]; then
  ok "Database is open and accepting connections ($DB_HOST:$DB_PORT)"
else
  fail "Database did not accept app-user connections after 20s."
fi

# Verify server identity every run -- refuse to treat a non-MySQL server
# (e.g. MariaDB restored by mistake on the same port) as healthy.
if [ "$DB_UP" = 1 ]; then
  ENGINE_INFO=$(mysql_app -sN -e "SELECT VERSION(), @@version_comment;" 2>/dev/null)
  if echo "$ENGINE_INFO" | grep -qi mariadb; then
    fail "Connected server identifies as MariaDB, not Oracle MySQL: $ENGINE_INFO"
    DB_UP=0
  elif [ -n "$ENGINE_INFO" ]; then
    ok "Engine verified: $(echo "$ENGINE_INFO" | tr '\n' ' ')"
  else
    warn "Could not read server version/comment to verify engine identity"
  fi
fi

if [ "$DB_UP" = 1 ] && [ "$RESET_DB" = 1 ]; then
  log "  ... (--reset-db) rebuilding the database from scratch"
  mysql_app -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
fi

if [ "$DB_UP" = 1 ]; then
  HAS_TBL=$(mysql_app -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null)
  if [ "${HAS_TBL:-0}" -ge 20 ] 2>/dev/null; then
    ok "Database '$DB_NAME' exists (${HAS_TBL} tables)"
  else
    warn "Database is missing/incomplete — auto-repairing now (schema.sql + demo_seed.sql)"
    mysql_app "$DB_NAME" < "$PROJECT_DIR/database/schema.sql" 2>>"$REPORT" \
      && ok "Applied schema.sql" || fail "schema.sql failed (app user needs CREATE/ALTER/INDEX on $DB_NAME)"
    mysql_app "$DB_NAME" < "$PROJECT_DIR/database/demo_seed.sql" 2>>"$REPORT" \
      && ok "Loaded demo_seed.sql" || fail "demo_seed.sql failed"
  fi

  PHP_DB=$("$PHP_BIN" -c "$PHP_INI" -r '
    $m=@new mysqli("'"$DB_HOST"'","'"$DB_USER"'","'"$DB_PASS"'","'"$DB_NAME"'",'"$DB_PORT"');
    if($m->connect_errno){echo "ERR:".$m->connect_error;exit;}
    $r=$m->query("SELECT COUNT(*) c FROM users"); $x=$r?$r->fetch_assoc():["c"=>"?"];
    echo "OK:".$m->server_info.":users=".$x["c"];' 2>&1)
  case "$PHP_DB" in
    OK:*) ok "PHP <-> MySQL via mysqli: ${PHP_DB#OK:}" ;;
    *)    fail "PHP cannot connect to the database: $PHP_DB" ;;
  esac
fi

# ============================================================================
#  Phase 3: Frontend — PHP web server (with the /feedback routing fix)
# ============================================================================
head_ "Phase 3: Frontend — PHP web server"

BIND_HOST="0.0.0.0"
if ip -o -4 addr show 2>/dev/null | grep -qw "$HTTP_HOST"; then
  ok "$HTTP_HOST is assigned to this machine (URL will be reachable)"
else
  warn "$HTTP_HOST is not currently assigned on this machine — server still listens on all interfaces"
fi

nohup "$PHP_BIN" -c "$PHP_INI" -S "$BIND_HOST:$HTTP_PORT" -t "$PROJECT_DIR" "$ROUTER" \
     > "$LOG_DIR/php-server.log" 2>&1 &
echo $! > "$RUN_DIR/php-server.pid"
disown 2>/dev/null || true
log "  ... started PHP server (PID: $(cat "$RUN_DIR/php-server.pid"))"

WEB_UP=0
for i in $(seq 1 15); do
  if wget -q --timeout=10 --tries=1 -O /dev/null "http://127.0.0.1:$HTTP_PORT/" 2>/dev/null; then WEB_UP=1; break; fi
  sleep 1
done
[ "$WEB_UP" = 1 ] && ok "Web server responds on port $HTTP_PORT" \
                  || fail "Web server is not responding. Check $LOG_DIR/php-server.log"

# ============================================================================
#  Phase 4: Health checks
# ============================================================================
head_ "Phase 4: Page and API health checks"

BASE="http://127.0.0.1:$HTTP_PORT"
HDR="$RUN_DIR/.hc_hdr"; BODY="$RUN_DIR/.hc_body"

check_url() {
  local path="$1" expect="$2" label="$3"
  timeout 15 wget -q --timeout=10 --tries=1 -S -O "$BODY" "$BASE$path" 2>"$HDR"
  local code; code=$(grep -m1 'HTTP/' "$HDR" | awk '{print $2}')
  local err;  err=$(grep -ioE 'Fatal error|Parse error|Uncaught|call to undefined|mysqli_sql_exception' "$BODY" 2>/dev/null | head -1)
  if [ -n "$err" ]; then
    fail "$label — PHP error on page: $err  ($path)"
  elif echo "${code:-000}" | grep -qE "$expect"; then
    ok "$label — HTTP ${code}  ($path)"
  else
    fail "$label — unexpected status: HTTP ${code:-?}  ($path)"
  fi
}

# check_redirects_to_feedback <path> <label> — regression test for the bug
# where short URLs like /feedback silently served the home page.
check_redirects_to_feedback() {
  local path="$1" label="$2"
  timeout 15 wget -q --timeout=10 --tries=1 -O "$BODY" "$BASE$path" 2>/dev/null
  if grep -qi 'feedback-modal\|feedbackOpinion\|yourRating' "$BODY" 2>/dev/null; then
    ok "$label — correctly serves the feedback kiosk page ($path)"
  elif grep -qE 'btn-prima|IdealQ - home' "$BODY" 2>/dev/null; then
    fail "$label — BUG: serves the HOME PAGE instead of the feedback page ($path)"
  else
    fail "$label — unexpected content ($path)"
  fi
}

if [ "$WEB_UP" = 1 ]; then
  check_url "/"                                     '^(200)$'     "Home page"
  check_url "/beaa/admin/account/login.php"         '^(200)$'     "Admin login page"
  check_url "/beaa/feedback/"                       '^(200)$'     "Feedback screen (full path)"
  check_url "/beaa/counter/?id=1"                   '^(200)$'     "Clerk counter"
  check_url "/beaa/display/?id=1"                   '^(200)$'     "Counter display"
  check_url "/beaa/bigdisplay/?id=1"                '^(200)$'     "Big display"
  check_url "/beaa/api/kiosk/get.php?id=0&kiosk=1&language=ar"  '^(200)$'  "API: kiosk service list"
  check_url "/beaa/api/checkupdate.php?display=1"   '^(200)$'     "API: check update"

  # Regression checks for the reported bug (short URL, matches what the
  # kiosk devices on the LAN were actually requesting per the server log).
  check_redirects_to_feedback "/feedback"  "Short URL /feedback"
  check_redirects_to_feedback "/feedback/" "Short URL /feedback/"

  # End-to-end admin auth check.
  CJ="$RUN_DIR/.hc_cookies"; rm -f "$CJ"
  timeout 15 wget -q --timeout=10 --tries=1 --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
       -O /dev/null "$BASE/beaa/admin/account/login.php" 2>/dev/null
  LOGIN_CODE=$(timeout 15 wget -q --timeout=12 --tries=1 --keep-session-cookies --save-cookies "$CJ" --load-cookies "$CJ" \
       --max-redirect=0 \
       --post-data="username=admin.demo@example.com&password=AdminDemo@123" \
       -S -O /dev/null "$BASE/beaa/admin/account/login.php" 2>&1 | grep -m1 'HTTP/' | awk '{print $2}')
  if [ "${LOGIN_CODE:-}" = "302" ]; then
    timeout 15 wget -q --timeout=10 --tries=1 --load-cookies "$CJ" -O "$BODY" "$BASE/beaa/admin/index.php" 2>/dev/null
    if grep -qi 'mainPage\|iDEAL' "$BODY" 2>/dev/null && ! grep -qiE 'Fatal|Uncaught' "$BODY"; then
      ok "Admin login + dashboard work end-to-end"
    else
      fail "Admin login succeeded but the dashboard has an issue"
    fi
  else
    fail "Admin login did not return the expected redirect (HTTP ${LOGIN_CODE:-?})"
  fi

  # Real write check: issue a kiosk ticket, confirm it lands in the database.
  if [ "$DB_UP" = 1 ]; then
    BEFORE=$(mysql_root -sN -e "SELECT COUNT(*) FROM $DB_NAME.events;" 2>/dev/null)
    TID=$(timeout 15 wget -q --timeout=12 --tries=1 -O - "$BASE/beaa/api/kiosk/set.php?category=1&kiosk=1&lang=ar" 2>/dev/null)
    AFTER=$(mysql_root -sN -e "SELECT COUNT(*) FROM $DB_NAME.events;" 2>/dev/null)
    if [ "${AFTER:-0}" -gt "${BEFORE:-0}" ] 2>/dev/null; then
      ok "Ticket issuance works (events: $BEFORE -> $AFTER, ticket: $TID)"
    else
      warn "Ticket issuance did not increase the event count — response: $TID"
    fi
  fi

  # Final sanity check against the exact URL that was reported broken.
  check_redirects_to_feedback "/feedback" "Reported URL http://$HTTP_HOST:$HTTP_PORT/feedback"

  rm -f "$HDR" "$BODY" "$CJ"
fi

# ============================================================================
#  Final report
# ============================================================================
head_ "Final report"
log "  ${C_OK}PASS: $PASS${C_0}   ${C_WARN}WARN: $WARN${C_0}   ${C_ERR}FAIL: $FAIL${C_0}"
if [ "$FAIL" -gt 0 ]; then
  log ""
  log "  ${C_ERR}Errors:${C_0}"
  for e in "${FAILURES[@]}"; do log "   - $e"; done
fi

log ""
log "----------------------------------------------------------------"
if [ "$FAIL" -eq 0 ]; then
  log "  ${C_OK}System is fully up and the /feedback bug is fixed.${C_0}"
else
  log "  ${C_ERR}System is up but has issues — see the errors above.${C_0}"
fi
log ""
log "  Links:"
log "    Home         : http://$HTTP_HOST:$HTTP_PORT/"
log "    Admin login  : http://$HTTP_HOST:$HTTP_PORT/beaa/admin/account/login.php"
log "    Clerk counter: http://$HTTP_HOST:$HTTP_PORT/beaa/counter/?id=1"
log "    Feedback     : http://$HTTP_HOST:$HTTP_PORT/feedback"
log "    Big display  : http://$HTTP_HOST:$HTTP_PORT/beaa/bigdisplay/?id=1"
log "    Display      : http://$HTTP_HOST:$HTTP_PORT/beaa/display/?id=1"
log ""
log "  Admin login : admin.demo@example.com / AdminDemo@123"
log "  Stop with   : bash scripts/stop_meqs.sh"
log "  This report : $REPORT"
log "----------------------------------------------------------------"

[ "$FAIL" -eq 0 ] && exit 0 || exit 1
