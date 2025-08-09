# Verwende PHP 8.3 mit Apache
FROM php:8.3-apache

# Installiere die benötigten PHP-Extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    mysqli \
    pdo_mysql \
    pdo

# Produktions-PHP-Konfiguration aktivieren
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Kopiere die Anwendungsdateien ins Image
COPY ./public/ /var/www/html/

# Setze die richtigen Berechtigungen
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Starte Apache
CMD ["apache2-foreground"]