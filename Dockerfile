FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

EXPOSE 80

CMD ["apache2-foreground"]
