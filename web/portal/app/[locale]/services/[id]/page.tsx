import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { Picture } from '../../../../components/Media'
import { getSections, getService } from '../../../../lib/api'
import { getMessages, isLocale, localePath } from '../../../../lib/i18n'

type Props = { params: Promise<{ locale: string; id: string }> }

/** The services section being hidden hides its detail pages too. */
async function load(locale: string, id: string) {
  if (!isLocale(locale) || !/^\d+$/.test(id)) notFound()
  const [service, sections] = await Promise.all([getService(locale, id), getSections(locale)])
  if (!service || !sections.some((s) => s.key === 'services')) notFound()

  return service
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, id } = await params
  const service = await load(locale, id)

  return { title: service.title, description: service.description?.slice(0, 160) || undefined }
}

export default async function ServiceDetail({ params }: Props) {
  const { locale, id } = await params
  const service = await load(locale, id)
  const messages = getMessages(locale)

  return (
    <article className="mx-auto max-w-4xl px-4 py-14">
      <Link href={localePath(locale, '/services')} className="text-brand-700 hover:underline">
        <span aria-hidden="true">←</span> {messages.pages.back}
      </Link>
      <h1 className="mt-4 font-display text-4xl font-semibold">{service.title}</h1>
      <div className={`mt-8 grid items-start gap-8 ${service.image ? 'md:grid-cols-2' : ''}`}>
        {service.image && <Picture image={service.image} alt={service.title} sizes="(min-width: 768px) 50vw, 100vw" className="aspect-[4/3] w-full rounded-lg object-cover" />}
        {service.description && <p className="text-lg whitespace-pre-line text-ink-muted">{service.description}</p>}
      </div>
    </article>
  )
}
