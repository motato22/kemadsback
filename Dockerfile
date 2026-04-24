FROM serversideup/php:8.4-frankenphp
USER root
RUN install-php-extensions intl gd bcmath pcntl
USER www-data
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
RUN composer install --no-dev --no-interaction && \
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
