import type { FieldType } from './components/FormField'

export interface SettingsField {
  /** Dot path into the settings object: `phone`, `social_links.x`, `hours.mon.open`. */
  name: string
  label: string
  type?: Extract<FieldType, 'text' | 'textarea' | 'checkbox'>
  /** Renders `name` and `name_2nd` (top-level text fields only). */
  bilingual?: boolean
  required?: boolean
  hint?: string
}

/** An uploaded image kept by its own endpoint (`POST|DELETE /v1/settings/<name>`), not by the PATCH form. */
export interface SettingsImage {
  name: string
  /** Tailwind size classes for the preview of the current image. */
  preview?: string
  label: string
  hint: string
  /** Also accept an .ico file. */
  ico?: boolean
}

export interface SettingsTab {
  id: string
  label: string
  fields: SettingsField[]
  images?: SettingsImage[]
}

/** The singleton screen: one record, one form, fields grouped into tabs. Edit-only, gated by settings.view / settings.update. */
export interface SingletonConfig {
  key: string
  path: string
  title: string
  endpoint: string
  tabs: SettingsTab[]
}

const DAYS = [
  ['mon', 'Monday'],
  ['tue', 'Tuesday'],
  ['wed', 'Wednesday'],
  ['thu', 'Thursday'],
  ['fri', 'Friday'],
  ['sat', 'Saturday'],
  ['sun', 'Sunday'],
] as const

const SOCIAL = [
  ['facebook', 'Facebook'],
  ['instagram', 'Instagram'],
  ['x', 'X'],
  ['linkedin', 'LinkedIn'],
  ['youtube', 'YouTube'],
  ['tiktok', 'TikTok'],
  ['whatsapp', 'WhatsApp'],
] as const

export const settings: SingletonConfig = {
  key: 'settings',
  path: '/settings',
  title: 'Settings',
  endpoint: '/v1/settings',
  tabs: [
    {
      id: 'general',
      label: 'General',
      images: [
        { name: 'logo', preview: 'max-h-24', label: 'Site logo', hint: 'Shown in the portal header and footer and in this admin. JPEG, PNG or WebP; a wide transparent PNG works best. Until you upload one, the built-in logo is shown.' },
        { name: 'favicon', preview: 'size-16', label: 'Favicon', hint: 'The small icon in the browser tab. An .ico or a square PNG (at least 64 x 64 px). Until you upload one, the built-in icon is shown.', ico: true },
      ],
      fields: [
        { name: 'site_name', label: 'Site name', bilingual: true, required: true },
        { name: 'tagline', label: 'Tagline', bilingual: true },
        { name: 'currency', label: 'Currency code', hint: 'For example USD, EUR or BDT.' },
        { name: 'currency_symbol', label: 'Currency symbol', hint: 'Shown before every price on the site, for example $, € or ৳.' },
      ],
    },
    {
      id: 'contact',
      label: 'Contact',
      fields: [
        { name: 'address', label: 'Address', type: 'textarea', bilingual: true },
        { name: 'phone', label: 'Phone' },
        { name: 'email', label: 'Email' },
      ],
    },
    {
      id: 'hours',
      label: 'Opening hours',
      fields: DAYS.flatMap(([day, label]) => [
        { name: `hours.${day}.closed`, label: `${label}: closed`, type: 'checkbox' as const },
        { name: `hours.${day}.open`, label: `${label}: opens`, hint: 'HH:MM, 24-hour' },
        { name: `hours.${day}.close`, label: `${label}: closes`, hint: 'HH:MM, 24-hour' },
      ]),
    },
    {
      id: 'social',
      label: 'Social links',
      fields: SOCIAL.map(([network, label]) => ({ name: `social_links.${network}`, label, hint: 'Full URL, https://...' })),
    },
  ],
}

/** The landing-page hero: its own screen (permissions hero.view / hero.update) over the hero section row. */
export const hero: SingletonConfig = {
  key: 'hero',
  path: '/hero',
  title: 'Hero',
  endpoint: '/v1/hero',
  tabs: [
    {
      id: 'hero',
      label: 'Landing page hero',
      images: [
        {
          name: 'image',
          label: 'Banner',
          preview: 'max-h-40',
          hint: 'A wide image, at least 1600 x 700 px. The title, description and buttons stay readable on top of any banner. Until you upload one, a green gradient is shown.',
        },
      ],
      fields: [
        { name: 'title', label: 'Title', bilingual: true, required: true },
        { name: 'content', label: 'Description', type: 'textarea', bilingual: true },
      ],
    },
  ],
}

/** The About us section: its own screen (permissions about.view / about.update) over the about section row. */
export const about: SingletonConfig = {
  key: 'about',
  path: '/about',
  title: 'About us',
  endpoint: '/v1/about',
  tabs: [
    {
      id: 'about',
      label: 'About us',
      images: [{ name: 'image', label: 'Image', preview: 'max-h-40', hint: 'Shown beside the text. JPEG, PNG or WebP. Without an image the text is centred.' }],
      fields: [
        { name: 'title', label: 'Title', bilingual: true, required: true },
        { name: 'content', label: 'Text', type: 'textarea', bilingual: true, hint: 'Leave a blank line between paragraphs.' },
      ],
    },
  ],
}

/** Top-level API keys the form owns (bilingual fields add their `_2nd` twin). Only these are sent back. */
export function settingsKeys(config: SingletonConfig): string[] {
  const keys = config.tabs.flatMap((tab) => tab.fields).flatMap((f) => [f.name.split('.')[0]!, ...(f.bilingual ? [`${f.name}_2nd`] : [])])

  return [...new Set(keys)]
}
