import type { Settings } from './api'

/** An amount as the site shows it: the currency symbol from Settings (or the currency code when there is no symbol) and the number in the page language. */
export function formatPrice(amount: string | number, settings: Pick<Settings, 'currency' | 'currency_symbol'>, locale: string): string {
  const value = Number(amount)
  const number = Number.isFinite(value) ? new Intl.NumberFormat(locale, { maximumFractionDigits: 2 }).format(value) : String(amount)
  const symbol = settings.currency_symbol?.trim()
  if (symbol) return `${symbol}${number}`

  const code = settings.currency?.trim()

  return code ? `${code} ${number}` : number
}

/** One feature per line, blanks dropped. */
export function featureList(features: string | null | undefined): string[] {
  return (features ?? '').split('\n').map((line) => line.trim()).filter(Boolean)
}
