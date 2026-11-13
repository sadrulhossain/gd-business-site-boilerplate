#!/bin/sh
# Builds the portal once per image (the .next volume remembers which image built it), then serves it.
set -eu
cd /app/web/portal

if [ "$(cat .next/IMAGE_ID 2>/dev/null || true)" != "$(cat /app/IMAGE_ID)" ]; then
  echo "portal: building for image $(cat /app/IMAGE_ID)"
  rm -rf .next/* 2>/dev/null || true
  pnpm exec next build
  cp /app/IMAGE_ID .next/IMAGE_ID
fi

exec pnpm exec next start --hostname 0.0.0.0 --port 3000
