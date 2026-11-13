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

export interface Category {
  id: number
  name: string
  slug: string
}

export interface Post {
  id: number
  title: string
  slug: string
  excerpt: string | null
  /** Sanitised HTML (the API's HtmlSanitizer), safe to render as is. */
  body: string | null
  image: Media | null
  category: Category | null
  seo_title: string | null
  seo_description: string | null
  published_at: string | null
  updated_at: string
}

export interface PostPage {
  posts: Post[]
  page: number
  lastPage: number
}

/** One page of published posts (12 per page), optionally of one category. */
export async function getPosts(locale: string, { page = 1, category }: { page?: number; category?: string } = {}): Promise<PostPage> {
  const body = (await apiFor(locale).request('GET', '/v1/public/posts', { query: { page, category } })) as {
    data: Post[]
    meta?: { current_page: number; last_page: number }
  }

  return { posts: body.data, page: body.meta?.current_page ?? 1, lastPage: body.meta?.last_page ?? 1 }
}

/** One published post by slug; null when it is a draft, scheduled or gone. */
export async function getPost(locale: string, slug: string): Promise<Post | null> {
  try {
    return ((await apiFor(locale).request('GET', `/v1/public/posts/${encodeURIComponent(slug)}`)) as { data: Post }).data
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) return null
    throw error
  }
}

export async function getCategories(locale: string): Promise<Category[]> {
  return ((await apiFor(locale).request('GET', '/v1/public/categories')) as Listing<Category>).data
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
