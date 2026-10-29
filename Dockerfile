# Stage 1: Build assets with Node
FROM node:22-alpine AS nodebuild
WORKDIR /app
COPY package.json package-lock.json vite.backend.config.js ./
RUN npm install --no-progress --no-audit --no-fund

COPY . .
RUN npm run build:backend

# Stage 2: PHP + Composer + Laravel setup
# FROM php:8.3-fpm-alpine
FROM php:8.3.36-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    unzip \
    git \
    curl \
    ffmpeg \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    dcron \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring zip gd pcntl \
    && apk cache clean \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Copy built assets from Node stage
COPY --from=nodebuild /app/public ./public

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Laravel setup
# Laravel setup
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
    
# Copy configs
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/default /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh


# Expose port
EXPOSE 80

# Entrypoint
CMD ["/entrypoint.sh"]
