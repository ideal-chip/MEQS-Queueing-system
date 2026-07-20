#!/usr/bin/env bash
# ============================================================================
#  MEQS / iDEAL-Q  —  Working local runtime launcher
#  Starts MariaDB (portable build) + PHP 8.1 built-in server with the locally
#  compiled mysqli + mbstring extensions. Created during the repair pass.
#
#  Why this file exists:
#    The original scripts/start_local_runtime.sh points at
#    .runtime/static-php/buildroot/bin/php, which requires GLIBC 2.38 and does
#    NOT run on this machine (GLIBC 2.35). This launcher uses the conda PHP
#    8.1.13 build under .runtime/env, loaded with .runtime/php-ext/php.ini
#    (mysqli.so + mbstring.so built for this exact CPU/GLIBC).
# ============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

PHP_BIN="$PROJECT_DIR/.runtime/env/bin/php"
PHP_INI="$PROJECT_DIR/.runtime/php-ext/php.ini"
MYSQLD_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadbd"
MYSQL_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadb"
RUN_DIR="$PROJECT_DIR/.runtime/run"
LOG_DIR="$PROJECT_DIR/.runtime/logs"
MYSQL_DATA="$PROJECT_DIR/.runtime/mysql-data"

DB_PORT=3307
HTTP_HOST="0.0.0.0"
HTTP_PORT=8000

mkdir -p "$RUN_DIR" "$LOG_DIR" "$RUN_DIR/sessions"

# ---- 1) MariaDB ------------------------------------------------------------
if ! pgrep -f "$MYSQLD_BIN.*$MYSQL_DATA" >/dev/null 2>&1; then
  echo "[MEQS] starting MariaDB on 127.0.0.1:$DB_PORT ..."
  "$MYSQLD_BIN" \
    --no-defaults \
    --basedir="$PROJECT_DIR/.runtime/mariadb" \
    --datadir="$MYSQL_DATA" \
    --socket="$RUN_DIR/mysql.sock" \
    --pid-file="$RUN_DIR/mysql.pid" \
    --port="$DB_PORT" \
    --bind-address=127.0.0.1 \
    --log-error="$LOG_DIR/mariadb.err" &
  echo $! > "$RUN_DIR/mariadb.wrapper.pid"
else
  echo "[MEQS] MariaDB already running."
fi

# wait for the server to accept connections
for _ in $(seq 1 30); do
  if "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot -e "SELECT 1" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

# ---- 2) Ensure the demo database exists ------------------------------------
if ! "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot \
      -e "USE project_demo_db; SELECT 1 FROM zones LIMIT 1;" >/dev/null 2>&1; then
  echo "[MEQS] bootstrapping project_demo_db (schema + demo seed) ..."
  "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot < "$PROJECT_DIR/database/create_demo_database.sql"
  "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot project_demo_db < "$PROJECT_DIR/database/schema.sql"
  "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P"$DB_PORT" -uroot project_demo_db < "$PROJECT_DIR/database/demo_seed.sql"
else
  echo "[MEQS] project_demo_db already present."
fi

# ---- 3) PHP built-in server ------------------------------------------------
if ! pgrep -f "$PHP_BIN -c $PHP_INI -S $HTTP_HOST:$HTTP_PORT" >/dev/null 2>&1; then
  echo "[MEQS] starting PHP $("$PHP_BIN" -r 'echo PHP_VERSION;') server on $HTTP_HOST:$HTTP_PORT ..."
  ( cd "$PROJECT_DIR" && "$PHP_BIN" -c "$PHP_INI" -S "$HTTP_HOST:$HTTP_PORT" -t "$PROJECT_DIR" "$PROJECT_DIR/router.php" \
      > "$LOG_DIR/php-server.log" 2>&1 & echo $! > "$RUN_DIR/php-server.pid" )
else
  echo "[MEQS] PHP server already running."
fi

SERVER_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"
cat <<EOF

[MEQS] runtime is up.
  Loaded PHP extensions : mysqli (local build) + mbstring (local build)
  Local   : http://127.0.0.1:$HTTP_PORT/
  Network : http://${SERVER_IP:-<ip>}:$HTTP_PORT/

  Home        : http://127.0.0.1:$HTTP_PORT/
  Admin login : http://127.0.0.1:$HTTP_PORT/beaa/admin/account/login.php
  Counter     : http://127.0.0.1:$HTTP_PORT/beaa/counter/?id=1
  Feedback    : http://127.0.0.1:$HTTP_PORT/beaa/feedback/
  Big display : http://127.0.0.1:$HTTP_PORT/beaa/bigdisplay/?id=1
  Display     : http://127.0.0.1:$HTTP_PORT/beaa/display/?id=1

  Demo admin  : admin.demo@example.com / AdminDemo@123
  Stop with   : bash scripts/stop_meqs.sh
EOF
