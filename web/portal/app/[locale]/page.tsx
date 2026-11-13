import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { JsonLd } from '../../components/JsonLd'
import { SectionRenderer } from '../../components/sections'
import { getSections, getSettings } from '../../lib/api'
import { getMessages, isLocale } from '../../lib/i18n'
import { localBusinessJsonLd, pageMetadata } from '../../lib/seo'

type Props = { params: Promise<{ locale: string }> }

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale } = await params
  if (!isLocale(locale)) return {}
  const settings = await getSettings(locale)

  // No title: the layout's default (site name | tagline) applies.
  return pageMetadata({ locale, path: '/', siteName: settings.site_name, description: settings.tagline })
}

export default async function Home({ params }: Props) {
  const { locale } = await params
  if (!isLocale(locale)) notFound()

  const [settings, sections] = await Promise.all([getSettings(locale), getSections(locale)])

  return (
    <>
      <JsonLd data={localBusinessJsonLd(settings, locale)} />
      <SectionRenderer sections={sections} locale={locale} settings={settings} messages={getMessages(locale)} />
    </>
  )
}
