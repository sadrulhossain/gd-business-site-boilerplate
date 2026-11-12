# Reference patterns

Copy these shapes, do not invent new ones. Reference module: Sections (`Section*` under `api/app`);
singleton reference: Settings. Paths below are relative to `api/app` unless noted.

## New module checklist
1. **Migration + Model**: `#[Fillable]`, `casts()`. Bilingual text = columns `x` + `x_2nd`, `use HasSecondLanguage`.
2. **Policy** `Policies/<Model>Policy.php`: one method per action, `$user->checkPermissionTo('<module>.<action>')`.
   Permission names are `<module>.<action>`; the superadmin passes via `Gate::before`.
3. **FormRequest** `Http/Requests/Api/V1/<Action><Model>Request.php`: `authorize()` returns
   `$this->user()?->can('<ability>', $model)` (403 comes before validation). `rules()` use `sometimes`
   for PATCH and wrap bilingual fields in `Bilingual::rules([...])`.
4. **Resource** `Http/Resources/Api/V1/<Model>Resource.php`: explicit field list, both languages, no model dumps.
5. **Controller** `Http/Controllers/Api/V1/<Model>Controller.php`: thin. Reads call `Gate::authorize()`; writes
   type-hint the FormRequest, `$model->update($request->validated())`, return the Resource. The first docblock
   line is the Scramble title. Never build error JSON by hand: `Exceptions/JsonErrorRenderer` shapes it.
6. **Routes** in `routes/api.php`, inside the `auth:api` group: `apiResource(...)->only([...])`, `apiSingleton`
   for one-row tables; no create/delete for seeded rows. Non-model routes use `->middleware('can:<perm>')`.
7. **Seeder** `database/seeders/<Module>ModuleSeeder.php` extends `ModuleSeeder` (permissions, menu tree, role
   grants). Seed defaults with `firstOrCreate` so re-seeding never overwrites edits. Add it to `DatabaseSeeder`.
8. **Tests** `tests/Feature/<Module>/`: 401 anonymous, 403 missing permission, happy path, 422 validation
   (assert the field keys), seeder idempotence. Authenticate with `actingAsRole($role, [$perms])` (`tests/Pest.php`).

## Uploads
- `Services/MediaService`: `store($file, '<dir>')` returns the path kept in the model's `image` column;
  `describe($path)` goes in the Resource; `delete($old)` on replace or remove.
- Images upload through their own multipart endpoint: `POST|DELETE <resource>/{id}/image`
  (`SectionImageController`). Validate with `MediaService::imageRules()` (mime by content, size, dimensions).
- Files live at `<dir>/<random>/original.<ext>`; the queued `Jobs/GenerateWebpVariants` adds `<width>.webp`.

## Config and environment
- `env()` is read only in `config/*.php` (e.g. `config/locales.php`, `config/uploads.php`).
- Tests force their environment in `tests/bootstrap.php` (testing DB, array cache, sync queue); compose env
  beats phpunit.xml. Each compose service lists its own `environment:` (see `x-php` in `docker-compose.yml`).

## Public reads, contact, webhook (phase 6)
- Public reads live in the `LocalizePublicResponse` route group (`public/*`): the controller's `publicIndex`/`publicShow` skips
  `Gate::authorize` and returns visible rows only. `?lang=` collapses `x`/`x_2nd` pairs; no per-resource code is needed.
- Rich text: assign it to a model attribute whose setter calls `Services/HtmlSanitizer` (see `Post::body`); never sanitise in controllers.
- Public forms: honeypot field (`prohibited` rule), `Services/Captcha` checked in the FormRequest's `after()`, named limiter in
  `AppServiceProvider`, queued Notification to `Notification::route('mail', ...)`. Answer with `{"data":{"received":true}}`.
- Content-change webhook: add the model to the list in `AppServiceProvider::boot()` (it gets `ContentObserver`); tag = table name.
  Not for private data (contact messages). Tests use `Http::fake()` per test.
- OpenAPI: `make openapi` exports Scramble's spec to `packages/api-types/openapi.json`; commit it when routes change.

## Frontend (phase 7)
- Theme: import `tailwindcss` then `@gd/tokens/<app>.css` in the app's one CSS file. Use token utilities (`bg-primary-600`, `text-ink`,
  `border-line`, `font-display`), never raw hex. New tokens go in packages/tokens, not in an app.
- Data: type API data with `components['schemas'][...]` from `@gd/api-types`; call the API with `@gd/api-client` (`createClient`), never raw fetch.
- Regenerate after API changes: `make openapi` (migrated DB), then `pnpm generate:types`; commit both outputs.
- Tailwind only sees literal class names: map values to full class strings, do not build them with template pieces.

## Admin module (phase 8)
- A CRUD screen is one entry in `web/admin/src/modules.ts`: `key` (permission prefix: `<key>.view|create|update|delete`), `path` (matches the
  `/me/menus` row), `endpoint`, `columns`, `fields` (`bilingual: true` renders "Title (EN)" / "Title (FR)" from `/v1/meta/locales`). The router,
  permission gate and buttons follow from it; add a page component only when the screen is not plain CRUD.
- Calls go through `src/api.ts` (token + refresh handled); never read tokens from the store in components. Use `can('x.y')` for UI gating,
  the API remains the authority.
- Shared pieces: `DataTable`, `FormField`, `BilingualField`, `ConfirmDialog`, `Toast`; do not add a second of any.
