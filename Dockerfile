# ==============================================================================
# Stage 1 - Frontend Assets Compilation (Vite + Vue + PWA)
# ==============================================================================
FROM node:22-alpine AS frontend

WORKDIR /app

# Install dependencies cleanly based on lockfile
COPY package*.json ./
RUN npm ci

# Copy full application code and build assets
COPY . .
RUN npm run build


# ==============================================================================
# Stage 2 - Production Web Server (FrankenPHP + PHP 8.4)
# ==============================================================================
FROM dunglas/frankenphp:1.4-php8.4-alpine AS runner

# Install necessary system utilities
RUN apk add --no-cache \
    git \
    unzip \
    zip \
    bash \
    gnu-libiconv

# Install required PHP extensions for SQLite, text handling, and caching
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    opcache \
    intl

# Bring Composer into the production stage
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application source files
COPY . .

# 🔥 FIXED: Safely sync all built assets out of the public folder at once
COPY --from=frontend /app/public/ ./public/

# Run production-optimized Composer install
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# Set correct folder permissions so the server worker can read/write files
RUN chown -R www-data:www-data /app

# Copy the dynamic entrypoint script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/run-app
RUN chmod +x /usr/local/bin/run-app

# Switch to standard secure web server user context
USER www-data

# Environment configuration defaults for the FrankenPHP engine
ENV SERVER_NAME=":80"
ENV FRANKENPHP_CONFIG=""

# Fire the entrypoint script where live Railway environment variables are active!
ENTRYPOINT ["/usr/local/bin/run-app"]