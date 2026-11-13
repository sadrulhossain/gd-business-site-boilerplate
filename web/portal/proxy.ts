import { NextResponse, type NextRequest } from 'next/server'
import { defaultLocale, locales } from './lib/i18n'

/**
 * Locale routing. The default locale has no URL prefix: "/blog" is rewritten to the internal "/<default>/blog", and an
 * explicit "/<default>/blog" redirects to "/blog" so each page has one address. The second locale keeps its "/<code>" prefix.
 */
/** The locale of this request, for server components that have no route params (not-found.tsx). */
function withLocale(request: NextRequest, locale: string): Headers {
  const headers = new Headers(request.headers)
  headers.set('x-locale', locale)

  return headers
}

export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl

  if (pathname === `/${defaultLocale}` || pathname.startsWith(`/${defaultLocale}/`)) {
    const url = request.nextUrl.clone()
    url.pathname = pathname.slice(defaultLocale.length + 1) || '/'

    return NextResponse.redirect(url, 308)
  }

  if (locales.some((code) => code !== defaultLocale && (pathname === `/${code}` || pathname.startsWith(`/${code}/`)))) {
    return NextResponse.next({ request: { headers: withLocale(request, pathname.split('/')[1]!) } })
  }

  const url = request.nextUrl.clone()
  url.pathname = `/${defaultLocale}${pathname === '/' ? '' : pathname}`

  return NextResponse.rewrite(url, { request: { headers: withLocale(request, defaultLocale) } })
}

export const config = {
  // Everything except the API routes, Next internals and files with an extension (icons, images).
  matcher: ['/((?!api|_next|.*\\..*).*)'],
}
