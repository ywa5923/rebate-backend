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

# Dependency install logic:
# - If RUN_FRESH=true: remove vendor and install deps
# - Else: install deps only if vendor/ is missing
if [ "$RUN_FRESH" = "true" ]; then
    echo "RUN_FRESH=true: removing vendor and reinstalling composer dependencies..."
    rm -rf vendor
    runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
    php artisan migrate:fresh --force
    php artisan key:generate
    php artisan config:cache
    php artisan app:vps-load-data
else
    if [ ! -d "vendor" ]; then
        echo "Installing composer dependencies (vendor/ missing)..."
        runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
    fi
fi

# Start PHP-FPM
exec php-fpm 
