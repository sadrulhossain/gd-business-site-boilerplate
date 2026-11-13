import { NextResponse, type NextRequest } from 'next/server'
import { defaultLocale, locales } from './lib/i18n'

/**
 * Locale routing. The default locale has no URL prefix: "/blog" is rewritten to the internal "/<default>/blog", and an
 * explicit "/<default>/blog" redirects to "/blog" so each page has one address. The second locale keeps its "/<code>" prefix.
 */
export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl

  if (pathname === `/${defaultLocale}` || pathname.startsWith(`/${defaultLocale}/`)) {
    const url = request.nextUrl.clone()
    url.pathname = pathname.slice(defaultLocale.length + 1) || '/'

    return NextResponse.redirect(url, 308)
  }

  if (locales.some((code) => code !== defaultLocale && (pathname === `/${code}` || pathname.startsWith(`/${code}/`)))) {
    return NextResponse.next()
  }

  const url = request.nextUrl.clone()
  url.pathname = `/${defaultLocale}${pathname === '/' ? '' : pathname}`

  return NextResponse.rewrite(url)
}

export const config = {
  // Everything except the API routes, Next internals and files with an extension (icons, images).
  matcher: ['/((?!api|_next|.*\\..*).*)'],
}
