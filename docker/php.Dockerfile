FROM node:22-alpine AS assets

WORKDIR /app

# Build Vite assets so Laravel can read public/build/manifest.json
COPY package*.json ./
RUN npm install
COPY . .
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
