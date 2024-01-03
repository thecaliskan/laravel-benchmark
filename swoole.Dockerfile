FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions pcntl sockets swoole

COPY . /var/www
COPY .env.example /var/www/.env

WORKDIR /var/www

RUN composer install --no-dev

ENTRYPOINT ["php", "artisan", "octane:start", "--server=swoole", "--port=9802", "--workers=16", "--host=0.0.0.0"]
