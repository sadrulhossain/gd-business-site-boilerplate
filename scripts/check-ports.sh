#!/bin/sh
# Checks every host-published port from .env: host listeners and other containers.
# Exit 1 if any port is taken by something that is not this compose project.
set -u
cd "$(dirname "$0")/.."
[ -f .env ] || { echo "ports: .env missing (cp .env.example .env)"; exit 1; }
set -a; . ./.env; set +a
proj="${COMPOSE_PROJECT_NAME:-}"
published="$(docker ps --format '{{.Names}}|{{.Ports}}' 2>/dev/null || true)"
listeners="$(netstat -an 2>/dev/null || true)"
bad=0
for var in NGINX_HTTP_PORT MARIADB_PORT MAILPIT_SMTP_PORT MAILPIT_UI_PORT; do
  eval "port=\${$var:-}"
  if [ -z "$port" ]; then echo "UNSET $var"; bad=1; continue; fi
  owner="$(printf '%s\n' "$published" | grep -E ":$port->" | cut -d'|' -f1 | head -n 1)"
  if [ -n "$owner" ]; then
    case "$owner" in
      "$proj"-*) echo "ok    $var=$port (this project: $owner)" ;;
      *) echo "BUSY  $var=$port (container $owner)"; bad=1 ;;
    esac
  elif printf '%s\n' "$listeners" | grep -E "[.:]$port[[:space:]].*LISTEN" >/dev/null; then
    echo "BUSY  $var=$port (host listener)"; bad=1
  else
    echo "free  $var=$port"
  fi
done
exit $bad
