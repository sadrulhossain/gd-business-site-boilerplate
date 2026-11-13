# Security review (phase 15)

Reviewed 2026-11-13 on branch `feature/phase-15-security-a11y-perf`. Status: **fixed** = changed in this phase, **accepted** = documented risk,
**open** = needs a decision or an action outside this repo.

Run the gates any time: `make security` (route audit, secrets, version floors). `make check` includes them.

## Findings

| # | Area | Finding | Severity | Status | Where |
|---|------|---------|----------|--------|-------|
| 1 | Auth / PKCE | `/oauth/authorize` accepted `code_challenge_method=plain`, and defaulted to `plain` when the method was omitted (league/oauth2-server always registers it), so a client could send the verifier itself as the challenge | Medium | fixed: middleware refuses anything but `S256` with a plain 400 and no redirect | `RequirePkceS256`, `AuthHardeningTest` |
| 2 | Throttle | All portal visitors shared **one** contact/captcha throttle bucket: the API saw the portal's IP and ignored `X-Forwarded-For`. One bot could lock the form for everyone | High | fixed: nginx `set_real_ip_from` trusts `X-Forwarded-For` only from `TRUSTED_PROXY_CIDR` (default: Docker range). Verified: 8 distinct IPs gave 8 buckets; the 6th request from one IP gave 429 | `default.conf.template`, ENV.md |
| 3 | CORS | No `config/cors.php`: Laravel answered `Access-Control-Allow-Origin: *` on every API route | Low (bearer tokens, no cookies) | fixed: closed by default, `CORS_ALLOWED_ORIGINS` allowlist, never credentials. The admin is same-origin through nginx and the portal calls the API server-side | `config/cors.php`, `RouteAuditTest` |
| 4 | Headers | No security headers on any vhost; `Server: nginx/1.30.6` and `X-Powered-By: PHP/...` exposed | Medium | fixed: `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`, COOP, HSTS and a CSP per vhost; `server_tokens off`, `expose_php=0`, Next `poweredByHeader: false`. Uploads get `CSP: default-src 'none'; sandbox` | `docker/nginx/`, `docker/php/security.ini` |
| 5 | CSP strength | The dev CSP for portal and admin needs `'unsafe-inline'` and `'unsafe-eval'` (Next/Vite dev, inline hydration scripts) | Medium | **open**: tighten for production with per-request nonces (`proxy.ts` can set one) and drop both. The API vhost CSP is already `default-src 'none'` | this file |
| 6 | HSTS | The header is sent but only takes effect over TLS; the dev stack is plain http | Info | accepted: terminate TLS in front (load balancer or Caddy) and keep the header | - |
| 7 | Routes / policies | Needed proof that every `auth:api` route has a permission or policy | - | verified: `php artisan security:routes` lists routes with neither a `can:` middleware nor a Gate/policy call; 0 found. Pest asserts that, asserts 401 for anonymous callers on every guarded route and 403 for a no-permission user on every collection route | `AuditRoutes`, `RouteAuditTest` |
| 8 | Uploads | Validated by content (real image, allowlist jpeg/png/webp, size, pixel cap), random server-side names, stored under `UPLOADS_CONTAINER_PATH`; nginx serves `/uploads/` as static (`^~` beats the php regex, volume mounted read-only) | - | verified; added tests for PHP/HTML/SVG payloads renamed to `.jpg`/`.png`, path traversal in the client name, and that no executable or markup extension is ever stored | `AuthHardeningTest` |
| 9 | Captcha / contact | One-time sum captcha, honeypot, `throttle:contact` (5/min/IP), `throttle:30,1` on the captcha endpoint | - | verified; added a test for the captcha throttle. A sum captcha stops casual bots, not a targeted one: swap for a hosted captcha if spam appears | `ContactTest`, `AuthHardeningTest` |
| 10 | Redis ACL | The brief asks for a Redis ACL review | - | not applicable: Redis was removed in phase 8 (DECISIONS 010); cache, sessions and queue use MariaDB. If Redis returns: an ACL user per app, `requirepass`, no host port, `-@dangerous` | DECISIONS.md |
| 11 | Secrets | `.env` is untracked; no keys or tokens in tracked files; no credential defaults in `config/`; `change-me` placeholders in `.env.example`. A local, uncommitted edit of `api/.env.example` holds a real-looking `SUPERADMIN_PASSWORD` | Medium (if committed) | fixed: `scripts/check-secrets.sh` fails on tracked env files, private keys and token patterns, literal credential defaults, non-placeholder secrets in staged example files, and `change-me` when `APP_ENV=production`. **Action:** that local edit is left out of the commit; move the password to `.env` | `make security` |
| 12 | PHP hardening | The PHP2Shell RCE fixes ship in 8.5.12 (already the pin). Workarounds `session.upload_progress.enabled=0` and `fatal_error_backtraces=0` applied as defence in depth | - | fixed | `docker/php/security.ini` |
| 13 | Showcase page | `/<locale>/showcase` (design-token preview) was public | Low | fixed: 404 in production builds | `showcase/page.tsx` |
| 14 | Webhook | `/api/revalidate`: HMAC-SHA256 over `timestamp.body`, 5 minute window, constant-time compare, 503 without a secret | - | verified (existing `RevalidateWebhookTest`) | - |
| 15 | Placeholder secrets | `PORTAL_REVALIDATE_SECRET` and `MARIADB_*` ship as `change-me` | Medium | accepted with a gate: `make security` fails when `APP_ENV=production` and `.env` still has `change-me` | `check-secrets.sh` |
| 16 | Tokens | Access token 1 h, refresh 30 d, password grant refused, PKCE required, memory-only tokens in the SPA, logout revokes both | - | verified (existing tests) | `OauthPkceTest` |
| 17 | Test isolation | `BilingualTest` assumed `LOCALE_SUPPORTED=en` and failed once the dev `.env` enabled a second language | Low | fixed: `tests/bootstrap.php` pins it | `tests/bootstrap.php` |

