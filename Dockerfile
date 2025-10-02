FROM php:8.2-fpm

# Install system dependencies and PHP extensions commonly required by Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Install composer from the official Composer image (pinned)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first to leverage Docker cache for dependencies
COPY composer.json composer.lock* /var/www/html/

# Run a safe composer install if composer files are present (fails gracefully otherwise)
RUN if [ -f composer.json ]; then composer install --no-interaction --prefer-dist --optimize-autoloader || true; fi

# Copy application source
COPY . /var/www/html

# Ensure storage and cache directories are writable by the www-data user
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000

CMD ["php-fpm"]
