FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=ghcr.io/roadrunner-server/roadrunner:latest /usr/bin/rr /usr/local/bin/rr

RUN install-php-extensions pcntl sockets

COPY . /var/www
COPY .env.example /var/www/.env

WORKDIR /var/www

RUN composer install --no-dev

ENTRYPOINT ["php", "artisan", "octane:start", "--server=roadrunner", "--port=9803", "--workers=16", "--host=0.0.0.0"]
