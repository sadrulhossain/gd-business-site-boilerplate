#!/bin/sh
# Restore a backup made by scripts/backup.sh into the running stack. Replaces the database and the uploads.
#   sh scripts/restore.sh backups/20261113-020000
set -eu
cd "$(dirname "$0")/.."

dir="${1:?usage: restore.sh <backup directory>}"
COMPOSE="${COMPOSE:-docker compose -f docker-compose.prod.yml --env-file .env.prod}"
for f in database.sql.gz uploads.tar.gz oauth-keys.tar.gz; do
  [ -f "$dir/$f" ] || { echo "restore: missing $dir/$f" >&2; exit 1; }
done

printf 'This REPLACES the database and uploads of the running stack with %s. Type yes: ' "$dir"
read -r answer
[ "$answer" = yes ] || { echo "restore: cancelled"; exit 1; }

echo "restore: database"
zcat "$dir/database.sql.gz" | $COMPOSE exec -T mariadb sh -c 'exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"'

echo "restore: uploads"
$COMPOSE exec -T php sh -c 'find "$UPLOADS_CONTAINER_PATH" -mindepth 1 -delete' </dev/null
zcat "$dir/uploads.tar.gz" | $COMPOSE exec -T php sh -c 'tar -C "$UPLOADS_CONTAINER_PATH" -xf -'

echo "restore: oauth keys"
zcat "$dir/oauth-keys.tar.gz" | $COMPOSE exec -T php sh -c 'tar -C /var/www/api/storage -xf - && chown www-data:www-data /var/www/api/storage/oauth-*.key && chmod 600 /var/www/api/storage/oauth-*.key'

$COMPOSE restart php php-queue php-scheduler
echo "restore: done"
