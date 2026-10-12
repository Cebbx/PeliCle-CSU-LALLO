#!/bin/sh

# Auto-create .env if not present
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# Ensure basic production settings in .env
sed -i 's/APP_ENV=local/APP_ENV=production/g' /var/www/html/.env 2>/dev/null || true
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' /var/www/html/.env 2>/dev/null || true

# Check if external DB (PostgreSQL / MySQL) is reachable. If not, safely fall back to SQLite
php -r '
$conn = getenv("DB_CONNECTION") ?: "sqlite";
$host = getenv("DB_HOST");
$url = getenv("DATABASE_URL") ?: getenv("DB_URL");
if ($url && preg_match("@//.*?:.*?@([^:/]+)@i", $url, $m)) { $host = $m[1]; }
if ($conn === "pgsql" || ($url && str_contains($url, "postgres"))) {
    if (!$host || (!filter_var($host, FILTER_VALIDATE_IP) && gethostbyname($host) === $host)) {
        fwrite(STDERR, "External PostgreSQL host ($host) is unreachable or does not resolve.\n");
        exit(1);
    }
}
exit(0);
' 2>/dev/null || {
    echo "NOTICE: External PostgreSQL database is unreachable. Automatically falling back to persistent local SQLite."
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    unset DATABASE_URL
    unset DB_URL
    unset DB_HOST
    unset DB_PORT
    unset DB_USERNAME
    unset DB_PASSWORD
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/g' /var/www/html/.env 2>/dev/null || true
}

# Prepare storage, cache, and database directories
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public \
         /var/www/html/storage/logs \
         /var/www/html/database

if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Configure dynamic PORT for Render / Koyeb / Docker host
PORT=${PORT:-80}
sed -i "s/listen .*/listen $PORT;/g" /etc/nginx/http.d/default.conf 2>/dev/null || true

# Generate APP_KEY if missing in both env and .env file
if [ -z "$APP_KEY" ] && ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    php artisan key:generate --force || true
fi

# Run setup routines safely (never let them crash container startup)
php artisan storage:link --force || true
php artisan migrate --force || true
php artisan db:seed --force || true
php artisan optimize:clear || true

# Fix permissions AFTER artisan commands so www-data owns sqlite, logs, sessions, and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Start PHP-FPM in background and Nginx in foreground
php-fpm -D
exec nginx -g 'daemon off;'
