#!/bin/bash

set -e

echo "🔧 Enabling all modules..."
php artisan module:enable --all || true

echo "🔧 Running Laravel Migrations..."
php artisan migrate --force || true

echo "🔗 Creating Storage Symlink..."
php artisan storage:link || true

# echo "🔧 Seeding Database..."
# php artisan db:seed --force || true

echo "🚀 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "📦 Starting Supervisor (Nginx + PHP-FPM + Queues + Reverb)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf