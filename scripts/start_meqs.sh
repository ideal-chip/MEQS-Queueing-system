#!/usr/bin/env bash
# ============================================================================
#  MEQS / iDEAL-Q  —  Working local runtime launcher
#
#  This now just delegates to run/run_all.sh, the canonical launcher (clears
#  caches, verifies Oracle MySQL is up via systemd, starts the PHP server,
#  runs health checks). Kept as a thin wrapper so old muscle-memory/scripts
#  invoking scripts/start_meqs.sh keep working.
# ============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
exec bash "$PROJECT_DIR/run/run_all.sh" "$@"
