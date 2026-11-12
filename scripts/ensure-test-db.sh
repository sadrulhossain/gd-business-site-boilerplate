#!/bin/sh
# Pest refreshes "<MARIADB_DATABASE>_testing"; never the dev database. Idempotent.
set -u
cd "$(dirname "$0")/.."
docker compose exec -T mariadb sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`${MARIADB_DATABASE}_testing\`; GRANT ALL ON \`${MARIADB_DATABASE}_testing\`.* TO \"$MARIADB_USER\"@\"%\";"' 2>&1 | grep -v "Using a password"
exit 0