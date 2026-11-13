ARG PHP_VERSION
ARG COMPOSER_VERSION
FROM composer:${COMPOSER_VERSION} AS composer

FROM php:${PHP_VERSION}-fpm-alpine
ARG UPLOADS_CONTAINER_PATH

RUN apk add --no-cache git unzip libwebp libjpeg-turbo libpng \
 && apk add --no-cache --virtual .build $PHPIZE_DEPS libwebp-dev libjpeg-turbo-dev libpng-dev \
 && docker-php-ext-configure gd --with-webp --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql pcntl \
 && apk del .build

# Uploads volume mount point: owned by www-data so a fresh named volume is writable.
RUN mkdir -p ${UPLOADS_CONTAINER_PATH} && chown www-data:www-data ${UPLOADS_CONTAINER_PATH}

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY security.ini $PHP_INI_DIR/conf.d/zz-security.ini
COPY artisan-or-idle.sh fpm-start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/artisan-or-idle.sh /usr/local/bin/fpm-start.sh
