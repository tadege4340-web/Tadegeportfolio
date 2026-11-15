FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Allow CV uploads up to 4 MB
RUN printf "upload_max_filesize=5M\npost_max_size=6M\n" > /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/

EXPOSE 80