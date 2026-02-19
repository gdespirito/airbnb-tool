# Stage 1: Install PHP dependencies
FROM composer:2 AS php-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction

# Stage 2: Build frontend assets (needs PHP for Wayfinder plugin)
FROM node:22-alpine AS build-assets
RUN apk add --no-cache php84 php84-tokenizer php84-mbstring php84-phar php84-openssl php84-ctype php84-session php84-xml php84-dom php84-xmlwriter php84-pdo php84-pdo_sqlite php84-fileinfo php84-curl && ln -sf /usr/bin/php84 /usr/bin/php
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=php-deps /app/vendor ./vendor
RUN cp .env.example .env && rm -f bootstrap/cache/*.php && php artisan key:generate --no-interaction && npm run build && rm .env

# Stage 3: Final application image
FROM php:8.4-fpm-alpine AS app

# Install system dependencies and PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apk add --no-cache nginx supervisor sqlite curl && \
    install-php-extensions pdo_mysql pdo_sqlite mbstring xml curl zip bcmath pcntl

WORKDIR /var/www/html

# Copy supervisor and nginx configs
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy composer dependencies
COPY --from=php-deps /app/vendor ./vendor

# Copy built frontend assets
COPY --from=build-assets /app/public/build ./public/build

# Copy application code
COPY . .

# Remove dev files not needed in production
RUN rm -rf node_modules tests .git docker .dockerignore bootstrap/cache/*.php

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy and set entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

ENTRYPOINT ["/entrypoint.sh"]
