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

<!-- TODO: the phase table was not included in the Phase 0 brief ("[paste the table below]").
     Paste phases 1..N here, one row each, with a [ ] checkbox. -->
