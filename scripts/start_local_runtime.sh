#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="$PROJECT_DIR/.runtime/static-php/buildroot/bin/php"
MYSQLD_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadbd"
MYSQL_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadb"
RUN_DIR="$PROJECT_DIR/.runtime/run"
LOG_DIR="$PROJECT_DIR/.runtime/logs"
MYSQL_DATA="$PROJECT_DIR/.runtime/mysql-data"

mkdir -p "$RUN_DIR" "$LOG_DIR"

if ! pgrep -f "$MYSQLD_BIN.*$MYSQL_DATA" >/dev/null 2>&1; then
  "$MYSQLD_BIN" \
    --no-defaults \
    --basedir="$PROJECT_DIR/.runtime/mariadb" \
    --datadir="$MYSQL_DATA" \
    --socket="$RUN_DIR/mysql.sock" \
    --pid-file="$RUN_DIR/mysql.pid" \
    --port=3307 \
    --bind-address=127.0.0.1 \
    --log-error="$LOG_DIR/mariadb.err" &
  echo $! > "$RUN_DIR/mariadb.wrapper.pid"
fi

for _ in $(seq 1 30); do
  if "$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P3307 -uroot -e "SELECT 1" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

if ! pgrep -f "$PHP_BIN -S 127.0.0.1:8000" >/dev/null 2>&1; then
  (cd "$PROJECT_DIR" && "$PHP_BIN" -S 127.0.0.1:8000 > "$LOG_DIR/php-server.log" 2>&1 & echo $! > "$RUN_DIR/php-server.pid")
fi

echo "MEQS local runtime is running:"
echo "  Admin:        http://127.0.0.1:8000/beaa/admin/account/login.php"
echo "  Counter:      http://127.0.0.1:8000/beaa/counter/"
echo "  File browser: http://127.0.0.1:8000/beaa/admin/file-browser.php"
