'use client'

import { usePathname } from 'next/navigation'
import { useEffect, useRef, useState, type ReactNode } from 'react'

/** A top-bar button that opens a panel of links. Closes on outside click, Escape and navigation. */
export function NavDropdown({ label, children }: { label: string; children: ReactNode }) {
  const [open, setOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)
  const pathname = usePathname()

  useEffect(() => setOpen(false), [pathname])
  useEffect(() => {
    if (!open) return
    const close = (e: MouseEvent | KeyboardEvent) => {
      if (e instanceof KeyboardEvent ? e.key === 'Escape' : !ref.current?.contains(e.target as Node)) setOpen(false)
    }
    document.addEventListener('mousedown', close)
    document.addEventListener('keydown', close)

    return () => {
      document.removeEventListener('mousedown', close)
      document.removeEventListener('keydown', close)
    }
  }, [open])

  return (
    <div ref={ref} className="relative">
      <button
        type="button"
        aria-expanded={open}
        aria-haspopup="true"
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-1.5 rounded-full px-3 py-2 text-[0.95rem] font-medium text-ink-muted transition-colors hover:bg-brand-50 hover:text-brand-700 aria-expanded:bg-brand-50 aria-expanded:text-brand-700"
      >
        {label}
        <svg viewBox="0 0 24 24" aria-hidden="true" className={`size-4 transition-transform ${open ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
          <path d="m6 9 6 6 6-6" />
        </svg>
      </button>
      <ul hidden={!open} className="absolute end-0 top-full z-50 mt-2 w-64 space-y-0.5 rounded-lg border border-line bg-surface p-2 shadow-lift">
        {children}
      </ul>
    </div>
  )
}
