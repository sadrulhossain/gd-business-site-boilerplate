import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { SectionBlock } from '../components/sections'
import { getSections, getSettings } from './api'
import { pageMetadata } from './seo'
import { getMessages, isLocale } from './i18n'

type Props = { params: Promise<{ locale: string }> }

/** Section keys that have a page of their own, and where it lives. The header links here instead of to a Home anchor. */
export const SECTION_PAGES: Record<string, string> = { about: '/about', services: '/services', gallery: '/gallery', faq: '/faq' }

/**
 * A page made of Home's section views: `main` is the page (its title is the h1 and the metadata title; a hidden or
 * missing section means the site does not offer it, so 404), `extra` sections are appended when visible.
 */
export function sectionPage(main: string, extra: string[] = []) {
  async function load(locale: string) {
    if (!isLocale(locale)) notFound()
    const sections = await getSections(locale)
    const section = sections.find((s) => s.key === main)
    if (!section) notFound()

    return { section, extras: extra.flatMap((key) => sections.filter((s) => s.key === key)), sections }
  }

  async function generateMetadata({ params }: Props): Promise<Metadata> {
    const { locale } = await params
    const [{ section }, settings] = await Promise.all([load(locale), getSettings(locale)])

    return pageMetadata({ locale, path: SECTION_PAGES[main] ?? `/${main}`, siteName: settings.site_name, title: section.title, description: section.content, image: section.image?.url })
  }

  async function Page({ params }: Props) {
    const { locale } = await params
    const [{ section, extras }, settings] = await Promise.all([load(locale), getSettings(locale)])
    const ctx = { locale, settings, messages: getMessages(locale) }

    return (
      <>
        <SectionBlock section={section} page {...ctx} />
        {extras.map((s) => (
          <SectionBlock key={s.id} section={s} {...ctx} />
        ))}
      </>
    )
  }

  return { generateMetadata, Page }
}
