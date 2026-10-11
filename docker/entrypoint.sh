#!/bin/sh
set -e

# Generate .env dynamically so Laravel Dotenv and PHP-FPM always have the Cloudflare R2 credentials
cat <<EOF > /var/www/html/.env
APP_NAME="${APP_NAME:-Kasir Ruang Coding}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-base64:avPjIy5XvwOD7VeojYlezL5BuDjtOXqHvJf/lT47VuE=}"
APP_DEBUG="${APP_DEBUG:-true}"
APP_URL="${APP_URL:-http://localhost}"
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
SESSION_DRIVER="${SESSION_DRIVER:-database}"
CACHE_STORE="${CACHE_STORE:-database}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-s3}"
FILAMENT_FILESYSTEM_DISK="${FILAMENT_FILESYSTEM_DISK:-s3}"
AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID:-72234a70cda7637f4c8c979a0f4fa26d}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY:-4c3721a5b0f4a747d127da9a5d7ee0b10f6292931aaa875668e881712bfa5cc1}"
AWS_DEFAULT_REGION="${AWS_DEFAULT_REGION:-us-east-1}"
AWS_BUCKET="${AWS_BUCKET:-kasir-storage}"
AWS_URL="${AWS_URL:-https://pub-929c060eb04f4e5396ff48eb59c53f4f.r2.dev}"
AWS_ENDPOINT="${AWS_ENDPOINT:-https://d2ebaace9dce0f041a800a3a16a396a6.r2.cloudflarestorage.com}"
AWS_USE_PATH_STYLE_ENDPOINT="${AWS_USE_PATH_STYLE_ENDPOINT:-true}"
EOF
chown www-data:www-data /var/www/html/.env
chmod 644 /var/www/html/.env

# Ensure PHP-FPM preserves environment variables
sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true
echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

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
# Final ownership and permissions fix after all artisan commands run as root
mkdir -p /var/www/html/storage/logs
touch /var/www/html/storage/logs/laravel.log
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 666 /var/www/html/storage/logs/laravel.log 2>/dev/null || true
chmod 666 /var/www/html/database/database.sqlite 2>/dev/null || true

# Start php-fpm in background
php-fpm -D

# Start Nginx in foreground
echo "Nginx starting on port 80..."
exec nginx -g "daemon off;"
