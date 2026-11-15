
FROM php:8.2-apache

# Install system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set application directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Install PHP dependencies, including AWS SDK
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Configure large file uploads
RUN printf "upload_max_filesize=100M\npost_max_size=110M\nmemory_limit=256M\nmax_execution_time=300\nmax_input_time=300\n" > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80
