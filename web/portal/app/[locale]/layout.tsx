import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import type { ReactNode } from 'react'
import { Footer } from '../../components/Footer'
import { Header } from '../../components/Header'
import { getSections, getSettings } from '../../lib/api'
import { getMessages, isLocale, locales } from '../../lib/i18n'
import { siteUrl } from '../../lib/seo'
import '../globals.css'

const RTL = new Set(['ar', 'he', 'fa', 'ur'])

export function generateStaticParams() {
  return locales.map((locale) => ({ locale }))
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params
  if (!isLocale(locale)) return {}
  const settings = await getSettings(locale)

  return {
    title: { default: settings.tagline ? `${settings.site_name} | ${settings.tagline}` : settings.site_name, template: `%s | ${settings.site_name}` },
    description: settings.tagline ?? undefined,
    metadataBase: new URL(siteUrl()),
    icons: { icon: '/icon.svg' },
  }
}

export default async function LocaleLayout({ children, params }: { children: ReactNode; params: Promise<{ locale: string }> }) {
  const { locale } = await params
  if (!isLocale(locale)) notFound()

  const [settings, sections] = await Promise.all([getSettings(locale), getSections(locale)])
  const messages = getMessages(locale)

  return (
    <html lang={locale} dir={RTL.has(locale.split('-')[0]!) ? 'rtl' : 'ltr'}>
      <body className="flex min-h-screen flex-col bg-canvas font-sans text-base text-ink">
        <a href="#main" className="sr-only focus:not-sr-only focus:absolute focus:start-2 focus:top-2 focus:z-10 focus:rounded-md focus:bg-surface focus:px-3 focus:py-2">
          {messages.nav.skip}
        </a>
        <Header locale={locale} settings={settings} sections={sections} messages={messages} />
        <main id="main" className="flex-1">
          {children}
        </main>
        <Footer locale={locale} settings={settings} messages={messages} />
      </body>
    </html>
  )
}
