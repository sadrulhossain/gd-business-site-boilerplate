#!/bin/sh
# One container, two dev servers: web/admin (Vite) and web/portal (Next), in one pnpm workspace.
set -eu
cd /app
corepack enable
echo "installing workspace deps"
if [ -f pnpm-lock.yaml ]; then pnpm install --frozen-lockfile --reporter=silent
else pnpm install --reporter=silent; fi
trap 'kill 0' TERM INT
(cd /app/web/admin && exec pnpm run dev) &
(cd /app/web/portal && exec pnpm run dev) &
wait