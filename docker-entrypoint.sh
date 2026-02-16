#!/bin/bash
set -e

# Generate .env from Railway environment variables
# Railway injects env vars into the container — this writes them to .env so Laravel can read them
cat > /app/.env <<EOF
APP_NAME="${APP_NAME:-Krayin CRM}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL}"
APP_TIMEZONE="${APP_TIMEZONE:-UTC}"
APP_LOCALE="${APP_LOCALE:-en}"
APP_CURRENCY="${APP_CURRENCY:-USD}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-${MYSQLHOST}}"
DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE}}"
DB_USERNAME="${DB_USERNAME:-${MYSQLUSER}}"
DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD}}"
DB_PREFIX="${DB_PREFIX}"

BROADCAST_DRIVER="${BROADCAST_DRIVER:-log}"
CACHE_DRIVER="${CACHE_DRIVER:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"

MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST}"
MAIL_PORT="${MAIL_PORT:-587}"
MAIL_USERNAME="${MAIL_USERNAME}"
MAIL_PASSWORD="${MAIL_PASSWORD}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-Krayin CRM}"
EOF

# Mark app as already installed — prevents installer wizard from wiping the database
touch /app/storage/installed

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

# Run pending migrations (safe — only applies new ones, never wipes)
php artisan migrate --force

# Clear and cache config for performance
php artisan config:clear
php artisan config:cache

# Start the server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
