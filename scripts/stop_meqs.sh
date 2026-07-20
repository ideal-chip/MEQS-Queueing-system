#!/usr/bin/env bash
# ============================================================================
#  MEQS / iDEAL-Q  —  Stop the local runtime (PHP web server only)
#
#  The database is Oracle MySQL 8.4, running as the systemd service "mysql".
#  This script does not stop it -- that's a deliberate, separate action
#  (`sudo systemctl stop mysql`), not something a routine app-restart script
#  should do silently. The old portable MariaDB under .runtime/mariadb is
#  no longer used by the app at all; it's left untouched here.
# ============================================================================
set -uo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR="$PROJECT_DIR/.runtime/run"

if [ -f "$RUN_DIR/php-server.pid" ]; then
  kill "$(cat "$RUN_DIR/php-server.pid")" 2>/dev/null && echo "[MEQS] PHP server stopped."
  rm -f "$RUN_DIR/php-server.pid"
fi
pkill -f "env/bin/php -c .*-S 0.0.0.0:8000" 2>/dev/null || true

echo "[MEQS] done. (MySQL service left running -- stop it yourself with: sudo systemctl stop mysql)"
