#!/bin/sh
# Dev: bind-mounted storage is owned by root/host user, php-fpm workers run as www-data.
for d in storage bootstrap/cache; do
  [ -d "/var/www/api/$d" ] && chmod -R a+rwX "/var/www/api/$d"
done
exec php-fpm
