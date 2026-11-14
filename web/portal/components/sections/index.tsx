import Link from 'next/link'
import type { ReactNode } from 'react'
import { getList, getPricing, type Faq, type GalleryItem, type Section, type Pricing, type Service, type Settings, type TeamMember, type Testimonial } from '../../lib/api'
import { localePath, type Messages } from '../../lib/i18n'
import { Picture } from '../Media'
import { PricingPlans } from '../PricingPlans'
import { DefaultMark } from '../SiteLogo'

interface Ctx {
  locale: string
  settings: Settings
  messages: Messages
  /** On its own page the section title is the page's h1 instead of an h2. */
  page?: boolean
}

type SectionView = (props: { section: Section } & Ctx) => ReactNode | Promise<ReactNode>

const wrap = 'mx-auto max-w-6xl px-4 py-12 sm:py-16 lg:py-20'
const card = 'relative rounded-lg border border-line bg-surface p-6 shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift'
const btnPrimary = 'inline-flex items-center justify-center rounded-full bg-brand-600 px-7 py-3.5 font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-lift'
const btnGhost = 'inline-flex items-center justify-center rounded-full border border-brand-200 bg-white/70 px-7 py-3.5 font-semibold text-brand-700 transition hover:border-brand-400 hover:bg-white'

const heroPrimary = 'inline-flex items-center justify-center rounded-full bg-white px-7 py-3.5 font-semibold text-brand-800 shadow-lift transition hover:-translate-y-0.5 hover:bg-brand-50'
const heroGhost = 'inline-flex items-center justify-center rounded-full border-2 border-white/70 px-7 py-3.5 font-semibold text-white transition hover:bg-white/15'

const initial = (name: string) => name.trim().charAt(0).toUpperCase()

function Arrow() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="ms-1.5 inline size-4 rtl:rotate-180" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14M13 6l6 6-6 6" />
    </svg>
  )
}

/** A section's full-width band with its centred heading, intro text and content. `dark` is the navy band. */
function Frame({ section, children, intro = true, page = false, dark = false, tint = false }: { section: Section; children?: ReactNode; intro?: boolean; page?: boolean; dark?: boolean; tint?: boolean }) {
  const Heading = page ? 'h1' : 'h2'

  return (
    <section id={section.key} aria-labelledby={`${section.key}-title`} className={`scroll-mt-20 ${dark ? 'relative overflow-hidden bg-night text-white' : tint ? 'bg-surface' : ''}`}>
      {dark && <div aria-hidden="true" className="pointer-events-none absolute -start-24 top-0 size-80 rounded-full bg-brand-600/30 blur-3xl" />}
      <div className={`${wrap} relative`}>
        <div className="mx-auto max-w-2xl text-center">
          <span aria-hidden="true" className="mx-auto mb-4 block h-1.5 w-12 rounded-full bg-gradient-to-r from-brand-500 to-accent-400" />
          <Heading id={`${section.key}-title`} className="font-display text-3xl font-extrabold tracking-tight text-balance sm:text-4xl">
            {section.title}
          </Heading>
          {intro && section.content && <p className={`mt-4 text-lg whitespace-pre-line ${dark ? 'text-white/75' : 'text-ink-muted'}`}>{section.content}</p>}
        </div>
        {children && <div className="mt-10 sm:mt-12">{children}</div>}
      </div>
    </section>
  )
}

