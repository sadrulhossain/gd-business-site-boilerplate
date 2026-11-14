'use client'

import { usePathname } from 'next/navigation'
import { useEffect, useState, type ReactNode } from 'react'

/** Hamburger button and slide-down panel for small screens. Closes on navigation and on Escape. */
export function MobileNav({ label, children }: { label: string; children: ReactNode }) {
  const [open, setOpen] = useState(false)
  const pathname = usePathname()

  useEffect(() => setOpen(false), [pathname])
  useEffect(() => {
    if (!open) return
    const onKey = (e: KeyboardEvent) => e.key === 'Escape' && setOpen(false)
    document.addEventListener('keydown', onKey)

    return () => document.removeEventListener('keydown', onKey)
  }, [open])

  return (
    <div className="md:hidden">
      <button
        type="button"
        aria-expanded={open}
        aria-controls="mobile-menu"
        aria-label={label}
        onClick={() => setOpen((v) => !v)}
        className="flex size-11 items-center justify-center rounded-md border border-line bg-surface text-ink shadow-sm"
      >
        <svg viewBox="0 0 24 24" aria-hidden="true" className="size-6" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
          {open ? <path d="M6 6l12 12M18 6 6 18" /> : <path d="M4 7h16M4 12h16M4 17h16" />}
        </svg>
      </button>
      <div
        id="mobile-menu"
        hidden={!open}
        className="absolute inset-x-0 top-full max-h-[calc(100dvh-4.5rem)] overflow-y-auto border-t border-line bg-surface/95 px-4 py-4 shadow-lg backdrop-blur-lg"
      >
        {children}
      </div>
    </div>
  )
}
