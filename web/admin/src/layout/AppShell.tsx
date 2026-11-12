import { useQuery } from '@tanstack/react-query'
import { NavLink, Outlet } from 'react-router'
import { api } from '../api'
import { signOut } from '../auth/signOut'
import { useAuth } from '../auth/store'
import { ToastHost } from '../components/Toast'
import type { MenuItem } from '../types'

const link = 'block rounded-md px-3 py-1.5 hover:bg-primary-800'

function MenuNode({ item }: { item: MenuItem }) {
  if (item.children.length > 0) {
    return (
      <li className="space-y-1">
        <p className="px-3 pt-3 text-sm font-semibold text-primary-200 uppercase">{item.title}</p>
        <ul className="space-y-0.5">
          {item.children.map((child) => (
            <MenuNode key={child.id} item={child} />
          ))}
        </ul>
      </li>
    )
  }

  return (
    <li>
      <NavLink to={item.path ?? '/'} end={item.path === '/'} className={({ isActive }) => `${link} ${isActive ? 'bg-primary-700' : ''}`}>
        {item.title}
      </NavLink>
    </li>
  )
}

/** Sidebar (from /me/menus, already filtered by permission), top bar and the routed page. */
export function AppShell() {
  const user = useAuth((s) => s.user)
  const menus = useQuery({
    queryKey: ['menus'],
    queryFn: async () => ((await api.request('GET', '/v1/me/menus')) as { data: MenuItem[] }).data,
  })

  return (
    <div className="flex min-h-screen bg-canvas text-ink">
      <aside className="w-56 shrink-0 bg-primary-900 p-3 text-white">
        <p className="px-3 py-2 text-lg font-semibold">Admin</p>
        <nav aria-label="Main navigation">
          <ul className="space-y-0.5">{menus.data?.map((item) => <MenuNode key={item.id} item={item} />)}</ul>
        </nav>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex items-center justify-end gap-3 border-b border-line bg-surface px-4 py-2">
          <span className="text-ink-muted">{user?.name}</span>
          <button type="button" className="rounded-md border border-line px-3 py-1 hover:bg-primary-50" onClick={() => void signOut()}>
            Sign out
          </button>
        </header>
        <main className="flex-1 p-6">
          <Outlet />
        </main>
      </div>

      <ToastHost />
    </div>
  )
}
