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
php artisan route:clear
php artisan view:cache


# Create storage symlink in the web root
if [ ! -L /var/www/html/storage ] && [ ! -d /var/www/html/storage ]; then
    ln -s /var/www/html/core/storage/app/public /var/www/html/storage
fi

# Fix permissions after any new files
chown -R www-data:www-data /var/www/html/core/storage /var/www/html/core/bootstrap/cache

echo "✅ Deployment setup complete. Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
