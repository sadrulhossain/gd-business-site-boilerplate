import { useQuery } from '@tanstack/react-query'
import { useEffect, useRef, useState } from 'react'
import { Link, NavLink, Outlet, useLocation } from 'react-router'
import { api } from '../api'
import { signOut } from '../auth/signOut'
import { useAuth } from '../auth/store'
import { Icon } from '../components/Icon'
import { ThemeToggle } from '../components/ThemeToggle'
import { ToastHost } from '../components/Toast'
import type { MenuItem } from '../types'

const navLink = 'flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-sidebar-text transition-colors hover:bg-white/30 dark:hover:bg-white/10'
const navActive = '!bg-primary-600 !text-white shadow-md'

function MenuNode({ item, collapsed }: { item: MenuItem; collapsed: boolean }) {
  if (item.children.length > 0) {
    return (
      <li className="space-y-1">
        {collapsed ? <hr className="my-2 border-sidebar-line" /> : <p className="px-3 pt-4 pb-1 text-xs font-semibold tracking-wider text-sidebar-text/70 uppercase">{item.title}</p>}
        <ul className="space-y-1">
          {item.children.map((child) => (
            <MenuNode key={child.id} item={child} collapsed={collapsed} />
          ))}
        </ul>
      </li>
    )
  }

  return (
    <li>
      <NavLink
        to={item.path ?? '/'}
        end={item.path === '/'}
        title={collapsed ? (item.title ?? undefined) : undefined}
        className={({ isActive }) => `${navLink} ${collapsed ? 'justify-center' : ''} ${isActive ? navActive : ''}`}
      >
        <Icon name={item.icon} className="size-[18px]" />
        <span className={collapsed ? 'sr-only' : 'truncate'}>{item.title}</span>
      </NavLink>
    </li>
  )
}

function UserMenu() {
  const user = useAuth((s) => s.user)
  const [open, setOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

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

  const initial = (user?.name ?? '?').trim().charAt(0).toUpperCase()

  return (
    <div ref={ref} className="relative">
      <button type="button" className="btn btn-ghost !gap-2 !px-2" aria-haspopup="menu" aria-expanded={open} onClick={() => setOpen((v) => !v)}>
        <span className="flex size-8 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold text-white">{initial}</span>
        <span className="hidden max-w-40 truncate text-ink sm:inline">{user?.name}</span>
        <Icon name="chevron-down" />
      </button>
      {open && (
        <div role="menu" className="card absolute right-0 z-30 mt-2 w-56 p-1 shadow-lg">
          <div className="border-b border-line px-3 py-2">
            <p className="truncate font-semibold">{user?.name}</p>
            <p className="truncate text-sm text-ink-muted">{user?.email}</p>
          </div>
          <Link role="menuitem" to="/profile" onClick={() => setOpen(false)} className="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-primary-50">
            <Icon name="user" /> Profile
          </Link>
          <button type="button" role="menuitem" className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-primary-50" onClick={() => void signOut()}>
            <Icon name="log-out" /> Sign out
          </button>
        </div>
      )}
    </div>
  )
}

/** Glass sidebar (from /me/menus, already filtered by permission), top bar and the routed page. */
export function AppShell() {
  const menus = useQuery({
    queryKey: ['menus'],
    queryFn: async () => ((await api.request('GET', '/v1/me/menus')) as { data: MenuItem[] }).data,
  })
  const [collapsed, setCollapsed] = useState(() => {
    try {
      return localStorage.getItem('admin-sidebar') === 'collapsed'
    } catch {
      return false
    }
  })
  const [drawer, setDrawer] = useState(false)
  const location = useLocation()

  useEffect(() => setDrawer(false), [location.pathname])

  function toggleCollapsed() {
    const next = !collapsed
    setCollapsed(next)
    try {
      localStorage.setItem('admin-sidebar', next ? 'collapsed' : 'expanded')
    } catch {
      /* not persisted */
    }
  }

  // On the mobile drawer the sidebar is always expanded.
  const slim = collapsed && !drawer

  return (
    <div className="flex min-h-screen bg-canvas text-ink">
      {drawer && <div className="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden" onClick={() => setDrawer(false)} aria-hidden="true" />}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex shrink-0 flex-col border-r border-sidebar-line bg-cover bg-center transition-all lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 ${
          drawer ? 'translate-x-0' : '-translate-x-full'
        } ${slim ? 'w-[4.5rem]' : 'w-64'}`}
        style={{ backgroundImage: 'url(/images/sidebar-bg-img.jpg)' }}
      >
        <div className="flex h-full flex-col bg-sidebar-glass p-3 backdrop-blur-md">
          <div className={`flex h-12 items-center gap-3 px-2 ${slim ? 'justify-center' : ''}`}>
            <span className="flex size-9 items-center justify-center rounded-lg bg-primary-600 text-white shadow-md">
              <Icon name="layers" className="size-5" />
            </span>
            {!slim && <p className="text-lg font-bold text-sidebar-text">Admin</p>}
            <button type="button" className="ml-auto text-sidebar-text lg:hidden" onClick={() => setDrawer(false)} aria-label="Close menu">
              <Icon name="x" className="size-5" />
            </button>
          </div>

          <nav aria-label="Main navigation" className="mt-3 flex-1 overflow-y-auto">
            <ul className="space-y-1">{menus.data?.map((item) => <MenuNode key={item.id} item={item} collapsed={slim} />)}</ul>
          </nav>

          <button type="button" className={`${navLink} mt-2 hidden lg:flex ${collapsed ? 'justify-center' : ''}`} onClick={toggleCollapsed} aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}>
            <Icon name={collapsed ? 'chevrons-right' : 'chevrons-left'} className="size-[18px]" />
            {!collapsed && <span>Collapse</span>}
          </button>
        </div>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="sticky top-0 z-20 flex h-16 items-center gap-2 border-b border-line bg-surface/85 px-4 backdrop-blur-md">
          <button type="button" className="btn btn-ghost size-9 !p-0 lg:hidden" onClick={() => setDrawer(true)} aria-label="Open menu">
            <Icon name="menu" className="size-5" />
          </button>
          <div className="ml-auto flex items-center gap-1">
            <ThemeToggle />
            <UserMenu />
          </div>
        </header>
        <main className="flex-1 p-4 sm:p-6">
          <Outlet />
        </main>
      </div>

      <ToastHost />
    </div>
  )
}
