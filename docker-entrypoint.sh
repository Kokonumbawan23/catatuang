#!/bin/sh
set -e # Exit immediately if any command fails

echo "🚀 Container starting up..."

# 1. Ensure the SQLite file exists before running migrations
mkdir -p database storage/framework/views
touch database/database.sqlite

# 2. Run optimizations now that Railway variables are live
echo "✨ Optimizing Laravel configuration & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Run database migrations and seeds safely
echo "🗄️ Running database migrations..."
php artisan migrate --force
php artisan db:seed --force

# 4. Start the production server (FrankenPHP) on the dynamic Railway port
echo "⚡ Starting web server..."
exec frankenphp php-server --port ${PORT:-80} --root public/