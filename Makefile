SHELL := /bin/sh
COMPOSE := docker compose
LOG := .cache/make.log

# run quietly: log everything, print only the last 20 lines on failure
define quiet
mkdir -p .cache; $(1) >$(LOG) 2>&1 || { tail -n 20 $(LOG); exit 1; }
endef

.PHONY: up down ports versions security t check env openapi web e2e

env:
	@[ -f .env ] || { cp .env.example .env; sed -i.bak "s|^APP_KEY=.*|APP_KEY=base64:$$(openssl rand -base64 32)|" .env; rm -f .env.bak; echo "created .env from .env.example (APP_KEY generated; change the passwords)"; }

up: env
	@$(call quiet,sh scripts/check-ports.sh)
	@$(call quiet,$(COMPOSE) build -q php)
	@$(call quiet,$(COMPOSE) up -d --build --wait --quiet-pull)
	@sh scripts/ensure-test-db.sh
	@$(COMPOSE) ps --format '{{.Name}} {{.Status}}'

down:
	@$(call quiet,$(COMPOSE) down --remove-orphans)
	@echo "stack down"

ports:
	@sh scripts/check-ports.sh

versions:
	@bad=0; for kv in $$(grep -E '^[A-Z_]+_VERSION=' .env); do \
	  k=$${kv%%=*}; v=$${kv#*=}; \
	  if grep -qF "| $$v " docs/VERSIONS.md; then echo "ok       $$k=$$v"; else echo "MISMATCH $$k=$$v"; bad=1; fi; \
	done; exit $$bad

# security gates: routes without a policy/permission, secrets in the repo, versions below their floor (docs/SECURITY.md)
security:
	@$(COMPOSE) exec -T php php artisan security:routes
	@sh scripts/check-secrets.sh
	@node scripts/check-floors.mjs | tail -n 1

# targeted tests: make t FILTER=SomeTest
t:
	@[ -n "$(FILTER)" ] || { echo "usage: make t FILTER=<name>"; exit 2; }
	@$(call quiet,$(COMPOSE) exec -T php vendor/bin/pest --compact --filter='$(FILTER)')
	@tail -n 3 $(LOG)

# run once at the end of a phase (needs the stack up)
check:
	@$(call quiet,$(COMPOSE) config -q)
	@for f in scripts/*.sh docker/*/*.sh; do sh -n $$f || exit 1; done
	@[ -n "$$($(COMPOSE) ps --status running -q php)" ] || { echo "check: stack is down, run make up"; exit 1; }
	@$(call quiet,$(COMPOSE) exec -T nginx nginx -t)
	@$(call quiet,$(COMPOSE) exec -T php vendor/bin/pint --test)
	@$(call quiet,$(COMPOSE) exec -T php vendor/bin/phpstan analyse --memory-limit=1G --no-progress)
	@$(call quiet,$(COMPOSE) exec -T php vendor/bin/pest --compact)
	@$(call quiet,$(COMPOSE) exec -T php composer audit)
	@$(MAKE) --no-print-directory versions
	@$(MAKE) --no-print-directory security
	@echo "check: ok"

openapi:
	@mkdir -p packages/api-types
	@$(COMPOSE) exec -T php php artisan scramble:export --path=storage/openapi.json >/dev/null
	@mv api/storage/openapi.json packages/api-types/openapi.json
	@echo "wrote packages/api-types/openapi.json"

# JS workspace: install, regenerate API types, typecheck, build both apps (one-off node container)
web:
	@$(call quiet,$(COMPOSE) run --rm --no-deps -T -e NODE_ENV=production node sh -c 'cd /app && corepack enable && pnpm install --frozen-lockfile && pnpm generate:types && pnpm -r --if-present typecheck && pnpm -r --if-present build')
	@tail -n 3 $(LOG)

# Login + dashboard screenshots (web/admin/e2e/screenshots). Needs the stack up and E2E_EMAIL / E2E_PASSWORD of a user with dashboard.view in the environment.
e2e:
	@$(COMPOSE) --profile e2e run --rm --no-deps -T e2e npx playwright test 2>&1 | tail -n 15
