# Versions

Checked 2026-11-11. Pin = what we build against. Floor = minimum safe version (never go below).
Dependencies not listed here must not be added (CLAUDE.md rule 2).

| Component | Pin | Floor | Source | Date | Docker Hub |
|-----------|-----|-------|--------|------|------------|
| PHP | 8.5.12 | 8.5.12 | https://www.php.net/releases/8.5/en.php | 2026-11-11 | `php:8.5.12-{cli,fpm}-alpine` exists |
| Laravel | 13.29.0 (`^13.29`) | >=13.29.0 | https://github.com/laravel/framework/releases/tag/v13.29.0 | 2026-11-11 | n/a (v13.39.0 is current) |
| Node.js 24 LTS "Krypton" | 24.22.3 | 24.22.3 | https://nodejs.org/en/blog/vulnerability/november-2026-security-releases | 2026-11-11 | `node:24.22.3-alpine` **not published yet** (latest 24.22.2) |
| Next.js | 16.4.5 | 16.4.5 | https://github.com/vercel/next.js/releases/tag/v16.4.5 | 2026-11-11 | n/a (v16.4.6 is current) |
| nginx | 1.30.6 | 1.30.6 | https://nginx.org/en/CHANGES-1.30 | 2026-11-11 | `nginx:1.30.6-alpine` exists |
| PostgreSQL | 18.6 | 18.6 | https://www.postgresql.org/docs/release/18.6/ | 2026-11-11 | `postgres:18.6-alpine` exists |
| Redis | 8.10.3 | 8.10.3 | https://github.com/redis/redis/releases/tag/8.10.3 | 2026-11-11 | `redis:8.10.3-alpine` exists |

## Notes
- Node: the 11 Nov 2026 official security release patches 24.x at **v24.22.3** (fixes 16 CVEs, highest severity High; 26.x -> v26.13.1, 22.x -> v22.23.5). v24.22.3 is on nodejs.org/dist (dated 2026-11-10), but the official Docker image has only 24.22.2 so far. Until `node:24.22.3-*` appears, do not build images from `node:24.22.2` as the pin; recheck the tag.
- Next.js: pinned to 16.4.5 as specified; 16.4.6 exists and is a candidate bump.
- Laravel: 13.29.0 is the floor; Composer constraint `^13.29` resolves to latest 13.x.
