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

# 3. Run database migrations and seeds safely
echo "🗄️ Running database migrations..."
php artisan migrate --force
php artisan db:seed --force

# 4. Generate Caddyfile for FrankenPHP with dynamic port
echo "⚡ Generating Caddyfile..."
cat > /etc/caddy/Caddyfile << 'CADDYFILE'
{
    SERVER_NAME
}

root * /app/public
php_server
CADDYFILE

# 5. Start the production server (FrankenPHP)
echo "⚡ Starting web server..."
exec frankenphp run