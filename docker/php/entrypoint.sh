#!/bin/bash

# Fix Laravel permissions (for mounted volumes)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM
exec php-fpm
