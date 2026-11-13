import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { Icon } from '../components/Icon'

/** Dashboard card order: key => [label, admin path, icon]. The API only returns counts the user may see. */
const CARDS: Record<string, [string, string, string]> = {
  unread_messages: ['Unread messages', '/messages', 'mail'],
  posts: ['Posts', '/posts', 'file-text'],
  categories: ['Categories', '/categories', 'tag'],
  services: ['Services', '/services', 'briefcase'],
  testimonials: ['Testimonials', '/testimonials', 'message-square'],
  team: ['Team members', '/team', 'users'],
  gallery: ['Gallery items', '/gallery', 'image'],
  faqs: ['FAQs', '/faqs', 'help-circle'],
}

export function Dashboard() {
  const name = useAuth((s) => s.user?.name)
  const counts = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => ((await api.request('GET', '/v1/dashboard')) as { data: Record<string, number> }).data,
  })

  return (
    <section className="space-y-6">
      <header className="rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 p-6 text-white shadow-lg">
        <h1 className="text-2xl font-bold">Dashboard</h1>
        <p className="mt-1 text-white/85">Welcome back, {name}. Here is what is happening on your site.</p>
      </header>

      {counts.isError && (
        <p role="alert" className="text-danger">
          Could not load the counts.
        </p>
      )}

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {Object.entries(CARDS)
          .filter(([key]) => counts.data && key in counts.data)
          .map(([key, [label, path, icon]]) => (
            <Link key={key} to={path} className="card group p-4 transition hover:-translate-y-0.5 hover:border-primary-400 hover:shadow-md">
              <span className="flex size-10 items-center justify-center rounded-lg bg-primary-100 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                <Icon name={icon} className="size-5" />
              </span>
              <p className="mt-3 text-3xl font-bold" data-testid={`count-${key}`}>
                {counts.data?.[key]}
              </p>
              <p className="text-ink-muted">{label}</p>
            </Link>
          ))}
      </div>
    </section>
  )
}
