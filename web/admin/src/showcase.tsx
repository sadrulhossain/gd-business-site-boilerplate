import { ApiError, createClient } from '@gd/api-client'
import { useState } from 'react'

// Full class names on purpose: Tailwind only generates utilities it can find as literal strings.
const primary = [
  ['50', 'bg-primary-50'],
  ['100', 'bg-primary-100'],
  ['200', 'bg-primary-200'],
  ['300', 'bg-primary-300'],
  ['400', 'bg-primary-400'],
  ['500', 'bg-primary-500'],
  ['600', 'bg-primary-600'],
  ['700', 'bg-primary-700'],
  ['800', 'bg-primary-800'],
  ['900', 'bg-primary-900'],
] as const

const semantic = [
  ['success', 'bg-success'],
  ['warning', 'bg-warning'],
  ['danger', 'bg-danger'],
  ['ink', 'bg-ink'],
  ['ink-muted', 'bg-ink-muted'],
  ['line', 'bg-line'],
] as const

const api = createClient({
  baseUrl: `${location.protocol}//api.${location.host.replace(/^admin\./, '')}/api`,
})

/** Admin theme showcase: tokens from @gd/tokens/admin.css and one typed call through @gd/api-client. */
export function Showcase() {
  const [health, setHealth] = useState('not checked')

  async function checkHealth() {
    setHealth('checking...')
    try {
      setHealth(JSON.stringify(await api.get('/v1/health')))
    } catch (error) {
      setHealth(error instanceof ApiError ? `${error.status} ${error.code}` : 'API unreachable')
    }
  }

  return (
    <main className="mx-auto max-w-4xl space-y-8 bg-canvas p-6 text-ink">
      <header>
        <h1 className="text-2xl font-semibold">Admin theme</h1>
        <p className="text-ink-muted">Tokens from @gd/tokens/admin.css, dense and neutral.</p>
      </header>

      <section className="space-y-2">
        <h2 className="text-lg font-semibold">Primary</h2>
        <div className="grid grid-cols-5 gap-2 sm:grid-cols-10">
          {primary.map(([step, bg]) => (
            <div key={step} className="space-y-1 text-center text-sm">
              <div className={`${bg} h-12 rounded-md border border-line`} />
              {step}
            </div>
          ))}
        </div>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-semibold">Semantic</h2>
        <div className="grid grid-cols-3 gap-2 sm:grid-cols-6">
          {semantic.map(([name, bg]) => (
            <div key={name} className="space-y-1 text-center text-sm">
              <div className={`${bg} h-12 rounded-md border border-line`} />
              {name}
            </div>
          ))}
        </div>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-semibold">Type and radius</h2>
        <p className="text-sm">text-sm: the quick brown fox</p>
        <p className="text-base">text-base: the quick brown fox</p>
        <p className="text-xl">text-xl: the quick brown fox</p>
        <div className="flex gap-3">
          <div className="h-12 w-20 rounded-sm border border-line bg-surface p-1 text-sm">sm</div>
          <div className="h-12 w-20 rounded-md border border-line bg-surface p-1 text-sm">md</div>
          <div className="h-12 w-20 rounded-lg border border-line bg-surface p-1 text-sm">lg</div>
        </div>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-semibold">Buttons</h2>
        <div className="flex gap-2">
          <button className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700">Primary</button>
          <button className="rounded-md border border-line bg-surface px-3 py-1.5 hover:bg-primary-50">Secondary</button>
          <button className="rounded-md bg-danger px-3 py-1.5 text-white">Danger</button>
        </div>
      </section>

      <section className="space-y-2">
        <h2 className="text-lg font-semibold">Typed API client</h2>
        <button onClick={checkHealth} className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700">
          GET /v1/health
        </button>
        <pre className="rounded-md border border-line bg-surface p-3 font-mono text-sm">{health}</pre>
      </section>
    </main>
  )
}
