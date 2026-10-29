#!/bin/bash

set -e

echo "🔧 Running Laravel Migrations..."
php artisan migrate --force || true

echo "🔗 Creating Storage Symlink..."
php artisan storage:link || true

echo "🚀 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "📦 Starting Supervisor (Nginx + PHP-FPM + Queues + Reverb)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf