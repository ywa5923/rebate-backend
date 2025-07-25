FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    zip \
    nano \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        mbstring \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
        exif \
        pcntl \
        opcache \
        pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/php.ini   
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application source
COPY . /var/www/html

# Install Laravel PHP dependencies
RUN composer install --no-scripts --no-interaction --prefer-dist

# Add root to www-data group
RUN useradd -u 1000 -G www-data john \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html 

# Expose port
EXPOSE 9000

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]

