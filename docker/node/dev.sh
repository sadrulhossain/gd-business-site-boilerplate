#!/bin/sh
# One container, two dev servers: web/admin (Vite) and web/portal (Next).
set -eu
for app in admin portal; do
  cd "/app/web/$app"
  if [ ! -f node_modules/.package-lock.json ]; then
    echo "installing $app deps"
    if [ -f package-lock.json ]; then npm ci --no-audit --no-fund --loglevel=error
    else npm install --no-audit --no-fund --loglevel=error; fi
  fi
done
trap 'kill 0' TERM INT
(cd /app/web/admin && exec npm run dev) &
(cd /app/web/portal && exec npm run dev) &
wait
