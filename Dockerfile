FROM serversideup/php:8.4-frankenphp

USER root

# # Install server dependencies
# RUN apt-get update \
#     && apt-get install -y ca-certificates gnupg git \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install PHP extensions including pcntl for Octane
RUN install-php-extensions intl gd bcmath pcntl

USER www-data

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

RUN composer install --no-dev --no-interaction && \
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
