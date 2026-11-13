import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { JsonLd } from '../../../../components/JsonLd'
import { Picture } from '../../../../components/Media'
import { getPost, getSettings } from '../../../../lib/api'
import { getMessages, isLocale, localePath } from '../../../../lib/i18n'
import { articleJsonLd, pageMetadata } from '../../../../lib/seo'

type Props = { params: Promise<{ locale: string; slug: string }> }

async function load(locale: string, slug: string) {
  if (!isLocale(locale) || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) notFound()
  const post = await getPost(locale, slug)
  if (!post) notFound()

  return post
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, slug } = await params
  const [post, settings] = await Promise.all([load(locale, slug), getSettings(locale)])

  return pageMetadata({
    locale,
    path: `/blog/${post.slug}`,
    siteName: settings.site_name,
    title: post.seo_title || post.title,
    description: post.seo_description || post.excerpt,
    image: post.image?.url,
    type: 'article',
    publishedTime: post.published_at,
    modifiedTime: post.updated_at,
  })
}

export default async function PostPage({ params }: Props) {
  const { locale, slug } = await params
  const [post, settings] = await Promise.all([load(locale, slug), getSettings(locale)])
  const t = getMessages(locale).blog
  const date = post.published_at ? new Intl.DateTimeFormat(locale, { dateStyle: 'long' }).format(new Date(post.published_at)) : null

  return (
    <article className="mx-auto max-w-3xl px-4 py-14">
      <JsonLd data={articleJsonLd(post, settings, locale)} />
      <Link href={localePath(locale, '/blog')} className="text-brand-700 hover:underline">
        <span aria-hidden="true">←</span> {t.back}
      </Link>
      <h1 className="mt-4 font-display text-4xl leading-tight font-semibold">{post.title}</h1>
      <p className="mt-3 text-ink-muted">
        {post.category && (
          <Link href={`${localePath(locale, '/blog')}?category=${post.category.slug}`} className="text-brand-700 hover:underline">
            {post.category.name}
          </Link>
        )}
        {post.category && date && ' · '}
        {date && <time dateTime={post.published_at ?? undefined}>{date}</time>}
      </p>
      {post.image && <Picture image={post.image} alt={post.title} sizes="(min-width: 768px) 48rem, 100vw" className="mt-8 aspect-[16/9] w-full rounded-lg object-cover" />}
      {/* The API sanitises `body` on every write (HtmlSanitizer allowlist), so it is rendered as HTML. */}
      {post.body && <div className="post-body mt-8 text-lg" dangerouslySetInnerHTML={{ __html: post.body }} />}
    </article>
  )
}
