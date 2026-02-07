#!/bin/bash

# Fix Laravel permissions (for mounted volumes)
#chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
#chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM


# Navigate to the working directory
cd /var/www/html
# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install composer dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    #composer install --no-scripts --no-interaction --prefer-dist
     runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
fi

# Run migrations (optional)
# php artisan migrate --force

# Only in local and only when RUN_FRESH=true
if [ "$RUN_FRESH" = "true" ]; then
   php artisan migrate:fresh --force
   php artisan key:generate
   php artisan config:cache
   php artisan app:vps-load-data
fi

# Start PHP-FPM
exec php-fpm 
