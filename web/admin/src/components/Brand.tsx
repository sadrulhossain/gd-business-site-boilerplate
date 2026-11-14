import { useBranding } from '../lib/branding'
import { Icon } from './Icon'

const SIZES = { sm: 'size-9 rounded-lg', lg: 'size-16 rounded-2xl' } as const

/** The uploaded logo, or the built-in mark until one is uploaded (Settings > General). */
export function Brand({ size = 'sm', showName = false }: { size?: keyof typeof SIZES; showName?: boolean }) {
  const branding = useBranding()
  const name = branding?.site_name ?? 'Admin'

  return (
    <span className="inline-flex items-center gap-3">
      {branding?.logo ? (
        <img src={branding.logo.url} alt={showName ? '' : name} className={`${SIZES[size]} bg-white/70 object-contain p-1 shadow-md`} />
      ) : (
        <span className={`${SIZES[size]} flex items-center justify-center bg-gradient-to-br from-primary-400 to-primary-700 text-white shadow-md`}>
          <Icon name="layers" className={size === 'lg' ? 'size-8' : 'size-5'} />
        </span>
      )}
      {showName && <span className="truncate text-lg font-bold text-sidebar-text">{name}</span>}
    </span>
  )
}
