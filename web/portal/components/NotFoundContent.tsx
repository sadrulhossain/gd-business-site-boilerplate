'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'

export type NotFoundTexts = { title: string; text: string; home: string }

/**
 * Localised 404 body. A not-found file gets no route params and reading request headers would make every page dynamic,
 * so the server file passes the texts of all locales and the locale is read here from the URL (the default locale has no prefix).
 */
export function NotFoundContent({ texts, defaultLocale }: { texts: Record<string, NotFoundTexts>; defaultLocale: string }) {
  const first = usePathname().split('/')[1] ?? ''
  const locale = first !== defaultLocale && first in texts ? first : defaultLocale
  const t = texts[locale]!

  return (
    // Next renders 404s inside its own <html> (no lang attribute), hence `lang` on the content wrapper.
    <div lang={locale} className="mx-auto max-w-3xl px-4 py-20 text-center">
      <p className="font-display text-6xl font-semibold text-brand-600">404</p>
      <h1 className="mt-4 font-display text-3xl font-semibold">{t.title}</h1>
      <p className="mt-3 text-ink-muted">{t.text}</p>
      <Link href={locale === defaultLocale ? '/' : `/${locale}`} className="mt-6 inline-block rounded-full bg-brand-600 px-6 py-3 font-semibold text-white hover:bg-brand-700">
        {t.home}
      </Link>
    </div>
  )
}
