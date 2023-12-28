FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions pcntl sockets

COPY . /var/www

WORKDIR /var/www

RUN apk add jq

RUN wget -Ofrankenphp $(wget -O- https://api.github.com/repos/dunglas/frankenphp/releases/latest | jq '.assets[] | select(.name=="frankenphp-linux-x86_64") | .browser_download_url' -r)

RUN composer install --no-dev

RUN composer env-generate

ENTRYPOINT ["php", "artisan", "octane:start", "--server=frankenphp", "--port=9804", "--workers=16", "--host=0.0.0.0"]
