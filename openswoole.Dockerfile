FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions pcntl sockets openswoole

COPY . /var/www

WORKDIR /var/www

RUN composer install --no-dev

RUN composer env-generate

RUN php -r "file_exists('.env') || copy('.env.example', '.env');" && php artisan key:generate --ansi

ENTRYPOINT ["php", "artisan", "octane:start", "--server=swoole", "--port=9801", "--workers=16", "--host=0.0.0.0"]
