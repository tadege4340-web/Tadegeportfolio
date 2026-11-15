
FROM php:8.2-apache

# Install PHP database extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set application directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Install PHP dependencies, including AWS SDK
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Configure file uploads
RUN printf "upload_max_filesize=5M\npost_max_size=6M\n" > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80
