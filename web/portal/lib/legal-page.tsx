import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { getSettings } from './api'
import { fill, getMessages, isLocale } from './i18n'

type Props = { params: Promise<{ locale: string }> }

/** Privacy and terms: text lives in messages/*.json (`legal.<kind>`), with the business name and email filled in from settings. */
export function legalPage(kind: 'privacy' | 'terms') {
  async function generateMetadata({ params }: Props): Promise<Metadata> {
    const { locale } = await params
    if (!isLocale(locale)) notFound()

    return { title: getMessages(locale).pages[kind] }
  }

  async function Page({ params }: Props) {
    const { locale } = await params
    if (!isLocale(locale)) notFound()

    const messages = getMessages(locale)
    const settings = await getSettings(locale)
    const values = { site: settings.site_name, email: settings.email ?? settings.site_name }

    return (
      <article className="mx-auto max-w-3xl px-4 py-14">
        <h1 className="font-display text-3xl font-semibold">{messages.pages[kind]}</h1>
        <p className="mt-3 text-sm text-ink-muted">{messages.legal.updated}</p>
        {messages.legal[kind].map((block) => (
          <section key={block.heading} className="mt-8 space-y-2">
            <h2 className="text-xl font-semibold">{block.heading}</h2>
            <p className="text-ink-muted">{fill(block.body, values)}</p>
          </section>
        ))}
      </article>
    )
  }

  return { generateMetadata, Page }
}
