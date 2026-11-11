# Decisions

Log of architecture decisions. Format: `## NNN — Title` / Date / Decision / Why.

## 001 — phpredis, not Predis
2026-11-12. Use the phpredis extension (PECL redis 6.3.0). It compiles on PHP 8.5.12-fpm-alpine and loads; ACL user auth works through Laravel's `REDIS_USERNAME`/`REDIS_PASSWORD`. Predis is not installed.

## 002 — JSON error format
2026-11-12. Every exception renders as `{"error": {"code", "message", "details"?}}` via `App\Exceptions\JsonErrorRenderer`; `shouldRenderJsonWhen` is always true (API-only app). 5xx messages are hidden unless `APP_DEBUG`.

## 003 — API-only Laravel
2026-11-12. Skeleton web route, `/up`, welcome view, npm/Vite files and the skeleton's own CLAUDE.md/AGENTS.md were removed. Routes live in `routes/api.php` under `/api/v1`. Scramble serves docs at `/docs/api` (local env only).

## 004 — Tests use the compose services
2026-11-12. Pest runs inside the php container against the real Postgres/Redis from the container env (phpunit.xml forces cache/session to array, queue to sync). Health tests therefore need the stack up.
