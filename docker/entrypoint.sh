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
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/g' /var/www/html/.env 2>/dev/null || true

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

# Generate APP_KEY if missing
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    php artisan key:generate --force || true
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run setup routines safely (never let them crash container startup)
php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force 2>/dev/null || true
php artisan db:seed --force 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# Start PHP-FPM in background and Nginx in foreground
php-fpm -D
exec nginx -g 'daemon off;'
