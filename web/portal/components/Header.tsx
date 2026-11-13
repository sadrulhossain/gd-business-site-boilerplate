import Link from 'next/link'
import type { Section, Settings } from '../lib/api'
import { defaultLocale, localePath, locales, type Messages } from '../lib/i18n'
import { SECTION_PAGES } from '../lib/section-page'
import { LanguageSwitcher } from './LanguageSwitcher'

/** Site name and one anchor per visible section (the hero is the page top, so it has none). */
export function Header({ locale, settings, sections, messages }: { locale: string; settings: Settings; sections: Section[]; messages: Messages }) {
  const home = localePath(locale)

  return (
    <header className="border-b border-line bg-surface">
      <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-3">
        <Link href={home} className="font-display text-xl font-semibold text-brand-700">
          {settings.site_name}
        </Link>
        <nav aria-label={messages.nav.label} className="flex flex-wrap items-center gap-x-5 gap-y-1">
          {sections
            .filter((s) => s.key !== 'hero')
            .map((s) => (
              <Link key={s.key} href={SECTION_PAGES[s.key] ? localePath(locale, SECTION_PAGES[s.key]) : `${home}#${s.key}`} className="text-ink-muted hover:text-brand-700">
                {s.title}
              </Link>
            ))}
          {locales.length > 1 && <LanguageSwitcher current={locale} label={messages.language.label} locales={locales} unprefixed={defaultLocale} />}
        </nav>
      </div>
    </header>
  )
}
