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

## Admin modules (phase 9)
- CrudPage extensions, each used by two modules: `type: 'checkgroup'` (string[]; grouped options render a matrix) with `optionsFrom`
  (options fetched from the API) for users/roles; `reorder: {endpoint, group?}` (drag rows, sends `PUT {ids}`) for sections/menus.
- Inline boolean save in a table cell: `ToggleCell` from a column's `render`. Not a CrudPage feature.
- Single-record screens are not CrudPage: `SettingsPage` + a `SingletonConfig` (tabs of dot-path fields, e.g. `hours.mon.open`).
- API: reorder endpoints are `PUT <resource>/reorder {ids}` registered before the resource route (`ReorderRequest` subclass per table).
  The superadmin role is never assignable, editable or deletable through the API; a non-superadmin can only grant permissions they hold.

## Content modules (phase 10)
- `image: true` on a CrudConfig adds the image panel to the edit form (`POST|DELETE <endpoint>/<id>/image`); show it in the list with a column `render`.
- Uploads use `lib/upload.ts` `uploadFile` (XHR, for progress; same token refresh as the client). Validate with `checkImage` first, the API stays the authority.
- Bulk upload is `GalleryUploader` (create item, then upload; a failed upload deletes the empty item), mounted via `pages` in router.tsx.
- Read-only screens that are not CrudPage (contact inbox) are a page plus an `InboxConfig` (`messagesConfig.ts`).

## Blog and profile (phase 11)
- Rich text: field `type: 'richtext'` (TipTap, `components/RichText.tsx`, HTML in and out; works with `bilingual`). Keep its extensions inside the
  HtmlSanitizer allowlist (api `HtmlSanitizer::ALLOWED`); add a tag there first if the editor needs one.
- `status` (draft|published) is the API's view of `is_published` (`ResolvesPostStatus`); forms use `status`, never the boolean.
- Own account: `PATCH /v1/me` (`ProfileController`), page `pages/Profile.tsx`; no permission, any signed-in user.

## Portal (phase 12)
- Locales: `lib/i18n.ts` reads LOCALE_DEFAULT / LOCALE_SUPPORTED. `proxy.ts` rewrites unprefixed paths to `/<default>/...` and redirects an explicit
  default prefix away; pages live under `app/[locale]/`. Build links with `localePath(locale, path)`, never by hand. Env is server-only: client
  components take `locales` / `unprefixed` as props (see `LanguageSwitcher`).
- Text: `messages/en.json` (default locale) and `messages/second.json` (second); add every key to both. Content text comes from the API with `?lang=`.
- Data: only through `lib/api.ts` (`apiFor(locale)`, server side, `API_INTERNAL_URL`). Reads are `revalidate: 3600` and tagged with the API table name;
  `POST /api/revalidate` (HMAC, same secret as the API webhook) expires a tag. New public resource: add it to `TAGS` there.
- Sections: add a view to `components/sections/index.tsx` keyed by the section `key`; the renderer skips `is_visible: false` and falls back to a generic view.
- Styling: logical utilities only (`ms-`, `me-`, `ps-`, `pe-`, `text-start`, `border-s`), so RTL locales work. `images.unoptimized` is on: use `components/Media.tsx`.
- Dev note: after changing a server component that a client component consumes, a stale Turbopack cache can serve old props; `rm -rf web/portal/.next` and restart node.
- Screenshots: `docker compose --profile e2e run --rm --no-deps -T -w /app/web/portal e2e npx playwright test`.

## Portal pages (phase 13)
- A page for a section is `lib/section-page.tsx` `sectionPage('<key>', [extra keys])` in a two-line `page.tsx`: it renders the same `SectionBlock` view as Home
  (title becomes the h1) and 404s when the section is hidden. Add the key to `SECTION_PAGES` so the header links to the page instead of a Home anchor.
- Static legal text (`privacy`, `terms`) lives in `messages/*.json` under `legal`, with `{site}` / `{email}` filled from settings (`lib/legal-page.tsx`).
  It is a generic template: the business must review it.
- `getMessages` falls back key by key to `en.json`, so a missing or empty entry in `second.json` shows the default text. API content does the same via `?lang=`.
- 404: `app/[locale]/[...rest]/page.tsx` calls `notFound()` for unknown paths; `not-found.tsx` must not read request headers (that makes every locale route dynamic, no ISR): `NotFoundContent` picks the locale from the URL.
- Proxy matcher: keep the double backslash in `.*\..*`; a single one silently matches nothing useful. After editing `proxy.ts` restart node with a clean `.next`.

## Blog, contact and SEO (phase 14)
- Every page builds its metadata with `lib/seo.ts` `pageMetadata({locale, path, siteName, ...})`: title, description, canonical, hreflang (all locales + x-default), Open Graph, Twitter.
  `SITE_URL` is the public origin used for all absolute URLs; `metadataBase` is set in the locale layout.
- JSON-LD goes through `components/JsonLd.tsx` (escapes `<`): LocalBusiness on Home, Article on a post. Build the objects in `lib/seo.ts`.
- `app/sitemap.ts` and `app/robots.ts` use the same cached, tagged fetches as the pages, so the revalidate webhook refreshes them. New public page: add it to the sitemap.
- Blog: `/blog` (`?category=<slug>`, `?page=n`, canonical is the plain list), `/blog/<slug>`. Post `body` is sanitised by the API and rendered as HTML in `.post-body`.
- Contact: `components/ContactForm.tsx` talks only to `app/api/contact/*` (server proxies to the API; the captcha is one-time, refetched after every submit). The honeypot field is `website`.
- Smoke tests: `web/portal/e2e/smoke.spec.ts` (`docker compose --profile e2e run --rm --no-deps -T -w /app/web/portal e2e npx playwright test smoke`). The contact test leaves one message with subject `e2e-smoke`; the blog test skips without published posts.

## Security and quality gates (phase 15)
- `make security` (also part of `make check`): `php artisan security:routes` (every `auth:api` route needs `can:` or a policy/Gate call; own-account routes are listed in `AuditRoutes::ALLOWED`), `scripts/check-secrets.sh`, `scripts/check-floors.mjs`. A new route gets its check; do not add allowlist entries.
- nginx: `include /etc/nginx/snippets/security-headers.conf;` in every server and in every location that sets its own `add_header` (those replace inherited ones). Set the CSP per vhost.
- The OAuth authorize step is S256-only (`RequirePkceS256`). Cross-origin API access is closed; list origins in `CORS_ALLOWED_ORIGINS` only when a browser app needs it.
- Portal: never read `headers()` or `cookies()` in the locale layout, `not-found.tsx` or shared components (every page turns dynamic and loses ISR). `web/portal/e2e/a11y.spec.ts` (axe) must stay at zero violations; white text needs `bg-brand-700` or darker.
- Dev edits do not reach the node container's file watcher on Windows: after changing code, stop node, delete `web/portal/.next` and start it again.
