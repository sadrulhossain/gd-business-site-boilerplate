import { notFound } from 'next/navigation'
import { SectionRenderer } from '../../components/sections'
import { getSections, getSettings } from '../../lib/api'
import { getMessages, isLocale } from '../../lib/i18n'

export default async function Home({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params
  if (!isLocale(locale)) notFound()

  const [settings, sections] = await Promise.all([getSettings(locale), getSections(locale)])

  return <SectionRenderer sections={sections} locale={locale} settings={settings} messages={getMessages(locale)} />
}
