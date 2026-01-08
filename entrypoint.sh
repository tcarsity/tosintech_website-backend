#!/usr/bin/env sh

set -e

echo "Starting Laravel container..."

# Ensure permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Run migrations safely
php artisan migrate --force || true

# Optional: seed only once (comment out if you want)
php artisan db:seed --force || true

# Start PHP-FPM
exec php-fpm
