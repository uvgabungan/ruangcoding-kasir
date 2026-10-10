#!/bin/sh
set -e

# Cache config & route if APP_KEY is provided
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Run storage link
php artisan storage:link || true

# Run migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force || true
fi

# Start php-fpm in background
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx on port 80..."
exec nginx -g "daemon off;"
