import type { Settings } from '../lib/api'
import type { Messages } from '../lib/i18n'

const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as const

/** Opening hours as a definition list; renders nothing when settings have none. Used by the footer and the contact page. */
export function HoursList({ settings, messages, className = 'text-ink-muted' }: { settings: Settings; messages: Messages; className?: string }) {
  const hours = DAYS.flatMap((day) => {
    const entry = settings.hours?.[day]

    return entry ? [{ day, entry }] : []
  })
  if (hours.length === 0) return null

  return (
    <dl className={`grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 ${className}`}>
      {hours.map(({ day, entry }) => (
        <div key={day} className="contents">
          <dt>{messages.days[day]}</dt>
          <dd>{entry.closed || !entry.open ? messages.footer.closed : `${entry.open} - ${entry.close}`}</dd>
        </div>
      ))}
    </dl>
  )
}