## Dependency and image scans

Run 2026-11-13. `composer audit`: none. `pnpm audit --prod` and `pnpm audit`: none. Trivy (`aquasec/trivy:latest`, HIGH and CRITICAL, OS and language packages):

| Image | Result | Decision |
|-------|--------|----------|
| `gdsite-php:8.5.12` (alpine 3.24.4) | 0 | clean |
| `axllent/mailpit:v1.33.1` | 0 | clean (dev profile only) |
| `node:24.22.3-alpine` | 4: `brace-expansion` 5.0.9 and `undici` 6.28.0, both inside the image's bundled **npm** (`/usr/local/lib/node_modules/npm`) | accepted: npm is never run at request time (pnpm via corepack); our own tree is clean under `pnpm audit`. For a production image: `rm -rf /usr/local/lib/node_modules/npm`, or take the next `node:24` tag |
| `nginx:1.30.6-alpine` | 1 HIGH: `libxslt` 1.1.43-r3 (fixed in r4) | accepted: the xslt module is neither loaded nor used by our config. Recheck when `nginx:1.30.x-alpine` is rebuilt |
| `mariadb:11.8.9` (ubuntu 24.04) | 1 CRITICAL and 10 HIGH, all Go `stdlib` v1.24.6 inside the image's bundled `gosu` binary | accepted: `gosu` only drops privileges at container start and takes no network input. Recheck on the next 11.8.x image; the pin is fixed by VERSIONS.md |

Version floors: `node scripts/check-floors.mjs` compares the `.env` image pins, `composer.lock` and `pnpm-lock.yaml` with the floors in VERSIONS.md and fails below one.
Its first run found `postcss` 8.5.23 (pulled in by Next 16.4.6; floor 8.5.32): fixed with a pnpm `overrides` entry. Advisory check on the web, 2026-11-13: Node 24.22.3 (Nov 2026 release),
Next 16.4.5 and later (3 Nov 2026 release) and PHP 8.5.12 (21 Oct 2026, PHP2Shell) are current. No nginx 1.30, MariaDB 11.8 or Laravel 13 advisory was found above our floors.

## Accessibility and performance (portal)

- axe (WCAG 2.0/2.1 A and AA) through Playwright, `web/portal/e2e/a11y.spec.ts`: home, about, services, blog, contact, privacy, terms and a 404 page in **both languages**, 16 pages, 0 violations.
  The first run found 16 colour-contrast failures (white on `brand-600` is 4.1:1): buttons and active chips now use `brand-700`. The blog detail page is not scanned (no published post in the dev data).
- Lighthouse 13.6.0 (run with `npx` in the Playwright container, not a dependency), production build (`next build` and `next start`), default mobile profile (4x CPU slowdown), on this dev machine (benchmarkIndex 371, a slow host):

  | Page | Performance | SEO | Accessibility | Best practices |
  |------|-------------|-----|---------------|----------------|
  | `/` | 73 | **100** | 100 | 78 |
  | `/contact` | 75 | 100 | 100 | 78 |
  | `/bn` | 76 | 100 | 100 | 78 |
  | `/blog` | 86 | 100 | 100 | 78 |

  Without the CPU slowdown (what a normal machine does): `/` 85, `/contact` 99, `/bn` 96. **SEO 100 is met on every page. Performance 90+ is not demonstrated** under the default mobile throttle on this host:
  the limit is total blocking time from the Next/React client runtime (about 120 KB of JS, 159 KB per page); layout is fine (CLS 0, LCP 0.7 to 1.0 s). Re-measure on the target VPS before sign-off.
  Best practices 78 comes from `is-on-https` and `redirects-http`, which only pass behind TLS.
- Speed fix: `not-found.tsx` read `headers()`, which made every locale route dynamic (no ISR, about 500 ms server time). It now takes the locale from the URL, so Home, About, Services, Contact, FAQ, Gallery, Privacy,
  Terms and the post and service detail pages are built once and revalidated hourly or by the tag webhook. The blog index stays dynamic (it reads `?page` and `?category`); its API reads are still cached.
  A production build needs the API reachable at build time.
