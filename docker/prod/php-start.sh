#!/bin/sh
# php-fpm entrypoint (production): storage layout, OAuth keys, migrations, framework caches, then php-fpm.
# Config comes from the container environment only; there is no .env file in the image.
set -eu
cd /var/www/api

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

# Upload limits follow UPLOADS_MAX_MB (post body gets 1 MB headroom for multipart overhead).
max="${UPLOADS_MAX_MB:-5}"
printf 'upload_max_filesize=%sM\npost_max_size=%sM\n' "$max" "$((max + 1))" > /usr/local/etc/php/conf.d/zz-uploads.ini

# Passport keys live in the storage volume: generated once, readable by www-data only.
[ -f storage/oauth-private.key ] || php artisan passport:keys --quiet
chown www-data:www-data storage/oauth-*.key
chmod 600 storage/oauth-*.key

php artisan migrate --force --no-interaction
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
php artisan event:cache --quiet

exec php-fpm
