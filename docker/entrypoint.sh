#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting application..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
