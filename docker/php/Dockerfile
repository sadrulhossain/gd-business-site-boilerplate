ARG PHP_VERSION
ARG COMPOSER_VERSION
FROM composer:${COMPOSER_VERSION} AS composer

FROM php:${PHP_VERSION}-fpm-alpine
ARG PHPREDIS_VERSION

RUN apk add --no-cache libpq git unzip \
 && apk add --no-cache --virtual .build $PHPIZE_DEPS postgresql-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql pcntl \
 && printf '\n\n\n\n\n\n\n' | pecl install redis-${PHPREDIS_VERSION} \
 && docker-php-ext-enable redis \
 && apk del .build

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY artisan-or-idle.sh fpm-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/artisan-or-idle.sh /usr/local/bin/fpm-start.sh
