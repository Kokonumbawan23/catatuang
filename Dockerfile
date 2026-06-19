# =========================
# Stage 1 - Frontend (Vite)
# =========================
FROM node:22 AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build


# =========================
# Stage 2 - PHP / Laravel
# =========================
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# copy hasil build vite
COPY --from=frontend /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --optimize-autoloader

# Optional:
# RUN php artisan optimize:clear

CMD php artisan migrate --force \
&& php artisan db:seed --force \
&& php artisan serve --host=0.0.0.0 --port=$PORT