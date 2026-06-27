FROM node:22-alpine AS assets

WORKDIR /app

# maryUI's Tailwind sources live in Composer's vendor directory and its
# installer is an artisan command, so the asset image needs PHP + Composer.
RUN apk add --no-cache \
    composer \
    php84 \
    php84-bcmath \
    php84-ctype \
    php84-curl \
    php84-dom \
    php84-fileinfo \
    php84-gd \
    php84-iconv \
    php84-intl \
    php84-mbstring \
    php84-openssl \
    php84-pcntl \
    php84-pdo \
    php84-pdo_mysql \
    php84-phar \
    php84-session \
    php84-simplexml \
    php84-sodium \
    php84-tokenizer \
    php84-xml \
    php84-xmlwriter \
    php84-zip \
    && ln -sf /usr/bin/php84 /usr/bin/php

# Build Vite assets so Laravel can read public/build/manifest.json
COPY composer.json composer.lock package*.json ./
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts
RUN npm install
COPY . .
RUN mkdir -p \
    storage/app \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi
RUN php artisan mary:install --npm --no-css
RUN npm run build

FROM php:8.4.16-fpm

# Install system dependencies and PHP extension build deps
RUN apt-get update && apt-get install -y \
    git \
    vim \
    curl \
    unzip \
    zip \
    supervisor \
    nginx \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libsodium-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD before installing it
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    sodium

# Install phpredis for Laravel's default REDIS_CLIENT=phpredis configuration
RUN pecl install redis \
    && docker-php-ext-enable redis

# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy built Vite assets into the PHP image for Laravel's @vite manifest lookup
COPY --from=assets /app/public/build /var/www/html/public/build

# Copy configs
COPY ./docker/supervisord.conf /etc/supervisord.conf
# COPY ./docker/nginx.conf /etc/nginx/sites-available/default

# Create Laravel writable directories before Composer runs artisan scripts
RUN mkdir -p \
    /var/www/html/storage/app \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# Install production Composer dependencies during the image build
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
