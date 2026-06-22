#!/bin/sh
set -e

cd /var/www/html/core

# Copy .env if not exists (Coolify injects env vars directly, but just in case)
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run migrations
php artisan migrate --force --no-interaction

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link || true

# Fix permissions after any new files
chown -R www-data:www-data /var/www/html/core/storage /var/www/html/core/bootstrap/cache

echo "✅ Deployment setup complete. Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
