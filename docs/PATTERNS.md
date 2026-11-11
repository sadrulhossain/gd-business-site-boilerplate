# Reference patterns

Canonical patterns to reuse (CLAUDE.md rule 3). Each names the reference file.

- **Invokable v1 controller**: `api/app/Http/Controllers/Api/V1/LocalesController.php`, registered in `api/routes/api.php` under `Route::prefix('v1')`. Response body is `{"data": ...}`; a docblock title/description feeds Scramble.
- **Error responses**: throw/abort normally; `api/app/Exceptions/JsonErrorRenderer.php` shapes them. Never build error JSON by hand.
- **Config from env**: `api/config/locales.php` (env read only in config files).
- **Feature test**: `api/tests/Feature/LocalesTest.php` (Pest `it(...)`, `getJson`, `assertExactJson`). Feature tests extend `Tests\TestCase` via `tests/Pest.php`.
- **Compose service env**: explicit `environment:` list per service (see `x-php` in `docker-compose.yml`).
