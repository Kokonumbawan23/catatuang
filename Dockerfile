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
ENV SERVER_NAME=":80" 
ENV FRANKENPHP_CONFIG=""

# Native inline execution to completely bypass script line-ending bugs
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    exec frankenphp php-server --port ${PORT:-80} --root public/