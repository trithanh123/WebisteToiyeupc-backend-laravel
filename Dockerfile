FROM php:8.4-cli

# Install system dependencies (Debian-based để fix SSL/TLS với Render PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    openssl \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Fix SSL compatibility: dùng printf để tạo newline thật (echo \n không work trong sh)
# Force TLS 1.2 + SECLEVEL=1 để tương thích với Render PostgreSQL SSL proxy
RUN printf 'openssl_conf = openssl_init\n\n[openssl_init]\nssl_conf = ssl_sect\n\n[ssl_sect]\nsystem_default = system_default_sect\n\n[system_default_sect]\nMinProtocol = TLSv1.2\nMaxProtocol = TLSv1.2\nCipherString = DEFAULT:@SECLEVEL=1\n' > /etc/ssl/openssl-custom.cnf
ENV OPENSSL_CONF=/etc/ssl/openssl-custom.cnf
ENV PGSSLMODE=require
ENV PGSSLMAXPROTOCOLVERSION=TLSv1.2

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
    --no-progress \
    --no-scripts

# Copy rest of the application
COPY . .

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start command - migration chạy riêng, không block server start
CMD sh -c "php artisan config:cache && \
           php artisan route:cache && \
           php artisan view:cache && \
           php artisan migrate --force || echo '=== MIGRATION FAILED - CHECK DB CONNECTION ===' && \
           php artisan serve --host=0.0.0.0 --port=8000"
