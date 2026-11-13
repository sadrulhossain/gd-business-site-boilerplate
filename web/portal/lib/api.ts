import { ApiError, createClient } from '@gd/api-client'
import { defaultLocale } from './i18n'

/**
 * Server-side API access with classic Next caching: every public read is cached for REVALIDATE seconds and tagged with
 * the table name the API's webhook sends (/api/revalidate), so an edit in the admin refreshes just that content.
 */
export const REVALIDATE = 3600

/** "/v1/public/team" -> "team_members", the tag ContentObserver sends (the model's table name). */
const TAGS: Record<string, string> = {
  settings: 'settings',
  sections: 'sections',
  faqs: 'faqs',
  services: 'services',
  testimonials: 'testimonials',
  team: 'team_members',
  gallery: 'gallery_items',
  categories: 'categories',
  posts: 'posts',
}

export function tagFor(url: string): string[] {
  const resource = new URL(url).pathname.split('/public/')[1]?.split('/')[0]
  const tag = resource ? TAGS[resource] : undefined

  return tag ? [tag] : []
}

const baseUrl = () => process.env.API_INTERNAL_URL ?? 'http://api.localhost/api'

/** One client per locale: `?lang=` makes the API return a single language per field (the second falls back to the first). */
export function apiFor(locale: string) {
  return createClient({
    baseUrl: baseUrl(),
    lang: locale === defaultLocale ? undefined : locale,
    fetch: (input, init) => fetch(input, { ...init, next: { revalidate: REVALIDATE, tags: tagFor(String(input)) } }),
  })
}

export interface Media {
  url: string
  webp: Record<string, string>
}

export interface Settings {
  site_name: string
  tagline: string | null
  address: string | null
  phone: string | null
  email: string | null
  hours: Record<string, { open: string | null; close: string | null; closed: boolean }> | null
  social_links: Record<string, string | null>
}

export interface Section {
  id: number
  key: string
  title: string
  content: string | null
  image: Media | null
  sort_order: number
  is_visible: boolean
}

export interface Service {
  id: number
  title: string
  description: string | null
  image: Media | null
}

export interface Testimonial {
  id: number
  author_name: string
  author_role: string | null
  quote: string
  image: Media | null
}

export interface TeamMember {
  id: number
  name: string
  role: string
  bio: string | null
  image: Media | null
}

export interface GalleryItem {
  id: number
  title: string | null
  caption: string | null
  image: Media | null
}

export interface Faq {
  id: number
  question: string
  answer: string
}

type Listing<T> = { data: T[] }

export async function getSettings(locale: string): Promise<Settings> {
  return ((await apiFor(locale).request('GET', '/v1/public/settings')) as { data: Settings }).data
}

/** Visible sections in display order. The API already filters; `is_visible` is checked again so a stale cache cannot show a hidden one. */
export async function getSections(locale: string): Promise<Section[]> {
  const rows = ((await apiFor(locale).request('GET', '/v1/public/sections')) as Listing<Section>).data

  return rows.filter((section) => section.is_visible)
}

/** One visible service; null when it is hidden or gone (the API answers 404). */
export async function getService(locale: string, id: string): Promise<Service | null> {
  try {
    return ((await apiFor(locale).request('GET', `/v1/public/services/${encodeURIComponent(id)}`)) as { data: Service }).data
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) return null
    throw error
  }
}

export async function getList<T>(locale: string, resource: 'services' | 'testimonials' | 'team' | 'gallery' | 'faqs'): Promise<T[]> {
  return ((await apiFor(locale).request('GET', `/v1/public/${resource}`)) as Listing<T>).data
}