const Hero: SectionView = ({ section, locale, messages }) => (
  <section id={section.key} aria-labelledby="hero-title" className="relative isolate overflow-hidden bg-night text-white">
    {/* Banner (or a gradient when none is uploaded) under a dark scrim, so the text and buttons are readable on any image. */}
    {section.image ? (
      <Picture image={section.image} sizes="100vw" loading="eager" className="absolute inset-0 -z-20 size-full object-cover" />
    ) : (
      <div aria-hidden="true" className="absolute inset-0 -z-20 bg-gradient-to-br from-brand-800 via-brand-900 to-night" />
    )}
    <div aria-hidden="true" className="absolute inset-0 -z-10 bg-gradient-to-r from-night/90 via-night/70 to-night/40 rtl:bg-gradient-to-l" />
    <div aria-hidden="true" className="absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-gradient-to-t from-night/60 to-transparent" />
    {!section.image && (
      <>
        <div aria-hidden="true" className="pointer-events-none absolute -top-24 -end-24 -z-10 size-[26rem] rounded-full bg-brand-500/30 blur-3xl" />
        <div aria-hidden="true" className="pointer-events-none absolute -bottom-32 -start-24 -z-10 size-[22rem] rounded-full bg-accent-400/20 blur-3xl" />
      </>
    )}
    <div className="mx-auto max-w-6xl px-4 py-20 sm:py-28 lg:py-36">
      <div className="max-w-2xl space-y-6 text-center md:text-start">
        <h1 id="hero-title" className="font-display text-4xl leading-[1.08] font-extrabold tracking-tight text-balance text-white drop-shadow-sm sm:text-5xl lg:text-6xl">
          {section.title}
        </h1>
        {section.content && <p className="max-w-xl text-lg whitespace-pre-line text-white/90 sm:text-xl max-md:mx-auto">{section.content}</p>}
        <div className="flex flex-col items-stretch justify-center gap-3 sm:flex-row md:justify-start">
          <Link href={localePath(locale, '/contact')} className={heroPrimary}>
            {messages.nav.contact}
            <Arrow />
          </Link>
          <Link href={localePath(locale, '/about')} className={heroGhost}>
            {messages.sections.readMore}
          </Link>
        </div>
      </div>
    </div>
  </section>
)

const About: SectionView = ({ section, page }) => {
  const Heading = page ? 'h1' : 'h2'

  return (
    <section id={section.key} aria-labelledby="about-title" className="scroll-mt-20 bg-surface">
      <div className={`${wrap} grid items-center gap-10 ${section.image ? 'md:grid-cols-2 md:gap-14' : ''}`}>
        <div className={`space-y-4 ${section.image ? '' : 'mx-auto max-w-3xl text-center'}`}>
          <span aria-hidden="true" className={`block h-1.5 w-12 rounded-full bg-gradient-to-r from-brand-500 to-accent-400 ${section.image ? '' : 'mx-auto'}`} />
          <Heading id="about-title" className="font-display text-3xl font-extrabold tracking-tight text-balance sm:text-4xl">
            {section.title}
          </Heading>
          {section.content && <p className="text-lg leading-relaxed whitespace-pre-line text-ink-muted">{section.content}</p>}
        </div>
        {section.image && (
          <div className="relative">
            <div aria-hidden="true" className="absolute -start-3 -bottom-3 size-full rounded-xl bg-gradient-to-tr from-brand-200 to-accent-300/60" />
            <Picture image={section.image} sizes="(min-width: 768px) 50vw, 100vw" className="relative aspect-[4/3] w-full rounded-xl object-cover shadow-lift" />
          </div>
        )}
      </div>
    </section>
  )
}

