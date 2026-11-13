import { headers } from 'next/headers'
import Link from 'next/link'
import { defaultLocale, getMessages, isLocale, localePath } from '../../lib/i18n'

/**
 * Localised 404, rendered inside the locale layout (header and footer included). A not-found file receives no route
 * params, so the proxy passes the request's locale in the `x-locale` header.
 */
export default async function NotFound() {
  const requested = (await headers()).get('x-locale') ?? defaultLocale
  const locale = isLocale(requested) ? requested : defaultLocale
  const messages = getMessages(locale)

  return (
    // Next renders 404s inside its own <html> (no lang attribute), hence `lang` on the content wrapper.
    <div lang={locale} className="mx-auto max-w-3xl px-4 py-20 text-center">
      <p className="font-display text-6xl font-semibold text-brand-600">404</p>
      <h1 className="mt-4 font-display text-3xl font-semibold">{messages.notFound.title}</h1>
      <p className="mt-3 text-ink-muted">{messages.notFound.text}</p>
      <Link href={localePath(locale)} className="mt-6 inline-block rounded-md bg-brand-600 px-4 py-2 text-white hover:bg-brand-700">
        {messages.notFound.home}
      </Link>
    </div>
  )
}
