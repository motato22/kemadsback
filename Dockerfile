FROM serversideup/php:8.4-frankenphp

USER root

RUN install-php-extensions intl gd bcmath pcntl

USER www-data
WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts && \
    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache
