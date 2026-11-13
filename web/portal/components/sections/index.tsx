import Link from 'next/link'
import type { ReactNode } from 'react'
import { getList, type Faq, type GalleryItem, type Section, type Service, type Settings, type TeamMember, type Testimonial } from '../../lib/api'
import { localePath, type Messages } from '../../lib/i18n'
import { Picture } from '../Media'

interface Ctx {
  locale: string
  settings: Settings
  messages: Messages
  /** On its own page the section title is the page's h1 instead of an h2. */
  page?: boolean
}

type SectionView = (props: { section: Section } & Ctx) => ReactNode | Promise<ReactNode>

const wrap = 'mx-auto max-w-6xl px-4 py-14'
const heading = 'font-display text-3xl font-semibold'
const card = 'rounded-md border border-line bg-surface p-5'

function Frame({ section, children, intro = true, page = false }: { section: Section; children?: ReactNode; intro?: boolean; page?: boolean }) {
  const Heading = page ? 'h1' : 'h2'

  return (
    <section id={section.key} aria-labelledby={`${section.key}-title`} className={`${wrap} scroll-mt-4`}>
      <Heading id={`${section.key}-title`} className={heading}>
        {section.title}
      </Heading>
      {intro && section.content && <p className="mt-3 max-w-3xl whitespace-pre-line text-ink-muted">{section.content}</p>}
      {children && <div className="mt-8">{children}</div>}
    </section>
  )
}

const Hero: SectionView = ({ section }) => (
  <section id={section.key} aria-labelledby="hero-title" className="bg-brand-50">
    <div className="mx-auto grid max-w-6xl items-center gap-8 px-4 py-20 md:grid-cols-2">
      <div className="space-y-4">
        <h1 id="hero-title" className="font-display text-5xl leading-tight font-semibold text-brand-800">
          {section.title}
        </h1>
        {section.content && <p className="max-w-xl text-lg whitespace-pre-line text-ink-muted">{section.content}</p>}
      </div>
      {section.image && <Picture image={section.image} sizes="(min-width: 768px) 50vw, 100vw" className="aspect-[4/3] w-full rounded-lg object-cover" />}
    </div>
  </section>
)

const About: SectionView = ({ section, page }) => {
  const Heading = page ? 'h1' : 'h2'

  return (
  <section id={section.key} aria-labelledby="about-title" className={`${wrap} scroll-mt-4`}>
    <div className={`grid items-center gap-8 ${section.image ? 'md:grid-cols-2' : ''}`}>
      <div className="space-y-3">
        <Heading id="about-title" className={heading}>
          {section.title}
        </Heading>
        {section.content && <p className="whitespace-pre-line text-ink-muted">{section.content}</p>}
      </div>
      {section.image && <Picture image={section.image} sizes="(min-width: 768px) 50vw, 100vw" className="aspect-[4/3] w-full rounded-lg object-cover" />}
    </div>
  </section>
  )
}

const Services: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<Service>(locale, 'services')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((s) => (
            <li key={s.id} className={`${card} space-y-2`}>
              {s.image && <Picture image={s.image} className="mb-3 aspect-[3/2] w-full rounded-sm object-cover" />}
              <h3 className="text-lg font-semibold">
                <Link href={localePath(locale, `/services/${s.id}`)} className="hover:text-brand-700 hover:underline">
                  {s.title}
                </Link>
              </h3>
              {s.description && <p className="whitespace-pre-line text-ink-muted">{s.description}</p>}
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const Testimonials: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<Testimonial>(locale, 'testimonials')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          {items.map((t) => (
            <li key={t.id} className={card}>
              <figure className="space-y-3">
                <blockquote className="border-s-4 border-brand-300 ps-4 text-lg">{t.quote}</blockquote>
                <figcaption className="text-ink-muted">
                  <strong className="text-ink">{t.author_name}</strong>
                  {t.author_role && <span>, {t.author_role}</span>}
                </figcaption>
              </figure>
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const Gallery: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<GalleryItem>(locale, 'gallery')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid grid-cols-2 gap-4 md:grid-cols-3">
          {items.map((g) => (
            <li key={g.id}>
              <figure>
                {g.image && <Picture image={g.image} alt={g.title ?? g.caption ?? ''} className="aspect-square w-full rounded-md object-cover" />}
                {g.caption && <figcaption className="mt-1 text-sm text-ink-muted">{g.caption}</figcaption>}
              </figure>
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const Faqs: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<Faq>(locale, 'faqs')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <div className="max-w-3xl space-y-3">
          {items.map((f) => (
            <details key={f.id} className={card}>
              <summary className="cursor-pointer font-semibold">{f.question}</summary>
              <p className="mt-2 whitespace-pre-line text-ink-muted">{f.answer}</p>
            </details>
          ))}
        </div>
      )}
    </Frame>
  )
}

const Team: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<TeamMember>(locale, 'team')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {items.map((m) => (
            <li key={m.id} className={`${card} space-y-1`}>
              {m.image && <Picture image={m.image} sizes="(min-width: 1024px) 25vw, 50vw" className="mb-3 aspect-square w-full rounded-sm object-cover" />}
              <h3 className="font-semibold">{m.name}</h3>
              <p className="text-brand-700">{m.role}</p>
              {m.bio && <p className="text-sm whitespace-pre-line text-ink-muted">{m.bio}</p>}
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const Contact: SectionView = ({ section, settings, messages, page }) => {
  const rows: { label: string; node: ReactNode }[] = []
  if (settings.phone) rows.push({ label: messages.sections.call, node: <a href={`tel:${settings.phone.replace(/s+/g, '')}`} className="text-brand-700 hover:underline">{settings.phone}</a> })
  if (settings.email) rows.push({ label: messages.sections.email, node: <a href={`mailto:${settings.email}`} className="text-brand-700 hover:underline">{settings.email}</a> })
  if (settings.address) rows.push({ label: messages.sections.visit, node: <span className="whitespace-pre-line">{settings.address}</span> })

  return (
    <Frame section={section} page={page}>
      <dl className="grid max-w-xl grid-cols-[auto_1fr] gap-x-6 gap-y-2">
        {rows.map((r) => (
          <div key={r.label} className="contents">
            <dt className="text-ink-muted">{r.label}</dt>
            <dd>{r.node}</dd>
          </div>
        ))}
      </dl>
    </Frame>
  )
}

/** A section without a dedicated view still renders: title, text and image. */
const Generic: SectionView = ({ section, page }) => (
  <Frame section={section} page={page}>{section.image && <Picture image={section.image} sizes="(min-width: 768px) 50vw, 100vw" className="max-w-xl rounded-md" />}</Frame>
)

const views: Record<string, SectionView> = {
  hero: Hero,
  about: About,
  services: Services,
  testimonials: Testimonials,
  gallery: Gallery,
  faq: Faqs,
  team: Team,
  contact: Contact,
}

/**
 * Renders the given sections in order, one view per `key`. Hidden sections (is_visible false) are skipped even if one
 * slips through a cache; unknown keys fall back to the generic view.
 */
/** One section by its key: the same view Home uses, so a dedicated page and Home never drift apart. */
export function SectionBlock({ section, ...ctx }: { section: Section } & Ctx) {
  const View = views[section.key] ?? Generic

  return <View section={section} {...ctx} />
}

export function SectionRenderer({ sections, ...ctx }: { sections: Section[] } & Ctx) {
  return (
    <>
      {sections
        .filter((section) => section.is_visible)
        .map((section) => (
          <SectionBlock key={section.id} section={section} {...ctx} />
        ))}
    </>
  )
}
