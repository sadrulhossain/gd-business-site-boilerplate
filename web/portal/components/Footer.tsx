import type { Settings } from '../lib/api'
import Link from 'next/link'
import { localePath, type Messages } from '../lib/i18n'

const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as const

const SOCIAL: Record<string, string> = { facebook: 'Facebook', instagram: 'Instagram', x: 'X', linkedin: 'LinkedIn', youtube: 'YouTube', tiktok: 'TikTok', whatsapp: 'WhatsApp' }

/** Contact details, opening hours and social links, all from public settings. */
export function Footer({ locale, settings, messages }: { locale: string; settings: Settings; messages: Messages }) {
  const social = Object.entries(settings.social_links ?? {}).filter(([, url]) => url)
  const hours = DAYS.flatMap((day) => {
    const entry = settings.hours?.[day]

    return entry ? [{ day, entry }] : []
  })

  return (
    <footer className="border-t border-line bg-surface">
      <div className="mx-auto grid max-w-6xl gap-8 px-4 py-8 md:grid-cols-3">
        <div className="space-y-2">
          <p className="font-display text-lg font-semibold">{settings.site_name}</p>
          {settings.tagline && <p className="text-ink-muted">{settings.tagline}</p>}
        </div>

        <div className="space-y-2">
          <h2 className="font-semibold">{messages.footer.contact}</h2>
          <address className="space-y-1 not-italic text-ink-muted">
            {settings.address && <p className="whitespace-pre-line">{settings.address}</p>}
            {settings.phone && (
              <p>
                <a href={`tel:${settings.phone.replace(/\s+/g, '')}`} className="hover:text-brand-700">
                  {settings.phone}
                </a>
              </p>
            )}
            {settings.email && (
              <p>
                <a href={`mailto:${settings.email}`} className="hover:text-brand-700">
                  {settings.email}
                </a>
              </p>
            )}
          </address>
          {social.length > 0 && (
            <ul aria-label={messages.footer.follow} className="flex flex-wrap gap-x-4 gap-y-1 pt-1">
              {social.map(([network, url]) => (
                <li key={network}>
                  <a href={url!} rel="noopener noreferrer" target="_blank" className="text-brand-700 hover:underline">
                    {SOCIAL[network] ?? network}
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>

        {hours.length > 0 && (
          <div className="space-y-2">
            <h2 className="font-semibold">{messages.footer.hours}</h2>
            <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-ink-muted">
              {hours.map(({ day, entry }) => (
                <div key={day} className="contents">
                  <dt>{messages.days[day]}</dt>
                  <dd>{entry.closed || !entry.open ? messages.footer.closed : `${entry.open} - ${entry.close}`}</dd>
                </div>
              ))}
            </dl>
          </div>
        )}
      </div>
      <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-1 border-t border-line px-4 py-3 text-sm text-ink-muted">
        <p>
          © {new Date().getFullYear()} {settings.site_name}. {messages.footer.rights}
        </p>
        <nav aria-label={messages.footer.legal} className="flex gap-4">
          <Link href={localePath(locale, '/privacy')} className="hover:text-brand-700">
            {messages.footer.privacy}
          </Link>
          <Link href={localePath(locale, '/terms')} className="hover:text-brand-700">
            {messages.footer.terms}
          </Link>
        </nav>
      </div>
    </footer>
  )
}
