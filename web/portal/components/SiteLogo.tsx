import type { Settings } from '../lib/api'

/** The built-in mark, shown until a logo is uploaded in the admin (Settings > General). */
export function DefaultMark({ className = 'size-9' }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" aria-hidden="true" className={className}>
      <defs>
        <linearGradient id="mark-g" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#34d399" />
          <stop offset="1" stopColor="#047857" />
        </linearGradient>
      </defs>
      <rect width="64" height="64" rx="16" fill="url(#mark-g)" />
      <path d="M20 44V20h14a8 8 0 0 1 0 16H28" fill="none" stroke="#fff" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
      <circle cx="46" cy="46" r="5" fill="#fbbf24" />
    </svg>
  )
}

/** Logo (uploaded or built-in) next to the site name. `dark` is for use on the dark footer. */
export function SiteLogo({ settings, dark = false }: { settings: Settings; dark?: boolean }) {
  return (
    <span className="inline-flex items-center gap-3">
      {settings.logo ? (
        // eslint-disable-next-line @next/next/no-img-element -- images.unoptimized: the API serves the upload as is.
        <img src={settings.logo.url} alt="" className={`h-9 w-auto max-w-40 object-contain ${dark ? 'rounded-md bg-white/90 p-1' : ''}`} />
      ) : (
        <DefaultMark />
      )}
      <span className={`font-display text-lg font-bold tracking-tight ${dark ? 'text-white' : 'text-ink'}`}>{settings.site_name}</span>
    </span>
  )
}
