import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { ContactForm } from '../../../components/ContactForm'
import { HoursList } from '../../../components/HoursList'
import { getSettings } from '../../../lib/api'
import { getMessages, isLocale } from '../../../lib/i18n'
import { pageMetadata } from '../../../lib/seo'

type Props = { params: Promise<{ locale: string }> }

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale } = await params
  if (!isLocale(locale)) return {}
  const settings = await getSettings(locale)
  const messages = getMessages(locale)

  return pageMetadata({ locale, path: '/contact', siteName: settings.site_name, title: messages.contactPage.title, description: messages.contactPage.intro })
}

export default async function ContactPage({ params }: Props) {
  const { locale } = await params
  if (!isLocale(locale)) notFound()

  const settings = await getSettings(locale)
  const messages = getMessages(locale)
  const t = messages.contactPage
  const mapUrl = settings.address ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(settings.address.replace(/\s*\n\s*/g, ', '))}` : null

  return (
    <div className="mx-auto grid max-w-6xl gap-10 px-4 py-10 sm:py-14 md:grid-cols-2">
      <div className="space-y-4">
        <h1 className="font-display text-3xl font-extrabold tracking-tight text-balance sm:text-4xl">{t.title}</h1>
        <p className="text-ink-muted">{t.intro}</p>
        <ContactForm messages={t} />
      </div>

      <aside className="space-y-8" aria-label={t.details}>
        <section className="space-y-2">
          <h2 className="text-xl font-semibold">{t.details}</h2>
          <address className="space-y-1 not-italic text-ink-muted">
            {settings.address && <p className="whitespace-pre-line">{settings.address}</p>}
            {settings.phone && (
              <p>
                <a href={`tel:${settings.phone.replace(/\s+/g, '')}`} className="text-brand-700 hover:underline">
                  {settings.phone}
                </a>
              </p>
            )}
            {settings.email && (
              <p>
                <a href={`mailto:${settings.email}`} className="text-brand-700 hover:underline">
                  {settings.email}
                </a>
              </p>
            )}
          </address>
          {mapUrl && (
            <a href={mapUrl} target="_blank" rel="noopener noreferrer" className="inline-block text-brand-700 underline">
              {t.map}
            </a>
          )}
        </section>

        <section className="space-y-2">
          <h2 className="text-xl font-semibold">{t.hours}</h2>
          <HoursList settings={settings} messages={messages} />
        </section>
      </aside>
    </div>
  )
}
