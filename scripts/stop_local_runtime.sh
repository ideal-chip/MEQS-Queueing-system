#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR="$PROJECT_DIR/.runtime/run"

if [ -f "$RUN_DIR/php-server.pid" ]; then
  kill "$(cat "$RUN_DIR/php-server.pid")" 2>/dev/null || true
  rm -f "$RUN_DIR/php-server.pid"
fi

if [ -f "$RUN_DIR/mysql.pid" ]; then
  kill "$(cat "$RUN_DIR/mysql.pid")" 2>/dev/null || true
  rm -f "$RUN_DIR/mysql.pid"
fi

echo "MEQS local runtime stopped."
