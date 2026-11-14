import { Brand } from './Brand'

/** Full-screen, centred loading state: the brand mark over a soft glow, a spinner and the status text. */
export function Splash({ label = 'Signing in...' }: { label?: string }) {
  return (
    <div className="relative flex min-h-screen flex-col items-center justify-center gap-6 overflow-hidden bg-canvas px-6 text-center">
      <div aria-hidden="true" className="pointer-events-none absolute top-1/2 left-1/2 size-[28rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/15 blur-3xl" />
      <div className="relative flex flex-col items-center gap-5">
        <Brand size="lg" />
        <span aria-hidden="true" className="size-7 animate-spin rounded-full border-[3px] border-primary-200 border-t-primary-600" />
        <p role="status" className="text-sm font-medium tracking-wide text-ink-muted">
          {label}
        </p>
      </div>
    </div>
  )
}
