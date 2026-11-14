import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { Picture } from '../../../../components/Media'
import { PricingPlans } from '../../../../components/PricingPlans'
import { getPricing, getSections, getService, getSettings } from '../../../../lib/api'
import { getMessages, isLocale, localePath } from '../../../../lib/i18n'
import { pageMetadata } from '../../../../lib/seo'

type Props = { params: Promise<{ locale: string; id: string }> }

/** The services section being hidden hides its detail pages too. */
async function load(locale: string, id: string) {
  if (!isLocale(locale) || !/^\d+$/.test(id)) notFound()
  const [service, sections] = await Promise.all([getService(locale, id), getSections(locale)])
  if (!service || !sections.some((s) => s.key === 'services')) notFound()

  return service
}

// Rendered on first request, then cached (ISR) until the content tag is revalidated.
export function generateStaticParams() {
  return []
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, id } = await params
  const [service, settings] = await Promise.all([load(locale, id), getSettings(locale)])

  return pageMetadata({ locale, path: `/services/${service.id}`, siteName: settings.site_name, title: service.title, description: service.description, image: service.image?.url })
}

export default async function ServiceDetail({ params }: Props) {
  const { locale, id } = await params
  const service = await load(locale, id)
  const messages = getMessages(locale)
  const [sections, settings] = await Promise.all([getSections(locale), getSettings(locale)])
  const plans = sections.some((s) => s.key === 'pricing') ? await getPricing(locale, service.id) : []

  return (
    <article className="mx-auto max-w-4xl px-4 py-10 sm:py-14">
      <Link href={localePath(locale, '/services')} className="text-brand-700 hover:underline">
        <span aria-hidden="true">←</span> {messages.pages.back}
      </Link>
      <h1 className="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{service.title}</h1>
      <div className={`mt-8 grid items-start gap-8 ${service.image ? 'md:grid-cols-2' : ''}`}>
        {service.image && <Picture image={service.image} alt={service.title} sizes="(min-width: 768px) 50vw, 100vw" className="aspect-[4/3] w-full rounded-lg object-cover" />}
        {service.description && <p className="text-lg whitespace-pre-line text-ink-muted">{service.description}</p>}
      </div>
      {plans.length > 0 && (
        <section aria-labelledby="service-pricing" className="mt-14">
          <h2 id="service-pricing" className="mb-8 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
            {messages.pricing.forService}
          </h2>
          <PricingPlans plans={plans} settings={settings} locale={locale} messages={messages} />
        </section>
      )}
    </article>
  )
}
