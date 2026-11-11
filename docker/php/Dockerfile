ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache libpq \
 && apk add --no-cache --virtual .build $PHPIZE_DEPS postgresql-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql pcntl \
 && apk del .build

COPY artisan-or-idle.sh /usr/local/bin/artisan-or-idle.sh
RUN chmod +x /usr/local/bin/artisan-or-idle.sh
