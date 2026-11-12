import type { ReactNode } from 'react'
import './globals.css'

export const metadata = { title: 'Portal' }

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en">
      <body className="bg-canvas font-sans text-base text-ink">{children}</body>
    </html>
  )
}