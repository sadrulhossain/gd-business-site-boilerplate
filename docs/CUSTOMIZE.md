# Customize

Everything here is a code change in your fork, then `make check`. Content (texts, images, services, posts, hours, section visibility) is edited in the admin, not in code.

## Brand colours and fonts

Colours, radii and fonts are design tokens, one file per app, shared through the `@gd/tokens` package:

| App | File | Utilities |
|-----|------|-----------|
| Portal | `packages/tokens/portal.css` | `bg-brand-600`, `text-ink`, `border-line`, `font-display` |
| Admin | `packages/tokens/admin.css` | `bg-primary-600`, `text-ink`, `border-line` |

Change the values inside `@theme { ... }`: the ten `--color-brand-50` to `--color-brand-900` steps (portal) or `--color-primary-*` (admin), `--color-canvas` / `--color-surface` / `--color-ink` / `--color-line`, `--font-display`, `--radius-*`.


The admin theme is light and dark: the semantic colours (`--canvas`, `--surface`, `--ink`, `--line`, ...) are CSS variables set in `:root` and `.dark` at the top of `packages/tokens/admin.css`; the `@theme` block maps them to utilities. `--color-primary-300` to `800` are fixed brand steps. The sidebar is glass over `web/admin/public/images/sidebar-bg-img.jpg` (swap the image to rebrand). `web/admin/public/theme.js` applies the saved theme before first paint (the CSP forbids inline scripts). Icons are inline SVG in `web/admin/src/components/Icon.tsx`; add a path set there for a new menu icon name.
Rules that keep the site accessible and consistent:

- Use token utilities only, never raw hex in components (the tokens reset Tailwind's palette on purpose, so an unknown colour name produces nothing).
- White text on a brand colour needs 4.5:1 contrast: the portal uses `bg-brand-700` or darker behind white text. After changing the brand, run the axe spec
  (`docker compose --profile e2e run --rm --no-deps -T -w /app/web/portal e2e npx playwright test a11y`) and fix any `color-contrast` it reports.
- Tailwind only sees literal class names. Write `'bg-brand-700'`, do not build class names from pieces.
- Logo and favicon: upload them in the admin under Access > Settings > General. The favicon may be an .ico, PNG, JPEG or WebP. They show in the portal header, footer and tab icon and in the admin sidebar and tab. Until you upload them, the built-in mark (`components/SiteLogo.tsx`, `web/portal/public/icon.svg`) is used. The site name, tagline, address, phone, email and opening hours are Settings in the admin.
- Landing page: the hero (title, description, banner) is under Content > Hero; About us (title, text, image) is under Content > About us; the other sections are under Content > Sections. Pricing is a section that starts hidden: add prices per service under Content > Pricing, then switch the Pricing section on in Content > Sections. It then appears in the Explore menu, as a "See our pricing" block on the landing page, on /pricing, and on each service page. The currency code and symbol are in Settings > General.
- Sample content: `php artisan db:seed --class="DatabaseSeedersSiteSiteDataSeeder"` fills settings, sections, services, testimonials, team, FAQs and blog posts with demo text (or run one `Site*Seeder` alone). They are not part of `DatabaseSeeder`; re-running updates the same rows.
- Fonts: the portal uses the system sans-serif stack for headings (no font download). To use a web font, self-host it and update `--font-display`; the CSP allows `font-src 'self' data:` only.

## A second language

The site is bilingual by configuration: one default language and at most one more.

1. In `.env` / `.env.prod`: `LOCALE_DEFAULT=en`, `LOCALE_SUPPORTED=en,bn` (use any two codes; the first one that is not the default becomes the second language). `LOCALE_FALLBACK` stays the default.
   Restart the stack. The portal then serves the second language at `/<code>` (the default has no prefix), shows a language switcher, adds hreflang links and lists both languages in the sitemap.
2. Texts that are not content live in `web/portal/messages/en.json` (the default; also the fallback for any missing key) and `web/portal/messages/second.json`. Translate `second.json`; an empty or missing key falls back to the default text.
   The legal pages (`legal.privacy`, `legal.terms`) are a generic template in these files: **have them reviewed for your business and country**.
3. Content texts have a second column for every translatable field (`title` and `title_2nd`). The admin shows both fields once a second language is enabled. Empty second-language text falls back to the default.
4. The `<html dir>` switches to `rtl` for `ar`, `he`, `fa`, `ur`; the CSS uses logical properties (`ms-*`, `text-start`, `border-s`) so layouts mirror without extra work. Keep using them.
5. Another pair than English/Bengali: also check `web/portal/lib/i18n.ts` (language names) and the test fixtures that assume `bn`.

To run with one language only, set `LOCALE_SUPPORTED=en`; the switcher and the `/<code>` routes disappear.

## Add a module (a new managed content type)

Follow [PATTERNS.md](PATTERNS.md) literally; it is the contract. The shortest path is to copy a close relative (Services for a list with an image, FAQs for a plain list, Settings for a single row):

1. API, in the order of "New module checklist": migration and model, policy, FormRequests, Resource, controller, routes inside the `auth:api` group, `<Module>ModuleSeeder` (permissions `<module>.view|create|update|delete`, a menu row, role grants) added to `DatabaseSeeder`, tests.
2. `php artisan security:routes` must still pass: every new route needs a `can:` middleware or a policy/Gate call.
3. Public reads (if the portal shows it): a `publicIndex` in the controller, a route in the `public/*` group, and add the model to the webhook list in `AppServiceProvider::boot()` so editing it refreshes the portal.
4. `make openapi` then `pnpm generate:types`, commit `packages/api-types`.
5. Admin: one entry in `web/admin/src/modules.ts` (see "Admin module" in PATTERNS.md). Plain CRUD needs no page component.
6. Portal: a fetch function in `web/portal/lib/api.ts` (tag it with the table name), then a component or page (see "Portal" and "Portal pages").
7. Add the new route to `web/portal/app/sitemap.ts` if it is a public page, and keep `a11y.spec.ts` green.
8. Production: run the new module seeder once: `dcp exec php php artisan db:seed --class=<Module>ModuleSeeder --force` (see UPGRADE.md).

## Add a section to the home page

Sections are the blocks of the home page (hero, about, services, contact, ...). The admin can show, hide, reorder and edit their text and image; code is needed only for a new kind.

1. API: add the key and its default title to `DEFAULTS` in `api/database/seeders/SectionsModuleSeeder.php` (`'key' => ['Title', visible]`; optional sections start hidden) and seed it (`db:seed --class=SectionsModuleSeeder`). Seeding only creates missing rows; it never overwrites edits.
2. Portal: render it in `web/portal/components/sections/index.tsx` (`SectionBlock` picks the view by `section.key`; the generic text and image view is the default, so a text-only section needs no code).
   If it lists data (like services), fetch it in `lib/api.ts` and pass it in.
3. Optional own page (`/about`, `/services`, ...): add the key to `SECTION_PAGES` in `web/portal/lib/section-page.tsx` and create `app/[locale]/<key>/page.tsx` containing `export default sectionPage('<key>')` (copy `about/page.tsx`); add it to the sitemap's paths if it needs more than the default. The header then links to the page instead of a home anchor.
4. Add the section title to the messages if it needs a fixed label, in both message files.
5. Run the axe spec and `make check`.
