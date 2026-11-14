import type { Settings } from '../lib/api'
import Link from 'next/link'
import { localePath, type Messages } from '../lib/i18n'
import { HoursList } from './HoursList'
import { SiteLogo } from './SiteLogo'
import { SocialIcon } from './SocialIcon'

const SOCIAL: Record<string, string> = { facebook: 'Facebook', instagram: 'Instagram', x: 'X', linkedin: 'LinkedIn', youtube: 'YouTube', tiktok: 'TikTok', whatsapp: 'WhatsApp' }

/** Dark footer: contact details, opening hours and social links, all from public settings. */
export function Footer({ locale, settings, messages }: { locale: string; settings: Settings; messages: Messages }) {
  const social = Object.entries(settings.social_links ?? {}).filter(([, url]) => url)
  const hasHours = Object.keys(settings.hours ?? {}).length > 0
  const link = 'text-white/75 transition-colors hover:text-white'

  return (
    <footer className="relative overflow-hidden bg-night text-white/80">
      <div aria-hidden="true" className="pointer-events-none absolute -top-32 -end-32 size-96 rounded-full bg-brand-600/25 blur-3xl" />
      <div className="relative mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 md:py-16 lg:grid-cols-3">
        <div className="space-y-4">
          <SiteLogo settings={settings} dark />
          {settings.tagline && <p className="max-w-xs text-white/70">{settings.tagline}</p>}
          {social.length > 0 && (
            <ul aria-label={messages.footer.follow} className="flex flex-wrap gap-3 pt-1">
              {social.map(([network, url]) => (
                <li key={network}>
                  <a
                    href={url!}
                    rel="noopener noreferrer"
                    target="_blank"
                    aria-label={SOCIAL[network] ?? network}
                    title={SOCIAL[network] ?? network}
                    className="flex size-11 items-center justify-center rounded-full border border-white/20 text-white/85 transition hover:-translate-y-0.5 hover:border-white hover:bg-white hover:text-night"
                  >
                    <SocialIcon network={network} />
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="space-y-3">
          <h2 className="text-sm font-semibold tracking-widest text-accent-400 uppercase">{messages.footer.contact}</h2>
          <address className="space-y-2 not-italic">
            {settings.address && <p className="whitespace-pre-line text-white/75">{settings.address}</p>}
            {settings.phone && (
              <p>
                <a href={`tel:${settings.phone.replace(/\s+/g, '')}`} className={link}>
                  {settings.phone}
                </a>
              </p>
            )}
            {settings.email && (
              <p className="break-words">
                <a href={`mailto:${settings.email}`} className={link}>
                  {settings.email}
                </a>
              </p>
            )}
          </address>
        </div>

        {hasHours && (
          <div className="space-y-3">
            <h2 className="text-sm font-semibold tracking-widest text-accent-400 uppercase">{messages.footer.hours}</h2>
            <HoursList settings={settings} messages={messages} className="text-white/75" />
          </div>
        )}
      </div>
      <div className="relative border-t border-white/10 px-4 py-5">
        <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 text-sm text-white/65 sm:flex-row">
          <p>
            © {new Date().getFullYear()} {settings.site_name}. {messages.footer.rights}
          </p>
          <nav aria-label={messages.footer.legal} className="flex gap-5">
            <Link href={localePath(locale, '/privacy')} className={link}>
              {messages.footer.privacy}
            </Link>
            <Link href={localePath(locale, '/terms')} className={link}>
              {messages.footer.terms}
            </Link>
          </nav>
        </div>
        <p className="mx-auto mt-3 max-w-6xl text-center text-xs text-white/65 sm:text-left">
          {messages.footer.poweredBy.replace('{name}', 'Orvyx Technologies')}
        </p>
      </div>
    </footer>
  )
}
