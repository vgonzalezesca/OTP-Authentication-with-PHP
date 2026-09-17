FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    pdo_mysql opcache \
    && a2enmod headers \
    && a2enmod deflate

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=64'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache-custom.ini

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

RUN echo 'LogFormat "%h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\" %D" combined_timed' \
        >> /etc/apache2/apache2.conf \
    && sed -i 's#CustomLog \${APACHE_LOG_DIR}/access.log combined#CustomLog ${APACHE_LOG_DIR}/access.log combined_timed#' \
        /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
