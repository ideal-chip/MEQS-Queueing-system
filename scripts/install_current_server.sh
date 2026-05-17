#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_HOST="${APP_HOST:-127.0.0.1}"
APP_PORT="${APP_PORT:-8000}"

if [ "$(id -u)" -ne 0 ]; then
  echo "Run as root: sudo bash scripts/install_current_server.sh"
  exit 1
fi

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y php php-cli php-mysqli mysql-server nginx curl

cat > "$PROJECT_DIR/.env" <<ENV
APP_ENV=local
APP_BASE_URL=http://$APP_HOST:$APP_PORT
DB_HOST=localhost
DB_PORT=3306
DB_NAME=project_demo_db
DB_USER=project_demo_user
DB_PASSWORD=ProjectDemo@12345
PHP_SERVER_HOST=$APP_HOST
PHP_SERVER_PORT=$APP_PORT
ENV

systemctl enable --now mysql || systemctl enable --now mysqld || true

mysql -uroot < "$PROJECT_DIR/database/create_demo_database.sql"
mysql -uroot project_demo_db < "$PROJECT_DIR/database/schema.sql"
mysql -uroot project_demo_db < "$PROJECT_DIR/database/demo_seed.sql"

cat > /etc/systemd/system/meqs-demo.service <<SERVICE
[Unit]
Description=MEQS iDEAL-Q Demo PHP Server
After=network.target mysql.service

[Service]
Type=simple
WorkingDirectory=$PROJECT_DIR
ExecStart=/usr/bin/php -S $APP_HOST:$APP_PORT
Restart=always
RestartSec=3
User=$(logname 2>/dev/null || echo idealchip_server)
Environment=APP_ENV=local

[Install]
WantedBy=multi-user.target
SERVICE

systemctl daemon-reload
systemctl enable --now meqs-demo.service

echo "Installed and started:"
echo "  http://$APP_HOST:$APP_PORT/beaa/admin/account/login.php"
echo "  http://$APP_HOST:$APP_PORT/beaa/counter/"
echo "Run smoke tests:"
echo "  BASE_URL=http://$APP_HOST:$APP_PORT $PROJECT_DIR/tests/smoke.sh"
