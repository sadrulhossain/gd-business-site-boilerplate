# Environment variables

Root `.env` (untracked) is copied from `.env.example` by `make up`. Compose reads it for interpolation only;
each service lists its own `environment:` (no `env_file`). Commit only `*.example` files.

| Variable | Used by | Default | Notes |
|----------|---------|---------|-------|
| COMPOSE_PROJECT_NAME | compose | gdsite | container names are `${COMPOSE_PROJECT_NAME}-<tech>` |
| COMPOSE_PROFILES | compose | dev | `dev` enables mailpit |
| APP_ENV | php* | local | |
| APP_DOMAIN | nginx, node, php* | gdsite.localhost | portal = APP_DOMAIN, `admin.` and `api.` subdomains |
| PHP_VERSION, LARAVEL_VERSION, NODE_VERSION, NEXT_VERSION, NGINX_VERSION, POSTGRES_VERSION, REDIS_VERSION, MAILPIT_VERSION, VITE_VERSION, REACT_VERSION | compose build/images, `make versions` | see docs/VERSIONS.md | must match VERSIONS.md |
| NGINX_HTTP_PORT | nginx | 28080 | published on all interfaces |
| POSTGRES_PORT | postgres | 28432 | bound to 127.0.0.1 |
| REDIS_PORT | redis | 28379 | bound to 127.0.0.1 |
| MAILPIT_SMTP_PORT / MAILPIT_UI_PORT | mailpit | 28025 / 28026 | bound to 127.0.0.1 |
| PORTAL_DEV_PORT / ADMIN_DEV_PORT | node, nginx | 28300 / 28301 | not published; nginx proxies to them |
| POSTGRES_DB / POSTGRES_USER / POSTGRES_PASSWORD | postgres, php* | gdsite / gdsite / change-me | |
| REDIS_USER / REDIS_PASSWORD | redis, php* | gdsite / change-me | default Redis user is disabled; ACL user only |

`php*` = php, php-queue, php-scheduler.
