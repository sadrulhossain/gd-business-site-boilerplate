import type { Metadata } from 'next'
import type { Post, Settings } from './api'
import { defaultLocale, localePath, locales } from './i18n'

/** Public origin of the portal, for canonical URLs, Open Graph, the sitemap and JSON-LD. Set SITE_URL in production. */
export function siteUrl(): string {
  return (process.env.SITE_URL ?? 'http://localhost:3000').replace(/\/+$/, '')
}

export const absolute = (path: string): string => `${siteUrl()}${path}`

/** Canonical plus one hreflang per locale (and x-default) for the same page. Paths are unprefixed ("/blog"). */
export function alternates(locale: string, path: string): NonNullable<Metadata['alternates']> {
  return {
    canonical: localePath(locale, path),
    languages: { ...Object.fromEntries(locales.map((code) => [code, localePath(code, path)])), 'x-default': localePath(defaultLocale, path) },
  }
}

interface PageMeta {
  locale: string
  path: string
  siteName: string
  title?: string
  description?: string | null
  image?: string | null
  type?: 'website' | 'article'
  publishedTime?: string | null
  modifiedTime?: string | null
}

/**
 * Metadata for one page: title and description, canonical and hreflang, Open Graph and Twitter card.
 * `title` is the page's own part; the layout's template appends the site name. Without it the layout default applies.
 */
export function pageMetadata({ locale, path, siteName, title, description, image, type = 'website', publishedTime, modifiedTime }: PageMeta): Metadata {
  const text = description?.trim().slice(0, 200) || undefined

  return {
    ...(title ? { title } : {}),
    description: text,
    alternates: alternates(locale, path),
    openGraph: {
      type,
      siteName,
      locale,
      title: title ?? siteName,
      description: text,
      url: localePath(locale, path),
      images: image ? [{ url: image }] : undefined,
      ...(type === 'article' ? { publishedTime: publishedTime ?? undefined, modifiedTime: modifiedTime ?? undefined } : {}),
    },
    twitter: { card: image ? 'summary_large_image' : 'summary', title: title ?? siteName, description: text, images: image ? [image] : undefined },
  }
}

const DAY_IRI: Record<string, string> = {
  mon: 'Monday',
  tue: 'Tuesday',
  wed: 'Wednesday',
  thu: 'Thursday',
  fri: 'Friday',
  sat: 'Saturday',
  sun: 'Sunday',
}

/** schema.org LocalBusiness from public settings; empty fields are left out. */
export function localBusinessJsonLd(settings: Settings, locale: string): Record<string, unknown> {
  const opening = Object.entries(settings.hours ?? {})
    .filter(([day, entry]) => DAY_IRI[day] && !entry.closed && entry.open && entry.close)
    .map(([day, entry]) => ({ '@type': 'OpeningHoursSpecification', dayOfWeek: DAY_IRI[day], opens: entry.open, closes: entry.close }))
  const sameAs = Object.values(settings.social_links ?? {}).filter((url): url is string => Boolean(url))

  return clean({
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: settings.site_name,
    description: settings.tagline,
    url: absolute(localePath(locale)),
    telephone: settings.phone,
    email: settings.email,
    address: settings.address ? { '@type': 'PostalAddress', streetAddress: settings.address } : undefined,
    openingHoursSpecification: opening.length ? opening : undefined,
    sameAs: sameAs.length ? sameAs : undefined,
  })
}

/** schema.org Article for a blog post; the publisher is the business. */
export function articleJsonLd(post: Post, settings: Settings, locale: string): Record<string, unknown> {
  const url = absolute(localePath(locale, `/blog/${post.slug}`))

  return clean({
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: post.title,
    description: post.seo_description ?? post.excerpt,
    image: post.image?.url,
    datePublished: post.published_at ?? post.updated_at,
    dateModified: post.updated_at,
    inLanguage: locale,
    mainEntityOfPage: { '@type': 'WebPage', '@id': url },
    url,
    articleSection: post.category?.name,
    author: { '@type': 'Organization', name: settings.site_name },
    publisher: { '@type': 'Organization', name: settings.site_name },
  })
}

/** Drops undefined and null values, recursively through plain objects. */
function clean(value: Record<string, unknown>): Record<string, unknown> {
  return Object.fromEntries(
    Object.entries(value)
      .filter(([, v]) => v !== undefined && v !== null && v !== '')
      .map(([key, v]) => [key, v && typeof v === 'object' && !Array.isArray(v) ? clean(v as Record<string, unknown>) : v]),
  )
}
