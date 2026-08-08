FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-client \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    bcmath \
    pcntl

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (for caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# Copy rest of the application
COPY . .

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start command
CMD sh -c "php artisan config:cache && \
           php artisan route:cache && \
           php artisan view:cache && \
           php artisan migrate --force && \
           php artisan serve --host=0.0.0.0 --port=8000"
