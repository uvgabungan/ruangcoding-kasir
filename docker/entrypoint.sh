#!/bin/sh
set -e

# Default to sqlite if DB_CONNECTION is not set or set to sqlite
if [ -z "$DB_CONNECTION" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating sqlite database..."
        touch /var/www/html/database/database.sqlite
    fi
fi

# Ensure storage directories exist with right permissions
mkdir -p /var/www/html/storage/app/public/reports
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Link storage
php artisan storage:link --force || true

# Run migrations and setup database automatically
echo "Running migrations..."
php artisan migrate --force || true

echo "Checking and seeding default data..."
php artisan db:seed --force || true
php artisan tinker --execute="App\Models\User::updateOrCreate(['email' => 'admin@gmail.com'], ['name' => 'Owner Toko', 'password' => 'admin123']);" || true

echo "Setting up Filament Shield permissions..."
php artisan shield:generate --all --no-interaction || true
php artisan shield:super-admin --user=1 || true
php artisan tinker --execute="\$role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']); \$u = \App\Models\User::where('email', 'admin@gmail.com')->first(); if (\$u) { \$u->assignRole(\$role); }" || true

# Clear caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Start php-fpm in background
php-fpm -D

# Start Nginx in foreground
echo "Nginx starting on port 80..."
exec nginx -g "daemon off;"