const Services: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<Service>(locale, 'services')

  return (
    <Frame section={section} page={page}>
      {items.length === 0 ? (
        <p className="text-center text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((s, i) => (
            <li key={s.id} className={`${card} group flex flex-col`}>
              {s.image ? (
                <div className="-mx-2 -mt-2 mb-5 overflow-hidden rounded-md">
                  <Picture image={s.image} className="aspect-[3/2] w-full object-cover transition duration-500 group-hover:scale-105" />
                </div>
              ) : (
                <span aria-hidden="true" className="mb-5 flex size-12 items-center justify-center rounded-md bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-bold text-white shadow-soft">
                  {String(i + 1).padStart(2, '0')}
                </span>
              )}
              <h3 className="text-xl font-bold tracking-tight">
                <Link href={localePath(locale, `/services/${s.id}`)} className="after:absolute after:inset-0 hover:text-brand-700">
                  {s.title}
                </Link>
              </h3>
              {s.description && <p className="mt-2 line-clamp-4 whitespace-pre-line text-ink-muted">{s.description}</p>}
              <span aria-hidden="true" className="mt-auto pt-5 text-sm font-semibold text-brand-700">
                {messages.sections.readMore}
                <Arrow />
              </span>
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const PricingView: SectionView = async ({ section, locale, settings, messages, page }) => {
  if (!page) {
    // On Home: just the invitation; the prices live on their own page.
    return (
      <Frame section={section}>
        <p className="text-center">
          <Link href={localePath(locale, '/pricing')} className={btnPrimary}>
            {messages.pricing.cta}
            <Arrow />
          </Link>
        </p>
      </Frame>
    )
  }

  const plans = await getPricing(locale)
  const groups = plans.reduce<{ id: number; title: string; plans: Pricing[] }[]>((all, plan) => {
    const group = all.find((g) => g.id === plan.service_id)
    if (group) group.plans.push(plan)
    else all.push({ id: plan.service_id, title: plan.service?.title ?? '', plans: [plan] })

    return all
  }, [])

  return (
    <Frame section={section} page>
      {groups.length === 0 ? (
        <p className="text-center text-ink-muted">{messages.pricing.empty}</p>
      ) : (
        <div className="space-y-14">
          {groups.map((g) => (
            <section key={g.id} aria-labelledby={`pricing-service-${g.id}`}>
              <div className="mb-6 flex flex-wrap items-end justify-between gap-2">
                <h2 id={`pricing-service-${g.id}`} className="font-display text-2xl font-extrabold tracking-tight">
                  {g.title}
                </h2>
                <Link href={localePath(locale, `/services/${g.id}`)} className="font-semibold text-brand-700 hover:underline">
                  {messages.pricing.viewService}
                  <Arrow />
                </Link>
              </div>
              <PricingPlans plans={g.plans} settings={settings} locale={locale} messages={messages} />
            </section>
          ))}
        </div>
      )}
    </Frame>
  )
}

const Testimonials: SectionView = async ({ section, locale, messages, page }) => {
  const items = await getList<Testimonial>(locale, 'testimonials')

  return (
    <Frame section={section} page={page} dark>
      {items.length === 0 ? (
        <p className="text-center text-white/70">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {items.map((t) => (
            <li key={t.id} className="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur transition duration-300 hover:-translate-y-1 hover:bg-white/10">
              <figure className="flex h-full flex-col gap-5">
                <svg viewBox="0 0 24 24" aria-hidden="true" className="size-9 text-accent-400" fill="currentColor">
                  <path d="M9.4 5C5.3 6.6 3 9.7 3 14v5h7v-6H6.6c.1-2.4 1.4-4 3.4-5zm9 0c-4.1 1.6-6.4 4.7-6.4 9v5h7v-6h-3.4c.1-2.4 1.4-4 3.4-5z" />
                </svg>
                <blockquote className="text-lg leading-relaxed text-white/90">{t.quote}</blockquote>
                <figcaption className="mt-auto flex items-center gap-3">
                  {t.image ? (
                    <Picture image={t.image} sizes="48px" className="size-12 rounded-full object-cover ring-2 ring-white/30" />
                  ) : (
                    <span aria-hidden="true" className="flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-700 font-bold text-white">
                      {initial(t.author_name)}
                    </span>
                  )}
                  <span>
                    <strong className="block text-white">{t.author_name}</strong>
                    {t.author_role && <span className="text-sm text-white/65">{t.author_role}</span>}
                  </span>
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
    <Frame section={section} page={page} tint>
      {items.length === 0 ? (
        <p className="text-center text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3">
          {items.map((g) => (
            <li key={g.id}>
              <figure className="group overflow-hidden rounded-lg border border-line bg-surface shadow-soft">
                {g.image && (
                  <div className="overflow-hidden">
                    <Picture image={g.image} alt={g.title ?? g.caption ?? ''} className="aspect-square w-full object-cover transition duration-500 group-hover:scale-105" />
                  </div>
                )}
                {g.caption && <figcaption className="px-3 py-2 text-sm text-ink-muted">{g.caption}</figcaption>}
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
        <p className="text-center text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <div className="mx-auto max-w-3xl space-y-3">
          {items.map((f) => (
            <details key={f.id} className="group rounded-md border border-line bg-surface shadow-soft open:border-brand-300 open:shadow-lift">
              <summary className="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold marker:content-none [&::-webkit-details-marker]:hidden">
                {f.question}
                <span aria-hidden="true" className="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xl leading-none text-brand-700 transition-transform duration-200 group-open:rotate-45">
                  +
                </span>
              </summary>
              <p className="px-5 pb-5 whitespace-pre-line text-ink-muted">{f.answer}</p>
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
    <Frame section={section} page={page} tint>
      {items.length === 0 ? (
        <p className="text-center text-ink-muted">{messages.sections.empty}</p>
      ) : (
        <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {items.map((m) => (
            <li key={m.id} className={`${card} text-center`}>
              {m.image ? (
                <Picture image={m.image} sizes="(min-width: 1024px) 25vw, 50vw" className="mx-auto mb-4 size-28 rounded-full object-cover ring-4 ring-brand-100" />
              ) : (
                <span aria-hidden="true" className="mx-auto mb-4 flex size-28 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-700 text-4xl font-bold text-white ring-4 ring-brand-100">
                  {initial(m.name)}
                </span>
              )}
              <h3 className="text-lg font-bold">{m.name}</h3>
              <p className="font-medium text-brand-700">{m.role}</p>
              {m.bio && <p className="mt-2 text-sm whitespace-pre-line text-ink-muted">{m.bio}</p>}
            </li>
          ))}
        </ul>
      )}
    </Frame>
  )
}

const Contact: SectionView = ({ section, locale, settings, messages, page }) => {
  const rows: { label: string; node: ReactNode }[] = []
  const link = 'font-semibold text-brand-700 break-words hover:underline'
  if (settings.phone) rows.push({ label: messages.sections.call, node: <a href={`tel:${settings.phone.replace(/\s+/g, '')}`} className={link}>{settings.phone}</a> })
  if (settings.email) rows.push({ label: messages.sections.email, node: <a href={`mailto:${settings.email}`} className={link}>{settings.email}</a> })
  if (settings.address) rows.push({ label: messages.sections.visit, node: <span className="font-semibold whitespace-pre-line">{settings.address}</span> })

  return (
    <Frame section={section} page={page}>
      <dl className="mx-auto grid max-w-4xl gap-4 sm:grid-cols-3">
        {rows.map((r) => (
          <div key={r.label} className="rounded-lg border border-line bg-surface p-6 text-center shadow-soft">
            <dt className="mb-1 text-sm font-semibold tracking-widest text-brand-700 uppercase">{r.label}</dt>
            <dd>{r.node}</dd>
          </div>
        ))}
      </dl>
      {!page && (
        <p className="mt-8 text-center">
          <Link href={localePath(locale, '/contact')} className={btnPrimary}>
            {messages.sections.contactIntro}
            <Arrow />
          </Link>
        </p>
      )}
    </Frame>
  )
}

/** A section without a dedicated view still renders: title, text and image. */
const Generic: SectionView = ({ section, page }) => (
  <Frame section={section} page={page}>
    {section.image && <Picture image={section.image} sizes="(min-width: 768px) 50vw, 100vw" className="mx-auto max-w-xl rounded-lg shadow-soft" />}
  </Frame>
)

const views: Record<string, SectionView> = {
  hero: Hero,
  about: About,
  services: Services,
  pricing: PricingView,
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
