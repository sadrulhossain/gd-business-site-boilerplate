import { Link } from 'react-router'

function Message({ code, title, text }: { code: number; title: string; text: string }) {
  return (
    <section className="space-y-2 py-10 text-center" aria-labelledby={`error-${code}`}>
      <p className="text-4xl font-semibold text-link">{code}</p>
      <h1 id={`error-${code}`} className="text-2xl font-semibold">
        {title}
      </h1>
      <p className="text-ink-muted">{text}</p>
      <Link to="/" className="inline-block text-link underline">
        Back to the dashboard
      </Link>
    </section>
  )
}

export function Forbidden() {
  return <Message code={403} title="Not allowed" text="Your account does not have access to this page." />
}

export function NotFound() {
  return <Message code={404} title="Page not found" text="There is nothing at this address." />
}
