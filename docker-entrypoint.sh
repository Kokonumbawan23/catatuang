#!/bin/sh
set -e

php artisan cache:clear
php artisan migrate --force
php artisan db:seed --force

exec "$@"
