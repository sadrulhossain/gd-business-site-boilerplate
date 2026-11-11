# gd-business-site-boilerplate

## Repo map
- `api/` — Laravel API (PHP 8.5, PostgreSQL, Redis)
- `web/admin` — Next.js admin app
- `web/portal` — Next.js public portal
- `packages/` — shared packages
- `docker/` — Dockerfiles and compose config
- `docs/` — PHASES.md, VERSIONS.md, ENV.md, DECISIONS.md, PATTERNS.md

## Make commands
- `make up` / `make down` — start (builds, waits for healthy) / stop the stack
- `make ports` — check host ports from .env are free or ours
- `make versions` — check .env *_VERSION pins against docs/VERSIONS.md
- `make t FILTER=...` — targeted tests only
- `make check` — full lint + tests (run once, at the end of a phase)

## Rules
1) One phase at a time; read only CLAUDE.md, the phase row and files it names.
2) No repo-wide exploring, no unrelated refactors, no dependency not in VERSIONS.md.
3) Reuse the reference patterns in docs/PATTERNS.md; do not invent new ones.
4) While working run only targeted tests (make t FILTER=...); run make check once at the end.
5) Keep command output short (quiet flags, | tail -n 20). Never paste logs back.
6) No screenshots unless the phase says so.
7) Finish: tick the phase, git commit "phase N: title", reply in 8 lines or fewer
   (done, deviations, decisions I must make), then STOP and wait for approval.
8) Commits and PRs: never add a Claude signature, Co-Authored-By trailer, "Generated with" line
   or any other Claude attribution to commit messages, PR descriptions, code or docs. Anywhere.
