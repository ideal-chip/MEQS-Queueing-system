#!/usr/bin/env bash
set -u

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
MYSQL_CMD="${MYSQL_CMD:-mysql}"

failures=0

check_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "FAIL: missing command: $1"
    failures=$((failures + 1))
  else
    echo "PASS: command exists: $1"
  fi
}

check_http() {
  local label="$1"
  local url="$2"
  local expected="${3:-200}"
  local code
  code="$(curl -ks -o /tmp/meqs-smoke-body.txt -w '%{http_code}' "$url" || true)"
  if [ "$code" = "$expected" ]; then
    echo "PASS: $label ($code)"
  else
    echo "FAIL: $label expected $expected got $code"
    failures=$((failures + 1))
  fi
}

check_command php
check_command curl
check_command "$MYSQL_CMD"

if command -v "$MYSQL_CMD" >/dev/null 2>&1; then
  if "$MYSQL_CMD" -u"${DB_USER:-project_demo_user}" -p"${DB_PASSWORD:-ProjectDemo@12345}" -D"${DB_NAME:-project_demo_db}" -e "SELECT 1;" >/dev/null 2>&1; then
    echo "PASS: database connection"
  else
    echo "FAIL: database connection"
    failures=$((failures + 1))
  fi
fi

if command -v curl >/dev/null 2>&1; then
  check_http "home redirects/loads" "$BASE_URL/" "200"
  check_http "admin login page" "$BASE_URL/beaa/admin/account/login.php" "200"
  check_http "counter page" "$BASE_URL/beaa/counter/" "200"
fi

if [ "$failures" -gt 0 ]; then
  echo "Smoke tests failed: $failures"
  exit 1
fi

echo "Smoke tests passed"
