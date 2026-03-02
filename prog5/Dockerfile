FROM php:8-apache

# Serve files from server/public as the Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-libmysqlclient-dev \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set Apache document root
RUN sed -ri "s|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|g" /etc/apache2/sites-available/*.conf

# Enable custom Apache config (AllowOverride All for public/)
COPY apache2.conf /etc/apache2/conf-available/custom-apache2.conf
RUN a2enconf custom-apache2

COPY server/ /var/www/html/

# Ensure web files are readable and storage is writable by Apache
RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/uploads \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && chmod -R 775 /var/www/html/storage



