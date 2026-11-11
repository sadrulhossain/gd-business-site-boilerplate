# Phases

Tick a phase only when it is finished and committed.

| Done | # | Phase | Scope / files |
|------|---|-------|---------------|
| [x] | 0 | Repo scaffold and docs | .gitignore, .editorconfig, CLAUDE.md, docs/* |
| [x] | 1 | Docker infrastructure | docker-compose.yml, docker/*, scripts/check-ports.sh, Makefile, .env.example, hello apps in web/admin, web/portal, api/public |
| [x] | 2 | Laravel API | api/*, Dockerfile (composer, phpredis), /api/v1/health, /api/v1/meta/locales, JSON errors, Pest/Pint/Larastan/Scramble, make t/check |
| [x] | 3 | Auth and RBAC | Passport 13, spatie/laravel-permission, menus table, /me, /me/menus, seeders, access tests |

<!-- TODO: the phase table was not included in the Phase 0 brief ("[paste the table below]").
     Paste phases 1..N here, one row each, with a [ ] checkbox. -->
