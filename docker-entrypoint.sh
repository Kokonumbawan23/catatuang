#!/bin/sh
set -e

php artisan migrate --force

if [ ! -f /var/www/.seeded ]; then
    php artisan db:seed --force
    touch /var/www/.seeded
fi

exec "$@"
