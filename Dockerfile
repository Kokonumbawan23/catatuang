# =========================
# Stage 1 - Frontend (Vite)
# =========================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY vite.config.js tsconfig.json ./
COPY resources/js ./resources/js
RUN npm run build


# =========================
# Stage 2 - PHP / Laravel
# =========================
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libgmp-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gmp bcmath \
    && docker-php-ext-enable pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

COPY --from=frontend /app/public/build ./public/build
COPY --from=frontend /app/public/sw.js ./public/sw.js

ENTRYPOINT ["./docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
