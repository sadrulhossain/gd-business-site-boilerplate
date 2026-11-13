#!/bin/sh
# Backup: database dump + uploads + OAuth keys into backups/<timestamp>/, old backups pruned.
# MariaDB is the database (mariadb-dump, not pg_dump); the stack has no Redis, so there is nothing else to dump.
# Defaults to the production stack. Set COMPOSE to back up another one, e.g. COMPOSE="docker compose" for dev.
#   sh scripts/backup.sh            # KEEP_DAYS=14 BACKUP_DIR=backups
set -eu
cd "$(dirname "$0")/.."

COMPOSE="${COMPOSE:-docker compose -f docker-compose.prod.yml --env-file .env.prod}"
BACKUP_DIR="${BACKUP_DIR:-backups}"
KEEP_DAYS="${KEEP_DAYS:-14}"
dest="$BACKUP_DIR/$(date +%Y%m%d-%H%M%S)"
umask 077
mkdir -p "$dest"

echo "backup: database"
$COMPOSE exec -T mariadb sh -c 'exec mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction --routines --events --default-character-set=utf8mb4 "$MARIADB_DATABASE"' | gzip -9 > "$dest/database.sql.gz"

echo "backup: uploads"
$COMPOSE exec -T php sh -c 'tar -C "$UPLOADS_CONTAINER_PATH" -cf - .' | gzip -9 > "$dest/uploads.tar.gz"

echo "backup: oauth keys"
$COMPOSE exec -T php sh -c 'tar -C /var/www/api/storage -cf - oauth-private.key oauth-public.key' | gzip -9 > "$dest/oauth-keys.tar.gz"

# A dump that is empty or truncated is worse than none.
for f in database.sql.gz uploads.tar.gz oauth-keys.tar.gz; do
  gzip -t "$dest/$f" || { echo "backup: $f is corrupt" >&2; exit 1; }
done
zcat "$dest/database.sql.gz" | tail -n 1 | grep -q 'Dump completed' || { echo "backup: database dump did not complete" >&2; exit 1; }

find "$BACKUP_DIR" -mindepth 1 -maxdepth 1 -type d -mtime "+$KEEP_DAYS" -exec rm -rf {} +
echo "backup: done -> $dest ($(du -sh "$dest" | cut -f1))"
echo "backup: keep .env.prod (it holds APP_KEY) somewhere safe too; it is not part of this backup."
