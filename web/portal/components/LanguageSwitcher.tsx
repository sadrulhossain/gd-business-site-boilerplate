'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { localeName, localePath, stripLocale } from '../lib/i18n'

/**
 * One link per locale to the same page in that language; the parent renders it only with two locales.
 * `locales` and `unprefixed` come from the server (env is not available in the browser bundle).
 */
export function LanguageSwitcher({ current, label, locales, unprefixed }: { current: string; label: string; locales: string[]; unprefixed: string }) {
  const rest = stripLocale(usePathname(), locales, unprefixed)

  return (
    <ul aria-label={label} className="flex items-center gap-1">
      {locales.map((code) => (
        <li key={code}>
          <Link
            href={localePath(code, rest, unprefixed)}
            hrefLang={code}
            lang={code}
            aria-current={code === current ? 'true' : undefined}
            className={`rounded-sm px-2 py-1 ${code === current ? 'bg-brand-700 text-white' : 'text-ink-muted hover:bg-brand-50'}`}
          >
            {localeName(code)}
          </Link>
        </li>
      ))}
    </ul>
  )
}
