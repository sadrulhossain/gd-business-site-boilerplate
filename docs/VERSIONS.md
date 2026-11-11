# Versions

Checked 2026-11-11. Pin = what we build against. Floor = minimum safe version (never go below).
Dependencies not listed here must not be added (CLAUDE.md rule 2).

| Component | Pin | Floor | Source | Date | Docker Hub |
|-----------|-----|-------|--------|------|------------|
| PHP | 8.5.12 | 8.5.12 | https://www.php.net/releases/8.5/en.php | 2026-11-11 | `php:8.5.12-{cli,fpm}-alpine` exists |
| Laravel | 13.29.0 (`^13.29`) | >=13.29.0 | https://github.com/laravel/framework/releases/tag/v13.29.0 | 2026-11-11 | n/a (v13.39.0 is current) |
| Node.js 24 LTS "Krypton" | 24.22.3 | 24.22.3 | https://nodejs.org/en/blog/vulnerability/november-2026-security-releases | 2026-11-11 | `node:24.22.3-alpine` **not published yet** (latest 24.22.2) |
| Next.js | 16.4.6 | 16.4.6 | https://github.com/vercel/next.js/releases/tag/v16.4.6 | 2026-11-12 | n/a |
| nginx | 1.30.6 | 1.30.6 | https://nginx.org/en/CHANGES-1.30 | 2026-11-11 | `nginx:1.30.6-alpine` exists |
| PostgreSQL | 18.6 | 18.6 | https://www.postgresql.org/docs/release/18.6/ | 2026-11-11 | `postgres:18.6-alpine` exists |
| Redis | 8.10.3 | 8.10.3 | https://github.com/redis/redis/releases/tag/8.10.3 | 2026-11-11 | `redis:8.10.3-alpine` exists |
| Mailpit | v1.33.1 | v1.33.1 | https://github.com/axllent/mailpit/releases/tag/v1.33.1 | 2026-11-12 | `axllent/mailpit:v1.33.1` exists |
| Vite | 8.3.8 | 8.3.8 | https://www.npmjs.com/package/vite/v/8.3.8 | 2026-11-12 | n/a (npm) |
| React / React DOM | 19.3.1 | 19.3.1 | https://www.npmjs.com/package/react/v/19.3.1 | 2026-11-12 | n/a (npm) |
| Composer | 2.11.2 | 2.11.2 | https://hub.docker.com/_/composer (tag 2.11.2) | 2026-11-12 | `composer:2.11.2` exists |
| phpredis (PECL redis) | 6.3.0 | 6.3.0 | https://pecl.php.net/package/redis/6.3.0 | 2026-11-12 | builds on PHP 8.5.12 (see DECISIONS.md) |
| pestphp/pest | 4.7.8 | 4.7.8 | https://packagist.org/packages/pestphp/pest#v4.7.8 | 2026-11-12 | n/a (composer) |
| pestphp/pest-plugin-laravel | 4.1.0 | 4.1.0 | https://packagist.org/packages/pestphp/pest-plugin-laravel#v4.1.0 | 2026-11-12 | n/a (composer) |
| laravel/pint | 1.32.3 | 1.32.3 | https://packagist.org/packages/laravel/pint#v1.32.3 | 2026-11-12 | n/a (composer) |
| larastan/larastan | 3.15.0 | 3.15.0 | https://packagist.org/packages/larastan/larastan#v3.15.0 | 2026-11-12 | n/a (composer) |
| dedoc/scramble | 0.13.53 | 0.13.53 | https://packagist.org/packages/dedoc/scramble#v0.13.53 | 2026-11-12 | n/a (composer) |
| laravel/passport | 13.11.0 | 13.11.0 | https://packagist.org/packages/laravel/passport#v13.11.0 | 2026-11-12 | n/a (composer) |
| spatie/laravel-permission | 8.5.0 | 8.5.0 | https://packagist.org/packages/spatie/laravel-permission#8.5.0 | 2026-11-12 | n/a (composer) |

## Notes
- Node: the 11 Nov 2026 official security release patches 24.x at **v24.22.3** (fixes 16 CVEs, highest severity High; 26.x -> v26.13.1, 22.x -> v22.23.5). v24.22.3 is on nodejs.org/dist (dated 2026-11-10), but the official Docker image has only 24.22.2 so far. Until `node:24.22.3-*` appears, do not build images from `node:24.22.2` as the pin; recheck the tag.
- Next.js: bumped from 16.4.5 to 16.4.6 on 2026-11-12 (user decision).
- Laravel: 13.29.0 is the floor; Composer constraint `^13.29` resolves to latest 13.x.
- Phase 1 additions (Mailpit, Vite, React/React DOM) are new pins added to support the dev profile and hello apps; floors equal pins and advisories were not checked. Pending approval.
- Node 24.22.3 image: `.env` keeps the correct pin. Phase 1 was verified locally with `NODE_VERSION=24.22.2` (not published yet, see above). Remove the override once `node:24.22.3-alpine` exists.
- Phase 2: laravel/framework resolved to v13.42.0 (floor >=13.29.0); `composer audit` clean on 2026-11-12. Pest is pinned to 4.x as requested (5.3.5 exists). Predis not installed (phpredis builds).
- Phase 2: the Laravel 13 skeleton's own default dev dependencies (tinker, pail, pao, collision, mockery, faker, phpunit via Pest) are kept unpinned; everything else was added deliberately above.
- Phase 3: Passport 13.11.0 and spatie/laravel-permission 8.5.0 resolve with Laravel 13.42.0; `composer audit` clean 2026-11-12.
