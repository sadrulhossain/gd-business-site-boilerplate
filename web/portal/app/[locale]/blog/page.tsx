import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { Picture } from '../../../components/Media'
import { getCategories, getPosts, getSettings } from '../../../lib/api'
import { getMessages, isLocale, localePath } from '../../../lib/i18n'
import { pageMetadata } from '../../../lib/seo'

type Props = { params: Promise<{ locale: string }>; searchParams: Promise<{ page?: string; category?: string }> }

/** `/blog?category=<slug>&page=<n>`. Anything else in the query is ignored. */
function parse({ page, category }: { page?: string; category?: string }) {
  const n = Number.parseInt(page ?? '1', 10)

  return { page: Number.isFinite(n) && n > 0 ? n : 1, category: category && /^[a-z0-9-]+$/.test(category) ? category : undefined }
}

const href = (locale: string, { page, category }: { page?: number; category?: string }) => {
  const query = new URLSearchParams()
  if (category) query.set('category', category)
  if (page && page > 1) query.set('page', String(page))

  return `${localePath(locale, '/blog')}${query.size ? `?${query}` : ''}`
}

export async function generateMetadata({ params, searchParams }: Props): Promise<Metadata> {
  const { locale } = await params
  if (!isLocale(locale)) return {}
  const { page, category } = parse(await searchParams)
  const [settings, categories] = await Promise.all([getSettings(locale), getCategories(locale)])
  const messages = getMessages(locale)
  const name = categories.find((c) => c.slug === category)?.name
  const title = `${name ?? messages.blog.title}${page > 1 ? ` (${page})` : ''}`

  // Filtered and later pages canonicalise to the plain blog: they are views of one list, not landing pages.
  return pageMetadata({ locale, path: '/blog', siteName: settings.site_name, title, description: messages.blog.intro })
}

export default async function BlogIndex({ params, searchParams }: Props) {
  const { locale } = await params
  if (!isLocale(locale)) notFound()

  const query = parse(await searchParams)
  const messages = getMessages(locale)
  const t = messages.blog
  const [{ posts, page, lastPage }, categories] = await Promise.all([getPosts(locale, query), getCategories(locale)])
  const date = new Intl.DateTimeFormat(locale, { dateStyle: 'long' })

  return (
    <div className="mx-auto max-w-6xl px-4 py-10 sm:py-14">
      <h1 className="font-display text-3xl font-extrabold tracking-tight text-balance sm:text-4xl">{t.title}</h1>
      <p className="mt-2 text-ink-muted">{t.intro}</p>

      {categories.length > 0 && (
        <nav aria-label={t.categories} className="mt-6 flex flex-wrap gap-2">
          <Link
            href={href(locale, {})}
            aria-current={!query.category ? 'true' : undefined}
            className={`rounded-full px-4 py-1.5 ${!query.category ? 'bg-brand-700 text-white' : 'border border-line hover:bg-brand-50'}`}
          >
            {t.all}
          </Link>
          {categories.map((c) => (
            <Link
              key={c.id}
              href={href(locale, { category: c.slug })}
              aria-current={query.category === c.slug ? 'true' : undefined}
              className={`rounded-full px-4 py-1.5 ${query.category === c.slug ? 'bg-brand-700 text-white' : 'border border-line hover:bg-brand-50'}`}
            >
              {c.name}
            </Link>
          ))}
        </nav>
      )}

      {posts.length === 0 ? (
        <p className="mt-10 text-ink-muted">{t.empty}</p>
      ) : (
        <ul className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {posts.map((post) => (
            <li key={post.id} className="flex flex-col overflow-hidden rounded-lg border border-line bg-surface shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
              {post.image && <Picture image={post.image} className="aspect-[3/2] w-full object-cover" />}
              <div className="flex flex-1 flex-col gap-2 p-5">
                <p className="text-sm text-ink-muted">
                  {post.category && <span>{post.category.name} · </span>}
                  {post.published_at && <time dateTime={post.published_at}>{date.format(new Date(post.published_at))}</time>}
                </p>
                <h2 className="text-xl font-semibold">
                  <Link href={localePath(locale, `/blog/${post.slug}`)} className="hover:text-brand-700 hover:underline">
                    {post.title}
                  </Link>
                </h2>
                {post.excerpt && <p className="text-ink-muted">{post.excerpt}</p>}
              </div>
            </li>
          ))}
        </ul>
      )}

      {lastPage > 1 && (
        <nav aria-label={t.pagination} className="mt-10 flex items-center justify-between gap-4">
          {page > 1 ? (
            <Link href={href(locale, { ...query, page: page - 1 })} rel="prev" className="rounded-full border border-line px-5 py-2.5 font-medium hover:bg-brand-50">
              <span aria-hidden="true">←</span> {t.previous}
            </Link>
          ) : (
            <span />
          )}
          <span className="text-ink-muted">{t.pageOf.replace('{page}', String(page)).replace('{last}', String(lastPage))}</span>
          {page < lastPage ? (
            <Link href={href(locale, { ...query, page: page + 1 })} rel="next" className="rounded-full border border-line px-5 py-2.5 font-medium hover:bg-brand-50">
              {t.next} <span aria-hidden="true">→</span>
            </Link>
          ) : (
            <span />
          )}
        </nav>
      )}
    </div>
  )
}
