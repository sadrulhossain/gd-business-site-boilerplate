# Changelog

Format: [Keep a Changelog](https://keepachangelog.com), versions follow [SemVer](https://semver.org).
Each entry lists new environment variables and seeders to run, because upgrades need them (see docs/UPGRADE.md).

## [1.0.0] - 2026-11-13

First release.

### Added
- **API** (Laravel 13, PHP 8.5, MariaDB): sections, services, team, testimonials, gallery, FAQs, blog (categories, posts, sanitised rich text), contact inbox with captcha, honeypot and throttle,
  settings, users, roles and permissions, per-user menus, dashboard, image uploads with WebP variants, content-change webhook, OAuth 2 with PKCE (Passport), OpenAPI export.
- **Admin** (Vite, React 19): sign-in through the authorization-code flow with PKCE, CRUD screens driven by `modules.ts`, TipTap blog editor, profile, role matrix, bilingual fields.
- **Portal** (Next.js 16): Home and section pages, services, gallery, FAQ, blog (categories, pagination), contact page, legal pages, localised 404, two languages, ISR with tag revalidation,
  canonical/hreflang/Open Graph, `sitemap.xml`, `robots.txt`, JSON-LD.
- **Security**: S256-only PKCE, closed CORS, security headers and CSP per vhost, per-visitor throttling, upload validation by content, `make security` (route/policy audit, secrets scan, version floors).
  Findings and scans: docs/SECURITY.md.
- **Quality**: Pest, Larastan, Pint; Vitest; Playwright smoke and axe suites (WCAG 2 A/AA, both languages, zero violations); Lighthouse SEO 100.
- **Production** (`docker-compose.prod.yml`, `.env.prod.example`): portal as a Node server, admin built static and served by nginx, TLS from mounted certificates, only ports 80/443 published,
  private MariaDB, restart policies, healthchecks, rotated logs, uploads path from env. `scripts/backup.sh` and `scripts/restore.sh` (database, uploads, OAuth keys).
- **Docs**: README, DEPLOY, CUSTOMIZE, UPGRADE, ENV, PATTERNS, DECISIONS, SECURITY, SIZING, VERSIONS, PHASES.

### Notes
- Environment variables: see docs/ENV.md and `.env.prod.example`. Seeders: run `php artisan db:seed --force` once on first deploy.
- The database is MariaDB 11.8 and there is no Redis (docs/DECISIONS.md, 010); backups use `mariadb-dump`.
- Known limits: the portal CSP still allows inline scripts (nonces are the next step); Lighthouse performance 90+ was not reached under the default mobile throttle on the slow development host (docs/SECURITY.md).

[1.0.0]: https://github.com/sadrulhossain/gd-business-site-boilerplate/releases/tag/v1.0.0
