#!/usr/bin/env sh
set -e

echo "Running migrations (if possible)..."
php artisan migrate --force || echo "Migration skipped"

echo "Running seeders (if possible)..."
php artisan db:seed --force || echo "Seeding skipped"

echo "Starting Laravel HTTP server..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}

