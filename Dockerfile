FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Tắt GSSAPI (Kerberos) encryption mode để tránh lỗi rớt SSL handshake trên Render
ENV PGGSSENCMODE=disable

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

# Start command - bỏ các lệnh cache để Laravel nhận chuẩn xác biến môi trường từ Render
CMD sh -c "php artisan serve --host=0.0.0.0 --port=8000"
