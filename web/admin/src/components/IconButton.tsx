import type { ButtonHTMLAttributes } from 'react'
import { Icon } from './Icon'

const VARIANTS = {
  primary: 'btn btn-primary',
  secondary: 'btn btn-secondary',
  danger: 'btn btn-danger',
  ghost: 'btn btn-ghost',
  /** A small tinted square for table rows. */
  row: 'inline-flex size-8 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-primary-50 hover:text-primary-700 disabled:opacity-40',
  'row-danger': 'inline-flex size-8 items-center justify-center rounded-lg text-ink-muted transition-colors hover:bg-danger/10 hover:text-danger disabled:opacity-40',
} as const

/** An icon button. Icon only on list pages (row actions, pagination); `showText` adds the label beside the icon. `label` is always the accessible name and tooltip. */
export function IconButton({ icon, label, variant = 'secondary', showText = false, className = '', ...props }: { icon: string; label: string; variant?: keyof typeof VARIANTS; showText?: boolean } & ButtonHTMLAttributes<HTMLButtonElement>) {
  const sized = showText || variant.startsWith('row') ? '' : 'size-10 !p-0'

  return (
    <button type="button" aria-label={label} title={label} className={`${VARIANTS[variant]} ${sized} ${className}`} {...props}>
      <Icon name={icon} className="size-[18px]" />
      {showText && <span>{label}</span>}
    </button>
  )
}
