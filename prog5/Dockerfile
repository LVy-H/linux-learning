FROM php:8-apache

# Serve files from server/public as the Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-libmysqlclient-dev \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri "s|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|g" /etc/apache2/sites-available/*.conf \
 && sed -ri "s|<Directory /var/www/>|<Directory ${APACHE_DOCUMENT_ROOT}>|g" /etc/apache2/apache2.conf

COPY server/ /var/www/html/

# Ensure web files are readable by Apache
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html



