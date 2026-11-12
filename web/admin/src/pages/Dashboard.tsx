import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router'
import { api } from '../api'
import { useAuth } from '../auth/store'

/** Dashboard card order: key => [label, admin path]. The API only returns counts the user may see. */
const CARDS: Record<string, [string, string]> = {
  unread_messages: ['Unread messages', '/messages'],
  posts: ['Posts', '/posts'],
  categories: ['Categories', '/categories'],
  services: ['Services', '/services'],
  testimonials: ['Testimonials', '/testimonials'],
  team: ['Team members', '/team'],
  gallery: ['Gallery items', '/gallery'],
  faqs: ['FAQs', '/faqs'],
}

export function Dashboard() {
  const name = useAuth((s) => s.user?.name)
  const counts = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => ((await api.request('GET', '/v1/dashboard')) as { data: Record<string, number> }).data,
  })

  return (
    <section className="space-y-4">
      <header>
        <h1 className="text-2xl font-semibold">Dashboard</h1>
        <p className="text-ink-muted">Welcome back, {name}.</p>
      </header>

      {counts.isError && (
        <p role="alert" className="text-danger">
          Could not load the counts.
        </p>
      )}

      <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
        {Object.entries(CARDS)
          .filter(([key]) => counts.data && key in counts.data)
          .map(([key, [label, path]]) => (
            <Link key={key} to={path} className="rounded-lg border border-line bg-surface p-4 hover:border-primary-400">
              <p className="text-3xl font-semibold" data-testid={`count-${key}`}>
                {counts.data?.[key]}
              </p>
              <p className="text-ink-muted">{label}</p>
            </Link>
          ))}
      </div>
    </section>
  )
}
