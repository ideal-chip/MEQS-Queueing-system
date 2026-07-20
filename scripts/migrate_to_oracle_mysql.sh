#!/usr/bin/env bash
# ============================================================================
# MEQS — MariaDB -> Oracle MySQL 8.4 data migration (Phases 4-6)
#
# Run this AFTER Oracle MySQL 8.4 is installed and running as the "mysql"
# systemd service (see backups/mysql-migration-*/oracle-mysql-install-commands.sh).
# This script itself needs no root/sudo — only MySQL client access.
#
# What it does:
#   1. Creates the project database + a scoped app user in the new MySQL
#      instance (idempotent: safe to re-run).
#   2. Imports the latest full-dump.sql backup (schema + data + FKs).
#   3. Verifies row counts match the source MariaDB instance exactly.
#   4. Prints the new credentials to save into .env (never into git).
#
# It does NOT touch MariaDB, does NOT delete anything, and does NOT modify
# application code — that's a separate, reviewable step once this succeeds.
# ============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# ---- Oracle MySQL connection (root, for provisioning only) ----------------
MYSQL_NEW_HOST="${MYSQL_NEW_HOST:-127.0.0.1}"
MYSQL_NEW_PORT="${MYSQL_NEW_PORT:-3306}"
MYSQL_NEW_ROOT_USER="${MYSQL_NEW_ROOT_USER:-root}"
# MYSQL_NEW_ROOT_PASSWORD: export this yourself before running, or you'll be
# prompted. Never hardcode it here or pass it as a bare -p flag (shows in ps).

# ---- New project database + scoped app user --------------------------------
DB_NAME="${DB_NAME:-project_demo_db}"
DB_USER="${DB_USER:-project_demo_user}"
# Generate a fresh strong password; do not reuse the MariaDB-era one.
DB_PASS="$(openssl rand -base64 24 | tr -d '=+/' | cut -c1-24)"

# ---- Locate the latest backup ----------------------------------------------
LATEST_BACKUP_DIR="$(ls -dt "$PROJECT_DIR"/backups/mysql-migration-*/ 2>/dev/null | head -1)"
if [ -z "$LATEST_BACKUP_DIR" ]; then
    echo "No backup found under backups/mysql-migration-*/. Run the backup step first." >&2
    exit 1
fi
DUMP_FILE="${LATEST_BACKUP_DIR}full-dump.sql"
ROWCOUNTS_BEFORE="${LATEST_BACKUP_DIR}row-counts-exact-before.txt"
[ -f "$DUMP_FILE" ] || { echo "Missing $DUMP_FILE" >&2; exit 1; }

echo "[migrate] Using backup: $LATEST_BACKUP_DIR"

MYSQL="mysql -h$MYSQL_NEW_HOST -P$MYSQL_NEW_PORT -u$MYSQL_NEW_ROOT_USER -p"

# ---- 1) Create database + scoped user (idempotent) -------------------------
"$MYSQL" <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
-- Scoped grants only: no SUPER/FILE/PROCESS/CREATE USER, no access to other schemas.
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, DROP
    ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, DROP
    ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL
echo "[migrate] Database + user created."

# ---- 2) Import the full dump ------------------------------------------------
echo "[migrate] Importing $DUMP_FILE ..."
"$MYSQL" "$DB_NAME" < "$DUMP_FILE"
echo "[migrate] Import complete."

# ---- 3) Verify engine identity ----------------------------------------------
ENGINE_INFO="$("$MYSQL" -N -e "SELECT VERSION(), @@version_comment;")"
echo "[migrate] Engine: $ENGINE_INFO"
if echo "$ENGINE_INFO" | grep -qi mariadb; then
    echo "[migrate] FATAL: target server reports MariaDB, not Oracle MySQL. Aborting verification." >&2
    exit 1
fi

# ---- 4) Row count diff -------------------------------------------------------
echo "[migrate] Comparing row counts against pre-migration snapshot..."
DIFF_FOUND=0
while IFS=$'\t' read -r table before_count; do
    [ -z "$table" ] && continue
    after_count=$("$MYSQL" -N "$DB_NAME" -e "SELECT COUNT(*) FROM \`$table\`;" 2>/dev/null || echo "ERROR")
    if [ "$after_count" != "$before_count" ]; then
        echo "  MISMATCH: $table  before=$before_count after=$after_count"
        DIFF_FOUND=1
    else
        echo "  OK: $table = $after_count"
    fi
done < "$ROWCOUNTS_BEFORE"

if [ "$DIFF_FOUND" = "1" ]; then
    echo "[migrate] FATAL: row count mismatch detected. Do NOT proceed to cut the app over." >&2
    exit 1
fi

echo
echo "=================================================================="
echo "[migrate] SUCCESS. All row counts match. New credentials (save into"
echo ".env now — this is the only time the password is printed):"
echo "  DB_HOST=$MYSQL_NEW_HOST"
echo "  DB_PORT=$MYSQL_NEW_PORT"
echo "  DB_NAME=$DB_NAME"
echo "  DB_USER=$DB_USER"
echo "  DB_PASSWORD=$DB_PASS"
echo "=================================================================="
