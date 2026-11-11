#!/bin/sh
# Dev: bind-mounted storage is owned by root/host user, php-fpm workers run as www-data.
for d in storage bootstrap/cache; do
  [ -d "/var/www/api/$d" ] && chmod -R a+rwX "/var/www/api/$d"
done

# Passport OAuth keys (git-ignored): generate once, readable by www-data only.
if [ -f /var/www/api/vendor/autoload.php ]; then
  [ -f /var/www/api/storage/oauth-private.key ] || php /var/www/api/artisan passport:keys --quiet
  chown www-data:www-data /var/www/api/storage/oauth-*.key
  chmod 600 /var/www/api/storage/oauth-*.key
fi

exec php-fpm
