#!/bin/sh
# Run `php artisan <args>` once Laravel exists in /var/www/api; wait until then.
until [ -f /var/www/api/artisan ]; do
  echo "artisan not found (Laravel not installed yet); waiting"
  sleep 15
done
exec php /var/www/api/artisan "$@"
