#!/bin/sh
# Pest refreshes "<POSTGRES_DB>_testing"; never the dev database. Idempotent.
set -u
cd "$(dirname "$0")/.."
docker compose exec -T postgres sh -c 'createdb -U "$POSTGRES_USER" "${POSTGRES_DB}_testing"' 2>&1 | grep -v "already exists"
exit 0
