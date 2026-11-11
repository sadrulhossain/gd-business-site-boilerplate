SHELL := /bin/sh
COMPOSE := docker compose
LOG := .cache/make.log

# run quietly: log everything, print only the last 20 lines on failure
define quiet
mkdir -p .cache; $(1) >$(LOG) 2>&1 || { tail -n 20 $(LOG); exit 1; }
endef

.PHONY: up down ports versions t check env

env:
	@[ -f .env ] || { cp .env.example .env; echo "created .env from .env.example (change the passwords)"; }

up: env
	@$(call quiet,sh scripts/check-ports.sh)
	@$(call quiet,$(COMPOSE) build -q php)
	@$(call quiet,$(COMPOSE) up -d --build --wait --quiet-pull)
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

# targeted tests: make t FILTER=SomeTest
t:
	@[ -n "$(FILTER)" ] || { echo "usage: make t FILTER=<name>"; exit 2; }
	@if [ -f api/artisan ]; then $(call quiet,$(COMPOSE) exec -T php php artisan test --filter='$(FILTER)'); echo "t: ok"; else echo "t: no test runner yet (Laravel not installed)"; fi

# run once at the end of a phase
check:
	@$(call quiet,$(COMPOSE) config -q)
	@for f in scripts/*.sh docker/*/*.sh; do sh -n $$f || exit 1; done
	@if [ -n "$$($(COMPOSE) ps --status running -q nginx)" ]; then \
	  $(call quiet,$(COMPOSE) exec -T nginx nginx -t); \
	  $(call quiet,$(COMPOSE) exec -T php php -l public/index.php); \
	else echo "check: stack down, skipped runtime checks"; fi
	@$(MAKE) --no-print-directory t FILTER=all
	@echo "check: ok"
