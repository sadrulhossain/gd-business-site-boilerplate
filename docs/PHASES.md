# Phases

Tick a phase only when it is finished and committed.

| Done | # | Phase | Scope / files |
|------|---|-------|---------------|
| [x] | 0 | Repo scaffold and docs | .gitignore, .editorconfig, CLAUDE.md, docs/* |
| [x] | 1 | Docker infrastructure | docker-compose.yml, docker/*, scripts/check-ports.sh, Makefile, .env.example, hello apps in web/admin, web/portal, api/public |
| [x] | 2 | Laravel API | api/*, Dockerfile (composer, phpredis), /api/v1/health, /api/v1/meta/locales, JSON errors, Pest/Pint/Larastan/Scramble, make t/check |
| [x] | 3 | Auth and RBAC | Passport 13, spatie/laravel-permission, menus table, /me, /me/menus, seeders, access tests |
| [x] | 4 | Settings, sections, media | settings (singleton), sections, MediaService + WebP queue, HasSecondLanguage, PATTERNS.md |
| [x] | 5 | FAQs, services, testimonials, team, gallery | migration + model + FormRequests + Resource + Policy + public/admin routes + seeder + feature test per module; image endpoints |
| [x] | 6 | Blog, contact, webhook, OpenAPI | posts + categories (HTMLPurifier sanitising), public POST /contact (Redis captcha, honeypot, throttle, queued mail), admin inbox, ContentObserver -> signed revalidate webhook, `?lang=` on public reads, packages/api-types/openapi.json |
| [x] | 7 | Frontend workspace | pnpm workspace (root package.json, pnpm-workspace.yaml, one pnpm-lock.yaml); packages/tokens (Tailwind 4 @theme, admin + portal themes), packages/api-types (openapi-typescript), packages/api-client (typed fetch); Tailwind 4 in web/admin (Vite) and web/portal (Next); one showcase route each (/showcase); docker/node/dev.sh on pnpm |
| [x] | 8 | Admin shell | API login page + PKCE login, memory-only tokens with single-flight refresh, route guards, sidebar from /me/menus, permission-gated routes, 403/404, dashboard counts; shared DataTable/FormField/BilingualField/ConfirmDialog/Toast; config-driven CrudPage; TanStack Query + Zustand; Vitest (store, guards); Playwright login + dashboard screenshots |

| [x] | 9 | Admin modules | users, roles (permission matrix), menus (tree + reorder), settings (tabbed bilingual singleton), sections (visibility toggle, drag reorder) as CrudPage configs; API: users, roles, permissions, menus, reorder endpoints; Vitest on configs |
| [x] | 10 | Content modules | services, FAQs, testimonials, team, gallery as CrudPage configs with image upload (progress); gallery multi-upload; contact messages read-only list/detail + mark read; Vitest on configs and upload helpers |
| [x] | 11 | Blog and profile | posts CrudPage config (TipTap rich text, bilingual, draft/published `status`, cover upload, SEO fields), categories, profile page (name, password: PATCH /me); posts SEO columns; Vitest on configs |
| [x] | 12 | Portal foundation | Next App Router, locale routing (default unprefixed, second at /<code>) with messages/en.json + second.json, header/footer from public settings, generateMetadata, language switcher, section renderer (is_visible), Home, signed revalidate route; API: public settings + sections; Home screenshots per language |
| [x] | 13 | Portal pages | About, Services, Service detail (/services/<id>), Gallery, FAQ, Privacy, Terms, localised 404, in both languages; pages reuse the section views; messages fall back to the default text; API public service detail |
| [x] | 14 | Blog, contact, SEO | Blog list (category filter, pagination) and detail, contact page (captcha, honeypot, states, hours, map link), metadata/Open Graph/canonical/hreflang, sitemap.xml, robots.txt, JSON-LD, ISR tags + revalidate, Playwright smoke |
| [x] | 15 | Security, accessibility, performance | Advisory and floor re-check, composer/pnpm audit, Trivy; review of PKCE, policies (route audit script), uploads, captcha/throttle, CORS, nginx headers, secrets; axe + Lighthouse on the portal; memory sizing; findings in docs/SECURITY.md |
| [x] | 16 | Production and release | docker-compose.prod.yml (portal Node server, admin static via nginx, TLS from mounted certs, no dev ports/Mailpit, private DB, restart + healthchecks), backup/restore scripts, README, DEPLOY, CUSTOMIZE, UPGRADE, CHANGELOG, tag v1.0.0 |

<!-- TODO: the phase table was not included in the Phase 0 brief ("[paste the table below]").
     Paste phases 1..N here, one row each, with a [ ] checkbox. -->
