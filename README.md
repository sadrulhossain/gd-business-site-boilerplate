# gd-business-site-boilerplate

A small-business website you can run on one VPS: a public portal (Next.js) in two languages, an admin app (React) to edit everything,
and a Laravel API behind both. Content: hero, about us, sections, services, pricing (per service, hidden until switched on), team, testimonials, gallery, FAQ, blog, contact inbox, settings (logo, favicon, currency, social links), users and roles.

| Part | Where | Stack |
|------|-------|-------|
| API | `api/` | Laravel 13, PHP 8.5, MariaDB, Passport (OAuth 2 + PKCE), spatie/permission |
| Portal | `web/portal` | Next.js 16 (App Router, ISR), Tailwind 4 |
| Admin | `web/admin` | Vite, React 19, TanStack Query, TipTap |
| Shared | `packages/` | design tokens, typed API client, OpenAPI types |

Versions and their security floors: [docs/VERSIONS.md](docs/VERSIONS.md).

## Quick start (development)

You need Docker with Compose, `make`, `openssl` and a browser. Nothing else is installed on the host.

```sh
git clone <your fork> site && cd site
make up        # creates .env (APP_KEY generated), builds, starts and waits until healthy
```

Then open (the `.localhost` names resolve to your machine in modern browsers; no hosts file edit needed):

| URL | What |
|-----|------|
| http://gdsite.localhost:28080 | Portal (`/bn` for the second language when enabled) |
| http://admin.gdsite.localhost:28080 | Admin (sign in with the superadmin below) |
| http://api.gdsite.localhost:28080/docs/api | API reference (Scramble, dev only) |
| http://localhost:28026 | Mailpit, catches the contact-form mail |

First run only: set `SUPERADMIN_EMAIL`, `SUPERADMIN_PASSWORD` and `OAUTH_ADMIN_REDIRECT_URI` in `api/.env` (copy `api/.env.example`), then
`docker compose exec php php artisan migrate --seed`. Change every `change-me` in `.env`.

Optional demo content in English and the second language (not part of `DatabaseSeeder`): `docker compose exec php php artisan db:seed --class="Database\Seeders\Site\SiteDataSeeder"`. Logo and favicon are uploaded in the admin under Settings; the built-in ones show until then. Footer credit: Powered by Orvyx Technologies.

Daily commands:

| Command | What |
|---------|------|
| `make up` / `make down` | start (build, wait healthy) / stop |
| `make t FILTER=Name` | targeted Pest tests in the php container |
| `make security` | route/policy audit, secrets scan, version floors |
| `make check` | everything: compose, scripts, nginx -t, Pint, Larastan, Pest, composer audit, versions, security (run once per change set; needs the stack up) |
| `make openapi` | re-export the API spec to `packages/api-types` (then `pnpm generate:types`) |

Browser tests (Playwright, official image): `docker compose --profile e2e run --rm --no-deps -T -w /app/web/portal e2e npx playwright test`.

The dev containers do not see file changes on Windows hosts: after editing portal code, stop `node`, delete `web/portal/.next` and start it again.

## Production

One VPS (1 vCPU / 1 GB is the minimum, 2 GB recommended: [docs/SIZING.md](docs/SIZING.md)), your domain, a TLS certificate.
`docker-compose.prod.yml` runs the portal as a Node server, serves the admin as built static files from nginx, publishes only ports 80 and 443,
keeps MariaDB private and restarts everything on failure. Steps: [docs/DEPLOY.md](docs/DEPLOY.md). Backups: `sh scripts/backup.sh`.

## Documentation

| File | Read it for |
|------|-------------|
| [docs/DEPLOY.md](docs/DEPLOY.md) | VPS setup, TLS, first deploy, backups, restore |
| [docs/CUSTOMIZE.md](docs/CUSTOMIZE.md) | brand colours, a second language, new modules and sections |
| [docs/UPGRADE.md](docs/UPGRADE.md) | updating a running site, rollback |
| [docs/PATTERNS.md](docs/PATTERNS.md) | the reference code shapes to copy |
| [docs/ENV.md](docs/ENV.md) | every environment variable |
| [docs/SECURITY.md](docs/SECURITY.md) | review findings, scans, accessibility and performance results |
| [docs/DECISIONS.md](docs/DECISIONS.md), [docs/PHASES.md](docs/PHASES.md) | why things are the way they are, what was built when |
| [CHANGELOG.md](CHANGELOG.md) | releases |

## Repository map

```
api/        Laravel API            docker/     Dockerfiles, nginx templates, prod entrypoints
web/admin   Admin SPA              docs/       the documents above
web/portal  Public portal          scripts/    ports, secrets, version floors, backup, restore
packages/   tokens, api-client, api-types
```
