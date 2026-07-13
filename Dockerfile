# =========================
# Stage 1 - Frontend (Vite)
# =========================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# =========================
# Stage 2 - PHP / Laravel (Production)
# =========================
FROM dunglas/frankenphp:1.4-php8.4-alpine AS runner

# Install system dependencies & PHP extensions needed for SQLite
RUN apk add --no-cache \
    git \
    unzip \
    zip \
    bash \
    gnu-libiconv

RUN install-php-extensions \
    pdo_sqlite \
    zip \
    opcache \
    intl

# Get modern composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application source files
COPY . .

# Copy compiled assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Production composer installation
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# Set correct folder permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# ⚡ CRITICAL RAILWAY ADJUSTMENTS ⚡
# 1. Railway injects the $PORT variable dynamically. Tell FrankenPHP to bind to it.
ENV SERVER_NAME=":80" 

# 2. Tell FrankenPHP to run via the standard Caddy proxy layer
ENV FRANKENPHP_CONFIG=""

# 3. Create an execution entrypoint script to run migrations safely
RUN echo '#!/bin/sh\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
exec frankenphp php-server --port ${PORT:-80} --root public/\n\
' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]