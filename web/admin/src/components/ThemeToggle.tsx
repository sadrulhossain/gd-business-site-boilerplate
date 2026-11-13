import { useState } from 'react'
import { Icon } from './Icon'

/** Light/dark switch. The initial class is set by public/theme.js; this keeps the choice in localStorage. */
export function ThemeToggle() {
  const [dark, setDark] = useState(() => document.documentElement.classList.contains('dark'))

  function toggle() {
    const next = !dark
    document.documentElement.classList.toggle('dark', next)
    try {
      localStorage.setItem('admin-theme', next ? 'dark' : 'light')
    } catch {
      /* the choice just does not persist */
    }
    setDark(next)
  }

  return (
    <button type="button" className="btn btn-ghost size-9 !p-0" onClick={toggle} aria-label={dark ? 'Switch to light theme' : 'Switch to dark theme'} aria-pressed={dark}>
      <Icon name={dark ? 'sun' : 'moon'} className="size-[18px]" />
    </button>
  )
}
