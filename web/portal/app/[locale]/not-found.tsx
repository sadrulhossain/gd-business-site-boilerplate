import { NotFoundContent } from '../../components/NotFoundContent'
import { defaultLocale, getMessages, locales } from '../../lib/i18n'

/**
 * Localised 404, rendered inside the locale layout (header and footer included). It must not read request headers:
 * that would turn every locale route dynamic and cost the static (ISR) pages. See NotFoundContent.
 */
export default function NotFound() {
  const texts = Object.fromEntries(locales.map((code) => [code, getMessages(code).notFound]))

  return <NotFoundContent texts={texts} defaultLocale={defaultLocale} />
}
