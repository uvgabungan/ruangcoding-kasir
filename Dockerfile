FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    zip \
    unzip \
    git \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Set default env during build
ENV APP_KEY="base64:avPjIy5XvwOD7VeojYlezL5BuDjtOXqHvJf/lT47VuE="
ENV APP_DEBUG="true"
ENV DB_CONNECTION="sqlite"
ENV FILESYSTEM_DISK="s3"
ENV FILAMENT_FILESYSTEM_DISK="s3"
ENV AWS_ACCESS_KEY_ID="72234a70cda7637f4c8c979a0f4fa26d"
ENV AWS_SECRET_ACCESS_KEY="4c3721a5b0f4a747d127da9a5d7ee0b10f6292931aaa875668e881712bfa5cc1"
ENV AWS_DEFAULT_REGION="us-east-1"
ENV AWS_BUCKET="kasir-storage"
ENV AWS_URL="https://pub-929c060eb04f4e5396ff48eb59c53f4f.r2.dev"
ENV AWS_ENDPOINT="https://d2ebaace9dce0f041a800a3a16a396a6.r2.cloudflarestorage.com"
ENV AWS_USE_PATH_STYLE_ENDPOINT="true"

# Copy source code
COPY . .

# Install dependencies and S3/R2 storage driver
RUN composer require league/flysystem-aws-s3-v3 --update-no-dev --no-interaction --optimize-autoloader

# Setup Nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Setup permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod +x docker/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker/entrypoint.sh"]
