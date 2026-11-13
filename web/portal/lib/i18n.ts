import en from '../messages/en.json'
import second from '../messages/second.json'

/**
 * Locales come from the same env as the API (LOCALE_DEFAULT, LOCALE_SUPPORTED). The default locale has no URL prefix;
 * the second one (first supported code that is not the default) lives at /<code>. Only two languages are modelled,
 * matching the API's x / x_2nd columns.
 */
const supported = (process.env.LOCALE_SUPPORTED ?? 'en').split(',').map((s) => s.trim()).filter(Boolean)

export const defaultLocale = process.env.LOCALE_DEFAULT ?? 'en'
export const secondLocale = supported.find((code) => code !== defaultLocale)
export const locales = secondLocale ? [defaultLocale, secondLocale] : [defaultLocale]

export type Messages = typeof en

export function isLocale(value: string): boolean {
  return locales.includes(value)
}

/** messages/en.json belongs to the default locale, messages/second.json to the second. */
export function getMessages(locale: string): Messages {
  return locale === secondLocale ? (second as Messages) : en
}

/** Browser-facing path of `path` ("/", "/blog") in `locale`. `fallback` is the unprefixed locale (the server's default). */
export function localePath(locale: string, path = '/', unprefixed = defaultLocale): string {
  if (locale === unprefixed) return path

  return path === '/' ? `/${locale}` : `/${locale}${path}`
}

/**
 * Removes a leading locale prefix from a browser pathname: "/bn/blog" -> "/blog", "/bn" -> "/".
 * Client components get `all` and `unprefixed` as props because the env vars exist only on the server.
 */
export function stripLocale(pathname: string, all: string[] = locales, unprefixed = defaultLocale): string {
  for (const code of all) {
    if (code !== unprefixed && (pathname === `/${code}` || pathname.startsWith(`/${code}/`))) {
      return pathname.slice(code.length + 1) || '/'
    }
  }

  return pathname
}

/** Display name of a locale in its own language, for the switcher. */
export function localeName(locale: string): string {
  try {
    const name = new Intl.DisplayNames([locale], { type: 'language' }).of(locale) ?? locale

    return name.charAt(0).toLocaleUpperCase(locale) + name.slice(1)
  } catch {
    return locale.toUpperCase()
  }
}
