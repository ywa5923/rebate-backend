#!/bin/bash
set -euo pipefail

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
    local max_attempts=30
    local attempt=1
    
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    
    while [ $attempt -le $max_attempts ]; do
        echo "Attempt $attempt/$max_attempts..."
        
        if php -r "
            try {
                \$pdo = new PDO(
                    'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
                    getenv('DB_USERNAME'),
                    getenv('DB_PASSWORD'),
                    [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Also check if the specific database exists
                \$stmt = \$pdo->query(\"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '\" . getenv('DB_DATABASE') . \"'\");
                if (\$stmt->fetch()) {
                    exit(0); // Success
                }
                exit(1); // DB doesn't exist yet
            } catch (Throwable \$e) {
                exit(1); // Connection failed
            }
        "; then
            echo "✅ MySQL is ready!"
            return 0
        fi
        
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ MySQL failed to become ready after $max_attempts attempts"
    exit 1
}

# Always wait for DB before running composer/migrations/seeding
wait_for_mysql

# Dependency install logic
if [ "$RUN_FRESH" = "true" ]; then
    echo "RUN_FRESH=true: removing vendor and reinstalling composer dependencies..."
    rm -rf vendor
    runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
    
    echo "Running migrations..."
    php artisan migrate:fresh --force
    
    echo "Generating key..."
    php artisan key:generate --force
    
    echo "Caching config..."
    php artisan config:cache
    
    echo "Loading data..."
    php artisan app:vps-load-data
else
    if [[ ! -d "vendor" ]]; then
        echo "Installing composer dependencies (vendor/ missing)..."
        runuser -u www-data -- composer install --no-scripts --no-interaction --prefer-dist
    fi
fi

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm