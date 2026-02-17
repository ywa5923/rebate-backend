#!/bin/bash
set -euo pipefail

# Fix Laravel permissions (for mounted volumes)
#chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
#chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Navigate to the working directory
cd /var/www/html

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure DB env vars are exported (defaults if missing)
export DB_HOST="${DB_HOST:-mysql}"
export DB_PORT="${DB_PORT:-3306}"
export DB_DATABASE="${DB_DATABASE:-forge}"
export DB_USERNAME="${DB_USERNAME:-forge}"
export DB_PASSWORD="${DB_PASSWORD:-}"

wait_for_mysql() {
  DB_HOST="${DB_HOST:-mysql}"
  DB_PORT="${DB_PORT:-3306}"
  DB_DATABASE="${DB_DATABASE:-forge}"
  DB_USERNAME="${DB_USERNAME:-forge}"
  DB_PASSWORD="${DB_PASSWORD:-}"

  echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
  until php -r 'try{
    new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
            getenv("DB_USERNAME"), getenv("DB_PASSWORD"),
            [PDO::ATTR_TIMEOUT=>2]);
  } catch (Throwable $e) { exit(1); }'; do
    sleep 2
  done
  echo "MySQL is up."
}

# Always wait for DB before running composer/migrations/seeding
wait_for_mysql

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
    if [[ ! -d "vendor" ]]; then
        echo "Installing composer dependencies (vendor/ missing)..."
        runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
    fi
fi

# Start PHP-FPM
exec php-fpm 
