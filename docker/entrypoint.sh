
#!/bin/sh
set -e

echo "=================================="
echo " Laravel container starting..."
echo "=================================="

# Wait for DB (important for Supabase)
sleep 5

if [ "$RUN_MIGRATIONS" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force
fi

if [ "$RUN_SEEDERS" = "true" ]; then
  echo "Running seeders..."
  php artisan db:seed --force
fi

echo "Starting Laravel server..."
exec "$@"
