import { useEffect, type ReactNode } from 'react'
import { useLocation } from 'react-router'
import { Splash } from '../components/Splash'
import { Forbidden } from '../pages/errors'
import { startLogin } from './oauth'
import { useAuth } from './store'

/**
 * Renders children only for a signed-in user whose account is loaded. Without a session it starts the sign-in
 * (a full-page redirect to the API login page, which returns here); until then it shows a status line.
 */
export function RequireAuth({ children }: { children: ReactNode }) {
  const status = useAuth((s) => s.status)
  const user = useAuth((s) => s.user)
  const location = useLocation()

  useEffect(() => {
    if (status === 'anonymous') void startLogin(location.pathname + location.search)
  }, [status, location.pathname, location.search])

  if (status === 'authenticated' && user) return <>{children}</>

  return <Splash />
}

/** Renders children only when the user holds `permission` (the superadmin holds all); otherwise the 403 page. */
export function RequirePermission({ permission, children }: { permission: string; children: ReactNode }) {
  const allowed = useAuth((s) => s.user?.permissions.includes(permission) ?? false)

  return allowed ? <>{children}</> : <Forbidden />
}
