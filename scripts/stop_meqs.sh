#!/usr/bin/env bash
# ============================================================================
#  MEQS / iDEAL-Q  —  Stop the local runtime (PHP server + MariaDB)
# ============================================================================
set -uo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR="$PROJECT_DIR/.runtime/run"
MYSQL_BIN="$PROJECT_DIR/.runtime/mariadb/bin/mariadb"

# ---- PHP server ----
if [ -f "$RUN_DIR/php-server.pid" ]; then
  kill "$(cat "$RUN_DIR/php-server.pid")" 2>/dev/null && echo "[MEQS] PHP server stopped."
  rm -f "$RUN_DIR/php-server.pid"
fi
pkill -f "env/bin/php -c .*-S 0.0.0.0:8000" 2>/dev/null || true

# ---- MariaDB (graceful shutdown) ----
"$MYSQL_BIN" --protocol=TCP -h127.0.0.1 -P3307 -uroot \
  -e "SHUTDOWN;" >/dev/null 2>&1 && echo "[MEQS] MariaDB shutdown requested."
sleep 1
if [ -f "$RUN_DIR/mariadb.wrapper.pid" ]; then
  kill "$(cat "$RUN_DIR/mariadb.wrapper.pid")" 2>/dev/null || true
  rm -f "$RUN_DIR/mariadb.wrapper.pid"
fi
echo "[MEQS] done."
