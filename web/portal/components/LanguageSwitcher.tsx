'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { localeName, localePath, stripLocale } from '../lib/i18n'

/**
 * A two-way toggle switch: one link per locale to the same page in that language, with a thumb that slides to the current one.
 * The parent renders it only with two locales. `locales` and `unprefixed` come from the server (env is not available in the browser bundle).
 */
export function LanguageSwitcher({ current, label, locales, unprefixed }: { current: string; label: string; locales: string[]; unprefixed: string }) {
  const rest = stripLocale(usePathname(), locales, unprefixed)
  const index = Math.max(0, locales.indexOf(current))

  return (
    <ul aria-label={label} className="relative grid grid-cols-2 rounded-full border border-line bg-brand-50 p-1">
      <li
        aria-hidden="true"
        className={`absolute inset-y-1 start-1 w-[calc(50%-0.25rem)] rounded-full bg-brand-600 shadow-soft transition-transform duration-300 ease-out ${index === 1 ? 'translate-x-full rtl:-translate-x-full' : ''}`}
      />
      {locales.map((code) => (
        <li key={code} className="relative z-10">
          <Link
            href={localePath(code, rest, unprefixed)}
            hrefLang={code}
            prefetch={false}
            lang={code}
            aria-current={code === current ? 'true' : undefined}
            className={`block min-w-14 rounded-full px-3 py-1.5 text-center text-sm font-semibold whitespace-nowrap transition-colors ${code === current ? 'text-white' : 'text-ink-muted hover:text-brand-700'}`}
          >
            {localeName(code)}
          </Link>
        </li>
      ))}
    </ul>
  )
}
