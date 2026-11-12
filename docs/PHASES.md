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

<!-- TODO: the phase table was not included in the Phase 0 brief ("[paste the table below]").
     Paste phases 1..N here, one row each, with a [ ] checkbox. -->
